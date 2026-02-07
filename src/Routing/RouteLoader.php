<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Routing;

//use SebLucas\Cops\Controller\CopsController;
use SebLucas\Cops\Input\Request;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route as SymfonyRoute;
use RuntimeException;

/**
 * Load routes from handlers with getRoutes()
 * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/Routing/Loader/ClosureLoader.php
 */
class RouteLoader extends Loader
{
    /**
     * @param \SebLucas\Cops\Routing\RouteCollection|null $routeCollection
     */
    public function __construct(private ?\SebLucas\Cops\Routing\RouteCollection $routeCollection = null) {}

    /**
     * Summary of load
     * @param mixed $resource not used here
     * @param string|null $type not used here
     * @return RouteCollection
     */
    public function load(mixed $resource, ?string $type = null): mixed
    {
        $routes = new RouteCollection();
        return $this->addRouteCollection($routes);
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return true;
    }

    /**
     * Summary of addRouteCollection
     * @param RouteCollection $routes
     * @throws \RuntimeException
     * @return RouteCollection
     */
    public function addRouteCollection($routes)
    {
        $seen = [];
        foreach ($this->routeCollection->all() as $name => $route) {
            [$path, $params, $methods, $options] = $route;
            // set route param in request once we find matching route
            $params[Request::ROUTE_PARAM] ??= $name;
            // Set the default controller for all COPS routes
            //$params['_controller'] = CopsController::class;
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
            if (!empty($seen[$name])) {
                throw new RuntimeException('Duplicate route name ' . $name . ' for ' . $path);
            }
            $seen[$name] = $path;
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
}
