<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Slugger;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Routing\UriGenerator;
use Exception;

/**
 * Summary of Route
 */
class Route
{
    public const HANDLER_PARAM = "_handler";
    public const ROUTE_PARAM = "_route";
    public const ROUTES_CACHE_FILE = 'url_cached_routes.php';
    public const KEEP_STATS = false;

    /** @var ?string */
    protected static $baseUrl = null;
    /** @var array<string, mixed> */
    protected static $routes = [];
    /** @var array<string, mixed> */
    protected static $static = [];
    /** @var class-string */
    protected static $sluggerClass = Slugger::class;
    /** @var Slugger|bool|null */
    protected static $slugger = null;
    /** @var array<string, class-string> */
    protected static $handlers = [];
    /** @var array<string, mixed> */
    public static $counters = [
        'link' => 0,
        'empty' => 0,
        'path' => 0,
        'match' => 0,
        'find' => 0,
        'replace' => 0,
        'baseLink' => 0,
        'basePage' => 0,
        'baseRoute' => 0,
        'route' => 0,
        'single' => 0,
        'group' => 0,
        'pageLink' => 0,
        'pagePage' => 0,
        'resource' => 0,
        'handler' => 0,
    ];

    /**
     * Match pathinfo against routes and return query params
     * @param string $path
     * @param ?string $method
     * @return ?array<mixed> array of query params or null if not found
     */
    public static function match($path, $method = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            self::$counters['match'] += 1;
        }
        if (empty($path) || $path == '/') {
            return [];
        }

        // match exact path
        if (self::has($path)) {
            return self::get($path);
        }

