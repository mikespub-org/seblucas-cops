<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Translation;

use function FastRoute\simpleDispatcher;

/**
 * Summary of Route
 */
class Route
{
    public const HANDLER_PARAM = "_handler";

    /** @var ?\Symfony\Component\HttpFoundation\Request */
    protected static $proxyRequest = null;
    /** @var ?string */
    protected static $baseUrl = null;
    /** @var array<string, mixed> */
    protected static $routes = [];
    /** @var string[] */
    protected static $skipPrefix = ['index', 'json', 'fetch', 'restapi', 'graphql', 'phpunit'];
    /** @var Dispatcher|null */
    protected static $dispatcher = null;
    /** @var array<string, mixed> */
    protected static $groups = [];

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
     * Set routes
     * @param array<string, array<mixed>> $routes
     * @return void
     */
    public static function setRoutes($routes = [])
    {
        static::$routes = $routes;
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
     * Get full URL path for relative path with optional params
     * @param string $path relative to base dir
     * @param array<mixed> $params (optional)
     * @return string
     */
    public static function path($path = null, $params = [])
    {
        if (!empty($path) && str_starts_with($path, '/')) {
            return $path . static::params($params);
        }
        return static::base() . $path . static::params($params);
    }

    /**
     * Get optional query string with ?
     * @param array<mixed> $params
     * @param string $prefix
     * @return string
     */
    public static function params($params = [], $prefix = '')
    {
        $queryParams = array_filter($params, function ($val) {
            if (empty($val) && strval($val) !== '0') {
                return false;
            }
            return true;
        });
        if (empty($queryParams)) {
            return $prefix;
        }
        $queryString = static::getQueryString($queryParams);
        return $prefix . '?' . $queryString;
    }

    /**
     * Get full link for handler with page and params
     * @param string|null $handler
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function link($handler = null, $page = null, $params = [])
    {
        $handler ??= 'index';
        // take into account handler when building page url, e.g. feed or zipper
        if (!in_array($handler, ['index', 'json', 'phpunit'])) {
            $params[self::HANDLER_PARAM] = $handler;
        } else {
            unset($params[self::HANDLER_PARAM]);
        }
        // ?page=... or /route/...
        $uri = static::page($page, $params);
        // same routes as HtmlHandler - see util.js
        if ($handler == 'json') {
            $handler = 'index';
        }
        // endpoint.php or handler or empty
        $endpoint = static::endpoint($handler);
        if (empty($endpoint) && str_starts_with($uri, '/')) {
            // URL format: /base/route/...
            return static::base() . substr($uri, 1);
        }
        // URL format: /base/endpoint.php?page=... or /base/handler/route/...
        return static::base() . $endpoint . $uri;
    }

    /**
     * Get endpoint for handler
     * @param string $handler
     * @return string
     */
    public static function endpoint($handler = 'index')
    {
        if (Config::get('front_controller')) {
            // no endpoint prefix for supported handlers
            return '';
        }
        // use default endpoint for supported handlers
        return Config::ENDPOINT['index'];
    }

    /**
     * Get uri for page with params
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function page($page, $params = [])
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
        return static::route($queryParams, $prefix);
    }

    /**
     * Summary of route
     * @param array<mixed> $params
     * @param string $prefix
     * @return string
     */
    public static function route($params, $prefix = '')
    {
        $route = static::getRouteForParams($params, $prefix);
        if (!is_null($route)) {
            return $route;
        }
        unset($params[self::HANDLER_PARAM]);
        if (empty($params)) {
            return $prefix;
        }
        $queryString = static::getQueryString($params);
        return $prefix . '?' . $queryString;
    }

    /**
     * Summary of getQueryString
     * @param array<mixed> $params
     * @return string
     */
    public static function getQueryString($params)
    {
        return http_build_query($params, '', null, PHP_QUERY_RFC3986);
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
            $base = dirname((string) $_SERVER['SCRIPT_NAME']);
        }
        if (!str_ends_with((string) $base, '/')) {
            $base .= '/';
        }
        static::setBaseUrl($base);
        return $base;
    }

