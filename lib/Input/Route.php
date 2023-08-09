<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Pages\Page;
use Exception;

/**
 * Summary of Route
 */
class Route
{
    //public static $endpoint = Config::ENDPOINT["index"];

    /**
     * Summary of routes
     * @var array<string, string|array>
     */
    protected static $routes = [
        "/index" => Page::INDEX,
        "/authors" => Page::ALL_AUTHORS,
        "/authors/letter" => ["page" => Page::ALL_AUTHORS, "letter" => 1],
        "/authors/letter/{id}" => Page::AUTHORS_FIRST_LETTER,
        "/authors/{id}" => Page::AUTHOR_DETAIL,
        "/books" => Page::ALL_BOOKS,
        "/books/letter" => ["page" => Page::ALL_BOOKS, "letter" => 1],
        "/books/letter/{id}" => Page::ALL_BOOKS_LETTER,
        "/books/year" => ["page" => Page::ALL_BOOKS, "year" => 1],
        "/books/year/{id}" => Page::ALL_BOOKS_YEAR,
        "/books/{id}" => Page::BOOK_DETAIL,
        "/series" => Page::ALL_SERIES,
        "/series/{id}" => Page::SERIE_DETAIL,
        "/search" => Page::OPENSEARCH,
        "/search/{query}" => Page::OPENSEARCH_QUERY,
        "/search/{query}/{scope}" => Page::OPENSEARCH_QUERY,
        "/recent" => Page::ALL_RECENT_BOOKS,
        "/tags" => Page::ALL_TAGS,
        "/tags/{id}" => Page::TAG_DETAIL,
        "/custom/{custom}" => Page::ALL_CUSTOMS,
        "/custom/{custom}/{id}" => Page::CUSTOM_DETAIL,
        "/about" => Page::ABOUT,
        "/languages" => Page::ALL_LANGUAGES,
        "/languages/{id}" => Page::LANGUAGE_DETAIL,
        "/customize" => Page::CUSTOMIZE,
        "/publishers" => Page::ALL_PUBLISHERS,
        "/publishers/{id}" => Page::PUBLISHER_DETAIL,
        "/ratings" => Page::ALL_RATINGS,
        "/ratings/{id}" => Page::RATING_DETAIL,
    ];
    protected static $exact = [];
    protected static $match = [];

    /**
     * Summary of match
     * @param string $path
     * @throws \Exception if the $path is not found in $routes
     * @return array|null
     */
    public static function match($path)
    {
        if (empty($path)) {
            return [];
        }

        // match exact path
        if (static::has($path)) {
            return static::get($path);
        }

        // match pattern
        $params = [];
        $found = [];
        foreach (static::listRoutes() as $route) {
            if (!str_contains($route, "{")) {
                continue;
            }
            $match = str_replace(["{", "}"], ["(?P<", ">\w+)"], $route);
            $pattern = "~$match~";
            if (preg_match($pattern, $path, $found)) {
                $params = static::get($route);
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
     * Check if static route exists
     * @param string $route
     * @return bool
     */
    public static function has($route)
    {
        return array_key_exists($route, static::$routes);
    }

    /**
     * Get query params for static route
     * @param string $route
     * @return array
     */
    public static function get($route)
    {
        $page = static::$routes[$route];
        if (is_array($page)) {
            return $page;
        }
        return ["page" => $page];
    }

    /**
     * Set route to page with optional static params
     * @param string $route
     * @param string $page
     * @param array $params
     * @return void
     */
    public static function set($route, $page, $params = [])
    {
        if (empty($params)) {
            static::$routes[$route] = $page;
            return;
        }
        $params["page"] = $page;
        static::$routes[$route] = $params;
    }

    /**
     * List routes in reverse order for match
     * @param bool $ordered
     * @return array
     */
    public static function listRoutes($ordered = true)
    {
        $routeList = array_keys(static::$routes);
        if ($ordered) {
            sort($routeList);
            // match longer routes first
            $routeList = array_reverse($routeList);
        }
        return $routeList;
    }

    /**
     * Get routes and query params
     * @return array<string, array>
     */
    public static function getRoutes()
    {
        $routeMap = [];
        foreach (array_keys(static::$routes) as $route) {
            $routeMap[$route] = static::get($route);
        }
        return $routeMap;
    }

    /**
     * Summary of link
     * @param string $page
     * @param array $params
     * @return string
     */
    public static function link($page, $params = [])
    {
        if (empty($page)) {
            return "/index";
        }
        $queryParams = array_merge(["page" => $page], $params);
        $queryString = http_build_query($queryParams);

        if (empty(static::$match)) {
            static::buildMatch();
        }

        // match exact query
        if (array_key_exists($queryString, static::$exact)) {
            return static::$exact[$queryString];
        }

        // match pattern
        $found = preg_replace(array_keys(static::$match), array_values(static::$match), '?' . $queryString);
        return $found;
    }

    protected static function buildMatch()
    {
        // Use cases:
        // 1. page=1
        // 2. page=1&letter=1
        // 3. page=2&id={id}
        // 4. page=15&custom={custom}&id={id}
        // 5. all of the above with extra params
        $matches = [];
        foreach (static::getRoutes() as $route => $queryParams) {
            $from = http_build_query($queryParams);
            $to = $route;
            $found = [];
            $ref = 1;
            if (preg_match_all("~\{(\w+)\}~", $route, $found)) {
                foreach ($found[1] as $param) {
                    $from .= '&' . $param . '=([^&"]+)';
                    $to = str_replace('{' . $param . '}', "\\$ref", $to);
                    $ref += 1;
                }
            } else {
                static::$exact[$from] = $to;
            }
            // replace & with ? if necessary
            $matches['~\?' . $from . '&~'] = $to . '?';
            $matches['~\?' . $from . '("|$)~'] = $to . "\\$ref";
        }
        // List matches in order for replaceLinks
        $matchList = array_keys($matches);
        sort($matchList);
        // match extra params first - & comes before ( so we don't need to reverse here
        //$matchList = array_reverse($matchList);
        static::$match = [];
        foreach ($matchList as $from) {
            static::$match[$from] = $matches[$from];
        }
    }

    public static function replaceLinks($output)
    {
        if (empty(static::$match)) {
            static::buildMatch();
        }
        return preg_replace(array_keys(static::$match), array_values(static::$match), $output);
    }
}
