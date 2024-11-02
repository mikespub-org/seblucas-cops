<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;

/**
 * Summary of BaseHandler
 */
abstract class BaseHandler
{
    public const HANDLER = "";
    public const PREFIX = "";
    public const PARAMLIST = [];
    public const GROUP_PARAM = "";

    /**
     * Array of path => params for this handler
     * Note: Route will add Route::HANDLER_PARAM => static::class to params
     * @return array<string, mixed>
     */
    public static function getRoutes()
    {
        return [];
    }

    /**
     * Get link for this specific handler and params (incl _route)
     * @param array<mixed> $params
     * @return string
     */
    public static function link($params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['baseLink'] += 1;
        }
        // use this specific handler to find the route
        $params[Route::HANDLER_PARAM] = static::class;
        return Route::process(static::class, null, $params);
    }

    /**
     * Get page link for this specific handler and params (incl _route)
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function page($page = null, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['basePage'] += 1;
        }
        // use this specific handler to find the route
        $params[Route::HANDLER_PARAM] = static::class;
        return Route::process(static::class, $page, $params);
    }

    /**
     * Generate link based on pre-defined route name for this handler (make visible)
     * @param string $routeName
     * @param array<mixed> $params
     * @return string|null
     */
    public static function route($routeName, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['baseRoute'] += 1;
        }
        $params[Route::ROUTE_PARAM] = $routeName;
        return static::link($params);
    }

    /**
     * Summary of findRoute
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function findRoute($params = [], $prefix = '')
    {
        $routes = static::findRoutes();
        // use _route if available
        $path = static::hasRouteName($routes, $params, $prefix);
        if ($path) {
            return $path;
        }
        unset($params[Route::ROUTE_PARAM]);
        $path = static::hasSingleRoute($routes, $params, $prefix);
        if ($path) {
            return $path;
        }
        return static::hasMatchingRoute($routes, $params, $prefix);
    }

    /**
     * Find all routes for matching - include parent routes in page handler
     * @return array<string, mixed>
     */
    public static function findRoutes()
    {
        $routes = static::getRoutes();
        return $routes;
    }

    /**
     * Summary of hasRouteName
     * @param array<mixed> $routes
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function hasRouteName($routes, $params = [], $prefix = '')
    {
        // use _route if available
        if (!isset($params[Route::ROUTE_PARAM])) {
            return null;
        }
        $name = $params[Route::ROUTE_PARAM];
        unset($params[Route::ROUTE_PARAM]);
        if (empty($name) || empty($routes[$name])) {
            return null;
        }
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['route'] += 1;
        }
        // @todo test FastRoute\GenerateUri - some issues left to deal with ;-)
        //return Route::generate($name, $params);
        $route = $routes[$name];
        // for known route, not all fixed params may be available (e.g. page) - ignore them
        $checkFixed = false;
        return Route::replacePathParams($route, $params, $prefix, $checkFixed);
    }

    /**
     * Summary of hasSingleRoute
     * @param array<mixed> $routes
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function hasSingleRoute($routes, $params = [], $prefix = '')
    {
        if (count($routes) > 1) {
            return null;
        }
        // @todo check if we have all the parameters we need
        $accept = array_intersect(array_keys($params), static::PARAMLIST);
        if (count($accept) < count(static::PARAMLIST)) {
            return null;
        }
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['single'] += 1;
        }
        $route = array_values($routes)[0];
        // for unknown route, fixed params are used to find the right route - check them
        $checkFixed = true;
        return Route::replacePathParams($route, $params, $prefix, $checkFixed);
    }

    /**
     * Summary of hasMatchingRoute - group by page for page handler, by resource for restapi etc.
     * @param array<mixed> $routes
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function hasMatchingRoute($routes, $params = [], $prefix = '')
    {
        if (empty(static::GROUP_PARAM)) {
            return Route::findMatchingRoute($routes, $params, $prefix);
        }
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['group'] += 1;
        }
        $match = $params[static::GROUP_PARAM] ?? '';
        // filter routes by static::GROUP_PARAM before matching
        $group = array_filter($routes, function ($route) use ($match) {
            // Add fixed if needed
            $route[] = [];
            [$path, $fixed] = $route;
            return $match == ($fixed[static::GROUP_PARAM] ?? '');
        });
        if (count($group) < 1) {
            return null;
        }
        return Route::findMatchingRoute($group, $params, $prefix);
    }

    /**
     * Summary of findRouteName - @todo adapt to actual routes for each handler
     * @param array<mixed> $params
     * @return string
     */
    public static function findRouteName($params)
    {
        if (!empty($params[Route::ROUTE_PARAM])) {
            return $params[Route::ROUTE_PARAM];
        }
        $name = static::HANDLER;
        if (count(static::getRoutes()) > 1) {
            $accept = array_intersect(array_keys($params), static::PARAMLIST);
            if (!empty($accept)) {
                $name = $name . '-' . implode('-', $accept);
            }
        }
        return $name;
    }

    /**
     * Summary of request
     * @param array<mixed> $params
     * @return Request
     */
    public static function request($params = [])
    {
        return Request::build($params, static::class);
    }

    public function __construct()
    {
        // ...
    }

    /**
     * @param Request $request
     * @return Response|void
     */
    abstract public function handle($request);
}
