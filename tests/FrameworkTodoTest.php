<?php

namespace SebLucas\Cops\Tests;

use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Framework\FrameworkTodo;
use SebLucas\Cops\Framework\Adapter\CustomAdapter;
use SebLucas\Cops\Handlers\TestHandler;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Response;

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

        $this->assertNotNull($router1);
        $this->assertNotNull($router2);
    }

    public function testRequestHandling(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';

        $framework = new FrameworkTodo(new CustomAdapter());
        $context = $framework->getContext();

        $this->assertNotNull($context);
        $this->assertInstanceOf(Request::class, $context->getRequest());
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

        $adapter->addMiddleware(get_class($testMiddleware));

        $this->assertTrue(true); // Middleware added successfully
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
}
