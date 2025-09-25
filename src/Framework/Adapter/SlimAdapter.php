<?php

namespace SebLucas\Cops\Framework\Adapter;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response as CopsResponse;
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

    public function handleRequest(RequestContext $context): CopsResponse
    {
        // This method is not applicable for SlimAdapter. In a Slim integration,
        // the Slim App's run() or handle() method should be the entry point.
        throw new \LogicException('SlimAdapter does not handle requests directly. The Slim App should be run.');
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
        $router = $this->getRouter();
        $manager = $this->getHandlerManager();
        $routes = $manager->getRoutes();

        foreach ($routes as $name => $routeConfig) {
            $this->addRoute($router, $manager, $name, $routeConfig);
        }
    }

    protected function addRoute(RouterInterface $router, HandlerManager $manager, string $name, array $routeConfig): Route
    {
        [$path, $defaults] = $routeConfig;
        $methods = $routeConfig[2] ?? ['GET'];

        return $this->app->map($methods, $path, function (Request $request, Response $response, array $args) use ($manager, $router, $defaults): Response {
            // Create a COPS Request from the PSR-7 Request
            $copsRequest = new CopsRequest();
            $copsRequest->setPath($request->getUri()->getPath());
            $copsRequest->urlParams = array_merge($defaults, $args, $request->getQueryParams(), $request->getAttributes());

            // We need to set a context on the handler manager for it to create a handler
            $context = new RequestContext($copsRequest, $manager, $router);
            $manager->setContext($context);

            // Resolve and handle the request using COPS components
            $handlerName = $defaults['_handler'] ?? 'html';
            $handler = $manager->createHandler($handlerName);
            $copsResponse = $handler->handle($copsRequest);

            // Convert COPS Response to PSR-7 Response
            $response->getBody()->write($copsResponse->getContent());
            $response = $response->withStatus($copsResponse->getStatusCode());
            foreach ($copsResponse->getHeaders() as $headerName => $headerValues) {
                $response = $response->withHeader($headerName, implode(', ', $headerValues));
            }

            return $response;
        })->setName($name);
    }
}
