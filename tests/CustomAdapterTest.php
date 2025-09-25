<?php

namespace SebLucas\Cops\Tests\Adapter;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework\Adapter\CustomAdapter;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Handlers\QueueBasedHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Middleware\TestMiddleware;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;

class CustomAdapterTest extends TestCase
{
    private CustomAdapter $adapter;
    private RouterInterface $router;
    private HandlerManager $handlerManager;

    protected function setUp(): void
    {
        // Use real dependencies for integration testing
        $this->router = new Routing();
        $this->handlerManager = new HandlerManager();
        $this->adapter = new CustomAdapter($this->router, $this->handlerManager);

        Database::clearDb();
    }

    public function testGetName(): void
    {
        $this->assertSame('custom', $this->adapter->getName());
    }

    public function testRegisterRoutes(): void
    {
        // The router should be empty initially - @todo already loaded via RouteLoader()
        //$this->assertCount(0, $this->router->getRouter()->getRouteCollection());

        $this->adapter->registerRoutes();

        // After registration, the router should contain routes from all default handlers
        $this->assertGreaterThan(10, count($this->router->getRouter()->getRouteCollection()));
    }

    public function testHandleRequest(): void
    {
        $this->adapter->registerRoutes();
        $request = new Request();
        $request->setPath('/check');

        $context = new RequestContext($request, $this->handlerManager, $this->router);

        $result = $this->adapter->handleRequest($context);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertStringContainsString('<title>COPS Configuration Check</title>', $result->getContent());
    }

    public function testHandleRequestWithMiddleware(): void
    {
        $this->adapter->registerRoutes();
        $request = new Request();
        $request->setPath('/check/more');

        $context = new RequestContext($request, $this->handlerManager, $this->router);

        // Add middleware
        $this->adapter->addMiddleware(TestMiddleware::class);

        $result = $this->adapter->handleRequest($context);

        $this->assertInstanceOf(Response::class, $result);

        // Check that the original handler was called with the modified request
        $this->assertStringContainsString("'hello' => 'world'", $result->getContent());

        // Check that the middleware modified the response
        $this->assertStringContainsString('Goodbye!', $result->getContent());
    }

    public function testCreateErrorHandler(): void
    {
        // We need to set a context on the handler manager for it to create a handler
        $context = new RequestContext(new Request(), $this->handlerManager, $this->router);
        $this->handlerManager->setContext($context);

        $errorHandler = $this->adapter->createErrorHandler();

        $this->assertInstanceOf(\SebLucas\Cops\Handlers\ErrorHandler::class, $errorHandler);
    }
}