        return Framework::getRouter()->match($path, $method);
    }

    /**
     * Check if static path exists
     * @param string $path
     * @return bool
     */
    public static function has($path)
    {
        return array_key_exists($path, self::$static);
    }

    /**
     * Get route params for static path
     * @param string $path
     * @return array<mixed>
     */
    public static function get($path)
    {
        $name = self::$static[$path];
        return self::$routes[$name][1];
    }

    /**
     * Set route to path with optional static params, methods and options
     * @param string $name
     * @param string $path
     * @param array<mixed> $params
     * @param array<mixed> $methods
     * @param array<mixed> $options
     * @return void
     */
    public static function set($name, $path, $params = [], $methods = [], $options = [])
    {
        self::$routes[$name] = [$path, $params, $methods, $options];
    }

    /**
     * Get routes and query params
     * @return array<string, array<mixed>>
     */
    public static function getRoutes()
    {
        return self::$routes;
    }

    /**
     * Get routes by group
     * @return array<string, array<mixed>>
     */
    public static function getGroups()
    {
        $groups = [];
        foreach (self::getRoutes() as $name => $route) {
            [$path, $params, $methods, $options] = $route;
            $group = $params[self::HANDLER_PARAM] ?? self::getHandler('html');
            $groups[$group] ??= [];
            if ($group::HANDLER == 'html') {
                $page = $params["page"] ?? '';
                $groups[$group][$page] ??= [];
                $groups[$group][$page][] = $name;
            } elseif ($group::HANDLER == 'restapi') {
                $resource = $params["_resource"] ?? '';
                $groups[$group][$resource] ??= [];
                $groups[$group][$resource][] = $name;
            } else {
                $groups[$group][] = $name;
            }
        }
        return $groups;
    }

    /**
     * Add routes for all handlers
     * @return void
     */
    public static function init()
    {
        if (self::count() > 0) {
            return;
        }
        foreach (Framework::getHandlers() as $handler) {
            self::addRoutes($handler::getRoutes(), $handler);
        }
    }

    /**
     * Add routes with name, path, params, methods and options
     * @param array<string, array<mixed>> $routes
     * @param class-string $handler
     * @return array<string, array<mixed>>
     */
    public static function addRoutes($routes, $handler)
    {
        foreach ($routes as $name => $route) {
            // Add params, methods and options if needed
            array_push($route, [], [], []);
            [$path, $params, $methods, $options] = $route;
            // Add static paths to $static
            if (!str_contains($path, '{')) {
                self::$static[$path] = $name;
            }
            // Add ["_handler" => $handler] to params
            if ($handler::HANDLER !== 'html') {
                $params[self::HANDLER_PARAM] ??= $handler;
            } else {
                // default routes can be used by html, json, phpunit, restapi without _resource, ...
            }
            // Add ["_route" => $name] to params
            $params[self::ROUTE_PARAM] ??= $name;
            // Add default GET method
            if (empty($methods)) {
                $methods[] = 'GET';
            }
            if (isset(self::$routes[$name])) {
                var_dump(self::$routes[$name]);
                throw new Exception('Duplicate route name ' . $name . ' for ' . $handler);
            }
            $routes[$name] = [$path, $params, $methods, $options];
        }
        self::$routes = array_merge(self::$routes, $routes);
        return $routes;
    }

    /**
     * Set routes
     * @param array<string, array<mixed>> $routes
     * @return void
     */
    public static function setRoutes($routes = [])
    {
        self::$routes = $routes;
    }

    /**
     * Count routes
     * @return int
     */
    public static function count()
    {
        return count(self::$routes);
    }

    /**
     * Summary of dump
     * @return void
     */
    public static function dump()
    {
        $cacheFile = dirname(__DIR__) . '/Routing/' . self::ROUTES_CACHE_FILE;
        $content = '<?php' . "\n\n";
        $content .= "// This file has been auto-generated by the COPS Input\Route class.\n\n";
        $content .= '$handlers = ' . Format::export(Framework::getHandlers()) . ";\n\n";
        $content .= '$static = ' . Format::export(self::$static) . ";\n\n";
        $content .= '$routes = ' . Format::export(self::$routes) . ";\n\n";
        $content .= "return [\n";
        $content .= "    'handlers' => \$handlers,\n";
        $content .= "    'static' => \$static,\n";
        $content .= "    'routes' => \$routes,\n";
        $content .= "];\n";
        file_put_contents($cacheFile, $content);
    }

    /**
     * Summary of load
     * @param bool $refresh
     * @return void
     */
    public static function load($refresh = false): void
    {
        $cacheFile = dirname(__DIR__) . '/Routing/' . self::ROUTES_CACHE_FILE;
        if ($refresh || !file_exists($cacheFile)) {
            self::init();
            self::dump();
            return;
        }
        try {
            $cache = require $cacheFile;
            self::$handlers = $cache["handlers"];
            self::$static = $cache["static"];
            self::$routes = $cache["routes"];
        } catch (Exception $e) {
            echo '<pre>' . $e . '</pre>';
        }
    }

    /**
     * Get full URL path for relative path with optional params
     * @param string $path relative to base dir
     * @param array<mixed> $params (optional)
     * @return string
     */
    public static function path($path = '', $params = [])
    {
        return UriGenerator::path($path, $params);
    }

    /**
     * Return absolute path for uri
     * @param string $uri
     * @param mixed $handler - @todo get rid of this = unused
     * @return string
     */
    public static function absolute($uri, $handler = 'html')
    {
        // endpoint.php or handler or empty
        $endpoint = self::endpoint($handler);
        if (empty($endpoint) && str_starts_with($uri, '/')) {
            // URL format: /base/route/...
            return self::base() . substr($uri, 1);
        }
        // URL format: /base/endpoint.php?page=... or /base/handler/route/...
        return self::base() . $endpoint . $uri;
    }

    /**
     * Get endpoint for handler
     * @todo get rid of param here - prefix is already included
     * @param string $handler
     * @return string
     */
    public static function endpoint($handler = 'html')
    {
        if (Config::get('front_controller')) {
            // no endpoint prefix for supported handlers
            return '';
        }
        // use default endpoint for supported handlers
        return Config::ENDPOINT['html'];
    }

    /**
     * Summary of getQueryString
     * @param array<mixed> $params
     * @return string
     */
    public static function getQueryString($params)
    {
        unset($params[self::HANDLER_PARAM]);
        unset($params[self::ROUTE_PARAM]);
        return http_build_query($params, '', null, PHP_QUERY_RFC3986);
    }

    /**
     * Summary of base
     * @return string
     */
    public static function base()
    {
        if (isset(self::$baseUrl)) {
            return self::$baseUrl;
        }
        if (!empty(Config::get('full_url'))) {
            $base = Config::get('full_url');
        } elseif (ProxyRequest::hasTrustedProxies()) {
            // use scheme and host + base path here to apply potential forwarded values
            $base = ProxyRequest::getProxyBaseUrl();
        } else {
            $base = dirname((string) $_SERVER['SCRIPT_NAME']);
        }
        if (!str_ends_with((string) $base, '/')) {
            $base .= '/';
        }
        self::setBaseUrl($base);
        return $base;
    }

    /**
     * Get handler class based on name
     * @param string|class-string $name
     * @return class-string
     */
    public static function getHandler($name)
    {
        return Framework::getHandlerManager()->getHandlerClass($name);
    }

    /**
     * Summary of getSlugger
     * @param ?string $locale
     * @return Slugger|bool|null
     */
    public static function getSlugger($locale = null)
    {
        if (!isset(self::$slugger)) {
            self::$slugger = new self::$sluggerClass($locale);
        }
        return self::$slugger;
    }

    /**
     * Summary of slugify
     * @param string $string
     * @return string
     */
    public static function slugify($string)
    {
        return (string) self::getSlugger()->slug($string, '_');
        // @deprecated 3.5.1 use Slugger()->slug()
        //return self::$sluggerClass::slugify($string);
    }

    /**
     * Summary of setLocale
     * @param ?string $locale
     * @return void
     */
    public static function setLocale($locale)
    {
        if (is_null($locale)) {
            self::$slugger = null;
            return;
        }
        self::$slugger = new self::$sluggerClass($locale);
    }

    /**
     * Summary of setBaseUrl
     * @param ?string $base
     * @return void
     */
    public static function setBaseUrl($base)
    {
        self::$baseUrl = $base;
        if (is_null($base)) {
            ProxyRequest::$proxyRequest = null;
        }
    }
}
