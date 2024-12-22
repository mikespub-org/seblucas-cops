<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Routing;

use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

/**
 * Routing based on Symfony routing component (test)
 *
 * Matching URLs is similar to nikic/fast-route, but generating URLs requires known route name
 * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/Routing/Router.php
 */
class Routing implements RouterInterface
{
    public const HANDLER_PARAM = "_handler";
    public const MATCHER_CACHE_FILE = 'url_matching_routes.php';
    public const GENERATOR_CACHE_FILE = 'url_generating_routes.php';

    public ?string $cacheDir = null;
    public ?Router $router = null;

    public function __construct(?string $cacheDir = null)
    {
        // force cache generation
        $this->cacheDir = $cacheDir ?? __DIR__;
    }

    /**
     * Match path with optional method and context
     * @param string $path
     * @param ?string $method
     * @param ?RequestContext $context
     * @return ?array<mixed> array of path params or null if not found
     */
    public function match($path, $method = null, $context = null)
    {
        if (empty($path) || $path == '/') {
            return [];
        }
        // reset router context to start fresh
        $this->setContext($context);
        if (!empty($method) && $method != 'GET') {
            // set router context with method
            $this->getRouter()->getContext()->setMethod($method);
        }
        $matcher = $this->getRouter()->getMatcher();
        try {
            $attributes = $matcher->match($path);
        } catch (ResourceNotFoundException $e) {
            // ... 404 Not Found
            //http_response_code(404);
            //throw new Exception("Invalid path " . htmlspecialchars($path));
            return null;
        } catch (MethodNotAllowedException $e) {
            // ... 405 Method Not Allowed
            //http_response_code(405);
            //throw new Exception("Invalid method " . htmlspecialchars($method) . " for path " . htmlspecialchars($path));
            return null;
        }
        return $attributes;
    }

    /**
     * Generate URL path for route name and params
     * @param string $name
     * @param array<mixed> $params
     * @throws RouteNotFoundException|InvalidParameterException|MissingMandatoryParametersException
     * @return string
     */
    public function generate($name, $params)
    {
        $generator = $this->getRouter()->getGenerator();
        try {
            $url = $generator->generate($name, $params, UrlGeneratorInterface::ABSOLUTE_PATH);
            return $url;
        } catch (RouteNotFoundException $e) {
            error_log($e->getMessage());
            throw $e;
        } catch (InvalidParameterException $e) {
            error_log($e->getMessage());
            throw $e;
        } catch (MissingMandatoryParametersException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    /**
     * Summary of context - @todo
     * @param mixed $request
     * @return RequestContext
     */
    public function context($request)
    {
        $baseUrl = UriGenerator::absolute('');
        // @todo get scheme and host - see Symfony\Request::getSchemeAndHttpHost()
        //$context = new RequestContext('/index.php', 'GET', 'localhost', 'http', 80, 443, '/', '');
        $context = new RequestContext($baseUrl, $request->method(), 'localhost', 'http', 80, 443, $request->path, $request->query());
        //$context->fromRequest($request);
        return $context;
    }

    /**
     * Get Symfony router for handler routes (cached)
     * @param bool $refresh
     * @return Router
     */
    public function getRouter($refresh = false)
    {
        if ($refresh) {
            $this->resetCache();
        }
        if (isset($this->router)) {
            return $this->router;
        }
        $loader = new RouteLoader();
        $resource = null;
        $options = ['cache_dir' => $this->cacheDir];
        $context = null;

        $this->router = new Router($loader, $resource, $options, $context);
        return $this->router;
    }

    /**
     * Set router context or reset it
     * @param ?RequestContext $context
     * @return void
     */
    public function setContext($context = null)
    {
        $context ??= new RequestContext();
        $this->getRouter()->setContext($context);
    }

    /**
     * Reset cache files used by UrlMatcher and UrlGenerator
     * @return void
     */
    public function resetCache()
    {
        $this->router = null;
        if (empty($this->cacheDir)) {
            return;
        }
        $cacheFile = $this->cacheDir . '/' . self::MATCHER_CACHE_FILE;
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        $cacheFile = $this->cacheDir . '/' . self::GENERATOR_CACHE_FILE;
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Add multiple routes at once
     * @param array<string, array<mixed>> $routes Array of routes with [path, params, methods, options]
     * @return void
     */
    public function addRoutes(array $routes): void
    {
        // ...
    }

    /**
     * Add single route - mainly for testing
     * @param string|array<string> $methods HTTP methods (GET, POST etc.)
     * @param string $path Route pattern with optional {param} placeholders
     * @param array<mixed> $params Fixed parameters including handler
     * @param array<string, string|int|bool|float> $options Extra options including route name
     * @return void
     */
    public function addRoute(string|array $methods, string $path, array $params, array $options = []): void
    {
        // ...
    }
}
