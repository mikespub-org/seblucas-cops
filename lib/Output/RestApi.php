<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Note;
use SebLucas\Cops\Calibre\Resource;
use SebLucas\Cops\Calibre\Preference;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\PageId;
use Exception;

/**
 * Basic REST API routing to JSON Renderer
 */
class RestApi
{
    public static string $endpoint = Config::ENDPOINT["restapi"];
    public static int $numberPerPage = 100;

    /**
     * Summary of extra
     * @var array<string, array<string>>
     */
    public static $extra = [
        "/custom" => [self::class, 'getCustomColumns'],
        "/databases" => [self::class, 'getDatabases'],
        "/openapi" => [self::class, 'getOpenApi'],
        "/routes" => [self::class, 'getRoutes'],
        "/notes" => [self::class, 'getNotes'],
        "/preferences" => [self::class, 'getPreferences'],
    ];

    /**
     * Summary of request
     * @var Request
     */
    protected Request $request;
    public bool $isExtra = false;

    /**
     * Summary of __construct
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Summary of getPathInfo
     * @return string
     */
    public function getPathInfo()
    {
        return $this->request->path("/index");
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
        if (array_key_exists($root, static::$extra)) {
            $params = Route::match($path);
            if (!empty($params['page']) && $params['page'] != PageId::REST_API) {
                return $params;
            }
            $this->isExtra = true;
            unset($params['page']);
            if (!empty($params)) {
                $this->setParams($params);
            }
            return call_user_func(static::$extra[$root], $this->request);
        }

        // match path with routes
        return Route::match($path);
    }

    /**
     * Summary of setParams
     * @param array<mixed> $params
     * @return Request
     */
    public function setParams($params)
    {
        foreach ($params as $param => $value) {
            $this->request->set($param, $value);
        }
        return $this->request;
    }

    /**
     * Summary of getJson
     * @return array<string, mixed>
     */
    public function getJson()
    {
        return JSONRenderer::getJson($this->request);
    }

    /**
     * Summary of getScriptName
     * @param Request $request
     * @return string
     */
    public static function getScriptName($request)
    {
        return $request->getEndpoint(static::$endpoint);
    }

