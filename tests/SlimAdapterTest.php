<?php

namespace SebLucas\Cops\Tests\Adapter;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use DI\Container;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use SebLucas\Cops\Framework\Adapter\SlimAdapter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Middleware\TestMiddleware;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

#[RequiresMethod('\Slim\App', '__construct')]
class SlimAdapterTest extends TestCase
{
    /** @var \Slim\App<\Psr\Container\ContainerInterface> */
    private App $app;
    private SlimAdapter $adapter;

    protected function setUp(): void
    {
        // Reset routes for tests
        Route::setRoutes();

        // 1. Create a DI Container
        $container = new Container();

        // 2. Teach the container how to create COPS services
        $container->set(HandlerManager::class, static function () {
            return new HandlerManager();
        });
        $container->set(RouterInterface::class, static function () {
            return new Routing();
        });
        // Add PSR-7 factories to the container for middleware bridging
        $container->set(ServerRequestFactoryInterface::class, static function () {
            return new ServerRequestFactory();
        });
        $container->set(ResponseFactoryInterface::class, static function () {
            return new ResponseFactory();
        });

        // 3. Set the container on the AppFactory and create the Slim App
        AppFactory::setContainer($container);
        $this->app = AppFactory::create();

        // 4. Create the SlimAdapter
        $this->adapter = new SlimAdapter($this->app);

        /**
        // 5. Register all COPS routes with the Slim App
        $adapter->registerRoutes();

        // 6. Add middleware (optional)
        // $adapter->addMiddleware(MyCustomMiddleware::class);

        // 7. Add Slim's error middleware
        $app->addErrorMiddleware(true, true, true);

        // 8. Run the app!
        $app->run();
         */
    }

    protected function createRequest(string $method, string $path): Request
    {
        $factory = new ServerRequestFactory();
        return $factory->createServerRequest($method, $path);
    }

    public function testGetName(): void
    {
        $this->assertSame('slim', $this->adapter->getName());
    }

    public function testRegisterRoutes(): void
    {
        // The router should be empty initially
        $this->assertCount(0, $this->app->getRouteCollector()->getRoutes());

        $this->adapter->registerRoutes();

        // After registration, the router should contain routes
        $this->assertGreaterThan(10, count($this->app->getRouteCollector()->getRoutes()));
    }

    public function testHandleRequest(): void
    {
        // Register COPS routes with the Slim App
        $this->adapter->registerRoutes();

        // Create a PSR-7 request for a specific COPS route
        $request = $this->createRequest('GET', '/check');

        // Handle the request through the full Slim application stack
        $response = $this->app->handle($request);

        // Assert the response is correct
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<title>COPS Configuration Check</title>', (string) $response->getBody());
    }

    public function testHandleRequestWithMiddleware(): void
    {
        // Add middleware to the Slim app via the adapter
        $this->adapter->addMiddleware(TestMiddleware::class);

        // Register COPS routes
        $this->adapter->registerRoutes();

        // Create a PSR-7 request
        $request = $this->createRequest('GET', '/check/more');

        // Handle the request
        $response = $this->app->handle($request);
        $content = (string) $response->getBody();

        // Assert the response is correct
        $this->assertSame(200, $response->getStatusCode());

        // Check that the original handler was called with the request modified by the middleware
        $this->assertStringContainsString("'hello' => 'world'", $content);

        // Check that the middleware also modified the response
        $this->assertStringContainsString('Goodbye!', $content);
    }
}
