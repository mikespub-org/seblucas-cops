<?php

namespace SebLucas\Cops\Framework\Adapter;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Routing\Router as LaravelRouter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response as CopsResponse;
use SebLucas\Cops\Routing\RouterInterface;

/**
 * Framework adapter for Laravel Framework
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LaravelAdapter implements AdapterInterface
{
    protected LaravelRouter $laravelRouter;

    public function __construct(protected Container $container)
    {
        $this->laravelRouter = $container->make('router');
    }

    public function getName(): string
    {
        return 'laravel';
    }

    public function handleRequest(RequestContext $context): CopsResponse
    {
        // This method is not applicable for LaravelAdapter. The Laravel Kernel's
        // handle() method should be the entry point.
        throw new \LogicException('LaravelAdapter does not handle requests directly. The Laravel Kernel should be run.');
    }

    public function getRouter(): RouterInterface
    {
        return $this->container->get(RouterInterface::class);
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->container->get(HandlerManager::class);
    }

    public function addMiddleware(string $middlewareClass): self
    {
        // Laravel middleware has a different signature. A full implementation
        // would require a more complex bridge. This is a placeholder.
        return $this;
    }

    public function registerRoutes(): void
    {
        $copsManager = $this->getHandlerManager();
        $copsRoutes = $copsManager->getRoutes();
        $copsRouter = $this->getRouter();

        foreach ($copsRoutes as $name => $routeConfig) {
            [$path, $defaults] = $routeConfig;
            $methods = $routeConfig[2] ?? ['GET'];

            // The path needs to be adjusted for Laravel's router
            $laravelPath = ltrim($path, '/');

            $this->laravelRouter->addRoute($methods, $laravelPath, function (LaravelRequest $request) use ($copsManager, $copsRouter, $defaults): LaravelResponse {
                // 1. Convert Laravel Request to COPS Request
                $copsRequest = new CopsRequest();
                $copsRequest->setPath($request->getPathInfo());
                $copsRequest->urlParams = array_merge(
                    $defaults,
                    $request->route()->parameters(),
                    $request->query(),
                );

                // We need to set a context on the handler manager for it to create a handler
                $context = new RequestContext($copsRequest, $copsManager, $copsRouter);
                $copsManager->setContext($context);

                // 2. Resolve and handle the request using COPS components
                $handlerName = $defaults['_handler'] ?? 'html';
                $handler = $copsManager->createHandler($handlerName);
                $copsResponse = $handler->handle($copsRequest);

                // 3. Convert COPS Response to Laravel Response
                return new LaravelResponse(
                    $copsResponse->getContent(),
                    $copsResponse->getStatusCode(),
                    $copsResponse->getHeaders(),
                );
            })->name($name);
        }
    }
}
