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
    public const PARAMLIST = [];

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
    public static function getLink($params = [])
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
     * Summary of getPageLink - currently unused (all calls set page in params)
     * @param string|int|null $page
     * @param array<mixed> $params
     * @return string
     */
    public static function getPageLink($page = null, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['pageLink'] += 1;
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
    public static function generate($routeName, $params = [])
    {
        /** @phpstan-ignore-next-line */
        if (Route::KEEP_STATS) {
            Route::$counters['generate'] += 1;
        }
        $params[Route::ROUTE_PARAM] = $routeName;
        return static::getLink($params);
    }

    /**
     * Summary of findRoute
     * @param array<mixed> $params
     * @return string|null
     */
    public static function findRoute($params = [])
    {
        $routes = static::getRoutes();
        // use _route if available
        if (isset($params[Route::ROUTE_PARAM])) {
            $name = $params[Route::ROUTE_PARAM];
            unset($params[Route::ROUTE_PARAM]);
            if (!empty($name) && !empty($routes[$name])) {
                return Route::findMatchingRoute([$name => $routes[$name]], $params);
            }
        }
        return Route::findMatchingRoute($routes, $params);
    }

    /**
     * Summary of findRouteName
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