    /**
     * Summary of getOutput
     * @param mixed $result
     * @return string
     */
    public function getOutput($result = null)
    {
        if (!isset($result)) {
            $path = $this->getPathInfo();
            $params = $this->matchPathInfo($path);
            if (!isset($params)) {
                header('Location: ' . Route::url(static::$endpoint) . '/index');
                exit;
            }
            if ($this->isExtra) {
                $result = $params;
            } else {
                $request = $this->setParams($params);
                $result = $this->getJson();
            }
        }
        $output = json_encode($result, JSON_UNESCAPED_SLASHES);
        //$endpoint = static::getScriptName($this->request);

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
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Custom Columns", "baseurl" => $baseurl, "entries" => []];
        foreach ($columns as $title => $column) {
            $column["navlink"] = Route::url($endpoint . "/custom/" . $column["id"], null, ["db" => $db]);
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
            return static::getDatabase($db, $request);
        }
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Databases", "baseurl" => $baseurl, "entries" => []];
        $id = 0;
        foreach (Database::getDbNameList() as $key) {
            array_push($result["entries"], ["class" => "Database", "title" => $key, "id" => $id, "navlink" => "{$baseurl}/databases/{$id}"]);
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
            return ["title" => "Database Invalid", "entries" => []];
        }
        $name = $request->get('name', null, '/^\w+$/');
        if (!empty($name)) {
            return static::getTable($database, $name, $request);
        }
        $title = "Database";
        $dbName = Database::getDbName($database);
        if (!empty($dbName)) {
            $title .= " $dbName";
        }
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $type = $request->get('type', null, '/^\w+$/');
        if (in_array($type, ['table', 'view'])) {
            $title .= " Type $type";
            $result = ["title" => $title, "baseurl" => $baseurl, "entries" => []];
            $entries = Database::getDbSchema($database, $type);
            foreach ($entries as $entry) {
                $entry["navlink"] = "{$baseurl}/databases/{$database}/{$entry['tbl_name']}";
                unset($entry["sql"]);
                array_push($result["entries"], $entry);
            }
            $result["version"] = Database::getUserVersion($database);
            return $result;
        }
        $result = ["title" => $title, "baseurl" => $baseurl, "entries" => []];
        $metadata = [
            "table" => "Tables",
            "view" => "Views",
        ];
        foreach ($metadata as $name => $title) {
            array_push($result["entries"], ["class" => "Metadata", "title" => $title, "navlink" => "{$baseurl}/databases/{$database}?type={$name}"]);
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
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => $title, "baseurl" => $baseurl, "entries" => []];
        if (!$request->hasValidApiKey()) {
            $result["error"] = "Invalid api key";
            return $result;
        }
        $query = "SELECT COUNT(*) FROM {$name}";
        $count = Database::querySingle($query, $database);
        $result["total"] = $count;
        $result["limit"] = static::$numberPerPage;
        $start = 0;
        $n = (int) $request->get('n', 1, '/^\d+$/');
        if ($n > 0 && $n < ceil($count / static::$numberPerPage)) {
            $start = ($n - 1) * static::$numberPerPage;
        }
        $result["offset"] = $start;
        $query = "SELECT * FROM {$name} LIMIT ?, ?";
        $res = Database::query($query, [$start, static::$numberPerPage], $database);
        while ($post = $res->fetchObject()) {
            $entry = (array) $post;
            $entry["navlink"] = "{$baseurl}/databases/{$database}/{$name}?id={$entry['id']}";
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
        $result = ["openapi" => "3.0.3", "info" => ["title" => "COPS REST API", "version" => Config::VERSION]];
        $result["servers"] = [["url" => Route::url(static::$endpoint), "description" => "COPS REST API Endpoint"]];
        $result["components"] = ["securitySchemes" => ["ApiKeyAuth" => ["type" => "apiKey", "in" => "header", "name" => "X-API-KEY"]]];
        $result["components"]["parameters"] = ["dbParam" => ["name" => "db", "in" => "query", "required" => false, "schema" => ["type" => "integer", "minimum" => 0]]];
        $result["paths"] = [];
        foreach (Route::getRoutes() as $route => $queryParams) {
            $params = [];
            $found = [];
            $queryString = http_build_query($queryParams);
            if (preg_match_all("~\{(\w+)\}~", $route, $found)) {
                foreach ($found[1] as $param) {
                    $queryString .= "&{$param}=" . '{' . $param . '}';
                    array_push($params, ["name" => $param, "in" => "path", "required" => true, "schema" => ["type" => "string"]]);
                }
            }
            $result["paths"][$route] = ["get" => ["summary" => "Route to " . $queryString, "responses" => ["200" => ["description" => "Result of " . $queryString]]]];
            if ($route == "/databases/{db}") {
                array_push($params, ["name" => "type", "in" => "query", "schema" => ["type" => "string", "enum" => ["table", "view"]]]);
            }
            if (!str_starts_with($route, "/databases") && !in_array($route, ["/openapi", "/routes", "/about"])) {
                array_push($params, ['$ref' => "#/components/parameters/dbParam"]);
            }
            if (!empty($params)) {
                $result["paths"][$route]["get"]["parameters"] = $params;
            }
            if ($route == "/databases/{db}/{name}") {
                $result["paths"][$route]["get"]["security"] = [["ApiKeyAuth" => []]];
            }
        }
        return $result;
    }

    /**
     * Summary of getRoutes
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getRoutes($request)
    {
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Routes", "baseurl" => $baseurl, "entries" => []];
        foreach (Route::getRoutes() as $route => $queryParams) {
            array_push($result["entries"], ["route" => $route, "params" => $queryParams]);
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
            return static::getNotesByType($type, $request);
        }
        $db = $request->database();
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Notes", "baseurl" => $baseurl, "databaseId" => $db, "entries" => []];
        foreach (Note::getCountByType($db) as $type => $count) {
            array_push($result["entries"], ["class" => "Notes Type", "title" => $type, "navlink" => Route::url("{$endpoint}/notes/{$type}", null, ["db" => $db]), "number" => $count]);
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
            return static::getNoteByTypeId($type, $id, $request);
        }
        $db = $request->database();
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Notes for {$type}", "baseurl" => $baseurl, "databaseId" => $db, "entries" => []];
        // @todo get item from notes + corresponding title from instance
        foreach (Note::getEntriesByType($type, $db) as $entry) {
            if (!empty($entry["title"])) {
                $title = Route::slugify($entry["title"]);
                array_push($result["entries"], ["class" => "Notes", "title" => $entry["title"], "id" => $entry["item"], "navlink" => Route::url("{$endpoint}/notes/{$type}/{$entry['item']}/{$title}", null, ["db" => $db]), "size" => $entry["size"], "timestamp" => $entry["mtime"]]);
            } else {
                array_push($result["entries"], ["class" => "Notes", "title" => $type, "id" => $entry["item"], "navlink" => Route::url("{$endpoint}/notes/{$type}/{$entry['item']}", null, ["db" => $db]), "size" => $entry["size"], "timestamp" => $entry["mtime"]]);
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
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Note for {$type} #{$id}", "baseurl" => $baseurl, "databaseId" => $db];
        $result = array_replace($result, (array) $note);
        $result["size"] = strlen($result["doc"]);
        $result["resources"] = [];
        $baseurl = Route::url(Resource::$endpoint);
        foreach ($note->getResources() as $hash => $resource) {
            $path = Resource::getResourcePath($hash, $db);
            $size = !empty($path) ? filesize($path) : 0;
            $mtime = !empty($path) ? filemtime($path) : 0;
            $link = $baseurl . $resource->getUri();
            $result["resources"][$hash] = ["hash" => $resource->hash, "name" => $resource->name, "url" => $link, "size" => $size, "mtime" => $mtime];
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
            return static::getPreferenceByKey($key, $request);
        }
        $db = $request->database();
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Preferences", "baseurl" => $baseurl, "databaseId" => $db, "entries" => []];
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
            array_push($result["entries"], ["class" => "Preference", "title" => $key, "navlink" => Route::url("{$endpoint}/preferences/" . rawurlencode($key), null, ["db" => $db]), "number" => $count]);
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
        $endpoint = static::getScriptName($request);
        $baseurl = Route::url($endpoint);
        $result = ["title" => "Preference for {$key}", "baseurl" => $baseurl, "databaseId" => $db];
        $result = array_replace($result, (array) $preference);
        return $result;
    }
}
