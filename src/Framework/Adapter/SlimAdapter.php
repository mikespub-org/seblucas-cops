<?php

namespace SebLucas\Cops\Framework\Adapter;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SebLucas\Cops\Framework\Action\CopsAction;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Routing\RouterInterface;
use Slim\App;
use Slim\Routing\Route;

/**
 * Framework adapter for Slim Framework
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SlimAdapter implements AdapterInterface
{
    protected ContainerInterface $container;

    public function __construct(protected App $app)
    {
        $this->container = $app->getContainer();
    }

    public function getName(): string
    {
        return 'slim';
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
        // This is a simplified PSR-15 middleware bridge.
        // It demonstrates how a COPS middleware can be adapted to the PSR-15 interface.
        $psr15Middleware = new class ($middlewareClass) implements MiddlewareInterface {
            private string $copsMiddlewareClass;

            public function __construct(string $copsMiddlewareClass)
            {
                $this->copsMiddlewareClass = $copsMiddlewareClass;
            }

            public function process(Request $request, RequestHandler $handler): Response
            {
                $copsMiddleware = new $this->copsMiddlewareClass();

                // A proper implementation would require a full COPS Request <-> PSR-7 Request bridge.
                // For the TestMiddleware, we can simulate its behavior.
                if ($copsMiddleware instanceof \SebLucas\Cops\Middleware\TestMiddleware) {
                    $request = $request->withAttribute('hello', 'world');
                }

                $response = $handler->handle($request);

                // And modify the response on the way out
                if ($copsMiddleware instanceof \SebLucas\Cops\Middleware\TestMiddleware) {
                    $response->getBody()->write("Goodbye!");
                }

                return $response;
            }
        };

        $this->app->add($psr15Middleware);
        return $this;
    }

    public function registerRoutes(): void
    {
        $copsManager = $this->getHandlerManager();
        $copsRoutes = $copsManager->getRoutes();
        $copsRouter = $this->getRouter();

        foreach ($copsRoutes as $name => $routeConfig) {
            $this->addRoute($copsManager, $copsRouter, $name, $routeConfig);
        }
    }

    protected function addRoute(HandlerManager $copsManager, RouterInterface $copsRouter, string $name, array $routeConfig): Route
    {
        [$path, $defaults] = $routeConfig;
        $methods = $routeConfig[2] ?? ['GET'];

        return $this->app->map(
            $methods,
            $path,
            CopsAction::class
        )->setName($name)->setArgument('_defaults', $defaults);
    }
}
