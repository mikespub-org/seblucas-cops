<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Routing;

/**
 * Common router interface for FastRouter and Symfony Routing
 */
interface RouterInterface
{
    /**
     * Match path with optional method
     * @param string $path
     * @param ?string $method
     * @return ?array<mixed> array of path params or null if not found
     */
    public function match($path, $method = null);

    /**
     * Generate URL path for route name and params
     * @param string $name
     * @param array<mixed> $params
     * @throws \Throwable
     * @return string
     */
    public function generate($name, $params);

    /**
     * Get internal router for handler routes (cached)
     * @param bool $refresh
     * @return mixed
     */
    public function getRouter($refresh = false);

    /**
     * Add multiple routes at once
     * @param array<string, array<mixed>> $routes Array of routes with [path, params, methods, options]
     * @return void
     */
    public function addRoutes(array $routes): void;

    /**
     * Add single route - mainly for testing
     * @param string|array<string> $methods HTTP methods (GET, POST etc.)
     * @param string $path Route pattern with optional {param} placeholders
     * @param array<mixed> $params Fixed parameters including handler
     * @param array<string, string|int|bool|float> $options Extra options including route name
     * @return void
     */
    public function addRoute(string|array $methods, string $path, array $params, array $options = []): void;

    /**
     * Summary of getRouteCollection
     * @return RouteCollection|null
     */
    public function getRouteCollection();
}
