<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Annotation;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Metadata;
use SebLucas\Cops\Calibre\Note;
use SebLucas\Cops\Calibre\Resource;
use SebLucas\Cops\Calibre\Preference;
use SebLucas\Cops\Calibre\User;
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Framework;
use Exception;

/**
 * Basic REST API routing to JSON Renderer
 * Note: this supports all other paths with /restapi prefix
 */
class RestApi extends BaseRenderer
{
    public const PREFIX = RestApiHandler::PREFIX;
    public static string $handler = RestApiHandler::class;
    public static int $numberPerPage = 100;
    public static bool $doRunHandler = true;
    protected static ?string $baseUrl = null;

    /**
     * Summary of extra
     * @var array<string, array<string>>
     */
    public static $extra = [
        "/custom" => [self::class, 'getCustomColumns'],
        "/databases" => [self::class, 'getDatabases'],
        "/openapi" => [self::class, 'getOpenApi'],
        "/routes" => [self::class, 'getRoutes'],
        "/handlers" => [self::class, 'getHandlers'],
        "/notes" => [self::class, 'getNotes'],
        "/preferences" => [self::class, 'getPreferences'],
        "/annotations" => [self::class, 'getAnnotations'],
        "/metadata" => [self::class, 'getMetadata'],
        "/user" => [self::class, 'getUser'],
    ];

    public bool $isExtra = false;

    /**
     * Summary of matchPathInfo
     * @param string $path
     * @throws Exception if the $path is not found in $routes or $extra
     * @return ?array<mixed>
     */
    public function matchPathInfo($path)
    {
        if ($path == '/') {
            return null;
        }

        // handle extra functions
        $root = '/' . explode('/', $path . '/')[1];
        if (array_key_exists($root, self::$extra)) {
            $params = Route::match($path);
            // @todo handle non-matching path here too!?
            $params ??= [];
            if (!empty($params['page']) && $params['page'] != PageId::REST_API) {
                return $params;
            }
            $this->isExtra = true;
            unset($params['page']);
            if (!empty($params)) {
                $this->request->setParams($params);
            }
            return call_user_func(self::$extra[$root], $this->request);
        }

        // match path with routes
        return Route::match($path);
    }

    /**
     * Summary of getJson
     * @return array<string, mixed>
     */
    public function getJson()
    {
        $json = new JsonRenderer();
        return $json->getJson($this->request);
    }

    /**
     * Summary of runHandler
     * @param string $path
     * @param array<string, mixed> $params
     * @param ?bool $run
     * @return array<string, mixed>|Response|null
     */
    public function runHandler($path, $params, $run = null)
    {
        // we are using class-string now
        if (empty($params[Route::HANDLER_PARAM]) || !in_array($params[Route::HANDLER_PARAM], Framework::getHandlers())) {
            return ["error" => "Invalid handler"];
        }
        $handler = $params[Route::HANDLER_PARAM]::HANDLER;
        if (!in_array($handler, ["check", "phpunit"]) && !$this->request->hasValidApiKey()) {
            return ["error" => "Invalid api key"];
        }
        $name = $params[Route::HANDLER_PARAM];
        // run via handler now
        $handler = Framework::createHandler($name);
        unset($params[Route::HANDLER_PARAM]);
        $run ??= self::$doRunHandler;
        if ($run) {
            $oldpath = $_SERVER['PATH_INFO'] ?? '';
            $oldparams = $_GET;
            $_SERVER['PATH_INFO'] = $path;
            $_GET = $params;
            // @todo create request without using globals
            $request = Framework::getRequest();
            $response = $handler->handle($request);
            $_SERVER['PATH_INFO'] = $oldpath;
            $_GET = $oldparams;
            return $response;
        }
        $result = [Route::HANDLER_PARAM => $name, "path" => $path, "params" => $params];
        return $result;
    }

    /**
     * Summary of getOutput
     * @param mixed $result
     * @return string|Response
     */
    public function getOutput($result = null)
    {
        if (isset($result)) {
            return json_encode($result, JSON_UNESCAPED_SLASHES);
        }
        self::$baseUrl ??= self::$handler::getBaseUrl();
        $path = $this->getPathInfo();
        $params = $this->matchPathInfo($path);
        if (!isset($params)) {
            // @todo find some better way to handle this
            Response::redirect(self::$handler::getHandlerLink(null, PageId::INDEX));
            return '';
        }
        if ($this->isExtra) {
            $result = $params;
        } elseif (empty($params[Route::HANDLER_PARAM]) || $params[Route::HANDLER_PARAM]::HANDLER == 'json') {
            $this->request->setParams($params);
            $result = $this->getJson();
        } else {
            // extra paths supported by other handlers
            $result = $this->runHandler($path, $params);
            if (is_null($result)) {
                return '';
            }
            if ($result instanceof Response) {
                return $result;
            }
        }
        $output = json_encode($result, JSON_UNESCAPED_SLASHES);

        return $output;
    }

