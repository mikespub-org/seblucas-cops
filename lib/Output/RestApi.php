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
use SebLucas\Cops\Pages\Page;
use Exception;

use function SebLucas\Cops\Request\setURLParam;

use const SebLucas\Cops\Config\COPS_ENDPOINTS;

/**
 * Basic REST API routing to JSON Renderer
 */
class RestApi
{
    /** @var array $config */

    public static $endpoint = COPS_ENDPOINTS["restapi"];

    /**
     * Summary of routes
     * @var array
     */
    public static $routes = [
        Page::INDEX => "/index",
        Page::ALL_AUTHORS => "/authors",
        Page::AUTHORS_FIRST_LETTER => "/authors_l/{id}",
        Page::AUTHOR_DETAIL => "/authors/{id}",
        Page::ALL_BOOKS => "/books",
        Page::ALL_BOOKS_LETTER => "/books_l/{id}",
        Page::BOOK_DETAIL => "/books/{id}",
        Page::ALL_SERIES => "/series",
        Page::SERIE_DETAIL => "/series/{id}",
        //Page::OPENSEARCH => "/search",
        Page::OPENSEARCH_QUERY => "/search/{query}",  // @todo scope
        Page::ALL_RECENT_BOOKS => "/recent",
        Page::ALL_TAGS => "/tags",
        Page::TAG_DETAIL => "/tags/{id}",
        Page::ALL_CUSTOMS => "/custom/{custom}",
        Page::CUSTOM_DETAIL => "/custom/{custom}/{id}",
        Page::ABOUT => "/about",
        Page::ALL_LANGUAGES => "/languages",
        Page::LANGUAGE_DETAIL => "/languages/{id}",
        Page::CUSTOMIZE => "/customize",
        Page::ALL_PUBLISHERS => "/publishers",
        Page::PUBLISHER_DETAIL => "/publishers/{id}",
        Page::ALL_RATINGS => "/ratings",
        Page::RATING_DETAIL => "/ratings/{id}",
    ];

    /**
     * Summary of extra
     * @var array
     */
    public static $extra = [
        "/custom" => [self::class, 'getCustomColumns'],
        "/databases" => [self::class, 'getDatabases'],
        "/openapi" => [self::class, 'getOpenApi'],
        "/routes" => [self::class, 'getRoutes'],
    ];

    /**
     * Summary of getPathInfo
     * @return string
     */
    public static function getPathInfo()
    {
        return $_SERVER["PATH_INFO"] ?? "/index";
    }

    /**
     * Summary of matchPathInfo
     * @param string $path
     * @throws Exception if the $path is not found in $routes or $extra
     * @return array|void
     */
    public static function matchPathInfo($path)
    {
        $params = [];

        // handle extra functions
        if (array_key_exists($path, self::$extra)) {
            echo json_encode(call_user_func(self::$extra[$path]), JSON_UNESCAPED_SLASHES);
            exit;
        }

        $matches = array_flip(self::$routes);

        // match exact path
        if (array_key_exists($path, $matches)) {
            $page = $matches[$path];
            $params["page"] = $page;
            return $params;
        }

        // match pattern
        $found = [];
        foreach ($matches as $route => $page) {
            if (!str_contains($route, "{")) {
                continue;
            }
            $route = str_replace("{", "(?P<", $route);
            $route = str_replace("}", ">\w+)", $route);
            $pattern = "~$route~";
            if (preg_match($pattern, $path, $found)) {
                $params["page"] = $page;
                break;
            }
        }
        if (empty($found)) {
            throw new Exception("Invalid route " . htmlspecialchars($path));
        }
        // set named params
        foreach ($found as $param => $value) {
            if (is_numeric($param)) {
                continue;
            }
            $params[$param] = $value;
        }
        return $params;
    }

    /**
     * Summary of setParams
     * @param mixed $params
     * @return void
     */
    public static function setParams($params)
    {
        foreach ($params as $param => $value) {
            setURLParam($param, $value);
        }
    }

    /**
     * Summary of getJson
     * @return mixed
     */
    public static function getJson()
    {
        return JSONRenderer::getJson();
    }

    /**
     * Summary of getScriptName
     * @return string
     */
    public static function getScriptName()
    {
        $script = explode("/", $_SERVER["SCRIPT_NAME"] ?? "/" . self::$endpoint);
        $link = array_pop($script);
        return $link;
    }

    /**
     * Summary of replaceLinks
     * @param string $output
     * @return string
     */
    public static function replaceLinks($output)
    {
        $link = self::getScriptName();
        $endpoint = $link;

        $search = [];
        $replace = [];
        foreach (self::$routes as $page => $route) {
            if (!str_contains($route, "{")) {
                $search[] = $link . "?page=" . $page . '"';
                $replace[] = $endpoint . $route . '"';
                continue;
            }
            $found = [];
            if (preg_match_all("~\{(\w+)\}~", $route, $found)) {
                //$search[] = $link . "?page=" . $page . "&id=";
                //$replace[] = $endpoint . $route . "/";
                // @todo: restapi.php?page=15&custom=2&id=2
                if (count($found[1]) > 1) {
                    continue;
                }
                $from = $link . "?page=" . $page;
                $to = $endpoint . $route;
                foreach ($found[1] as $param) {
                    $from .= "&" . $param . "=";
                    $to = str_replace("{" . $param . "}", "", $to);
                }
                $search[] = $from;
                $replace[] = $to;
            }
        }

        $output = str_replace($search, $replace, $output);
        return $output;
    }

    /**
     * Summary of getOutput
     * @param mixed $result
     * @return string
     */
    public static function getOutput($result = null)
    {
        if (!isset($result)) {
            $path = self::getPathInfo();
            $params = self::matchPathInfo($path);
            self::setParams($params);
            $result = self::getJson();
        }
        $output = json_encode($result);

        return self::replaceLinks($output);
    }

    /**
     * Summary of getCustomColumns
     * @return array
     */
    public static function getCustomColumns()
    {
        $columns = CustomColumnType::getAllCustomColumns();
        $endpoint = self::getScriptName();
        $result = ["title" => "Custom Columns", "entries" => []];
        foreach ($columns as $title => $column) {
            $column["navlink"] = $endpoint . "/custom/" . $column["id"];
            array_push($result["entries"], $column);
        }
        return $result;
    }

    /**
     * Summary of getDatabases
     * @return array
     */
    public static function getDatabases()
    {
        global $config;

        $result = ["title" => "Databases", "entries" => []];
        if (is_array($config['calibre_directory'])) {
            $result["entries"] = $config['calibre_directory'];
        } else {
            array_push($result["entries"], $config['calibre_directory']);
        }
        return $result;
    }

    /**
     * Summary of getOpenApi
     * @return array
     */
    public static function getOpenApi()
    {
        $result = ["openapi" => "3.1.0", "info" => ["title" => "COPS REST API", "version" => "1.0.0"], "paths" => []];
        return $result;
    }

    /**
     * Summary of getRoutes
     * @return array
     */
    public static function getRoutes()
    {
        $result = ["title" => "Routes", "entries" => []];
        foreach (self::$routes as $page => $route) {
            array_push($result["entries"], ["page" => $page, "route" => $route]);
        }
        return $result;
    }
}
