<?php

namespace SebLucas\Cops\Framework\Adapter;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;
//use SebLucas\Cops\Routing\FastRouter;
use SebLucas\Cops\Routing\Routing;
use SebLucas\Cops\Handlers\QueueBasedHandler;

/**
 * COPS custom adapter with core framework logic - @todo
 */
class CustomAdapter implements AdapterInterface
{
    /** @var array<string, class-string> */
    protected array $middlewares = [];

    public function __construct(
        protected readonly RouterInterface $router = new Routing(),  // new FastRouter(),
        protected readonly HandlerManager $manager = new HandlerManager()
    ) {}

    public function getName(): string
    {
        return 'custom';
    }

    public function registerRoutes(): void
    {
        // Reset routes for tests
        Route::setRoutes();
        // Collect all routes first
        $routes = [];
        foreach ($this->manager->getHandlers() as $handlerClass) {
            $routes = array_merge($routes, $this->manager->addHandlerRoutes($handlerClass));
        }
        // Add them all at once to router
        if (!empty($routes)) {
            $this->router->addRoutes($routes);
        }
    }

    public function handleRequest(RequestContext $context): Response
    {
        //$this->manager->setContext($context);
        // Match route and get handler
        $params = $context->matchRequest();
        $handler = $context->resolveHandler();

        // Apply middleware if configured
        if (!empty($this->middlewares)) {
            $queue = new QueueBasedHandler($context, $handler);
            foreach ($this->middlewares as $middlewareClass) {
                $queue->add(new $middlewareClass());
            }
            $handler = $queue;
        }

        // Handle request
        return $handler->handle($context->getRequest());
    }

    public function createErrorHandler(): mixed
    {
        return $this->manager->createHandler('error');
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->manager;
    }

    public function addMiddleware(string $middlewareClass): self
    {
        $this->middlewares[] = $middlewareClass;
        return $this;
    }
}
