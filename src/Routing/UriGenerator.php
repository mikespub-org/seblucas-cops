<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Routing;

use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Input\Route;

/**
 * Summary of UriGenerator
 */
class UriGenerator
{
    /**
     * Generate uri with FastRoute - @todo some issues left to deal with ;-)
     * @param string $name
     * @param array<mixed> $params
     * @return string|null
     */
    public static function generate($name, $params)
    {
        return Framework::getRouter()->generate($name, $params);
    }

    /**
     * Get full URL path for relative path with optional params
     * @param string $path relative to base dir
     * @param array<mixed> $params (optional)
     * @return string
     */
    public static function path($path = '', $params = [])
    {
        $queryParams = self::getQueryParams($params);
        if (!empty($path) && str_starts_with($path, '/')) {
            $prefix = $path;
        } else {
            $prefix = Route::base() . $path;
        }
        return self::getUriForParams($queryParams, $prefix);
    }

    /**
     * Process link with defined handler and params (incl. page)
     * @param class-string $handler defined in Route::link(), BaseHandler::link() or PageHandler::link() - @todo get rid of this = unused
     * @param array<mixed> $params with HANDLER_PARAM set (base), unset (page) or variable (link)
     * @param string $prefix (optional)
     * @return string
     */
    public static function process($handler, $params, $prefix = '')
    {
        // ?page=... or /route/...
        $uri = self::route($params, $prefix);
        return Route::absolute($uri, $handler);
    }

    /**
     * Get uri for route with params
     * @param array<mixed> $params
     * @param string $prefix (optional)
     * @return string
     */
    public static function route($params, $prefix = '')
    {
        $queryParams = self::getQueryParams($params);
        if (count($queryParams) < 1) {
            return $prefix;
        }
        $route = self::getRouteForParams($queryParams, $prefix);
        if (!is_null($route)) {
            return $route;
        }
        return self::getUriForParams($queryParams, $prefix);
    }

    /**
     * Summary of getQueryParams
     * @param array<mixed> $params
     * @return array<mixed> filtered params with optional handler, route and page param
     */
    public static function getQueryParams($params)
    {
        $queryParams = array_filter($params, function ($val) {
            if (empty($val) && strval($val) !== '0') {
                return false;
            }
            return true;
        });
        return $queryParams;
    }

    /**
     * Summary of getUriForParams
     * @param array<mixed> $params
     * @param string $prefix
     * @return string
     */
    public static function getUriForParams($params, $prefix = '')
    {
        $queryString = Route::getQueryString($params);
        if (empty($queryString)) {
            return $prefix;
        }
        return $prefix . '?' . $queryString;
    }

