<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Pages\PageId;
use Exception;

/**
 * Summary of Route
 */
class Route
{
    //public static $endpoint = Config::ENDPOINT["index"];

    /**
     * Summary of routes
     * @var array<string, mixed>
     */
    protected static $routes = [
        // Format: route => page, or route => [page => page, fixed => 1, ...] with fixed params
        "/index" => PageId::INDEX,
        "/authors" => PageId::ALL_AUTHORS,
        "/authors/letter" => ["page" => PageId::ALL_AUTHORS, "letter" => 1],
        "/authors/letter/{id}" => PageId::AUTHORS_FIRST_LETTER,
        "/authors/{id}" => PageId::AUTHOR_DETAIL,
        "/books" => PageId::ALL_BOOKS,
        "/books/letter" => ["page" => PageId::ALL_BOOKS, "letter" => 1],
        "/books/letter/{id}" => PageId::ALL_BOOKS_LETTER,
        "/books/year" => ["page" => PageId::ALL_BOOKS, "year" => 1],
        "/books/year/{id}" => PageId::ALL_BOOKS_YEAR,
        "/books/{id}" => PageId::BOOK_DETAIL,
        "/series" => PageId::ALL_SERIES,
        "/series/{id}" => PageId::SERIE_DETAIL,
        "/search" => PageId::OPENSEARCH,
        "/search/{query}" => PageId::OPENSEARCH_QUERY,
        "/search/{query}/{scope}" => PageId::OPENSEARCH_QUERY,
        "/recent" => PageId::ALL_RECENT_BOOKS,
        "/tags" => PageId::ALL_TAGS,
        "/tags/{id}" => PageId::TAG_DETAIL,
        "/custom/{custom}" => PageId::ALL_CUSTOMS,
        "/custom/{custom}/{id}" => PageId::CUSTOM_DETAIL,
        "/about" => PageId::ABOUT,
        "/languages" => PageId::ALL_LANGUAGES,
        "/languages/{id}" => PageId::LANGUAGE_DETAIL,
        "/customize" => PageId::CUSTOMIZE,
        "/publishers" => PageId::ALL_PUBLISHERS,
        "/publishers/{id}" => PageId::PUBLISHER_DETAIL,
        "/ratings" => PageId::ALL_RATINGS,
        "/ratings/{id}" => PageId::RATING_DETAIL,
        "/identifiers" => PageId::ALL_IDENTIFIERS,
        "/identifiers/{id}" => PageId::IDENTIFIER_DETAIL,
    ];
    // with use_url_rewriting = 1 - basic rewrites only
    /** @var array<string, mixed> */
    protected static $rewrites = [
        // Format: route => endpoint, or route => [endpoint, [fixed => 1, ...]] with fixed params
        "/download/{data}/{db}/{ignore}.{type}" => [Config::ENDPOINT["fetch"]],
        "/view/{data}/{db}/{ignore}.{type}" => [Config::ENDPOINT["fetch"], ["view" => 1]],
        "/download/{data}/{ignore}.{type}" => [Config::ENDPOINT["fetch"]],
        "/view/{data}/{ignore}.{type}" => [Config::ENDPOINT["fetch"], ["view" => 1]],
    ];
    /** @var array<string, mixed> */
    protected static $exact = [];
    /** @var array<string, mixed> */
    protected static $match = [];
    /** @var array<string, mixed> */
    protected static $endpoints = [];

    /**
     * Match pathinfo against routes and return query params
     * @param string $path
     * @throws \Exception if the $path is not found in $routes
     * @return array<mixed>|null
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
        $fixed = [];
        $found = [];
        foreach (static::listRoutes() as $route) {
            if (strpos($route, "{") === false) {
                continue;
            }
            $match = str_replace(["{", "}"], ["(?P<", ">\w+)"], $route);
            $pattern = "~^$match$~";
            if (preg_match($pattern, $path, $found)) {
                $fixed = static::get($route);
                break;
            }
        }
        if (empty($found)) {
            throw new Exception("Invalid route " . htmlspecialchars($path));
        }
        $params = [];
        // set named params
        foreach ($found as $param => $value) {
            if (is_numeric($param)) {
                continue;
            }
            $params[$param] = $value;
        }
        // for normal routes, put fixed params at the start
        $params = array_merge($fixed, $params);
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
     * @return array<mixed>
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
     * @param array<mixed> $params
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
     * @return array<string>
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
     * @return array<string, array<mixed>>
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
     * Find route for page with params and return link
     * @param string $page
     * @param array<mixed> $params
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

    /**
     * Summary of buildMatch
     * @return void
     */
    protected static function buildMatch()
    {
        // Use cases:
        // 1. page=1
        // 2. page=1&letter=1
        // 3. page=2&id={id}
        // 4. page=15&custom={custom}&id={id}
        // 5. all of the above with extra params
        [static::$match, static::$exact] = static::findMatches(static::getRoutes(), '\?');
    }

