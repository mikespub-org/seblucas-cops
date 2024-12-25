<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Routing\RouterInterface;
//use SebLucas\Cops\Routing\FastRouter;
use SebLucas\Cops\Routing\Routing;
use SebLucas\Cops\Routing\UriGenerator;

/**
 * Summary of RequestContext
 */
class RequestContext
{
    private Request $request;
    private HandlerManager $manager;
    private RouterInterface $router;
    /** @var array<mixed> */
    private ?array $matchParams = null;
    private ?BaseHandler $handler = null;
    private Config $config;
    private string $locale;

    public function __construct(Request $request, ?HandlerManager $manager = null, ?RouterInterface $router = null)
    {
        $this->request = $request;
        $this->manager = $manager ?? new HandlerManager();
        $this->router = $router ?? new Routing();  // new FastRouter();
        $this->config = new Config();
        $this->locale = $this->request->locale();
        $this->initializeContext();
    }

    protected function initializeContext(): void
    {
        $this->manager->setContext($this);
        // Load routes if not already cached
        UriGenerator::setLocale($this->locale);
    }

    /**
     * Summary of matchRequest
     * @return array<mixed>
     */
    public function matchRequest(): array
    {
        if (isset($this->matchParams)) {
            return $this->matchParams;
        }

        try {
            $path = $this->request->path();
            $method = $this->request->method();

            // Try to match the route
            $this->matchParams = $this->router->match($path, $method);
            if (!isset($this->matchParams)) {
                // this will call exit()
                //Response::sendError($this, "Invalid request path '$path'");
                error_log("COPS: Invalid request path '$path' from template " . $this->request->template());
                // delay reporting error until we're back in Framework
                $this->request->invalid = true;
                $this->matchParams = [];
            }

            // Enhance route with request context
            //$this->matchParams->setContext($this);
            // Update request with matched parameters
            $this->request->updateFromMatch($this->matchParams);

            return $this->matchParams;
        } catch (\Throwable $e) {
            // Return default route match for error handling
            return [
                Route::HANDLER_PARAM => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function resolveHandler(): BaseHandler
    {
        if ($this->handler) {
            return $this->handler;
        }

        // Get handler name based on request state
        $handlerName = $this->resolveHandlerName();

        try {
            $handlerClass = $this->manager->getHandlerClass($handlerName);
            $this->handler = $this->createHandler($handlerClass);
            return $this->handler;
        } catch (\RuntimeException $e) {
            // Fallback to error handler
            //return $this->createErrorHandler($e);
            $errorHandlerClass = $this->manager->getHandlerClass('error');
            $this->handler = $this->createHandler($errorHandlerClass);
            return $this->handler;
        }
    }

    protected function resolveHandlerName(): string
    {
        // 1. Check if request is invalid
        if ($this->request->invalid) {
            return 'error';
        }

        // 2. Check matched route handler
        if ($this->matchParams && !empty($this->matchParams[Route::HANDLER_PARAM])) {
            return $this->matchParams[Route::HANDLER_PARAM];
        }

        // 3. Check request parameters
        if (!empty($this->request->urlParams[Route::HANDLER_PARAM])) {
            $name = $this->request->urlParams[Route::HANDLER_PARAM];
            // return $this->normalizeHandlerName($name);
            return $name;
        }

        // 4. Content negotiation
        if ($this->request->isJson() || $this->request->isAjax()) {
            return 'json';
        }

        // 5. Default handler
        return 'html';
    }

    protected function normalizeHandlerName(string $name): string
    {
        // Convert handler class name to short name if needed - @todo not used here
        if (str_contains($name, '\\')) {
            $parts = explode('\\', $name);
            $className = end($parts);
            return str_replace('Handler', '', strtolower($className));
        }
        return $name;
    }

    /**
     * Summary of createHandler
     * @param class-string<BaseHandler> $handlerClass
     * @return BaseHandler
     */
    protected function createHandler(string $handlerClass): BaseHandler
    {
        // Create handler instance with context
        $handler = new $handlerClass($this);

        return $handler;
    }

    /**
     * Summary of generateUrl
     * @param string $name
     * @param array<mixed> $params
     * @return string
     */
    public function generateUrl(string $name, array $params = []): string
    {
        // Merge current route parameters with new ones
        //if ($this->matchParams) {
        //    $params = array_merge($this->matchParams, $params);
        //}

        try {
            return $this->router->generate($name, $params);
        } catch (\Throwable $e) {
            // Fallback to query string URL
            return $this->generateQueryStringUrl($params);
        }
    }

    /**
     * Summary of generateQueryStringUrl
     * @param array<mixed> $params
     * @return string
     */
    protected function generateQueryStringUrl(array $params): string
    {
        //$baseUrl = $this->request->baseUrl();
        $baseUrl = UriGenerator::absolute('');
        $queryString = http_build_query($params);
        return $baseUrl . ($queryString ? '?' . $queryString : '');
    }

    // Accessor methods
    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getHandler(): BaseHandler
    {
        return $this->handler;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->manager;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }
}
