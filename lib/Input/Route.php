<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use SebLucas\Cops\Pages\PageId;
use Exception;

use function FastRoute\simpleDispatcher;

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
        "/authors/letter/{id}" => PageId::AUTHORS_FIRST_LETTER,
        "/authors/letter" => ["page" => PageId::ALL_AUTHORS, "letter" => 1],
        "/authors/{id}/{title}" => PageId::AUTHOR_DETAIL,
        "/authors/{id}" => PageId::AUTHOR_DETAIL,
        "/authors" => PageId::ALL_AUTHORS,
        "/books/letter/{id}" => PageId::ALL_BOOKS_LETTER,
        "/books/letter" => ["page" => PageId::ALL_BOOKS, "letter" => 1],
        "/books/year/{id}" => PageId::ALL_BOOKS_YEAR,
        "/books/year" => ["page" => PageId::ALL_BOOKS, "year" => 1],
        "/books/{id}/{author}/{title}" => PageId::BOOK_DETAIL,
        "/books/{id}" => PageId::BOOK_DETAIL,
        "/books" => PageId::ALL_BOOKS,
        "/series/{id}/{title}" => PageId::SERIE_DETAIL,
        "/series/{id}" => PageId::SERIE_DETAIL,
        "/series" => PageId::ALL_SERIES,
        "/search/{query}/{scope}" => PageId::OPENSEARCH_QUERY,
        "/search/{query}" => PageId::OPENSEARCH_QUERY,
        "/search" => PageId::OPENSEARCH,
        "/recent" => PageId::ALL_RECENT_BOOKS,
        "/tags/{id}/{title}" => PageId::TAG_DETAIL,
        "/tags/{id}" => PageId::TAG_DETAIL,
        "/tags" => PageId::ALL_TAGS,
        "/custom/{custom}/{id}" => PageId::CUSTOM_DETAIL,
        "/custom/{custom}" => PageId::ALL_CUSTOMS,
        "/about" => PageId::ABOUT,
        "/languages/{id}/{title}" => PageId::LANGUAGE_DETAIL,
        "/languages/{id}" => PageId::LANGUAGE_DETAIL,
        "/languages" => PageId::ALL_LANGUAGES,
        "/customize" => PageId::CUSTOMIZE,
        "/publishers/{id}/{title}" => PageId::PUBLISHER_DETAIL,
        "/publishers/{id}" => PageId::PUBLISHER_DETAIL,
        "/publishers" => PageId::ALL_PUBLISHERS,
        "/ratings/{id}/{title}" => PageId::RATING_DETAIL,
        "/ratings/{id}" => PageId::RATING_DETAIL,
        "/ratings" => PageId::ALL_RATINGS,
        "/identifiers/{id}/{title}" => PageId::IDENTIFIER_DETAIL,
        "/identifiers/{id}" => PageId::IDENTIFIER_DETAIL,
        "/identifiers" => PageId::ALL_IDENTIFIERS,
    ];
    /** @var Dispatcher|null */
    protected static $dispatcher = null;
    /** @var array<string, mixed> */
    protected static $pages = [];
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
     * @return ?array<mixed>
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
        $params = [];
        $method = 'GET';

        $dispatcher = static::getSimpleDispatcher();
        $routeInfo = $dispatcher->dispatch($method, $path);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                //http_response_code(404);
                throw new Exception("Invalid route " . htmlspecialchars($path));
            case Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                //header('Allow: ' . implode(', ', $allowedMethods));
                //http_response_code(405);
                throw new Exception("Invalid method " . htmlspecialchars($method) . " for route " . htmlspecialchars($path));
            case Dispatcher::FOUND:
                $fixed = $routeInfo[1];
                $params = $routeInfo[2];
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
     * Summary of getSimpleDispatcher
     * @return Dispatcher
     */
    public static function getSimpleDispatcher()
    {
        static::$dispatcher ??= simpleDispatcher(function (RouteCollector $r) {
            static::addRouteCollection($r);
        });
        return static::$dispatcher;
    }

    /**
     * Summary of addRouteCollection
     * @param RouteCollector $r
     * @return void
     */
    public static function addRouteCollection($r)
    {
        foreach (static::getRoutes() as $route => $queryParams) {
            $r->addRoute('GET', $route, $queryParams);
        }
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
     * Get url with endpoint for page with params
     * @param string $endpoint
     * @param string|int|null $page
     * @param array<mixed> $params
     * @param string|null $separator
     * @return string
     */
    public static function url($endpoint, $page = null, $params = [], $separator = null)
    {
        return $endpoint . static::uri($page, $params, $separator);
    }

    /**
     * Get uri for page with params
     * @param string|int|null $page
     * @param array<mixed> $params
     * @param string|null $separator
     * @return string
     */
    public static function uri($page, $params = [], $separator = null)
    {
        $queryParams = array_filter($params, function ($val) {
            if (empty($val) && strval($val) !== '0') {
                return false;
            }
            return true;
        });
        if (!empty($page)) {
            $queryParams = array_merge(['page' => $page], $queryParams);
        }
        $prefix = '';
        if (count($queryParams) < 1) {
            return $prefix;
        }
        return static::rewrite($queryParams, $prefix, $separator);
    }

    /**
     * Get uri for query with params
     * @param string|null $query
     * @param array<mixed> $params
     * @param string|null $separator
     * @return string
     */
    public static function query($query, $params = [], $separator = null)
    {
        $prefix = '';
        $pos = strpos($query, '?');
        if ($pos !== false) {
            $prefix = substr($query, 0, $pos);
            $query = substr($query, $pos + 1);
        }
        $queryParams = [];
        if (!empty($query)) {
            parse_str($query, $queryParams);
            $params = array_merge($queryParams, $params);
        }
        $queryParams = array_filter($params, function ($val) {
            if (empty($val) && strval($val) !== '0') {
                return false;
            }
            return true;
        });
        if (count($queryParams) < 1) {
            return $prefix;
        }
        return static::rewrite($queryParams, $prefix, $separator);
    }

    /**
     * Summary of rewrite
     * @param array<mixed> $params
     * @param string $prefix
     * @param string|null $separator
     * @return string
     */
    public static function rewrite($params, $prefix = '', $separator = null)
    {
        if (Config::get('use_route_urls')) {
            $route = static::getPageRoute($params, $prefix, $separator);
            if (!is_null($route)) {
                return $route;
            }
        }
        $queryString = http_build_query($params, '', $separator);
        return $prefix . '?' . $queryString;
    }

    /**
     * Summary of getPageRoute
     * @param array<mixed> $params
     * @param string $prefix
     * @param string|null $separator
     * @return string|null
     */
    public static function getPageRoute($params, $prefix = '', $separator = null)
    {
        $page = $params['page'] ?? '';
        $pages = static::getPages();
        $routes = $pages[$page] ?? [];
        if (count($routes) < 1) {
            return null;
        }
        unset($params['page']);
        // find matching route based on fixed and/or path params - e.g. authors letter
        foreach ($routes as $route => $fixed) {
            if (count($fixed) > count($params)) {
                continue;
            }
            $subst = $params;
            // check and remove fixed params
            foreach ($fixed as $key => $val) {
                if (!isset($subst[$key]) || $subst[$key] != $val) {
                    continue 2;
                }
                unset($subst[$key]);
            }
            $found = [];
            // check and replace path params
            if (preg_match_all("~\{(\w+)\}~", $route, $found)) {
                if (count($found[1]) > count($subst)) {
                    continue;
                }
                foreach ($found[1] as $param) {
                    if ($param == 'ignore') {
                        $route = str_replace('{' . $param . '}', "$param", $route);
                        continue;
                    }
                    if (!isset($subst[$param])) {
                        continue 2;
                    }
                    $value = $subst[$param];
                    if (in_array($param, ['title', 'author'])) {
                        $value = str_replace(' ', '_', $value);
                    }
                    $route = str_replace('{' . $param . '}', "$value", $route);
                    unset($subst[$param]);
                }
            }
            echo "$route\n";
            if (count($subst) > 0) {
                return $prefix . $route . '?' . http_build_query($subst, '', $separator);
            }
            return $prefix . $route;
        }
        return null;
    }

    /**
     * Get mapping of pages to routes with query params
     * @return array<string, array<mixed>>
     */
    public static function getPages()
    {
        if (!empty(static::$pages)) {
            return static::$pages;
        }
        static::$pages = [];
        foreach (static::$routes as $route => $params) {
            if (!is_array($params)) {
                $page = $params;
                $params = [];
            } else {
                $page = $params["page"];
                unset($params["page"]);
            }
            static::$pages[$page] ??= [];
            static::$pages[$page][$route] = $params;
        }
        return static::$pages;
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
}