    /**
     * Summary of findMatches
     * @param array<mixed> $mapping
     * @param string $prefix
     * @return array<mixed>
     */
    protected static function findMatches($mapping, $prefix = '\?')
    {
        $matches = [];
        $exact = [];
        foreach ($mapping as $route => $fixed) {
            $from = '';
            $separator = '';
            // for normal routes, put fixed params at the start
            if ($prefix == '\?') {
                $from = http_build_query($fixed);
                $separator = '&';
            }
            $to = $route;
            $found = [];
            $ref = 1;
            if (preg_match_all("~\{(\w+)\}~", $route, $found)) {
                foreach ($found[1] as $param) {
                    if ($param == 'ignore') {
                        $to = str_replace('{' . $param . '}', "$param", $to);
                        continue;
                    }
                    $from .= $separator . $param . '=([^&"]+)';
                    $to = str_replace('{' . $param . '}', "\\$ref", $to);
                    $separator = '&';
                    $ref += 1;
                }
            } else {
                $exact[$from] = $to;
            }
            // for rewrite rules, put fixed params at the end
            if ($prefix !== '\?' && !empty($fixed)) {
                $from .= $separator . http_build_query($fixed);
            }
            // replace & with ? if necessary
            $matches['~' . $prefix . $from . '&~'] = $to . '?';
            $matches['~' . $prefix . $from . '("|$)~'] = $to . "\\$ref";
        }
        // List matches in order for replaceLinks
        $matchList = array_keys($matches);
        sort($matchList);
        // match extra params first - & comes before ( so we don't need to reverse here
        //$matchList = array_reverse($matchList);
        $matchMap = [];
        foreach ($matchList as $from) {
            $matchMap[$from] = $matches[$from];
        }
        return [$matchMap, $exact];
    }

    /**
     * Match rewrite rule for path and return endpoint with params
     * @param string $path
     * @return array<mixed>
     */
    public static function matchRewrite($path)
    {
        // match pattern
        $endpoint = '';
        $fixed = [];
        $found = [];
        foreach (static::listRewrites() as $route) {
            if (strpos($route, "{") === false) {
                continue;
            }
            // replace dots + ignore parts of the route
            $match = str_replace(['.', '{ignore}'], ['\.', '[^/&"?]*'], $route);
            $match = str_replace(["{", "}"], ["(?P<", ">\w+)"], $match);
            $pattern = "~^$match$~";
            if (preg_match($pattern, $path, $found)) {
                [$endpoint, $fixed] = static::getRewrite($route);
                break;
            }
        }
        if (empty($endpoint)) {
            throw new Exception("Invalid path " . htmlspecialchars($path));
        }
        $params = [];
        // set named params
        foreach ($found as $param => $value) {
            if (is_numeric($param)) {
                continue;
            }
            $params[$param] = $value;
        }
        // for rewrite rules, put fixed params at the end
        if (!empty($fixed)) {
            $params = array_merge($params, $fixed);
        }
        return [$endpoint, $params];
    }

    /**
     * Get endpoint and fixed params for rewrite rule
     * @param string $route
     * @return array<mixed>
     */
    public static function getRewrite($route)
    {
        $map = static::$rewrites[$route];
        if (!is_array($map)) {
            $map = [ $map ];
        }
        $endpoint = array_shift($map);
        $fixed = array_shift($map) ?? [];
        return [$endpoint, $fixed];
    }

    /**
     * List rewrite rules in reverse order for match
     * @param bool $ordered
     * @return array<string>
     */
    public static function listRewrites($ordered = true)
    {
        $rewriteList = array_keys(static::$rewrites);
        if ($ordered) {
            sort($rewriteList);
            // match longer routes first
            $rewriteList = array_reverse($rewriteList);
        }
        return $rewriteList;
    }

    /**
     * Find rewrite rule for endpoint with params and return link
     * @param string $endpoint
     * @param array<mixed> $params
     * @throws \Exception if the $endpoint is not found in $rewrites
     * @return string
     */
    public static function linkRewrite($endpoint, $params = [])
    {
        if (empty(static::$endpoints)) {
            static::buildEndpoints();
        }
        if (!array_key_exists($endpoint, static::$endpoints)) {
            throw new Exception("Invalid endpoint " . htmlspecialchars($endpoint));
        }
        $url = $endpoint . '?' . http_build_query($params);

        // Use cases:
        // 1. fetch.php?data={data}&type={type}
        // 2. fetch.php?data={data}&type={type}&view=1
        // 3. fetch.php?data={data}&db={db}&type={type}
        // 4. fetch.php?data={data}&db={db}&type={type}&view=1
        // 5. all of the above with extra params
        [$matches, $exact] = static::findMatches(static::$endpoints[$endpoint], preg_quote($endpoint . '?'));

        // match exact query
        if (array_key_exists($url, $exact)) {
            return $exact[$url];
        }

        // match pattern
        $found = preg_replace(array_keys($matches), array_values($matches), $url);
        return $found;
    }

    /**
     * Summary of buildEndpoints
     * @return void
     */
    public static function buildEndpoints()
    {
        foreach (static::$rewrites as $route => $map) {
            [$endpoint, $fixed] = static::getRewrite($route);
            if (!array_key_exists($endpoint, static::$endpoints)) {
                static::$endpoints[$endpoint] = [];
            }
            static::$endpoints[$endpoint][$route] = $fixed;
        }
    }

    /**
     * Summary of replaceLinks
     * @param string $output
     * @return string|null
     */
    public static function replaceLinks($output)
    {
        if (empty(static::$match)) {
            static::buildMatch();
        }
        // Note: this does not replace rewrite rules, as they are already generated in code when use_url_rewriting == 1
        return preg_replace(array_keys(static::$match), array_values(static::$match), $output);
    }
}
