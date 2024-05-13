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
        $expected = 87;
        $this->assertEquals($expected, Route::count());
        $routes = Route::getRoutes();

        // change protected property using closure bind & call or use reflection
        $resetRoutes = static function () {
            Route::$routes = [];
            return true;
        };
        $resetRoutes = \Closure::bind($resetRoutes, null, Route::class);
        $result = $resetRoutes();
        $this->assertTrue($result);

        $expected = 0;
        $this->assertEquals($expected, Route::count());

        Framework::addRoutes();

        $expected = 87;
        $this->assertEquals($expected, Route::count());

        $expected = $routes;
        $this->assertEquals($expected, Route::getRoutes());
    }
}
