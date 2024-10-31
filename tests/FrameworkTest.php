<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Framework;
use SebLucas\Cops\Handlers\CheckHandler;
use SebLucas\Cops\Middleware\TestMiddleware;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Input\Route;

class FrameworkTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // ...
    }

    /**
     * Summary of testAddRoutes
     * @return void
     */
    public function testAddRoutes(): void
    {
        Route::setRoutes();

        $expected = 0;
        $this->assertEquals($expected, Route::count());

        Framework::loadRoutes();

        $expected = 97;
        $this->assertEquals($expected, Route::count());
    }

    /**
     * Summary of getHandlers
     * @return array<array<mixed>>
     */
    public static function getHandlers(): array
    {
        $className = Framework::class;
        // test protected method using closure bind & call or use reflection
        // @see https://www.php.net/manual/en/closure.bind.php
        $getHandlers = \Closure::bind(static function () use ($className) {
            return $className::$handlers;
        }, null, $className);

        $result = [];
        foreach ($getHandlers() as $handler => $className) {
            array_push($result, [$handler, $className]);
        }
        return $result;
    }

    /**
     * Summary of testGetHandlers
     * @return void
     */
    public function testGetHandlers(): void
    {
        $handlers = $this->getHandlers();

        $expected = 17;
        $this->assertCount($expected, $handlers);
    }

    public function testgetRequest(): void
    {
        $_SERVER['PATH_INFO'] = '/check';
        $request = Framework::getRequest();

        $expected = 'check';
        $this->assertEquals($expected, $request->getHandler());

        unset($_SERVER['PATH_INFO']);
    }

    public function testgetHandler(): void
    {
        $handler = Framework::getHandler('check');

        $expected = CheckHandler::class;
        $this->assertEquals($expected, $handler::class);
    }

    public function testRunHome(): void
    {
        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>COPS</title>";
        $this->assertStringContainsString($expected, $output);
    }

    public function testRunCheck(): void
    {
        $_SERVER['PATH_INFO'] = '/check';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>COPS Configuration Check</title>";
        $this->assertStringContainsString($expected, $output);

        unset($_SERVER['PATH_INFO']);
    }

    public function testRunLoader(): void
    {
        $_SERVER['PATH_INFO'] = '/loader';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>COPS Loader</title>";
        $this->assertStringContainsString($expected, $output);

        unset($_SERVER['PATH_INFO']);
    }

    public function testMiddleware(): void
    {
        $className = Framework::class;
        // test protected method using closure bind & call or use reflection
        // @see https://www.php.net/manual/en/closure.bind.php
        $addMiddleware = \Closure::bind(static function ($add = null) use ($className) {
            if (!empty($add)) {
                array_push($className::$middlewares, $add);
            } else {
                $className::$middlewares = [];
            }
            return $className::$middlewares;
        }, null, $className);

        $_SERVER['PATH_INFO'] = '/check/more';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "'queryString' => ''";
        $this->assertStringContainsString($expected, $output);

        // add test middleware to framework
        $middlewares = $addMiddleware(new TestMiddleware());
        $expected = 1;
        $this->assertCount($expected, $middlewares);

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "queryString' => '_handler=check&more=more&hello=world'";
        $this->assertStringContainsString($expected, $output);
        $expected = "Goodbye!";
        $this->assertStringContainsString($expected, $output);

        // reset middleware again
        $middlewares = $addMiddleware(null);
        $expected = 0;
        $this->assertCount($expected, $middlewares);

        unset($_SERVER['PATH_INFO']);
    }
}
