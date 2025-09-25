<?php

/**
 * composer require --dev symfony/http-kernel
 * composer require --dev symfony/dependency-injection
 */

namespace SebLucas\Cops\Tests\Adapter;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Framework\Adapter\SymfonyAdapter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext as SymfonyRequestContext;
use Symfony\Component\Routing\Router as SymfonyRouter;

#[RequiresMethod('\Symfony\Component\HttpKernel\KernelInterface', 'getContainer')]
class SymfonyAdapterTest extends TestCase
{
    private SymfonyAdapter $adapter;
    private KernelInterface $kernel;
    private ContainerBuilder $container;
    private SymfonyRouter $symfonyRouter;

    protected function setUp(): void
    {
        // Reset COPS routes for tests
        Route::setRoutes();

        // 1. Create a DI Container
        $this->container = new ContainerBuilder();

        // 2. Teach the container how to create COPS services
        $this->container->set(HandlerManager::class, new HandlerManager());
        $this->container->set(RouterInterface::class, new Routing());

        // 3. Set up a mock Symfony Router
        $this->symfonyRouter = $this->createMock(SymfonyRouter::class);
        $this->symfonyRouter->method('getRouteCollection')->willReturn(new \Symfony\Component\Routing\RouteCollection());
        $this->container->set('router', $this->symfonyRouter);

        // 4. Create a mock Symfony Kernel
        $this->kernel = $this->createMock(KernelInterface::class);
        $this->kernel->method('getContainer')->willReturn($this->container);

        // 5. Create the SymfonyAdapter
        $this->adapter = new SymfonyAdapter($this->kernel);
    }

    public function testGetName(): void
    {
        $this->assertSame('symfony', $this->adapter->getName());
    }

    public function testRegisterRoutes(): void
    {
        // The router should be empty initially
        $this->assertCount(0, $this->symfonyRouter->getRouteCollection()->all());

        $this->adapter->registerRoutes();

        // After registration, the router should contain routes
        $this->assertGreaterThan(10, count($this->symfonyRouter->getRouteCollection()->all()));
    }

    public function testHandleRequest(): void
    {
        // Register COPS routes with the Symfony App
        $this->adapter->registerRoutes();

        // Create a Symfony request for a specific COPS route
        $request = Request::create('/check');

        // To test the full flow, we need to simulate what the Symfony Kernel would do:
        // 1. Match the request to a route to get the controller
        $context = new SymfonyRequestContext();
        $context->fromRequest($request);
        $matcher = new UrlMatcher($this->symfonyRouter->getRouteCollection(), $context);
        $attributes = $matcher->match($request->getPathInfo());
        $controller = $attributes['_controller'];
        $request->attributes->add($attributes);

        // 2. Execute the controller callable
        $response = $controller($request);

        // Assert the response is correct
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<title>COPS Configuration Check</title>', $response->getContent());
    }

    public function testDirectHandleRequestThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('SymfonyAdapter does not handle requests directly. The Symfony Kernel should be run.');
        $this->adapter->handleRequest(new \SebLucas\Cops\Input\RequestContext(new \SebLucas\Cops\Input\Request(), new HandlerManager(), new Routing()));
    }
}
