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
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use Exception;

/**
 * Basic REST API routing to JSON Renderer
 */
class RestApi
{
    public static string $endpoint = Config::ENDPOINT["restapi"];

    /**
     * Summary of extra
     * @var array<string, array<string>>
     */
    public static $extra = [
        "/custom" => [self::class, 'getCustomColumns'],
        "/databases" => [self::class, 'getDatabases'],
        "/openapi" => [self::class, 'getOpenApi'],
        "/routes" => [self::class, 'getRoutes'],
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
        return $this->request->path() ?? "/index";
    }

    /**
     * Summary of matchPathInfo
     * @param string $path
     * @throws Exception if the $path is not found in $routes or $extra
     * @return array<mixed>|null
     */
    public function matchPathInfo($path)
    {
        if ($path == '/') {
            return null;
        }

        // handle extra functions
        if (array_key_exists($path, self::$extra)) {
            $this->isExtra = true;
            return call_user_func(self::$extra[$path], $this->request);
        }

        // match path with routes
        return Route::match($path);
    }

    /**
     * Summary of setParams
     * @param mixed $params
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
        return $request->getEndpoint(self::$endpoint);
    }

    /**
     * Summary of replaceLinks
     * @param string $output
     * @param string $endpoint
     * @return string
     */
    public static function replaceLinks($output, $endpoint)
    {
        //$link = $endpoint;
        $output = Route::replaceLinks($output);
        return $output;
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
                header('Location: ' . $this->request->script() . '/index');
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
        $endpoint = self::getScriptName($this->request);

        return self::replaceLinks($output, $endpoint);
    }

    /**
     * Summary of getCustomColumns
     * @param Request $request
     * @return array<string, mixed>
     */
    public static function getCustomColumns($request)
    {
        $columns = CustomColumnType::getAllCustomColumns();
        $endpoint = self::getScriptName($request);
        $result = ["title" => "Custom Columns", "entries" => []];
        foreach ($columns as $title => $column) {
            $column["navlink"] = $endpoint . "/custom/" . $column["id"];
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
        $result = ["title" => "Databases", "entries" => []];
        if (is_array(Config::get('calibre_directory'))) {
            $result["entries"] = Config::get('calibre_directory');
        } else {
            array_push($result["entries"], Config::get('calibre_directory'));
        }
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
        $result["servers"] = [["url" => $request->script(), "description" => "COPS REST API Endpoint"]];
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
            if (!empty($params)) {
                $result["paths"][$route]["get"]["parameters"] = $params;
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
        $result = ["title" => "Routes", "entries" => []];
        foreach (Route::getRoutes() as $route => $queryParams) {
            array_push($result["entries"], ["route" => $route, "params" => $queryParams]);
        }
        return $result;
    }
}
