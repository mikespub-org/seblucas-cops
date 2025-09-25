<?php

namespace SebLucas\Cops\Framework\Adapter;

use Psr\Container\ContainerInterface;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response as CopsResponse;
use SebLucas\Cops\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Framework adapter for Symfony Framework
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SymfonyAdapter implements AdapterInterface
{
    protected ContainerInterface $container;

    public function __construct(protected KernelInterface $kernel)
    {
        $this->container = $kernel->getContainer();
    }

    public function getName(): string
    {
        return 'symfony';
    }

    public function handleRequest(RequestContext $context): CopsResponse
    {
        // This method is not applicable for SymfonyAdapter. The Symfony Kernel's
        // handle() method should be the entry point.
        throw new \LogicException('SymfonyAdapter does not handle requests directly. The Symfony Kernel should be run.');
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
        // Symfony's middleware are typically Kernel event listeners/subscribers.
        // A full implementation would require creating a subscriber that wraps
        // the COPS middleware and bridges the Request/Response objects.
        // This is a placeholder to satisfy the interface.
        // e.g., $this->container->get('event_dispatcher')->addSubscriber(new CopsMiddlewareSubscriber($middlewareClass));
        return $this;
    }

    public function registerRoutes(): void
    {
        /** @var \Symfony\Component\Routing\RouterInterface $symfonyRouter */
        $symfonyRouter = $this->container->get('router');
        $routeCollection = new RouteCollection();

        $copsManager = $this->getHandlerManager();
        $copsRoutes = $copsManager->getRoutes();
        $copsRouter = $this->getRouter();

        foreach ($copsRoutes as $name => $routeConfig) {
            [$path, $defaults] = $routeConfig;
            $methods = $routeConfig[2] ?? ['GET'];

            // Define the controller as a callable that bridges to COPS
            $controller = function (SymfonyRequest $request) use ($copsManager, $copsRouter, $defaults): SymfonyResponse {
                // 1. Convert Symfony Request to COPS Request
                $copsRequest = new CopsRequest();
                $copsRequest->setPath($request->getPathInfo());
                $copsRequest->urlParams = array_merge(
                    $defaults,
                    $request->attributes->get('_route_params', []),
                    $request->query->all()
                );

                // We need to set a context on the handler manager for it to create a handler
                $context = new RequestContext($copsRequest, $copsManager, $copsRouter);
                $copsManager->setContext($context);

                // 2. Resolve and handle the request using COPS components
                $handlerName = $defaults['_handler'] ?? 'html';
                $handler = $copsManager->createHandler($handlerName);
                $copsResponse = $handler->handle($copsRequest);

                // 3. Convert COPS Response to Symfony Response
                return new SymfonyResponse(
                    $copsResponse->getContent(),
                    $copsResponse->getStatusCode(),
                    $copsResponse->getHeaders()
                );
            };

            $defaults['_controller'] = $controller;

            $route = new Route($path, $defaults, [], [], '', [], $methods);
            $routeCollection->add($name, $route);
        }

        // Add the new collection to the main router
        $symfonyRouter->getRouteCollection()->addCollection($routeCollection);
    }
}
