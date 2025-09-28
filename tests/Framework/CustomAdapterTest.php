<?php

namespace SebLucas\Cops\Tests\Framework;

use SebLucas\Cops\Framework\Adapter\CustomAdapter;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework\FrameworkTodo;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Routing\RouterInterface;

class CustomAdapterTest extends TestCase
{
    private CustomAdapter $adapter;
    private RouterInterface $router;
    private HandlerManager $handlerManager;

    protected function setUp(): void
    {
        // Use real dependencies for integration testing
        $this->handlerManager = new HandlerManager();
        $framework = new FrameworkTodo($this->handlerManager);
        $this->router = $framework->getRouter();
        $this->adapter = new CustomAdapter($framework);

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
}
