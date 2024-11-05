<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
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
}
