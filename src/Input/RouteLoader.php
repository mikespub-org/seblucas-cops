<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Exception;

/**
 * Summary of RouteLoader
 * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/Routing/Loader/ClosureLoader.php
 */
class RouteLoader extends Loader
{
    /**
     * Summary of load
     * @param mixed $resource
     * @param string|null $type
     * @return RouteCollection
     */
    public function load(mixed $resource, string|null $type = null): mixed
    {
        $routes = new RouteCollection();
        return static::addRouteCollection($routes);
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
        foreach (Route::getRoutes() as $path => $queryParams) {
            [$path, $requirements] = static::parsePath($path);
            $route = new SymfonyRoute($path, $queryParams);
            if (!empty($requirements)) {
                $route->setRequirements($requirements);
            }
            $name = static::getPathName($path);
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
    public static function parsePath($path)
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
