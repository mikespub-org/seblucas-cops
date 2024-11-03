<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Routing\FastRouter;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;
use Exception;

/**
 * Summary of Route
 */
class Route
{
    public const HANDLER_PARAM = "_handler";
    public const ROUTE_PARAM = "_route";
    public const ROUTES_CACHE_FILE = 'url_cached_routes.php';
    public const KEEP_STATS = true;

    /** @var ?\Symfony\Component\HttpFoundation\Request */
    protected static $proxyRequest = null;
    /** @var ?string */
    protected static $baseUrl = null;
    /** @var array<string, mixed> */
    protected static $routes = [];
    /** @var array<string, mixed> */
    protected static $static = [];
    /** @var class-string */
    protected static $routerClass = FastRouter::class;
    //protected static $routerClass = Routing::class;
    /** @var RouterInterface|null */
    protected static $router = null;
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
     * Summary of getRouter
     * @return RouterInterface
     */
    public static function getRouter()
    {
        if (!isset(self::$router)) {
            self::$router = new self::$routerClass();
        }
        return self::$router;
    }

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

        return self::getRouter()->match($path, $method);
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
     * Generate uri with FastRoute - @todo some issues left to deal with ;-)
     * @param string $name
     * @param array<mixed> $params
     * @return string|null
     */
    public static function generate($name, $params)
    {
        return self::getRouter()->generate($name, $params);
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
     * @return void
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
    public static function load($refresh = false)
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
    public static function path($path = null, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            self::$counters['path'] += 1;
        }
        if (!empty($path) && str_starts_with($path, '/')) {
            return $path . self::params($params);
        }
        return self::base() . $path . self::params($params);
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
        $queryString = self::getQueryString($queryParams);
        return $prefix . '?' . $queryString;
    }

    /**
     * Get full link for handler with page and params (incl _route)
     *
     * The handler takes precedence over page or _route here, as it
     * will be variable (html/json or feed/opds or restapi or ...)
     *
     * @deprecated 3.4.2 use handler::route(), handler::page() or handler:link()
     * @param class-string|null $handler
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function link($handler = null, $page = null, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            self::$counters['link'] += 1;
            if (empty($handler)) {
                self::$counters['empty'] += 1;
            }
        }
        $handler ??= Route::getHandler('html');
        // take into account handler when building page url, e.g. feed or zipper
        if (!in_array($handler::HANDLER, ['html', 'json', 'phpunit'])) {
            $params[self::HANDLER_PARAM] = $handler;
        } else {
            // @todo do we still want to get rid of this here?
            unset($params[self::HANDLER_PARAM]);
        }
        return self::process($handler, $page, $params);
    }

    /**
     * Process link with defined handler, page and params
     * @param class-string $handler defined in Route::link(), BaseHandler::link() or PageHandler::link()
     * @param string|int|null $page
     * @param array<mixed> $params with HANDLER_PARAM set (base), unset (page) or variable (link)
     * @return string
     */
    public static function process($handler, $page, $params)
    {
        // ?page=... or /route/...
        $uri = self::page($page, $params);
        return self::absolute($uri, $handler);
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
        return self::route($queryParams, $prefix);
    }

    /**
     * Summary of route
     * @param array<mixed> $params
     * @param string $prefix
     * @return string
     */
    public static function route($params, $prefix = '')
    {
        $route = self::getRouteForParams($params, $prefix);
        if (!is_null($route)) {
            return $route;
        }
        unset($params[self::HANDLER_PARAM]);
        unset($params[self::ROUTE_PARAM]);
        if (empty($params)) {
            return $prefix;
        }
        $queryString = self::getQueryString($params);
        return $prefix . '?' . $queryString;
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
        } elseif (self::hasTrustedProxies()) {
            // use scheme and host + base path here to apply potential forwarded values
            $base = self::$proxyRequest->getSchemeAndHttpHost() . self::$proxyRequest->getBasePath();
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
        if (empty(self::$handlers)) {
            self::$handlers = Framework::getHandlers();
        }
        // we already have a handler class-string
        if (in_array($name, array_values(self::$handlers))) {
            return $name;
        }
        if (!isset(self::$handlers[$name])) {
            throw new Exception('Invalid handler name ' . htmlspecialchars($name));
        }
        return self::$handlers[$name];
    }

    /**
     * Summary of getRouteForParams
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function getRouteForParams($params, $prefix = '')
    {
        $default = self::getHandler('html');
        if (!empty($params[self::HANDLER_PARAM])) {
            $handler = $params[self::HANDLER_PARAM];
            // use page route with /restapi prefix instead
            if ($handler::HANDLER == 'restapi' && empty($params['_resource'])) {
                if (!empty($params[self::ROUTE_PARAM]) || !empty($params['page'])) {
                    $prefix = $prefix . $handler::PREFIX;
                    $handler = $default;
                }
            } elseif (in_array($handler::HANDLER, ['feed', 'opds'])) {
                // if we have a page, or if we have a route and it does *not* start with the handler name
                if (!empty($params['page']) || (!empty($params[self::ROUTE_PARAM]) && !str_starts_with($params[self::ROUTE_PARAM], $handler::HANDLER))) {
                    $prefix = $prefix . $handler::PREFIX;
                    $handler = $default;
                }
                // @todo same for feed with /feed/{path:.*} ?
            } elseif ($handler::HANDLER == 'phpunit') {
                $handler = $default;
            }
            unset($params[self::HANDLER_PARAM]);
        } elseif (isset($params[self::ROUTE_PARAM])) {
            // use default handler for page route
            $handler = $default;
        } elseif (isset($params['page'])) {
            // use default handler for page route
            $handler = $default;
            // @todo use _route later - see PageHandler::findRouteName()
        } else {
            // no page or handler, e.g. index.php?complete=1
            $route = '';
            unset($params[self::ROUTE_PARAM]);
            if (empty($params)) {
                return $prefix . $route;
            }
            return $prefix . $route . '?' . self::getQueryString($params);
        }

        $route = $handler::findRoute($params);
        if (!isset($route)) {
            return $route;
        }
        return $prefix . $route;
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
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            self::$counters['find'] += 1;
        }
        // find matching route based on fixed and/or path params - e.g. authors letter
        foreach ($routes as $name => $route) {
            $result = self::replacePathParams($route, $params, $prefix);
            if (isset($result)) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Replace path params for known route
     * @param array<mixed> $route
     * @param array<mixed> $params
     * @param string $prefix
     * @param bool $checkFixed true if we need to check fixed params, false for known route - see PageHandler::findRoute()
     * @return string|null
     */
    public static function replacePathParams($route, $params, $prefix = '', $checkFixed = true)
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['replace'] += 1;
        }
        // Add fixed if needed
        $route[] = [];
        [$path, $fixed] = $route;

        $subst = $params;
        // check and remove fixed params (incl. handler or page)
        foreach ($fixed as $key => $val) {
            if ($checkFixed) {
                // this isn't the route you're looking for...
                if (!isset($subst[$key]) || $subst[$key] != $val) {
                    return null;
                }
            }
            unset($subst[$key]);
        }
        $found = [];
        // check and replace path params + support custom patterns - see nikic/fast-route
        $count = preg_match_all("~\{(\w+(|:[^}]+))\}~", $path, $found);
        if ($count === false) {
            return null;
        }
        // no path parameters found in route - return what is left
        if ($count === 0) {
            if (count($subst) > 0) {
                return $prefix . $path . '?' . Route::getQueryString($subst);
            }
            return $prefix . $path;
        }
        // start checking path parameters
        if (in_array('ignore', $found[1])) {
            $subst['ignore'] ??= 'ignore';
        }
        if (count($found[1]) > count($subst)) {
            return null;
        }
        foreach ($found[1] as $param) {
            $pattern = '';
            if (str_contains($param, ':')) {
                [$param, $pattern] = explode(':', $param);
            }
            if (!isset($subst[$param])) {
                return null;
            }
            $value = $subst[$param];
            // @todo support unicode pattern for first letter - but see https://github.com/nikic/FastRoute/issues/154
            if (!empty($pattern) && !preg_match('/^' . $pattern . '$/', (string) $value)) {
                return null;
            }
            if (in_array($param, ['title', 'author', 'ignore'])) {
                $value = self::slugify($value);
                $value = rawurlencode($value);
            }
            if (!empty($pattern)) {
                $path = str_replace('{' . $param . ':' . $pattern . '}', "$value", $path);
            } else {
                $path = str_replace('{' . $param . '}', "$value", $path);
            }
            unset($subst[$param]);
        }
        if (count($subst) > 0) {
            return $prefix . $path . '?' . self::getQueryString($subst);
        }
        return $prefix . $path;
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
        // normalize first
        $string = Translation::normalizeUtf8String($string);

        // then clean the new string - e.g. 'sun wu' to 'sun_wu'
        return str_replace(array_keys($replace), array_values($replace), trim($string));
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
            self::$proxyRequest = null;
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
        if (!isset(self::$proxyRequest)) {
            $proxies = Config::get('trusted_proxies');
            $headers = Config::get('trusted_headers');
            $class::setTrustedProxies(is_array($proxies) ? $proxies : array_map('trim', explode(',', (string) $proxies)), self::resolveTrustedHeaders($headers));
            self::$proxyRequest = $class::createFromGlobals();
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
