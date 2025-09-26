<?php

namespace SebLucas\Cops\Tests\Framework;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Framework\FrameworkTodo;
use SebLucas\Cops\Framework\Adapter\CustomAdapter;
use SebLucas\Cops\Handlers\TestHandler;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\CheckHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;

class FrameworkTodoTest extends TestCase
{
    public function testFrameworkAndFrameworkTodoSingleton(): void
    {
        $framework1 = Framework::getInstance();
        $framework2 = Framework::getInstance();
        $this->assertSame($framework1, $framework2);

        $frameworkTodo1 = FrameworkTodo::getInstance();
        $frameworkTodo2 = FrameworkTodo::getInstance();
        $this->assertSame($frameworkTodo1, $frameworkTodo2);
    }

    public function testHandlerManagerAccess(): void
    {
        $handlerManager1 = Framework::getHandlerManager();
        $handlerManager2 = FrameworkTodo::getHandlerManager();

        $this->assertSame($handlerManager1->getHandlers(), $handlerManager2->getHandlers());
    }

    public function testRouterAccess(): void
    {
        $router1 = Framework::getRouter();
        $router2 = FrameworkTodo::getRouter();

        $this->assertInstanceOf(RouterInterface::class, $router1);
        $this->assertInstanceOf(RouterInterface::class, $router2);
        $this->assertNotSame($router1, $router2);
    }

    public function testRequestHandling(): void
    {
        $_SERVER['PATH_INFO'] = '/check';

        $framework = new FrameworkTodo(new CustomAdapter());
        $context = $framework->getContext();
        // match route and update request with matched parameters
        $params = $context->matchRequest();
        $handler = $context->resolveHandler();

        $request = $context->getRequest();
        $this->assertInstanceOf(Request::class, $request);

        $expected = '/check';
        $this->assertEquals($expected, $request->path());

        $expected = CheckHandler::class;
        $this->assertEquals($expected, $handler::class);
        $this->assertEquals($expected, $request->getHandler());

        unset($_SERVER['PATH_INFO']);
    }

    public function testMiddlewareSupport(): void
    {
        $adapter = new CustomAdapter();
        $framework = new FrameworkTodo($adapter);

        // Test middleware class
        $testMiddleware = new class {
            public function __invoke(Request $request, BaseHandler $handler): mixed
            {
                return $handler->handle($request);
            }
        };

        $result = $adapter->addMiddleware(get_class($testMiddleware));

        $expected = $adapter;
        $this->assertSame($expected, $result); // Middleware added successfully
    }

    public function testErrorHandling(): void
    {
        $framework = new FrameworkTodo();
        $handler = $framework->getHandlerManager()->createHandler('error');

        // set request handler to 'TestHandler' class to avoid exit() in Response::notFound()
        $request = Request::build([], TestHandler::class);
        $response = $handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testRouteRegistration(): void
    {
        $adapter = new CustomAdapter();
        $framework = new FrameworkTodo($adapter);

        // Verify routes are registered
        $router = $adapter->getRouter();
        //$this->assertNotEmpty($router->getRoutes());
        $expected = Route::count();
        $this->assertCount($expected, $router->getRouter()->getRouteCollection());
    }

    public function testRunCheck(): void
    {
        $_SERVER['PATH_INFO'] = '/check';

        ob_start();
        FrameworkTodo::run(true);
        $output = ob_get_clean();

        $expected = "<title>COPS Configuration Check</title>";
        $this->assertStringContainsString($expected, $output);

        unset($_SERVER['PATH_INFO']);
    }

    public function testRunNotFound(): void
    {
        $_SERVER['PATH_INFO'] = '/this-route-does-not-exist';

        // Capture error_log output to verify the error is logged
        $logFile = tempnam(sys_get_temp_dir(), 'cops_test_');
        ini_set('error_log', $logFile);

        ob_start();
        FrameworkTodo::run(true);
        $output = ob_get_clean();

        // The ErrorHandler should output a "Invalid request path" message
        $this->assertStringContainsString('<h1>Error</h1>', $output);
        $this->assertStringContainsString('<p>COPS: Invalid request path &#039;/this-route-does-not-exist&#039;</p>', $output);

        // Check that the error was logged
        $this->assertStringContainsString("COPS: Invalid request path '/this-route-does-not-exist' from template", file_get_contents($logFile));
        unlink($logFile);

        unset($_SERVER['PATH_INFO']);
    }

    public function testCreateRequestWithRedirectPathInfo(): void
    {
        unset($_SERVER['PATH_INFO']);
        $_SERVER['REDIRECT_PATH_INFO'] = '/test/path';

        $framework = new FrameworkTodo();
        $request = $framework->getContext()->getRequest();

        $this->assertEquals('/test/path', $request->path());

        unset($_SERVER['REDIRECT_PATH_INFO']);
        unset($_SERVER['PATH_INFO']);
    }
}
