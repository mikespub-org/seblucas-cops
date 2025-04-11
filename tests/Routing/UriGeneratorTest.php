<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Routing\UriGenerator;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;

class UriGeneratorTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testRoute(): void
    {
        $this->assertEquals("", UriGenerator::route(['page' => null, 'db' => null]));
        $this->assertEquals("?db=0", UriGenerator::route(['page' => null, 'db' => 0]));
        $this->assertEquals("?key=value", UriGenerator::route(['page' => null, 'key' => 'value', 'db' => null]));
        $this->assertEquals("?key=value&db=0", UriGenerator::route(['page' => null, 'key' => 'value', 'db' => 0]));
        $this->assertEquals("?key=value&db=0", UriGenerator::route(['page' => null, 'key' => 'value', 'otherKey' => null, 'db' => 0]));
        $this->assertEquals("?key=value&otherKey=other&db=0", UriGenerator::route(['page' => null, 'key' => 'value', 'otherKey' => 'other', 'db' => 0]));
        $this->assertEquals("/authors", UriGenerator::route(['page' => 'authors', 'db' => null]));
        $this->assertEquals("/authors?db=0", UriGenerator::route(['page' => 'authors', 'db' => 0]));
        $this->assertEquals("/authors?key=value", UriGenerator::route(['page' => 'authors', 'key' => 'value', 'db' => null]));
        $this->assertEquals("/authors?key=value&db=0", UriGenerator::route(['page' => 'authors', 'key' => 'value', 'db' => 0]));
        $this->assertEquals("/authors?key=value&db=0", UriGenerator::route(['page' => 'authors', 'key' => 'value', 'otherKey' => null, 'db' => 0]));
        $this->assertEquals("/authors?key=value&otherKey=other&db=0", UriGenerator::route(['page' => 'authors', 'key' => 'value', 'otherKey' => 'other', 'db' => 0]));
    }

    public function testFrontController(): void
    {
        $expected = '/recent';
        $uri = UriGenerator::route(['_route' => 'page-recent']);
        $this->assertEquals($expected, $uri);

        Config::set('front_controller', 'index.php');
        $expected = UriGenerator::base() . 'recent';
        $test = UriGenerator::absolute(UriGenerator::route(['_route' => 'page-recent']));
        $this->assertEquals($expected, $test);

        Config::set('front_controller', '');
        $expected = UriGenerator::base() . 'index.php/recent';
        $test = UriGenerator::absolute(UriGenerator::route(['_route' => 'page-recent']));
        $this->assertEquals($expected, $test);
    }

    public function testProxyBaseUrl(): void
    {
        Config::set('full_url', '');
        UriGenerator::setBaseUrl(null);

        $expected = 'vendor/bin/';
        $base = UriGenerator::base();
        $this->assertStringEndsWith($expected, $base);

        // @see https://github.com/mikespub-org/seblucas-cops/wiki/Reverse-proxy-configurations
        Config::set('trusted_proxies', 'private_ranges');
        Config::set('trusted_headers', ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port', 'x-forwarded-prefix']);
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.example.com';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = 8443;
        $_SERVER['HTTP_X_FORWARDED_PREFIX'] = '/books/';
        $_SERVER['REMOTE_ADDR'] = '::1';
        UriGenerator::setBaseUrl(null);

        $expected = 'https://www.example.com:8443/books/';
        $base = UriGenerator::base();
        $this->assertEquals($expected, $base);

        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
        unset($_SERVER['HTTP_X_FORWARDED_PORT']);
        unset($_SERVER['HTTP_X_FORWARDED_PREFIX']);
        unset($_SERVER['REMOTE_ADDR']);
        // this has priority over trusted proxies or script name
        Config::set('full_url', '/cops/');
        UriGenerator::setBaseUrl(null);

        $expected = '/cops/';
        $base = UriGenerator::base();
        $this->assertEquals($expected, $base);

        Config::set('trusted_proxies', '');
        Config::set('trusted_headers', []);
        Config::set('full_url', '');
        UriGenerator::setBaseUrl(null);
    }

    /**
     * Summary of linkProvider
     * @return array<mixed>
     */
    public static function linkProvider()
    {
        return RouteTest::linkProvider();
    }

    /**
     * @param mixed $link
     * @param mixed $expected
     * @param mixed $route
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('linkProvider')]
    public function testHandlerLink($link, $expected, $route)
    {
        // skip feed-page routes for handler::link() - use feed-path route with default page handler
        if (str_starts_with($route, "feed-page")) {
            $this->markTestSkipped("Skip feed-page routes here");
        }
        // skip opds-page routes for handler::link() - use opds-path route with default page handler
        if (str_starts_with($route, "opds-page")) {
            $this->markTestSkipped("Skip opds-page routes here");
        }
        $expected = "index.php" . $expected;
        $params = [];
        parse_str(parse_url((string) $link, PHP_URL_QUERY) ?? '', $params);
        $endpoint = parse_url((string) $link, PHP_URL_PATH);
        // 1. this will find handler name based on old endpoints
        $handler = "html";
        if ($endpoint !== RouteTest::OLD_ENDPOINT["html"]) {
            $testpoint = str_replace('.php', '', $endpoint);
            if (array_key_exists($testpoint, RouteTest::OLD_ENDPOINT)) {
                $params[Route::HANDLER_PARAM] = $testpoint;
                $handler = $testpoint;
            } else {
                // for epubreader.php, checkconfig.php etc.
                $flipped = array_flip(RouteTest::OLD_ENDPOINT);
                $params[Route::HANDLER_PARAM] = $flipped[$endpoint];
                $handler = $flipped[$endpoint];
            }
        }
        // 2. we pass handler class-string as param now
        $handler = Route::getHandler($handler);
        $test = $handler::link($params);
        $this->assertStringEndsWith($expected, $test);
    }

    /**
     * Summary of routeProvider
     * @return array<mixed>
     */
    public static function routeProvider()
    {
        return RouteTest::routeProvider();
    }

    /**
     * @param mixed $expected
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('routeProvider')]
    public function testGetRouteForParams($expected, $route, $params)
    {
        // skip feed-page routes for Route::getRouteForParams() - use feed-path route with default page handler
        if (str_starts_with($route, "feed-page")) {
            $this->markTestSkipped("Skip feed-page routes here");
        }
        // handle POST method for mail
        $method = null;
        if (!empty($params["_method"])) {
            $method = $params["_method"];
            unset($params["_method"]);
        }
        // pass handler class-string as param here
        if (!empty($params[Route::HANDLER_PARAM])) {
            $params[Route::HANDLER_PARAM] = Route::getHandler($params[Route::HANDLER_PARAM]);
        }
        $this->assertEquals($expected, UriGenerator::getRouteForParams($params));
    }
}
