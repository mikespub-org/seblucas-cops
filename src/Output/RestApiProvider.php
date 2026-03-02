<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
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
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Input\HasContextInterface;
use SebLucas\Cops\Input\HasContextTrait;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Routing\UriGenerator;
use Exception;

/**
 * Basic REST API routing to JSON Renderer
 * Note: this supports all other paths with /restapi prefix
 */
class RestApiProvider extends BaseRenderer implements HasContextInterface
{
    use HasContextTrait;

    public const DEFINITION_FILE = 'resources/openapi.json';
    public const RESTAPI_CACHE_FILE = 'resources/cache.restapi.php';
    public const PREFIX = RestApiHandler::PREFIX;
    public int $numberPerPage = 100;
    public bool $doRunHandler = true;
    protected ?string $baseUrl = null;

    /**
     * Summary of extra - use instance methods instead of static
     * @var array<string, string>
     */
    public array $extra = [
        "/custom" => 'getCustomColumns',
        "/databases" => 'getDatabases',
        "/openapi" => 'getOpenApi',
        "/routes" => 'getRoutes',
        "/handlers" => 'getHandlers',
        "/notes" => 'getNotes',
        "/preferences" => 'getPreferences',
        "/annotations" => 'getAnnotations',
        "/metadata" => 'getMetadata',
        "/user" => 'getUser',
    ];

