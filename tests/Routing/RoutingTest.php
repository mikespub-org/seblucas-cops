<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Routing;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Routing\RouteCollection;
use SebLucas\Cops\Routing\RouteLoader;
use SebLucas\Cops\Routing\Routing;

require_once dirname(__DIR__, 2) . "/config/test.php";
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Routing\UriGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        self::$routing = new Routing(new RouteCollection(new HandlerManager()));
    }

    public function testRouteLoader(): void
    {
        $manager = new HandlerManager();
        $routes = new RouteCollection($manager);
        $loader = new RouteLoader($routes);
        $resource = null;
        $routes = $loader->load($resource);

        $expected = count($manager->getRoutes());
        $this->assertCount($expected, $routes);
    }

    public function testGetRouter(): void
    {
        $manager = new HandlerManager();
        $routing = new Routing(new RouteCollection($manager));
        $router = $routing->getRouter();
        // force cache generation
        $matcher = $router->getMatcher();
        $generator = $router->getGenerator();

        $expected = count($manager->getRoutes());
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
        $this->assertEquals($expected, $result[Request::ROUTE_PARAM]);
        unset($result[Request::ROUTE_PARAM]);
        unset($result[Request::HANDLER_PARAM]);
        $expected = $params;
        unset($expected[Request::HANDLER_PARAM]);
        if (!empty($result['path'])) {
            // match path for actual result
            $result = self::$routing->match('/' . $result['path'], $method);
            //unset($result[Request::ROUTE_PARAM]);
        }
        if (!empty($expected['title']) && empty($extra['title'])) {
            $expected['title'] = UriGenerator::slugify($expected['title']);
        }
        $this->assertEquals($expected, $result);

        // @todo check/add default route for each handler?
        $endpoint = parse_url((string) $queryUrl, PHP_URL_PATH);
        $flipped = array_flip(RouteTest::OLD_ENDPOINT);
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
        unset($params[Request::HANDLER_PARAM]);
        unset($params["_method"]);
        $prefix = "";
        // use default page handler with prefix for opds-path + use _route in params to generate path
        if ($route == "opds-path" && !empty($params[Request::ROUTE_PARAM])) {
            $route = $params[Request::ROUTE_PARAM];
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
        $this->assertEquals($expected, $result[Request::ROUTE_PARAM]);
        unset($result[Request::ROUTE_PARAM]);
        unset($result[Request::HANDLER_PARAM]);
        $expected = $params;
        unset($expected[Request::ROUTE_PARAM]);
        unset($expected[Request::HANDLER_PARAM]);
        if (!empty($result['path'])) {
            // match path for actual result
            $result = self::$routing->match('/' . $result['path'], $method);
            unset($result[Request::ROUTE_PARAM]);
        }
        if (!empty($expected['title']) && empty($extra['title'])) {
            $expected['title'] = UriGenerator::slugify($expected['title']);
        }
        if (!empty($expected['ignore']) && empty($extra['ignore'])) {
            $expected['ignore'] = UriGenerator::slugify($expected['ignore']);
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
        unset($params[Request::HANDLER_PARAM]);
        unset($params["_method"]);
        $prefix = "";
        // use default page handler with prefix for feed-path + use _route in params to generate path
        if ($route == "feed-path" && !empty($params[Request::ROUTE_PARAM])) {
            $route = $params[Request::ROUTE_PARAM];
            $prefix = "/feed";
        }
        if (!empty($params['title']) && !in_array($route, ['feed-page-id', 'opds-page-id'])) {
            $params['title'] = UriGenerator::slugify($params['title']);
        }
        if (!empty($params['ignore'])) {
            $params['ignore'] = UriGenerator::slugify($params['ignore']);
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

    public function testRoutingContext(): void
    {
        $routing = new Routing();
        $router = $routing->getRouter();

        $route = "page-author";
        $params = ["id" => "1", "title" => "Title"];

        $expected = "/authors/1/Title";
        $result = $router->generate($route, $params);
        $this->assertEquals($expected, $result);

        $expected = "http://localhost/authors/1/Title";
        $result = $router->getGenerator()->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals($expected, $result);

        // set request context for symfony routing
        $request = new Request();
        $context = $routing->context($request);
        $router->setContext($context);

        $expected = "/vendor/bin/index.php";
        $result = $context->getBaseUrl();
        $this->assertEquals($expected, $result);

        $expected = "/vendor/bin/index.php/authors/1/Title";
        $result = $router->generate($route, $params);
        $this->assertEquals($expected, $result);

        $expected = "http://localhost/vendor/bin/index.php/authors/1/Title";
        $result = $router->getGenerator()->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals($expected, $result);
    }
}
