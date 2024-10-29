<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Exception;

/**
 * Summary of Routing
 * @see https://github.com/symfony/symfony/blob/7.1/src/Symfony/Component/Routing/Router.php
 */
class Routing
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
     * Summary of getRouter
     * @param ?RequestContext $context
     * @return Router
     */
    public function getRouter($context = null)
    {
        if (isset($this->router)) {
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
     * Summary of context
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
     * Summary of match
     * @param string $path
     * @return array<mixed>
     */
    public function match($path)
    {
        $matcher = $this->getRouter()->getMatcher();
        try {
            $attributes = $matcher->match($path);
        } catch (ResourceNotFoundException $e) {
            // ...
            throw $e;
        } catch (Exception $e) {
            // ...
            throw $e;
        }
        return $attributes;
    }

    /**
     * Summary of generate
     * @param string $name
     * @param array<mixed> $params
     * @return string
     */
    public function generate($name, $params)
    {
        $generator = $this->getRouter()->getGenerator();
        try {
            $url = $generator->generate($name, $params, UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (RouteNotFoundException $e) {
            // ...
            throw $e;
        }
        return $url;
    }
}
