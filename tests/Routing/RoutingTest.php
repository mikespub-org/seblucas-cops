<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Routing\RouteLoader;
use SebLucas\Cops\Routing\Routing;

require_once dirname(__DIR__, 2) . "/config/test.php";
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Routing\UriGenerator;
use Exception;

#[RequiresMethod('\Symfony\Component\Routing\Router', '__construct')]
class RoutingTest extends TestCase
{
    /** @var Routing */
    protected static $routing;

    public static function setUpBeforeClass(): void
    {
        Config::set("calibre_directory", dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$routing = new Routing();
    }

    public function testRouteLoader(): void
    {
        $loader = new RouteLoader();
        $resource = null;
        $routes = $loader->load($resource);

        $expected = Route::count();
        $this->assertCount($expected, $routes);
    }

    public function testGetRouter(): void
    {
        $routing = new Routing();
        $router = $routing->getRouter();
        // force cache generation
        $matcher = $router->getMatcher();
        $generator = $router->getGenerator();

        $expected = Route::count();
        $this->assertCount($expected, $router->getRouteCollection());
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
     * @param mixed $queryUrl
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("linkProvider")]
    public function testMatchLink($queryUrl, $routeUrl, $route, $params)
    {
        //echo '["' . $queryUrl . '", "' . $routeUrl . '", "' . $route . '", ' . json_encode($params) . "],\n";
        $query = parse_url((string) $routeUrl, PHP_URL_QUERY);
        // parse_url() does not decode URL-encoded characters in the path
        $path = parse_url((string) $routeUrl, PHP_URL_PATH);
        // handle POST method for mail
        $method = null;
        if (!empty($params["_method"])) {
            $method = $params["_method"];
            unset($params["_method"]);
        }
        $result = self::$routing->match($path, $method);
        if (!isset($result)) {
            $this->assertNull($routeUrl);
            return;
        }
        $extra = [];
        if (!empty($query)) {
            parse_str($query, $extra);
            $result = array_merge($result, $extra);
        }
        // @todo handle ignore
        //unset($result["ignore"]);

        $expected = $route;
        $this->assertEquals($expected, $result[Route::ROUTE_PARAM]);
        unset($result[Route::ROUTE_PARAM]);
        unset($result[Route::HANDLER_PARAM]);
        $expected = $params;
        unset($expected[Route::HANDLER_PARAM]);
        if (!empty($result['path'])) {
            // match path for actual result
            $result = self::$routing->match('/' . $result['path'], $method);
            //unset($result[Route::ROUTE_PARAM]);
        }
        if (!empty($expected['title']) && empty($extra['title'])) {
            $expected['title'] = UriGenerator::slugify($expected['title']);
        }
        $this->assertEquals($expected, $result);

        // @todo check/add default route for each handler?
        $endpoint = parse_url((string) $queryUrl, PHP_URL_PATH);
        $flipped = array_flip(Config::OLD_ENDPOINT);
        $handler = "html";
        if (!empty($flipped[$endpoint])) {
            $handler = $flipped[$endpoint];
        }
        if ($handler == "html") {
            return;
        }
        $path = "/$handler";
        try {
            $result = self::$routing->match($path);
        } catch (Exception) {
            // echo "Endpoint handler not supported: $handler\n";
        }
    }

    /**
     * @param mixed $queryUrl
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("linkProvider")]
    public function testGenerateLink($queryUrl, $routeUrl, $route, $params)
    {
        // @todo handle ignore
        // @todo handle restapi/route...
        unset($params[Route::HANDLER_PARAM]);
        unset($params["_method"]);
        $prefix = "";
        // use default page handler with prefix for opds-path + use _route in params to generate path
        if ($route == "opds-path" && !empty($params[Route::ROUTE_PARAM])) {
            $route = $params[Route::ROUTE_PARAM];
            $prefix = "/opds";
        }
        if (!empty($params['title']) && !in_array($route, ['feed-page-id', 'opds-page-id'])) {
            $params['title'] = UriGenerator::slugify($params['title']);
        }
        //if (!empty($params['file'])) {
        //    $params['file'] = implode('/', array_map('rawurlencode', explode('/', $params['file'])));
        //}
        try {
            $result = self::$routing->generate($route, $params);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $prefix . $result);
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
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("routeProvider")]
    public function testMatchRoute($routeUrl, $route, $params)
    {
        //echo '["' . $routeUrl . '", "' . $route . '", ' . json_encode($params) . "],\n";
        $query = parse_url((string) $routeUrl, PHP_URL_QUERY);
        // parse_url() does not decode URL-encoded characters in the path
        $path = parse_url((string) $routeUrl, PHP_URL_PATH);
        // handle POST method for mail
        $method = null;
        if (!empty($params["_method"])) {
            $method = $params["_method"];
            unset($params["_method"]);
        }
        $result = self::$routing->match($path, $method);
        if (!isset($result)) {
            $this->assertNull($routeUrl);
            return;
        }
        if (!isset($routeUrl)) {
            $expected = [];
            $this->assertEquals($expected, $result);
            return;
        }
        $extra = [];
        if (!empty($query)) {
            parse_str($query, $extra);
            $result = array_merge($result, $extra);
        }
        $expected = $route;
        $this->assertEquals($expected, $result[Route::ROUTE_PARAM]);
        unset($result[Route::ROUTE_PARAM]);
        unset($result[Route::HANDLER_PARAM]);
        $expected = $params;
        unset($expected[Route::ROUTE_PARAM]);
        unset($expected[Route::HANDLER_PARAM]);
        if (!empty($result['path'])) {
            // match path for actual result
            $result = self::$routing->match('/' . $result['path'], $method);
            unset($result[Route::ROUTE_PARAM]);
        }
        if (!empty($expected['title']) && empty($extra['title'])) {
            $expected['title'] = UriGenerator::slugify($expected['title']);
        }
        $this->assertEquals($expected, $result);
    }

    /**
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("routeProvider")]
    public function testGenerateRoute($routeUrl, $route, $params)
    {
        // skip feed-page-id routes for Routing::generate() - use feed-path route with default page handler
        //if (str_starts_with($route, "feed-page")) {
        //    $this->markTestSkipped("Skip feed-page routes here");
        //}
        unset($params[Route::HANDLER_PARAM]);
        unset($params["_method"]);
        $prefix = "";
        // use default page handler with prefix for feed-path + use _route in params to generate path
        if ($route == "feed-path" && !empty($params[Route::ROUTE_PARAM])) {
            $route = $params[Route::ROUTE_PARAM];
            $prefix = "/feed";
        }
        if (!empty($params['title']) && !in_array($route, ['feed-page-id', 'opds-page-id'])) {
            $params['title'] = UriGenerator::slugify($params['title']);
        }
        //if (!empty($params['file'])) {
        //    $params['file'] = implode('/', array_map('rawurlencode', explode('/', $params['file'])));
        //}
        try {
            $result = self::$routing->generate($route, $params);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $prefix . $result);
    }
}
