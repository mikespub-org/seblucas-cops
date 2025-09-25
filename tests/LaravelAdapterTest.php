<?php

/**
 * composer require --dev illuminate/routing
 * composer require --dev illuminate/events
 */

namespace SebLucas\Cops\Tests\Adapter;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router as LaravelRouter;
use SebLucas\Cops\Framework\Adapter\LaravelAdapter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;

#[RequiresMethod('\Illuminate\Routing\Router', '__construct')]
class LaravelAdapterTest extends TestCase
{
    private LaravelAdapter $adapter;
    private Container $container;
    private LaravelRouter $laravelRouter;

    protected function setUp(): void
    {
        // Reset COPS routes for tests
        Route::setRoutes();

        // 1. Create a Laravel DI Container
        $this->container = new Container();

        // Bind the callable dispatcher contract to its implementation
        $this->container->singleton(CallableDispatcherContract::class, function ($container) {
            return new CallableDispatcher($container);
        });

        // 2. Set up a mock Laravel Router
        $this->laravelRouter = new LaravelRouter(new Dispatcher(), $this->container);
        $this->container->instance('router', $this->laravelRouter);

        // 3. Teach the container how to create COPS services
        $this->container->instance(HandlerManager::class, new HandlerManager());
        $this->container->instance(RouterInterface::class, new Routing());

        // 4. Create the LaravelAdapter
        $this->adapter = new LaravelAdapter($this->container);
    }

    public function testGetName(): void
    {
        $this->assertSame('laravel', $this->adapter->getName());
    }

    public function testRegisterRoutes(): void
    {
        // The router should be empty initially
        $this->assertCount(0, $this->laravelRouter->getRoutes()->getRoutes());

        $this->adapter->registerRoutes();

        // After registration, the router should contain routes
        $this->assertGreaterThan(10, count($this->laravelRouter->getRoutes()->getRoutes()));
    }

    public function testHandleRequest(): void
    {
        // Register COPS routes with the Laravel App
        $this->adapter->registerRoutes();

        // Create a Laravel request for a specific COPS route
        $request = LaravelRequest::create('/check', 'GET');

        // Ensure the same request instance is used throughout the dispatch process
        $this->container->instance(LaravelRequest::class, $request);

        // Find the registered route and its action (controller)
        $route = $this->laravelRouter->getRoutes()->match($request);
        $request->setRouteResolver(fn() => $route);

        // Execute the route's action
        $response = $route->run($request);

        // Assert the response is correct
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<title>COPS Configuration Check</title>', $response->getContent());
    }

    public function testDirectHandleRequestThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('LaravelAdapter does not handle requests directly. The Laravel Kernel should be run.');
        $this->adapter->handleRequest(new \SebLucas\Cops\Input\RequestContext(new \SebLucas\Cops\Input\Request(), new HandlerManager(), new Routing()));
    }
}
