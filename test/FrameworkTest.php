<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Framework;
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

        Framework::addRoutes();

        $expected = 91;
        $this->assertEquals($expected, Route::count());
    }

    /**
     * Summary of getHandlers
     * @return array<array<mixed>>
     */
    public static function getHandlers(): array
    {
        // test protected method using closure bind & call or use reflection
        // @see https://www.php.net/manual/en/closure.bind.php
        $getHandlers = \Closure::bind(static function () {
            return Framework::$handlers;
        }, null, Framework::class);
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

        $expected = 15;
        $this->assertCount($expected, $handlers);
    }
}
