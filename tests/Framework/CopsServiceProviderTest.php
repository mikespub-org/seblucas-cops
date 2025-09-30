<?php

/**
 * composer require --dev illuminate/routing
 * composer require --dev illuminate/events
 */

namespace SebLucas\Cops\Tests\Framework;

use SebLucas\Cops\Framework\Providers\CopsServiceProvider;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router as LaravelRouter;
use SebLucas\Cops\Framework\Adapter\LaravelAdapter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\RouterInterface;

#[RequiresMethod('\Illuminate\Routing\Router', '__construct')]
class CopsServiceProviderTest extends TestCase
{
    private Container $container;
    private CopsServiceProvider $provider;

    protected function setUp(): void
    {
        // 1. Create a Laravel DI Container
        $this->container = new Container();

        // Bind the callable dispatcher contract to its implementation for the router to work
        $this->container->singleton(CallableDispatcherContract::class, function ($container) {
            return new CallableDispatcher($container);
        });

        // 2. Set up a mock Laravel Router and bind it to the container
        $laravelRouter = new LaravelRouter(new Dispatcher(), $this->container);
        $this->container->instance('router', $laravelRouter);

        // 3. Create an instance of the service provider
        $this->provider = new CopsServiceProvider($this->container);
    }

    public function testRegister(): void
    {
        // Run the register method
        $this->provider->register();

        // Assert that the core COPS services are bound in the container
        $this->assertTrue($this->container->bound(HandlerManager::class));
        $this->assertTrue($this->container->bound(RouterInterface::class));
        $this->assertTrue($this->container->bound(LaravelAdapter::class));

        // Assert that they are singletons (the same instance is returned each time)
        $instance1 = $this->container->make(HandlerManager::class);
        $instance2 = $this->container->make(HandlerManager::class);
        $this->assertSame($instance1, $instance2);
    }

    public function testBoot(): void
    {
        // First, register the services
        $this->provider->register();

        /** @var LaravelRouter $laravelRouter */
        $laravelRouter = $this->container->make('router');

        // The router should be empty initially
        $this->assertCount(0, $laravelRouter->getRoutes()->getRoutes());

        // Run the boot method
        $this->provider->boot();

        // After booting, the router should be populated with COPS routes
        $this->assertGreaterThan(10, count($laravelRouter->getRoutes()->getRoutes()));
    }

    public function testHandleRequest(): void
    {
        // 1. Bootstrap the provider to register services and routes
        $this->provider->register();
        $this->provider->boot();

        // 2. Create a Laravel request for a specific COPS route
        $request = \Illuminate\Http\Request::create('/check', 'GET');

        // 3. Simulate how the Laravel kernel would dispatch the request
        /** @var LaravelRouter $laravelRouter */
        $laravelRouter = $this->container->make('router');

        // Ensure the same request instance is used throughout the dispatch process
        $this->container->instance(\Illuminate\Http\Request::class, $request);

        // Find the registered route and its action (controller)
        $route = $laravelRouter->getRoutes()->match($request);
        $request->setRouteResolver(fn() => $route);

        // Execute the route's action, which is the bridge to the COPS handler
        $response = $route->run($request);

        // 4. Assert that the response from the COPS handler is correct
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<title>COPS Configuration Check</title>', $response->getContent());
    }

    public function testHandleRequestWithDispatch(): void
    {
        // 1. Bootstrap the provider to register services and routes
        $this->provider->register();
        $this->provider->boot();

        // 2. Create a Laravel request for a specific COPS route
        $request = \Illuminate\Http\Request::create('/check', 'GET');

        // Ensure the same request instance is used throughout the dispatch process
        $this->container->instance(\Illuminate\Http\Request::class, $request);

        // 3. Get the router and dispatch the request, which is a higher-level
        //    simulation of how the Laravel Kernel would handle the request.
        /** @var LaravelRouter $laravelRouter */
        $laravelRouter = $this->container->make('router');
        $response = $laravelRouter->dispatch($request);

        // 4. Assert that the response from the COPS handler is correct
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<title>COPS Configuration Check</title>', $response->getContent());
    }
}
