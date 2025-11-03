<?php

namespace SebLucas\Cops\Framework;

use SebLucas\Cops\Framework\Adapter\AdapterInterface;
use SebLucas\Cops\Framework\Adapter\CustomAdapter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Handlers\QueueBasedHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Middleware\AuthMiddleware;
use SebLucas\Cops\Routing\RouteCollection;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;

/**
 * COPS framework implementation - @todo
 */
class FrameworkTodo
{
    protected static ?self $instance = null;
    /** @var array<string, class-string> */
    protected array $middlewares = [];
    protected HandlerManager $manager;
    protected RouterInterface $router;
    protected CustomAdapter $adapter;
    protected RequestContext $context;

    public function __construct(?HandlerManager $manager = null, ?RouteCollection $routeCollection = null)
    {
        // 1. Build the service graph ("Poor Man's DI")
        $this->manager = $manager ?? new HandlerManager();

        // Create the processed RouteCollection from the HandlerManager
        $routeCollection ??= new RouteCollection($this->manager);

        // Create the router and pass it the collection
        $this->router = new Routing($routeCollection);

        $this->adapter = new CustomAdapter($this);
        // Register routes via adapter before creating context
        //$this->adapter->registerRoutes();
        $request = $this->createRequest();
        $this->context = new RequestContext(
            $request,
            $this->manager,
            $this->router,
        );

        // Add authentication middleware with updateConfig()
        $this->addMiddleware(AuthMiddleware::class);
    }

    public function getContext(?Request $request = null): RequestContext
    {
        if (isset($request)) {
            $this->context = new RequestContext($request, $this->manager, $this->router);
        }
        return $this->context;
    }

    public function handleRequest(?RequestContext $context = null): Response
    {
        $context ??= $this->context;
        try {
            //$this->manager->setContext($context);
            // Match route and get handler
            $params = $context->matchRequest();
            $handler = $context->resolveHandler();

            // Apply middleware - incl. AuthMiddleware with updateConfig()
            if (!empty($this->middlewares)) {
                $queue = new QueueBasedHandler($context, $handler);
                foreach ($this->middlewares as $middlewareClass) {
                    $queue->add(new $middlewareClass());
                }
                $handler = $queue;
            }

            // Handle request
            return $handler->handle($context->getRequest());
        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    protected function createRequest(): Request
    {
        if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REDIRECT_PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'];
        }
        return new Request();
    }

    protected function handleError(\Throwable $e): Response
    {
        error_log("COPS error: " . $e->getMessage());
        try {
            $handler = $this->manager->createHandler('error');
            return $handler->handle(new Request());
        } catch (\Exception $e2) {
            http_response_code(500);
            $response = new Response();
            return $response->setStatusCode(500)->setContent("Internal Server Error");
        }
    }

    /**
     * Summary of addMiddleware
     * @param class-string $middlewareClass
     */
    public function addMiddleware(string $middlewareClass): self
    {
        $this->middlewares[] = $middlewareClass;
        return $this;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->manager;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public static function getInstance(bool $reset = false): self
    {
        if (!self::$instance || $reset) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function run(bool $reset = false): void
    {
        // Handle request
        $response = self::getInstance($reset)->handleRequest();
        // Send response
        if ($response instanceof Response) {
            $response->send();
        }
    }
}