    public bool $isExtra = false;

    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->setHandler(RestApiHandler::class);
    }

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
        if (array_key_exists($root, $this->extra)) {
            $params = $this->getContext()->getRouter()->match($path);
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
            // Call instance method instead of static
            //return call_user_func($this->extra[$root], $this->request);
            $method = $this->extra[$root];
            return $this->$method($this->request);
        }

        // match path with routes
        return $this->getContext()->getRouter()->match($path);
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
        $handlers = $this->getContext()->getHandlerManager()->getHandlers();
        if (empty($params[Request::HANDLER_PARAM]) || !in_array($params[Request::HANDLER_PARAM], $handlers)) {
            return ["error" => "Invalid handler"];
        }
        $handler = $params[Request::HANDLER_PARAM]::HANDLER;
        if (!in_array($handler, ["check", "phpunit"]) && !$this->request->hasValidApiKey()) {
            return ["error" => "Invalid api key"];
        }
        $name = $params[Request::HANDLER_PARAM];
        // run via handler now
        $handler = Framework::createHandler($name);
        unset($params[Request::HANDLER_PARAM]);
        $run ??= $this->doRunHandler;
        if ($run) {
            // create request without using globals
            $request = Framework::getRequest($path, $params);
            $response = $handler->handle($request);
            return $response;
        }
        $result = [Request::HANDLER_PARAM => $name, "path" => $path, "params" => $params];
        return $result;
    }

    /**
     * Get base URL from handler
     */
    protected function getBaseUrl(): string
    {
        $this->baseUrl ??= RestApiHandler::getBaseUrl();
        return $this->baseUrl;
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
        $path = $this->getPathInfo();
        $params = $this->matchPathInfo($path);
        if (!isset($params)) {
            return Response::redirect($this->getRoute(PageId::ROUTE_INDEX));
        }
        if ($this->isExtra) {
            $result = $params;
        } elseif (empty($params[Request::HANDLER_PARAM]) || $params[Request::HANDLER_PARAM]::HANDLER == 'json') {
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
    public function getCustomColumns($request)
    {
        $db = $request->database();
        $columns = CustomColumnType::getAllCustomColumns();
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "Custom Columns",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        foreach ($columns as $title => $column) {
            $params = [];
            $params["custom"] = $column['id'];
            $params["db"] = $db;
            $column["navlink"] = $this->getRoute(CustomColumnType::ROUTE_ALL, $params);
            array_push($result["entries"], $column);
        }
        return $result;
    }

    /**
     * Summary of getDatabases
     * @param Request $request
     * @return array<string, mixed>
     */
    public function getDatabases($request)
    {
        $db = $request->database();
        if (!is_null($db) && Database::checkDatabaseAvailability($db)) {
            return $this->getDatabase($db, $request);
        }
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "Databases",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        $params = [];
        $id = 0;
        foreach (Database::getDbNameList() as $key) {
            $params['db'] = $id;
            $link = $this->getResource(Database::class, $params);
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
    public function getDatabase($database, $request)
    {
        if (!Database::isMultipleDatabaseEnabled() && $database != 0) {
            return [
                "title" => "Database Invalid",
                "entries" => [],
            ];
        }
        $name = $request->get('name', null, '/^\w+$/');
        if (!empty($name)) {
            return $this->getTable($database, $name, $request);
        }
        $title = "Database";
        $dbName = Database::getDbName($database);
        if (!empty($dbName)) {
            $title .= " $dbName";
        }
        $baseurl = $this->getBaseUrl();
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
                $entry["navlink"] = $this->getResource(Database::class, $params);
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
                "navlink" => $this->getResource(Database::class, $params),
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
    public function getTable($database, $name, $request)
    {
        $title = "Database";
        $dbName = Database::getDbName($database);
        if (!empty($dbName)) {
            $title .= " $dbName";
        }
        $title .= " Table $name";
        $baseurl = $this->getBaseUrl();
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
        $result["limit"] = $this->numberPerPage;
        $start = 0;
        $n = (int) $request->get('n', 1, '/^\d+$/');
        if ($n > 0 && $n < ceil($count / $this->numberPerPage)) {
            $start = ($n - 1) * $this->numberPerPage;
        }
        $result["offset"] = $start;
        $query = "SELECT * FROM {$name} LIMIT ?, ?";
        $res = Database::query($query, [$start, $this->numberPerPage], $database);
        while ($post = $res->fetchObject()) {
            $entry = (array) $post;
            $params['id'] = $entry['id'];
            $entry["navlink"] = $this->getResource(Database::class, $params);
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
    public function getOpenApi($request)
    {
        $openapi = new OpenApi();
        $routes = $this->getContext()->getRoutes();
        return $openapi->getDefinition($routes);
    }

    /**
     * Summary of getRoutes
     * @param Request $request
     * @return array<string, mixed>
     */
    public function getRoutes($request)
    {
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "Routes",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        $routes = $this->getContext()->getRoutes();
        foreach ($routes as $name => $route) {
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
    public function getHandlers($request)
    {
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "Handlers",
            "baseurl" => $baseurl,
            "entries" => [],
        ];
        $handlers = $this->getContext()->getHandlerManager()->getHandlers();
        foreach ($handlers as $name => $class) {
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
    public function getNotes($request)
    {
        $type = $request->get('type', null, '/^\w+$/');
        if (!empty($type)) {
            return $this->getNotesByType($type, $request);
        }
        $db = $request->database();
        $baseurl = $this->getBaseUrl();
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
            $link = $this->getResource(Note::class, $params);
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
    public function getNotesByType($type, $request)
    {
        $item = $request->getId('item');
        if (!empty($item)) {
            return $this->getNoteByTypeItem($type, $item, $request);
        }
        $db = $request->database();
        $baseurl = $this->getBaseUrl();
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
            $params['item'] = $entry['item'];
            if (!empty($entry["title"])) {
                $title = UriGenerator::slugify($entry["title"]);
                $params['title'] = $title;
                $link = $this->getResource(Note::class, $params);
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
                $link = $this->getResource(Note::class, $params);
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
     * @param int $item
     * @param Request $request
     * @return array<string, mixed>
     */
    public function getNoteByTypeItem($type, $item, $request)
    {
        $db = $request->database();
        $note = Note::getInstanceByTypeItem($type, $item, $db);
        if (empty($note)) {
            return ["error" => "Invalid note type item"];
        }
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "Note for {$type} #{$item}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $result = array_replace($result, get_object_vars($note));
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
    public function getPreferences($request)
    {
        $key = $request->get('key', null, '/^[\w\s:]+$/');
        if (!empty($key)) {
            return $this->getPreferenceByKey($key, $request);
        }
        $db = $request->database();
        $baseurl = $this->getBaseUrl();
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
            $link = $this->getResource(Preference::class, $params);
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
    public function getPreferenceByKey($key, $request)
    {
        $db = $request->database();
        $preference = Preference::getInstanceByKey($key, $db);
        if (empty($preference)) {
            return ["error" => "Invalid preference key"];
        }
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "Preference for {$key}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $result = array_replace($result, get_object_vars($preference));
        return $result;
    }

    /**
     * Summary of getAnnotations
     * @param Request $request
     * @return array<string, mixed>
     */
    public function getAnnotations($request)
    {
        $bookId = $request->getId('bookId');
        if (!empty($bookId)) {
            return $this->getAnnotationsByBookId($bookId, $request);
        }
        $db = $request->database();
        $baseurl = $this->getBaseUrl();
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
            $link = $this->getResource(Annotation::class, $params);
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
    public function getAnnotationsByBookId($bookId, $request)
    {
        $id = $request->getId('id');
        if (!empty($id)) {
            return $this->getAnnotationById($bookId, $id, $request);
        }
        $db = $request->database();
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "Annotations for {$bookId}",
            "baseurl" => $baseurl,
            "databaseId" => $db,
            "entries" => [],
        ];
        // @todo get item from annotations + corresponding title from instance
        foreach (Annotation::getInstancesByBookId($bookId, $db) as $instance) {
            $instance->setHandler($this->handler);
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
    public function getAnnotationById($bookId, $id, $request)
    {
        $db = $request->database();
        /** @var Annotation $annotation */
        $annotation = Annotation::getInstanceById($id, $db);
        if (empty($annotation->id)) {
            return ["error" => "Invalid annotation id"];
        }
        $baseurl = $this->getBaseUrl();
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
    public function getMetadata($request)
    {
        $bookId = $request->getId('bookId');
        if (empty($bookId)) {
            return ["error" => "Invalid book id"];
        }
        $db = $request->database();
        $baseurl = $this->getBaseUrl();
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
    public function getUser($request)
    {
        $username = $request->getUserName();
        if (empty($username)) {
            return ["error" => "Invalid username"];
        }
        $db = $request->database();
        $baseurl = $this->getBaseUrl();
        $result = [
            "title" => "User",
            "baseurl" => $baseurl,
            "databaseId" => $db,
        ];
        $result["username"] = $username;
        if ($request->path() == RestApiHandler::PREFIX . "/user/details") {
            $user = User::getInstanceByName($username);
            $result = array_replace($result, (array) $user);
        }
        return $result;
    }
}