    /**
     * Summary of getCustomColumns
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getCustomColumns($request)
    {
        $db = $request->database();
        $columns = CustomColumnType::getAllCustomColumns();
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Custom Columns",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        foreach ($columns as $title => $column) {
            $params = [];
            $params["custom"] = $column['id'];
            $params["db"] = $db;
            // @todo find some better way to generate restapi links for pages and handlers?
            $column["navlink"] = self::$handler::getHandlerLink(null, PageId::ALL_CUSTOMS, $params);
            array_push($result["entries"], $column);
        }
        return $result;
    }

    /**
     * Summary of getDatabases
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getDatabases($request)
    {
        $db = $request->database();
        if (!is_null($db) && Database::checkDatabaseAvailability($db)) {
            return self::getDatabase($db, $request);
        }
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Databases",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        $params = [];
        $id = 0;
        foreach (Database::getDbNameList() as $key) {
            $params['db'] = $id;
            $link = self::$handler::resource(Database::class, $params);
            array_push($result["entries"], [
                "class" => "Database",
                "title" => $key,
                "id" => $id,
                "navlink" => $link,
            ]);
            $id += 1;
        }
        return $result;
    }

    /**
     * Summary of getDatabase
     * @param int $database
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getDatabase($database, $request)
    {
        if (!Database::isMultipleDatabaseEnabled() && $database != 0) {
            return [
                "title" => "Database Invalid",
                "entries" => [],
            ];
        }
        $name = $request->get('name', null, '/^\w+$/');
        if (!empty($name)) {
            return self::getTable($database, $name, $request);
        }
        $title = "Database";
        $dbName = Database::getDbName($database);
        if (!empty($dbName)) {
            $title .= " $dbName";
        }
        $baseurl = self::$baseUrl;
        $params = [];
        $type = $request->get('type', null, '/^\w+$/');
        if (in_array($type, ['table', 'view'])) {
            $title .= " Type $type";
            $result = [
                "title" => $title,
                "baseurl" => $baseurl,
                "entries" => [],
            ];
            $params['db'] = $database;
            $entries = Database::getDbSchema($database, $type);
            foreach ($entries as $entry) {
                $params['name'] = $entry['tbl_name'];
                $entry["navlink"] = self::$handler::resource(Database::class, $params);
                unset($entry["sql"]);
                array_push($result["entries"], $entry);
            }
            $result["version"] = Database::getUserVersion($database);
            return $result;
        }
        $title .= " Types";
        $result = [
            "title" => $title,
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        $params['db'] = $database;
        $metadata = [
            "table" => "Tables",
            "view" => "Views",
        ];
        foreach ($metadata as $name => $title) {
            $params['type'] = $name;
            array_push($result["entries"], [
                "class" => "Metadata",
                "title" => $title,
                "navlink" => self::$handler::resource(Database::class, $params),
            ]);
        }
        $result["version"] = Database::getUserVersion($database);
        return $result;
    }

    /**
     * Summary of getTable
     * @param int $database
     * @param string $name
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getTable($database, $name, $request)
    {
        $title = "Database";
        $dbName = Database::getDbName($database);
        if (!empty($dbName)) {
            $title .= " $dbName";
        }
        $title .= " Table $name";
        $baseurl = self::$baseUrl;
        $result = [
            "title" => $title,
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        if (!$request->hasValidApiKey()) {
            $result["error"] = "Invalid api key";
            return $result;
        }
        $params = [];
        $params['db'] = $database;
        $params['name'] = $name;
        // add dummy functions for selecting in meta and tag_browser_* views
        Database::addSqliteFunctions($database);
        $query = "SELECT COUNT(*) FROM {$name}";
        $count = Database::querySingle($query, $database);
        $result["total"] = $count;
        $result["limit"] = self::$numberPerPage;
        $start = 0;
        $n = (int) $request->get('n', 1, '/^\d+$/');
        if ($n > 0 && $n < ceil($count / self::$numberPerPage)) {
            $start = ($n - 1) * self::$numberPerPage;
        }
        $result["offset"] = $start;
        $query = "SELECT * FROM {$name} LIMIT ?, ?";
        $res = Database::query($query, [$start, self::$numberPerPage], $database);
        while ($post = $res->fetchObject()) {
            $entry = (array) $post;
            $params['id'] = $entry['id'];
            $entry["navlink"] = self::$handler::resource(Database::class, $params);
            array_push($result["entries"], $entry);
        }
        $result["columns"] = Database::getTableInfo($database, $name);
        return $result;
    }

    /**
     * Summary of getOpenApi
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getOpenApi($request)
    {
        $openapi = new OpenApi();
        return $openapi->getDefinition();
    }

    /**
     * Summary of getRoutes
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getRoutes($request)
    {
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Routes",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        foreach (Route::getRoutes() as $name => $route) {
            array_push($result["entries"], [
                "name" => $name,
                "route" => $route,
            ]);
        }
        return $result;
    }

    /**
     * Summary of getHandlers
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getHandlers($request)
    {
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Handlers",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        foreach (Framework::getHandlers() as $name => $class) {
            $routes = $class::getRoutes();
            array_push($result["entries"], [
                "handler" => $name,
                "routes" => $routes,
            ]);
        }
        return $result;
    }

    /**
     * Summary of getNotes
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getNotes($request)
    {
        $type = $request->get('type', null, '/^\w+$/');
        if (!empty($type)) {
            return self::getNotesByType($type, $request);
        }
        $db = $request->database();
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Notes",
            "baseurl" => $baseurl,
            "databaseId" => $db,
            "entries" => [],
        ];
        $params = [];
        $params['db'] = $db;
        foreach (Note::getCountByType($db) as $type => $count) {
            $params['type'] = $type;
            $link = self::$handler::resource(Note::class, $params);
            array_push($result["entries"], [
                "class" => "Notes Type",
                "title" => $type,
                "navlink" => $link,
                "number" => $count,
            ]);
        }
        return $result;
    }

    /**
     * Summary of getNotesByType
     * @param string $type
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getNotesByType($type, $request)
    {
        $id = $request->getId('id');
        if (!empty($id)) {
            return self::getNoteByTypeId($type, $id, $request);
        }
        $db = $request->database();
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Notes for {$type}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
            "entries" => [],
        ];
        $params = [];
        $params['db'] = $db;
        $params['type'] = $type;
        // @todo get item from notes + corresponding title from instance
        foreach (Note::getEntriesByType($type, $db) as $entry) {
            $params['id'] = $entry['item'];
            if (!empty($entry["title"])) {
                $title = Route::slugify($entry["title"]);
                $params['title'] = $title;
                $link = self::$handler::resource(Note::class, $params);
                array_push($result["entries"], [
                    "class" => "Notes",
                    "title" => $entry["title"],
                    "id" => $entry["item"],
                    "navlink" => $link,
                    "size" => $entry["size"],
                    "timestamp" => $entry["mtime"],
                ]);
            } else {
                unset($params['title']);
                $link = self::$handler::resource(Note::class, $params);
                array_push($result["entries"], [
                    "class" => "Notes",
                    "title" => $type,
                    "id" => $entry["item"],
                    "navlink" => $link,
                    "size" => $entry["size"],
                    "timestamp" => $entry["mtime"],
                ]);
            }
        }
        return $result;
    }

    /**
     * Summary of getNoteByTypeId
     * @param string $type
     * @param int $id
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getNoteByTypeId($type, $id, $request)
    {
        $db = $request->database();
        $note = Note::getInstanceByTypeId($type, $id, $db);
        if (empty($note)) {
            return ["error" => "Invalid note type id"];
        }
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Note for {$type} #{$id}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $result = array_replace($result, (array) $note);
        $result["size"] = strlen($result["doc"]);
        $result["resources"] = [];
        foreach ($note->getResources() as $hash => $resource) {
            $path = Resource::getResourcePath($hash, $db);
            $size = !empty($path) ? filesize($path) : 0;
            $mtime = !empty($path) ? filemtime($path) : 0;
            $link = $resource->getUri();
            $result["resources"][$hash] = [
                "hash" => $resource->hash,
                "name" => $resource->name,
                "url" => $link,
                "size" => $size,
                "mtime" => $mtime,
            ];
        }
        return $result;
    }

    /**
     * Summary of getPreferences
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getPreferences($request)
    {
        $key = $request->get('key', null, '/^[\w\s:]+$/');
        if (!empty($key)) {
            return self::getPreferenceByKey($key, $request);
        }
        $db = $request->database();
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Preferences",
            "baseurl" => $baseurl,
            "databaseId" => $db,
            "entries" => [],
        ];
        $params = [];
        $params['db'] = $db;
        foreach (Preference::getInstances($db) as $key => $preference) {
            if (is_array($preference->val)) {
                $count = count($preference->val);
            } elseif (is_string($preference->val)) {
                $count = strlen($preference->val);
            } elseif (!is_null($preference->val)) {
                $count = 1;
            } else {
                $count = 0;
            }
            $params['key'] = rawurlencode($key);
            $link = self::$handler::resource(Preference::class, $params);
            array_push($result["entries"], [
                "class" => "Preference",
                "title" => $key,
                "navlink" => $link,
                "number" => $count,
            ]);
        }
        return $result;
    }

    /**
     * Summary of getPreferenceByKey
     * @param string $key
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getPreferenceByKey($key, $request)
    {
        $db = $request->database();
        $preference = Preference::getInstanceByKey($key, $db);
        if (empty($preference)) {
            return ["error" => "Invalid preference key"];
        }
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Preference for {$key}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $result = array_replace($result, (array) $preference);
        return $result;
    }

    /**
     * Summary of getAnnotations
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getAnnotations($request)
    {
        $bookId = $request->getId('bookId');
        if (!empty($bookId)) {
            return self::getAnnotationsByBookId($bookId, $request);
        }
        $db = $request->database();
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Annotations",
            "baseurl" => $baseurl,
            "databaseId" => $db,
            "entries" => [],
        ];
        foreach (Annotation::getCountByBookId($db) as $bookId => $count) {
            $params = [];
            $params['bookId'] = $bookId;
            $params['db'] = $db;
            $link = self::$handler::resource(Annotation::class, $params);
            array_push($result["entries"], [
                "class" => "Annotations",
                "title" => "Annotations for {$bookId}",
                "navlink" => $link,
                "number" => $count,
            ]);
        }
        return $result;
    }

    /**
     * Summary of getAnnotationsByBookId
     * @param int $bookId
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getAnnotationsByBookId($bookId, $request)
    {
        $id = $request->getId('id');
        if (!empty($id)) {
            return self::getAnnotationById($bookId, $id, $request);
        }
        $db = $request->database();
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "Annotations for {$bookId}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
            "entries" => [],
        ];
        // @todo get item from annotations + corresponding title from instance
        foreach (Annotation::getInstancesByBookId($bookId, $db) as $instance) {
            $instance->setHandler(self::$handler);
            $entry = $instance->getEntry();
            array_push($result["entries"], [
                "class" => $entry->className,
                "title" => $entry->title,
                "navlink" => $entry->getNavLink(),
            ]);
        }
        return $result;
    }

    /**
     * Summary of getAnnotationById
     * @param int $bookId
     * @param int $id
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getAnnotationById($bookId, $id, $request)
    {
        $db = $request->database();
        /** @var Annotation $annotation */
        $annotation = Annotation::getInstanceById($id, $db);
        if (empty($annotation->id)) {
            return ["error" => "Invalid annotation id"];
        }
        $baseurl = self::$baseUrl;
        $result = [
            "title" => $annotation->getTitle(),
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $result = array_replace($result, get_object_vars($annotation));
        return $result;
    }

