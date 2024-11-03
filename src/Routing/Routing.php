<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Routing;

use SebLucas\Cops\Input\Route;
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

    public ?string $cacheDir;
    public ?RequestContext $context;
    public ?Router $router;

    public function __construct(?string $cacheDir = null, ?RequestContext $context = null)
    {
        // force cache generation
        $this->cacheDir = $cacheDir ?? __DIR__;
        $this->context = $context;
    }

    /**
     * Get Symfony router for handler routes (cached)
     * @param ?RequestContext $context
     * @param bool $refresh
     * @return Router
     */
    public function getRouter($context = null, $refresh = false)
    {
        if ($refresh) {
            $this->resetCache();
        }
        if (isset($this->router)) {
            if (isset($context)) {
                $this->router->setContext($context);
            }
            return $this->router;
        }
        $loader = new RouteLoader();
        $resource = null;
        $options = ['cache_dir' => $this->cacheDir];
        $context ??= $this->context;

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
     * Summary of context - @todo
     * @param mixed $request
     * @return RequestContext
     */
    public function context($request)
    {
        $handler = $request->getHandler();
        $endpoint = Route::endpoint($handler);
        $baseUrl = Route::base() . $endpoint;
        // @todo get scheme and host - see Symfony\Request::getSchemeAndHttpHost()
        //$context = new RequestContext('/index.php', 'GET', 'localhost', 'http', 80, 443, '/', '');
        $context = new RequestContext($baseUrl, $request->method(), 'localhost', 'http', 80, 443, $request->path, $request->query());
        //$context->fromRequest($request);
        return $context;
    }

    /**
     * Match path with optional method
     * @param string $path
     * @param ?string $method
     * @return ?array<mixed> array of query params or null if not found
     */
    public function match($path, $method = null)
    {
        // reset router context to start fresh
        $this->setContext();
        if (!empty($method) && $method != 'GET') {
            // set router context with method
            $this->getRouter()->getContext()->setMethod($method);
        }
        $matcher = $this->getRouter()->getMatcher();
        try {
            $attributes = $matcher->match($path);
        } catch (ResourceNotFoundException $e) {
            // ...
            throw $e;
        } catch (MethodNotAllowedException $e) {
            // ...
            throw $e;
        }
        return $attributes;
    }

    /**
     * Generate URL path for route name and params
     * @param string $name
     * @param array<mixed> $params
     * @return string|null
     */
    public function generate($name, $params)
    {
        $generator = $this->getRouter()->getGenerator();
        try {
            $url = $generator->generate($name, $params, UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (RouteNotFoundException $e) {
            // ...
            throw $e;
        } catch (InvalidParameterException $e) {
            // ...
            throw $e;
        } catch (MissingMandatoryParametersException $e) {
            // ...
            throw $e;
        }
        return $url;
    }
}
