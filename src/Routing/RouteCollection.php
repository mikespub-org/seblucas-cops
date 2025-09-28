<?php

namespace SebLucas\Cops\Routing;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;

/**
 * A collection of route definitions.
 */
class RouteCollection
{
    /** @var array<string, array<mixed>> */
    private array $routes = [];

    /** @var array<string, string> */
    private array $staticPaths = [];

    /**
     * @param HandlerManager|null $handlerManager
     */
    public function __construct(?HandlerManager $handlerManager = null)
    {
        if (isset($handlerManager)) {
            foreach ($handlerManager->getHandlers() as $handlerClass) {
                if (method_exists($handlerClass, 'getRoutes')) {
                    $this->addRoutes($handlerClass::getRoutes(), $handlerClass);
                }
            }
        }
    }

    /**
     * Create a RouteCollection from a pre-defined array of routes.
     * Useful for testing or caching.
     *
     * @param array<string, array<mixed>> $routes
     */
    public static function fromArray(array $routes): self
    {
        $collection = new self();
        if (!empty($routes)) {
            $collection->addRoutes($routes);
        }
        return $collection;
    }

    /**
     * Add a batch of routes.
     *
     * @param array<string, array<mixed>> $routes
     * @param class-string<BaseHandler>|null $handler
     * @return void
     */
    public function addRoutes(array $routes, ?string $handler = null): void
    {
        foreach ($routes as $name => $route) {
            // Add params, methods and options if needed
            array_push($route, [], [], []);
            [$path, $params, $methods, $options] = $route;

            // Add static paths to the lookup table
            if (!str_contains($path, '{')) {
                $this->staticPaths[$path] = $name;
            }

            // Add ["_handler" => $handler] to params if provided
            if (isset($handler) && $handler::HANDLER !== 'html') {
                $params[Route::HANDLER_PARAM] ??= $handler;
            }

            // Add ["_route" => $name] to params
            $params[Route::ROUTE_PARAM] ??= $name;

            // Add default GET method
            if (empty($methods)) {
                $methods[] = 'GET';
            }

            if (isset($this->routes[$name])) {
                throw new \RuntimeException('Duplicate route name ' . $name . ' for ' . $handler);
            }

            $this->routes[$name] = [$path, $params, $methods, $options];
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function all(): array
    {
        return $this->routes;
    }

    public function count(): int
    {
        return count($this->routes);
    }

    /**
     * Check if a static path exists.
     */
    public function hasStatic(string $path): bool
    {
        return array_key_exists($path, $this->staticPaths);
    }

    /**
     * Get route params for a static path.
     *
     * @return array<mixed>|null
     */
    public function getStatic(string $path): ?array
    {
        if (!$this->hasStatic($path)) {
            return null;
        }
        $name = $this->staticPaths[$path];
        return $this->routes[$name][1] ?? null;
    }
}
