<?php

namespace SebLucas\Cops\Framework\Adapter;

use SebLucas\Cops\Framework\FrameworkTodo;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\RouterInterface;

/**
 * COPS custom adapter with core framework logic - @todo
 */
class CustomAdapter implements AdapterInterface
{
    /** @var array<string, class-string> */
    protected array $middlewares = [];

    public function __construct(
        protected readonly FrameworkTodo $framework,
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
        $manager = $this->getHandlerManager();
        foreach ($manager->getHandlers() as $handlerClass) {
            $routes = array_merge($routes, $manager->addHandlerRoutes($handlerClass));
        }
        // Add them all at once to router - @todo this doesn't do anything
        if (!empty($routes)) {
            $this->getRouter()->addRoutes($routes);
        }
    }

    public function getRouter(): RouterInterface
    {
        return $this->framework->getRouter();
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->framework->getHandlerManager();
    }

    public function addMiddleware(string $middlewareClass): self
    {
        $this->framework->addMiddleware($middlewareClass);
        return $this;
    }
}