    /**
     * Summary of getRouteForParams
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function getRouteForParams($params, $prefix = '')
    {
        if (!empty($params[self::HANDLER_PARAM])) {
            // keep page param and use handler as key here
            $group = $params[self::HANDLER_PARAM];
        } elseif (isset($params['page'])) {
            // use page param as key here
            $group = $params['page'];
            unset($params['page']);
        } else {
            // other routes
            $group = '';
        }
        // use page route with /restapi prefix instead
        if ($group == 'restapi' && empty($params['_resource']) && !empty($params['page'])) {
            $prefix = $prefix . '/restapi';
            $group = $params['page'];
            unset($params[self::HANDLER_PARAM]);
            unset($params['page']);
        }
        $groups = static::getGroups();
        $routes = $groups[$group] ?? [];
        if (count($routes) < 1) {
            return null;
        }
        return static::findMatchingRoute($routes, $params, $prefix);
    }

    /**
     * Summary of findMatchingRoute
     * @param array<mixed> $routes
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function findMatchingRoute($routes, $params, $prefix = '')
    {
        // find matching route based on fixed and/or path params - e.g. authors letter
        foreach ($routes as $route => $fixed) {
            if (count($fixed) > count($params)) {
                continue;
            }
            $subst = $params;
            // check and remove fixed params (incl. handler or page)
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
                    // @todo support unicode pattern for first letter - but see https://github.com/nikic/FastRoute/issues/154
                    if (!empty($pattern) && !preg_match('/^' . $pattern . '$/', (string) $value)) {
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
                return $prefix . $route . '?' . static::getQueryString($subst);
            }
            return $prefix . $route;
        }
        return null;
    }

    /**
     * Get mapping of pages or handlers to routes with fixed params
     * @return array<string, array<mixed>>
     */
    public static function getGroups()
    {
        if (!empty(static::$groups)) {
            return static::$groups;
        }
        static::$groups = [];
        foreach (static::$routes as $route => $params) {
            if (!is_array($params)) {
                // use page as key here
                $group = $params;
                $params = [];
            } elseif (!empty($params[self::HANDLER_PARAM])) {
                // keep page param and use handler as key here
                $group = $params[self::HANDLER_PARAM];
            } else {
                // use page param as key here
                $group = $params["page"] ?? '';
                unset($params["page"]);
            }
            static::$groups[$group] ??= [];
            static::$groups[$group][$route] = $params;
        }
        return static::$groups;
    }

    /**
     * Summary of slug - @todo check transliteration
     * @param string $string
     * @return string
     */
    public static function slugify($string)
    {
        $replace = [
            ' ' => '_',
            '&' => '-',
            '#' => '-',
            '"' => '',
            //"'" => '',
            ':' => '',
            ';' => '',
            '<' => '',
            '>' => '',
            '{' => '',
            '}' => '',
            '?' => '',
            ',' => '',
            '/' => '.',
            '\\' => '.',
        ];
        $string = str_replace(array_keys($replace), array_values($replace), trim($string));

        return Translation::normalizeUtf8String($string);
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
     * Check if we have trusted proxies defined in config/local.php
     * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/HttpKernel/Kernel.php#L741
     * @return bool
     */
    public static function hasTrustedProxies()
    {
        $class = Request::SYMFONY_REQUEST;
        if (!class_exists($class)) {
            return false;
        }
        if (empty(Config::get('trusted_proxies')) || empty(Config::get('trusted_headers'))) {
            return false;
        }
        if (!isset(static::$proxyRequest)) {
            $proxies = Config::get('trusted_proxies');
            $headers = Config::get('trusted_headers');
            $class::setTrustedProxies(is_array($proxies) ? $proxies : array_map('trim', explode(',', (string) $proxies)), static::resolveTrustedHeaders($headers));
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
        $class = Request::SYMFONY_REQUEST;
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