    /**
     * Summary of getMetadata
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getMetadata($request)
    {
        $bookId = $request->getId('bookId');
        if (empty($bookId)) {
            return ["error" => "Invalid book id"];
        }
        $db = $request->database();
        $baseurl = self::$baseUrl;
        $metadata = Metadata::getInstanceByBookId($bookId, $db);
        if (empty($metadata)) {
            $result["error"] = "Invalid metadata for book id";
            return $result;
        }
        $result = [
            "title" => "Metadata for {$bookId}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $element = $request->get('element');
        if (empty($element)) {
            $result["entries"] = $metadata;
            return $result;
        }
        $result["element"] = $element;
        $name = $request->get('name');
        if (empty($name)) {
            $result["entries"] = $metadata->getElement($element);
            return $result;
        }
        $result["name"] = $name;
        $result["entries"] = $metadata->getElementName($element, $name);
        return $result;
    }

    /**
     * Summary of getUser
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getUser($request)
    {
        $username = $request->getUserName();
        if (empty($username)) {
            return ["error" => "Invalid username"];
        }
        $db = $request->database();
        $baseurl = self::$baseUrl;
        $result = [
            "title" => "User",
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $result["username"] = $username;
        if ($request->path() == self::PREFIX . "/user/details") {
            $user = User::getInstanceByName($username);
            $result = array_replace($result, (array) $user);
        }
        return $result;
    }
}
