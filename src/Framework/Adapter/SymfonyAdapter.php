<?php

namespace SebLucas\Cops\Framework\Adapter;

use Psr\Container\ContainerInterface;
use SebLucas\Cops\Framework\Controller\CopsController;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Routing\RouterInterface;
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
        $routeCollection = $this->getRouteCollection();

        // Add the new collection to the main router
        $symfonyRouter->getRouteCollection()->addCollection($routeCollection);
    }

    /**
     * Get all COPS routes as a Symfony RouteCollection.
     *
     * This method is intended to be used by a dynamic route loader in a Symfony application,
     * as described in the framework integration documentation.
     *
     * @return RouteCollection
     */
    public function getRouteCollection(): RouteCollection
    {
        $collection = new RouteCollection();
        $copsManager = $this->getHandlerManager();
        $copsRoutes = $copsManager->getRoutes();

        foreach ($copsRoutes as $name => $routeConfig) {
            [$path, $defaults] = $routeConfig;
            $methods = $routeConfig[2] ?? ['GET'];

            // Set the generic CopsController for all routes
            $defaults['_controller'] = CopsController::class;

            $route = new Route(
                $path,
                $defaults,
                [], // requirements are parsed from path
                [], // options
                '', // host
                [], // schemes
                $methods
            );
            $collection->add($name, $route);
        }

        return $collection;
    }
}
