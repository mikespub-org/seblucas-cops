<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Routing;

use SebLucas\Cops\Input\Route;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Exception;

/**
 * Load routes from handlers with getRoutes()
 * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/Routing/Loader/ClosureLoader.php
 */
class RouteLoader extends Loader
{
    /**
     * Summary of load
     * @param mixed $resource not used here
     * @param string|null $type not used here
     * @return RouteCollection
     */
    public function load(mixed $resource, string|null $type = null): mixed
    {
        $routes = new RouteCollection();
        return self::addRouteCollection($routes);
    }

    public function supports(mixed $resource, string|null $type = null): bool
    {
        return true;
    }

    /**
     * Summary of addRouteCollection
     * @param RouteCollection $routes
     * @return RouteCollection
     */
    public static function addRouteCollection($routes)
    {
        $seen = [];
        foreach (Route::getRoutes() as $name => $route) {
            [$path, $params, $methods, $options] = $route;
            // set route param in request once we find matching route
            $params[Route::ROUTE_PARAM] ??= $name;
            [$path, $requirements] = self::getPathRequirements($path);
            // use the 'defaults' to store any fixed params here
            $route = new SymfonyRoute($path, $params);
            if (!empty($requirements)) {
                $route->setRequirements($requirements);
            }
            if (!empty($methods)) {
                $route->setMethods($methods);
            }
            // pass along extra options for Symfony Route - @todo
            if (!empty($options)) {
                $route->setOptions($options);
            }
            //$name = self::getPathName($path);
            if (!empty($seen[$name])) {
                throw new Exception('Duplicate route name ' . $name . ' for ' . $path);
            }
            $seen[$name] = $path;
            // @todo simplify if only one path for handler, e.g. calres-db-alg-digest
            //echo "'$name' => ['" . $route->getPath() . "', " . json_encode($route->getDefaults()) . ", " . json_encode($route->getRequirements()) . "],\n";
            $routes->add($name, $route);
        }
        return $routes;
    }

    /**
     * Check path params + extract custom patterns - see nikic/fast-route
     * This will convert
     *   [nikic/fast-route] $path = '/books/{id:\d+}'
     * into
     *   [symfony/routing] [$path, $requirements] = ['/books/{id}', ['id' => '\d+']]
     * @param string $path
     * @return array{0: string, 1: array<mixed>}
     */
    public static function getPathRequirements($path)
    {
        $requirements = [];
        $found = [];
        if (!preg_match_all("~\{(\w+(|:[^}]+))\}~", $path, $found)) {
            return [$path, $requirements];
        }
        foreach ($found[1] as $param) {
            $pattern = '';
            if (str_contains($param, ':')) {
                [$param, $pattern] = explode(':', $param);
            }
            if (!empty($pattern)) {
                $requirements[$param] = $pattern;
                $path = str_replace('{' . $param . ':' . $pattern . '}', '{' . $param . '}', $path);
            }
        }
        return [$path, $requirements];
    }

    /**
     * Summary of getPathName
     * @param string $path
     * @return string
     */
    public static function getPathName($path)
    {
        $name = ltrim($path, '/');
        $replace = [
            '/' => '-',
            '{' => '',
            '}' => '',
            '.jpg' => '',
            //'-ignore' => '',
        ];
        return str_replace(array_keys($replace), array_values($replace), $name);
    }
}