    /**
     * Get route for params based on:
     * 1. handler param + use default page handler with prefix for page routes
     * 2. route param
     * 3. page param
     * 4. other params as uri
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function getRouteForParams($params, $prefix = '')
    {
        $default = Route::getHandler('html');
        if (!empty($params[Route::HANDLER_PARAM])) {
            $handler = $params[Route::HANDLER_PARAM];
            if (in_array($handler::HANDLER, ['restapi', 'feed', 'opds'])) {
                // if we have a page, or if we have a route and it starts with page-*, e.g. _route=page-author
                if (!empty($params['page']) || str_starts_with($params[Route::ROUTE_PARAM] ?? '', 'page-')) {
                    // use page route with /handler prefix instead
                    $prefix = $prefix . $handler::PREFIX;
                    $handler = $default;
                // if we have a route and it does *not* start with the handler name, e.g. _route=check-more
                } elseif (!empty($params[Route::ROUTE_PARAM]) && !str_starts_with($params[Route::ROUTE_PARAM], $handler::HANDLER)) {
                    $prefix = $prefix . $handler::PREFIX;
                    $handlerName = explode('-', $params[Route::ROUTE_PARAM])[0];
                    $handler = Route::getHandler($handlerName);
                }
            } elseif ($handler::HANDLER == 'phpunit') {
                $handler = $default;
            }
            unset($params[Route::HANDLER_PARAM]);
        } elseif (isset($params[Route::ROUTE_PARAM])) {
            // use default handler for page route
            $handler = $default;
        } elseif (isset($params['page'])) {
            // use default handler for page route
            $handler = $default;
            // @todo use _route later - see PageHandler::findRouteName()
        } else {
            // no page or handler, e.g. index.php?complete=1
            return self::getUriForParams($params, $prefix);
        }

        $route = self::findRoute($handler, $params);
        if (!isset($route)) {
            return $route;
        }
        return $prefix . $route;
    }

    /**
     * Summary of findRoute
     * @param class-string $handler
     * @param array<mixed> $params
     * @param string $prefix
     * @return string|null
     */
    public static function findRoute($handler, $params = [], $prefix = '')
    {
        $routes = $handler::findRoutes();
        // use _route if available
        $path = self::hasRouteName($routes, $params, $prefix);
        if ($path) {
            return $path;
        }
        unset($params[Route::ROUTE_PARAM]);
        $path = self::hasSingleRoute($routes, $params, $prefix, $handler::PARAMLIST);
        if ($path) {
            return $path;
        }
        return self::hasMatchingRoute($routes, $params, $prefix, $handler::GROUP_PARAM);
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
        // @todo test FastRoute\GenerateUri - some issues left to deal with ;-)
        //return self::generate($name, $params);
        $route = $routes[$name];
        // for known route, not all fixed params may be available (e.g. page) - ignore them
        $checkFixed = false;
        return self::replacePathParams($route, $params, $prefix, $checkFixed);
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
        foreach ($routes as $name => $route) {
            $result = self::replacePathParams($route, $params, $prefix);
            if (isset($result)) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Summary of hasSingleRoute
     * @param array<mixed> $routes
     * @param array<mixed> $params
     * @param string $prefix
     * @param array<string> $paramList
     * @return string|null
     */
    public static function hasSingleRoute($routes, $params = [], $prefix = '', $paramList = [])
    {
        if (count($routes) > 1) {
            return null;
        }
        // @todo check if we have all the parameters we need
        $accept = array_intersect(array_keys($params), $paramList);
        if (count($accept) < count($paramList)) {
            return null;
        }
        $route = array_values($routes)[0];
        // for unknown route, fixed params are used to find the right route - check them
        $checkFixed = true;
        return self::replacePathParams($route, $params, $prefix, $checkFixed);
    }

    /**
     * Summary of hasMatchingRoute - group by page for page handler, by resource for restapi etc.
     * @param array<mixed> $routes
     * @param array<mixed> $params
     * @param string $prefix
     * @param string $groupName
     * @return string|null
     */
    public static function hasMatchingRoute($routes, $params = [], $prefix = '', $groupName = '')
    {
        if (empty($groupName)) {
            return self::findMatchingRoute($routes, $params, $prefix);
        }
        $match = $params[$groupName] ?? '';
        // filter routes by static::GROUP_PARAM before matching
        $group = array_filter($routes, function ($route) use ($match, $groupName) {
            // Add fixed if needed
            $route[] = [];
            [$path, $fixed] = $route;
            return $match == ($fixed[$groupName] ?? '');
        });
        if (count($group) < 1) {
            return null;
        }
        return self::findMatchingRoute($group, $params, $prefix);
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
            return self::getUriForParams($subst, $prefix . $path);
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
                $value = Route::slugify($value);
                $value = rawurlencode($value);
            }
            // search query
            if (in_array($param, ['query'])) {
                $value = rawurlencode($value);
            }
            // extra file
            if (in_array($param, ['file'])) {
                $value = implode('/', array_map('rawurlencode', explode('/', $value)));
            }
            // @todo do we need to handle 'comp' or 'path' anywhere?
            if (!empty($pattern)) {
                $path = str_replace('{' . $param . ':' . $pattern . '}', "$value", $path);
            } else {
                $path = str_replace('{' . $param . '}', "$value", $path);
            }
            unset($subst[$param]);
        }
        return self::getUriForParams($subst, $prefix . $path);
    }
}
