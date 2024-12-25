<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Routing;

use FastRoute\ConfigureRoutes;
use FastRoute\Dispatcher;
use FastRoute\FastRoute;
use FastRoute\GenerateUri;
use FastRoute\GenerateUri\UriCouldNotBeGenerated;
use SebLucas\Cops\Input\Route;
use Throwable;

/**
 * Routing based on nikic FastRoute
 */
class FastRouter implements RouterInterface
{
    public const FASTROUTE_CACHE_FILE = 'url_fastroute_cache.php';

    public ?string $cacheDir = null;
    public ?FastRoute $router = null;
    public int $routeCount = 0;
    protected ?Dispatcher $dispatcher = null;
    protected ?GenerateUri $uriGenerator = null;
    /** @var array<string, array<mixed>> */
    protected array $routes = [];

    public function __construct(?string $cacheDir = null)
    {
        // force cache generation
        $this->cacheDir = $cacheDir ?? __DIR__;
    }

    /**
     * Match path with optional method
     * @param string $path
     * @param ?string $method
     * @return ?array<mixed> array of path params or null if not found
     */
    public function match($path, $method = null)
    {
        if (empty($path) || $path == '/') {
            return [];
        }
        // match pattern
        $fixed = [];
        $params = [];
        $method ??= 'GET';

        $dispatcher = $this->getDispatcher();
        $routeInfo = $dispatcher->dispatch($method, $path);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                //http_response_code(404);
                //throw new Exception("Invalid path " . htmlspecialchars($path));
                return null;
            case Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                //header('Allow: ' . implode(', ', $allowedMethods));
                //http_response_code(405);
                //throw new Exception("Invalid method " . htmlspecialchars($method) . " for path " . htmlspecialchars($path));
                return null;
        }
        // use the 'handler' to store any fixed params here, with _handler and _route
        $fixed = $routeInfo[1];
        // path params found by dispatcher
        $params = $routeInfo[2];
        // extra options defined in route (_name) or set by route collector (_route = regex path)
        $extra = $routeInfo->extraParameters;
        // for normal routes, put fixed params at the start
        $params = array_merge($fixed, $params);
        // set _route param in request once we find matching route - FastRoute uses _name internally
        if (isset($extra[ConfigureRoutes::ROUTE_NAME]) && !isset($params[Route::ROUTE_PARAM])) {
            $params[Route::ROUTE_PARAM] = $extra[ConfigureRoutes::ROUTE_NAME];
        }
        unset($params['ignore']);
        return $params;
    }

    /**
     * Generate uri with FastRoute - @todo some issues left to deal with ;-)
     * @param string $name
     * @param array<mixed> $params
     * @throws UriCouldNotBeGenerated|Throwable
     * @return string
     */
    public function generate($name, $params)
    {
        $generator = $this->getUriGenerator();
        $params = array_map("strval", $params);
        // @todo slugify & rawurlencode title & author
        // @todo add fixed params!?
        // @todo add remaining params in query string
        try {
            return $generator->forRoute($name, $params);
        } catch (UriCouldNotBeGenerated $e) {
            error_log($e->getMessage());
            throw $e;
        } catch (Throwable $e) {
            // preg_match() issue like TypeError if param wasn't a string
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Add multiple routes at once
     * @param array<string, array<mixed>> $routes Array of routes with [path, params, methods, options]
     * @return void
     */
    public function addRoutes(array $routes): void
    {
        // Store all routes - no need to reset cache
        $this->routes = array_merge($this->routes, $routes);
    }

    /**
     * Add single route - mainly for testing
     * @param string|array<string> $methods
     * @param string $path
     * @param array<mixed> $params
     * @param array<string, string|int|bool|float> $options
     * @return void
     */
    public function addRoute(string|array $methods, string $path, array $params, array $options = []): void
    {
        $name = $options[ConfigureRoutes::ROUTE_NAME] ?? ($params[Route::ROUTE_PARAM] ?? '');
        if (empty($name)) {
            $name = 'route_' . md5($path . (is_array($methods) ? implode('', $methods) : $methods));
            $params[Route::ROUTE_PARAM] = $name;
            $options[ConfigureRoutes::ROUTE_NAME] = $name;
        }
        $this->routes[$name] = [$path, $params, is_array($methods) ? $methods : [$methods], $options];
    }

    /**
     * Get FastRoute router for handler routes (cached)
     * @param bool $refresh
     * @return FastRoute
     */
    public function getRouter($refresh = false)
    {
        if ($refresh) {
            $this->resetCache();
        }
        if (isset($this->router)) {
            return $this->router;
        }
        if (empty($this->cacheDir)) {
            $cacheKey = self::FASTROUTE_CACHE_FILE;
            $this->router = FastRoute::recommendedSettings($this->addRouteCollection(...), $cacheKey);
            $this->router = $this->router->disableCache();
        } else {
            $cacheKey = $this->cacheDir . '/' . self::FASTROUTE_CACHE_FILE;
            $this->router = FastRoute::recommendedSettings($this->addRouteCollection(...), $cacheKey);
        }
        return $this->router;
    }

    /**
     * Summary of getDispatcher
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        $this->dispatcher ??= $this->getRouter()->dispatcher();
        return $this->dispatcher;
    }

    /**
     * Summary of getUriGenerator
     * @return GenerateUri
     */
    public function getUriGenerator()
    {
        $this->uriGenerator ??= $this->getRouter()->uriGenerator();
        return $this->uriGenerator;
    }

    /**
     * Reset cache file used by FastRoute
     * @return void
     */
    public function resetCache()
    {
        $this->router = null;
        $this->routeCount = 0;
        if (empty($this->cacheDir)) {
            return;
        }
        $cacheFile = $this->cacheDir . '/' . self::FASTROUTE_CACHE_FILE;
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Summary of addRouteCollection
     * @see \FastRoute\RouteCollector
     * //@phpstan-import-type ExtraParameters from \FastRoute\DataGenerator
     * @phpstan-type ExtraParameters array<string, string|int|bool|float>
     * @param ConfigureRoutes $r
     * @return void
     */
    public function addRouteCollection($r)
    {
        foreach (Route::getRoutes() as $name => $route) {
            /** @var array<string, string|int|bool|float> $options */
            [$path, $params, $methods, $options] = $route;
            // set route param in request once we find matching route
            $params[Route::ROUTE_PARAM] ??= $name;
            // set route name in extra options for uri generator - FastRoute uses _name internally
            $options[ConfigureRoutes::ROUTE_NAME] ??= $name;
            //$handler = $params[self::HANDLER_PARAM] ?? '';
            //$r->addRoute($methods, $path, $handler, $params);
            // use the 'handler' to store any fixed params here, and pass along extra options for FastRoute
            $r->addRoute($methods, $path, $params, $options);
            $this->routeCount++;
        }

        // Then add any routes from adapter
        if (!empty($this->routes)) {
            foreach ($this->routes as $name => $route) {
                [$path, $params, $methods, $options] = $route;
                // set route param in request once we find matching route
                $params[Route::ROUTE_PARAM] ??= $name;
                // set route name in extra options for uri generator - FastRoute uses _name internally
                $options[ConfigureRoutes::ROUTE_NAME] ??= $name;
                // use the 'handler' to store any fixed params here, and pass along extra options for FastRoute
                $r->addRoute($methods, $path, $params, $options);
                $this->routeCount++;
            }
        }
    }
}
