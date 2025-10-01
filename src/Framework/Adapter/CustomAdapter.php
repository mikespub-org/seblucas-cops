<?php

namespace SebLucas\Cops\Framework\Adapter;

use SebLucas\Cops\Framework\FrameworkTodo;
use SebLucas\Cops\Handlers\HandlerManager;
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
        // In the standalone FrameworkTodo, routes are already processed and injected
        // into the router during construction. This method is only required for
        // external framework adapters that need to register routes at boot time.
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
