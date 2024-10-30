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
     * Note: Route will add Route::HANDLER_PARAM => static::HANDLER to params
     * @return array<string, mixed>
     */
    public static function getRoutes()
    {
        return [];
    }

    /**
     * Summary of getLink
     * @param array<mixed> $params
     * @return string
     */
    public static function getLink($params = [])
    {
        return Route::link(static::HANDLER, null, $params);
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
        if (!empty($params["_route"])) {
            $name = $params["_route"];
            unset($params["_route"]);
            if (!empty($routes[$name])) {
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
        if (!empty($params["_route"])) {
            return $params["_route"];
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
        return Request::build($params, static::HANDLER);
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
