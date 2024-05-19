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
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Pages\PageId;

use function FastRoute\simpleDispatcher;

/**
 * Summary of Route
 */
class Route
{
    public const HANDLER_PARAM = "_handler";
    public const SYMFONY_REQUEST = '\Symfony\Component\HttpFoundation\Request';

    /** @var ?\Symfony\Component\HttpFoundation\Request */
    protected static $proxyRequest = null;
    /** @var ?string */
    protected static $baseUrl = null;
    /** @var array<string, mixed> */
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
        "/libraries" => PageId::ALL_LIBRARIES,
        // extra routes supported by REST API
        "/custom" => [self::HANDLER_PARAM => "restapi"],
        "/databases/{db}/{name}" => [self::HANDLER_PARAM => "restapi"],
        "/databases/{db}" => [self::HANDLER_PARAM => "restapi"],
        "/databases" => [self::HANDLER_PARAM => "restapi"],
        "/openapi" => [self::HANDLER_PARAM => "restapi"],
        "/routes" => [self::HANDLER_PARAM => "restapi"],
        "/notes/{type}/{id}/{title}" => [self::HANDLER_PARAM => "restapi"],
        "/notes/{type}/{id}" => [self::HANDLER_PARAM => "restapi"],
        "/notes/{type}" => [self::HANDLER_PARAM => "restapi"],
        "/notes" => [self::HANDLER_PARAM => "restapi"],
        "/preferences/{key}" => [self::HANDLER_PARAM => "restapi"],
        "/preferences" => [self::HANDLER_PARAM => "restapi"],
        "/annotations/{bookId}/{id}" => [self::HANDLER_PARAM => "restapi"],
        "/annotations/{bookId}" => [self::HANDLER_PARAM => "restapi"],
        "/annotations" => [self::HANDLER_PARAM => "restapi"],
        "/metadata/{bookId}/{element}/{name}" => [self::HANDLER_PARAM => "restapi"],
        "/metadata/{bookId}/{element}" => [self::HANDLER_PARAM => "restapi"],
        "/metadata/{bookId}" => [self::HANDLER_PARAM => "restapi"],
        "/user/details" => [self::HANDLER_PARAM => "restapi"],
        "/user" => [self::HANDLER_PARAM => "restapi"],
        "/restapi/{route:.*}" => [self::HANDLER_PARAM => "restapi"],
        // extra routes supported by other endpoints (path starts with endpoint param)
        "/calres/{db:\d+}/{alg}/{digest}" => [self::HANDLER_PARAM => "calres"],
        // support custom pattern for route placeholders - see nikic/fast-route
        "/zipfs/{db:\d+}/{idData:\d+}/{component:.+}" => [self::HANDLER_PARAM => "zipfs"],
        "/epubfs/{data:\d+}/{comp:.+}" => [self::HANDLER_PARAM => "epubfs"],
        "/loader/{action}/{dbNum:\d+}/{authorId:\d+}" => [self::HANDLER_PARAM => "loader"],
        "/loader/{action}/{dbNum:\d+}" => [self::HANDLER_PARAM => "loader"],
        "/loader/{action}/" => [self::HANDLER_PARAM => "loader"],
        "/loader/{action}" => [self::HANDLER_PARAM => "loader"],
        "/loader" => [self::HANDLER_PARAM => "loader"],
        "/check" => [self::HANDLER_PARAM => "check"],
        "/feed/{page}/{id}" => [self::HANDLER_PARAM => "feed"],
        "/feed/{page}" => [self::HANDLER_PARAM => "feed"],
        "/feed" => [self::HANDLER_PARAM => "feed"],
        "/mail" => [self::HANDLER_PARAM => "mail"],
        "/opds/{page}/{id}" => [self::HANDLER_PARAM => "opds"],
        "/opds/{page}" => [self::HANDLER_PARAM => "opds"],
        "/opds" => [self::HANDLER_PARAM => "opds"],
        "/read/{db:\d+}/{data:\d+}" => [self::HANDLER_PARAM => "read"],
        // check if the path starts with the endpoint param or not here
        "/thumbs/{thumb}/{db:\d+}/{id:\d+}.jpg" => [self::HANDLER_PARAM => "fetch"],
        "/covers/{db:\d+}/{id:\d+}.jpg" => [self::HANDLER_PARAM => "fetch"],
        "/inline/{db:\d+}/{data:\d+}/{ignore}.{type}" => [self::HANDLER_PARAM => "fetch", "view" => 1],
        "/fetch/{db:\d+}/{data:\d+}/{ignore}.{type}" => [self::HANDLER_PARAM => "fetch"],
        // handle endpoint with page param
        "/zipper/{page}/{type}/{id}" => [self::HANDLER_PARAM => "zipper"],
        "/zipper/{page}/{type}" => [self::HANDLER_PARAM => "zipper"],
        "/zipper/{page}" => [self::HANDLER_PARAM => "zipper"],
        // @todo handle url rewriting if enabled separately - path parameters are different
        "/view/{data}/{db}/{ignore}.{type}" => [self::HANDLER_PARAM => "fetch", "view" => 1],
        "/view/{data}/{ignore}.{type}" => [self::HANDLER_PARAM => "fetch", "view" => 1],
        "/download/{data}/{db}/{ignore}.{type}" => [self::HANDLER_PARAM => "fetch"],
        "/download/{data}/{ignore}.{type}" => [self::HANDLER_PARAM => "fetch"],
    ];
    /** @var string[] */
    protected static $skipPrefix = ['index', 'json', 'fetch', 'restapi'];  // @todo handle 'json' routes correctly - see util.js
    /** @var Dispatcher|null */
    protected static $dispatcher = null;
    /** @var array<string, mixed> */
    protected static $pages = [];
    // with use_url_rewriting = 1 - basic rewrites only
    /** @var array<string, mixed> */
    protected static $rewrites = [
        // Format: route => endpoint, or route => [endpoint, [fixed => 1, ...]] with fixed params
        "/view/{data}/{db}/{ignore}.{type}" => [Config::ENDPOINT["fetch"], ["view" => 1]],
        "/view/{data}/{ignore}.{type}" => [Config::ENDPOINT["fetch"], ["view" => 1]],
        "/download/{data}/{db}/{ignore}.{type}" => [Config::ENDPOINT["fetch"]],
        "/download/{data}/{ignore}.{type}" => [Config::ENDPOINT["fetch"]],
    ];
    /** @var array<string, mixed> */
    protected static $endpoints = [];

    /**
     * Match pathinfo against routes and return query params
     * @param string $path
     * @return ?array<mixed> array of query params or null if not found
     */
    public static function match($path)
    {
        if (empty($path) || $path == '/') {
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
                //throw new Exception("Invalid route " . htmlspecialchars($path));
                return null;
            case Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                //header('Allow: ' . implode(', ', $allowedMethods));
                //http_response_code(405);
                //throw new Exception("Invalid method " . htmlspecialchars($method) . " for route " . htmlspecialchars($path));
                return null;
            case Dispatcher::FOUND:
                $fixed = $routeInfo[1];
                $params = $routeInfo[2];
        }
        // for normal routes, put fixed params at the start
        $params = array_merge($fixed, $params);
        unset($params['ignore']);
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
     * Add prefix for paths with this endpoint
     * @param string $name
     * @return bool
     */
    public static function addPrefix($name)
    {
        return !in_array($name, static::$skipPrefix);
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
     * Add routes and query params
     * @param array<string, array<mixed>> $routes
     * @return void
     */
    public static function addRoutes($routes)
    {
        static::$routes = array_merge(static::$routes, $routes);
    }

    /**
     * Count routes
     * @return int
     */
    public static function count()
    {
        return count(static::$routes);
    }

    /**
     * Get full url with endpoint for page with params
     * @param string|null $endpoint after going through Config::ENDPOINT
     * @param string|int|null $page
     * @param array<mixed> $params
     * @param string|null $separator
     * @return string
     */
    public static function url($endpoint = null, $page = null, $params = [], $separator = null)
    {
        $endpoint ??= Config::ENDPOINT['index'];
        if (!empty($endpoint) && substr($endpoint, 0, 1) === '/') {
            return $endpoint . static::page($page, $params, $separator);
        }
        // @todo take into account endpoint when building page url, e.g. feed.php or zipper.php
        return static::base() . $endpoint . static::page($page, $params, $separator);
    }

    /**
     * Get full link for handler with page and params
     * @param string|null $handler before going through Config::ENDPOINT
     * @param string|int|null $page
     * @param array<mixed> $params
     * @param string|null $separator
     * @return string
     */
    public static function link($handler = null, $page = null, $params = [], $separator = null)
    {
        $handler ??= 'index';
        // @todo take into account handler when building page url, e.g. feed or zipper
        if (Config::get('use_route_urls') && !in_array($handler, ['index', 'json', 'restapi'])) {
            $params[Route::HANDLER_PARAM] = $handler;
        } else {
            unset($params[Route::HANDLER_PARAM]);
        }
        // ?page=... or /route/...
        $page = static::page($page, $params, $separator);
        // @todo handle 'json' routes correctly - see util.js
        if ($handler == 'json') {
            $handler = 'index';
        }
        // endpoint.php or handler or empty
        $endpoint = static::endpoint($handler);
        if (empty($endpoint) && str_starts_with($page, '/')) {
            // URL format: /base/route/...
            return static::base() . substr($page, 1);
        }
        // URL format: /base/endpoint.php?page=... or /base/handler/route/...
        return static::base() . $endpoint . $page;
    }

    /**
     * Get endpoint for handler
     * @param string $handler
     * @return string
     */
    public static function endpoint($handler)
    {
        if (Config::get('use_front_controller')) {
            // @todo special case for restapi
            if (in_array($handler, ['restapi'])) {
                return $handler;
            }
            // no endpoint prefix needed for other handlers
            return '';
        }
        if (array_key_exists($handler, Config::ENDPOINT)) {
            $endpoint = Config::ENDPOINT[$handler];
        } elseif ($handler == 'phpunit') {
            $endpoint = $handler;
        } elseif (empty($handler)) {
            $endpoint = 'empty!';
        } else {
            throw new \Exception('Unknown handler ' . htmlspecialchars($handler));
        }
        return $endpoint;
    }

    /**
     * Get uri for page with params
     * @param string|int|null $page
     * @param array<mixed> $params
     * @param string|null $separator
     * @return string
     */
    public static function page($page, $params = [], $separator = null)
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
        return static::route($queryParams, $prefix, $separator);
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
        return static::route($queryParams, $prefix, $separator);
    }

    /**
     * Summary of route
     * @param array<mixed> $params
     * @param string $prefix
     * @param string|null $separator
     * @return string
     */
    public static function route($params, $prefix = '', $separator = null)
    {
        if (Config::get('use_route_urls')) {
            $route = static::getPageRoute($params, $prefix, $separator);
            if (!is_null($route)) {
                return $route;
            }
        }
        unset($params[Route::HANDLER_PARAM]);
        if (empty($params)) {
            return $prefix;
        }
        $queryString = http_build_query($params, '', $separator);
        return $prefix . '?' . $queryString;
    }

    /**
     * Summary of base
     * @return string
     */
    public static function base()
    {
        if (isset(static::$baseUrl)) {
            return static::$baseUrl;
        }
        if (!empty(Config::get('full_url'))) {
            $base = Config::get('full_url');
        } elseif (static::hasTrustedProxies()) {
            // use scheme and host + base path here to apply potential forwarded values
            $base = static::$proxyRequest->getSchemeAndHttpHost() . static::$proxyRequest->getBasePath();
        } else {
            $base = dirname($_SERVER['SCRIPT_NAME']);
        }
        if (!str_ends_with($base, '/')) {
            $base .= '/';
        }
        static::setBaseUrl($base);
        return $base;
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
        if (!empty($params[self::HANDLER_PARAM])) {
            // keep page param and use endpoint as key here
            $page = $params[self::HANDLER_PARAM];
        } elseif (isset($params['page'])) {
            $page = $params['page'];
            unset($params['page']);
        } else {
            $page = '';
        }
        $pages = static::getPages();
        $routes = $pages[$page] ?? [];
        if (count($routes) < 1) {
            return null;
        }
        return static::findMatchingRoute($routes, $params, $prefix, $separator);
    }

    /**
     * Summary of findMatchingRoute
     * @param array<mixed> $routes
     * @param array<mixed> $params
     * @param string $prefix
     * @param string|null $separator
     * @return string|null
     */
    public static function findMatchingRoute($routes, $params, $prefix = '', $separator = null)
    {
        // find matching route based on fixed and/or path params - e.g. authors letter
        foreach ($routes as $route => $fixed) {
            if (count($fixed) > count($params)) {
                continue;
            }
            $subst = $params;
            // check and remove fixed params (incl. endpoint or page)
            foreach ($fixed as $key => $val) {
                if (!isset($subst[$key]) || $subst[$key] != $val) {
                    continue 2;
                }
                unset($subst[$key]);
            }
            $found = [];
            // check and replace path params + support custom patterns - see nikic/fast-route
            if (preg_match_all("~\{(\w+(|:[^}]+))\}~", $route, $found)) {
                if (in_array('ignore', $found[1])) {
                    $subst['ignore'] = 'ignore';
                }
                if (count($found[1]) > count($subst)) {
                    continue;
                }
                foreach ($found[1] as $param) {
                    $pattern = '';
                    if (str_contains($param, ':')) {
                        [$param, $pattern] = explode(':', $param);
                    }
                    if (!isset($subst[$param])) {
                        continue 2;
                    }
                    $value = $subst[$param];
                    if (!empty($pattern) && !preg_match('/^' . $pattern . '$/', $value)) {
                        continue 2;
                    }
                    if (in_array($param, ['title', 'author', 'ignore'])) {
                        $value = static::slugify($value);
                    }
                    if (!empty($pattern)) {
                        $route = str_replace('{' . $param . ':' . $pattern . '}', "$value", $route);
                    } else {
                        $route = str_replace('{' . $param . '}', "$value", $route);
                    }
                    unset($subst[$param]);
                }
            }
            if (count($subst) > 0) {
                return $prefix . $route . '?' . http_build_query($subst, '', $separator);
            }
            return $prefix . $route;
        }
        return null;
    }

    /**
     * Get mapping of pages to routes with fixed params
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
            } elseif (!empty($params[self::HANDLER_PARAM])) {
                // keep page param and use endpoint as key here
                $page = $params[self::HANDLER_PARAM];
            } else {
                $page = $params["page"] ?? '';
                unset($params["page"]);
            }
            static::$pages[$page] ??= [];
            static::$pages[$page][$route] = $params;
        }
        return static::$pages;
    }

    /**
     * Summary of slug - @todo check transliteration
     * @param string $string
     * @return string
     */
    public static function slugify($string)
    {
        static $transliterator;

        $string = str_replace([' ', '&', '"'], ['_', '-', ''], trim($string));
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        // see https://www.drupal.org/project/rename_admin_paths/issues/3275140 for different order
        if (!isset($transliterator)) {
            $transliterator = transliterator_create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");
            //$transliterator = transliterator_create("Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;");
        }
        return transliterator_transliterate($transliterator, $string);
    }

    /**
     * Match rewrite rule for path and return endpoint with params
     * @param string $path
     * @return array<mixed>
     */
    public static function matchRewrite($path)
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            static::addRewriteRules($r);
        });

        // match pattern
        $endpoint = '';
        $params = [];
        $method = 'GET';
        $routeInfo = $dispatcher->dispatch($method, $path);

        if ($routeInfo[0] !== Dispatcher::FOUND) {
            return [$endpoint,  $params];
        }
        $map = $routeInfo[1];
        $params = $routeInfo[2];
        $endpoint = array_shift($map);
        $fixed = array_shift($map) ?? [];
        unset($params['ignore']);

        // for rewrite rules, put fixed params at the end
        $params = array_merge($params, $fixed);
        return [$endpoint,  $params];
    }

    /**
     * Summary of addRewriteRules
     * @param RouteCollector $r
     * @return void
     */
    public static function addRewriteRules($r)
    {
        foreach (static::$rewrites as $route => $map) {
            $r->addRoute('GET', $route, $map);
        }
    }

    /**
     * Summary of getUrlRewrite
     * @param string $endpoint
     * @param array<mixed> $params
     * @return string|null
     */
    public static function getUrlRewrite($endpoint, $params)
    {
        $endpoints = static::getEndpoints();
        $routes = $endpoints[$endpoint] ?? [];
        if (count($routes) < 1) {
            return null;
        }
        return static::findMatchingRoute($routes, $params);
    }

    /**
     * Get mapping of endpoints to rewrites with fixed params
     * @return array<string, array<mixed>>
     */
    public static function getEndpoints()
    {
        if (!empty(static::$endpoints)) {
            return static::$endpoints;
        }
        static::$endpoints = [];
        foreach (static::$rewrites as $route => $map) {
            if (!is_array($map)) {
                $map = [ $map ];
            }
            $endpoint = array_shift($map);
            $fixed = array_shift($map) ?? [];
            static::$endpoints[$endpoint] ??= [];
            static::$endpoints[$endpoint][$route] = $fixed;
        }
        return static::$endpoints;
    }

    /**
     * Summary of setBaseUrl
     * @param ?string $base
     * @return void
     */
    public static function setBaseUrl($base)
    {
        static::$baseUrl = $base;
        if (is_null($base)) {
            static::$proxyRequest = null;
        }
    }

    /**
     * Check if we have trusted proxies defined in config_local.php
     * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/HttpKernel/Kernel.php#L741
     * @return bool
     */
    public static function hasTrustedProxies()
    {
        $class = static::SYMFONY_REQUEST;
        if (!class_exists($class)) {
            return false;
        }
        if (empty(Config::get('trusted_proxies')) || empty(Config::get('trusted_headers'))) {
            return false;
        }
        if (!isset(static::$proxyRequest)) {
            $proxies = Config::get('trusted_proxies');
            $headers = Config::get('trusted_headers');
            $class::setTrustedProxies(is_array($proxies) ? $proxies : array_map('trim', explode(',', $proxies)), static::resolveTrustedHeaders($headers));
            static::$proxyRequest = $class::createFromGlobals();
        }
        return true;
    }

    /**
     * Convert trusted headers into bit field of Request::HEADER_*
     * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/FrameworkExtension.php#L3054
     * @param string[] $headers
     * @return int
     */
    protected static function resolveTrustedHeaders(array $headers)
    {
        $class = static::SYMFONY_REQUEST;
        $trustedHeaders = 0;

        foreach ($headers as $h) {
            $trustedHeaders |= match ($h) {
                'forwarded' => $class::HEADER_FORWARDED,
                'x-forwarded-for' => $class::HEADER_X_FORWARDED_FOR,
                'x-forwarded-host' => $class::HEADER_X_FORWARDED_HOST,
                'x-forwarded-proto' => $class::HEADER_X_FORWARDED_PROTO,
                'x-forwarded-port' => $class::HEADER_X_FORWARDED_PORT,
                'x-forwarded-prefix' => $class::HEADER_X_FORWARDED_PREFIX,
                default => 0,
            };
        }

        return $trustedHeaders;
    }
}
