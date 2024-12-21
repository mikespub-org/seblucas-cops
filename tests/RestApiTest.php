<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Calibre\Metadata;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Handlers\CheckHandler;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Output\RestApiProvider;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Pages\PageId;

class RestApiTest extends TestCase
{
    protected static string $script;
    protected static RestApiProvider $apiProvider;
    /** @var array<string, int> */
    protected static $expectedSize = [
        'calres' => 37341,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$script = $_SERVER["SCRIPT_NAME"];
        self::$apiProvider = new RestApiProvider(Framework::getRequest());
    }

    public static function tearDownAfterClass(): void
    {
        $_SERVER["SCRIPT_NAME"] = self::$script;
    }

    public function testGetPathInfo(): void
    {
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $expected = "/index";
        $test = $apiProvider->getPathInfo();
        $this->assertEquals($expected, $test);

        $_SERVER["PATH_INFO"] = "/books/2";
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);

        $expected = "/books/2";
        $test = $apiProvider->getPathInfo();
        $this->assertEquals($expected, $test);

        unset($_SERVER["PATH_INFO"]);
    }

    public function testMatchPathInfo(): void
    {
        $_SERVER["PATH_INFO"] = "/books/2";
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $path = $apiProvider->getPathInfo();

        $expected = ["page" => PageId::BOOK_DETAIL, "id" => 2, "_route" => "page-book-id"];
        $test = $apiProvider->matchPathInfo($path);
        $this->assertEquals($expected, $test);

        $_SERVER["PATH_INFO"] = "/restapi/openapi";
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $path = $apiProvider->getPathInfo();

        $expected = self::$apiProvider->getOpenApi($request);
        $test = $apiProvider->matchPathInfo($path);
        $this->assertEquals($expected, $test);

        $expected = true;
        $test = $apiProvider->isExtra;
        $this->assertEquals($expected, $test);

        unset($_SERVER["PATH_INFO"]);
    }

    public function testSetParams(): void
    {
        $_SERVER["PATH_INFO"] = "/books/2";
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $path = $apiProvider->getPathInfo();
        $params = $apiProvider->matchPathInfo($path);
        $request = $apiProvider->getRequest()->setParams($params);

        $expected = PageId::BOOK_DETAIL;
        $test = $request->get("page");
        $this->assertEquals($expected, $test);

        $expected = 2;
        $test = $request->getId();
        $this->assertEquals($expected, $test);

        unset($_SERVER["PATH_INFO"]);
    }

    public function testGetJson(): void
    {
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $renderer = new JsonRenderer();
        $expected = $renderer->getJson($request);
        $test = $apiProvider->getJson();
        unset($expected['counters']);
        unset($test['counters']);
        $this->assertEquals($expected, $test);
    }

    public function testGetOutput(): void
    {
        $request = Request::build([], basename(self::$script));
        $apiProvider = new RestApiProvider($request);
        $expected = true;
        $test = $apiProvider->getOutput();
        $this->assertEquals($expected, str_starts_with($test, '{"title":"COPS",'));
    }

    public function testGetCustomColumns(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Custom Columns";
        $test = self::$apiProvider->getCustomColumns($request);
        $this->assertEquals($expected, $test["title"]);
    }

    public function testGetDatabases(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Databases";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabase(): void
    {
        $request = Request::build(['db' => 0], basename(self::$script));
        $expected = "Database Types";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 2;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabaseTable(): void
    {
        $request = Request::build(['db' => 0, 'type' => 'table'], basename(self::$script));
        $expected = "Database Type table";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 43;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetTable(): void
    {
        $request = Request::build(['db' => 0, 'name' => 'books'], basename(self::$script));
        $expected = "Database Table books";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);

        $expected = "Invalid api key";
        $this->assertEquals($expected, $test["error"]);

        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');
        $request = Request::build(['db' => 0, 'name' => 'books'], basename(self::$script), $_SERVER);

        // less than self::$apiProvider->$numberPerPage;
        $expected = 16;
        $test = self::$apiProvider->getDatabases($request);
        $this->assertCount($expected, $test["entries"]);

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
    }

    public function testGetOpenApi(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "3.0.3";
        $test = self::$apiProvider->getOpenApi($request);
        $this->assertEquals($expected, $test["openapi"]);

        $cacheFile = dirname(__DIR__) . '/resources/openapi.json';
        if (file_exists($cacheFile)) {
            $content = file_get_contents($cacheFile);
            $expected = json_decode($content, true);
            $this->assertEquals($expected, $test);
        } else {
            $content = Format::json($test);
            file_put_contents($cacheFile, $content);
        }
    }

    public function testGetRoutes(): void
    {
        $request = Request::build([], basename(self::$script));
        $test = self::$apiProvider->getRoutes($request);
        $expected = "Routes";
        $this->assertEquals($expected, $test["title"]);
        $expected = Route::count();
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetHandlers(): void
    {
        $request = Request::build([], basename(self::$script));
        $test = self::$apiProvider->getHandlers($request);
        $expected = "Handlers";
        $this->assertEquals($expected, $test["title"]);
        $expected = count(Framework::getHandlers());
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNotes(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Notes";
        $test = self::$apiProvider->getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNotesByType(): void
    {
        $request = Request::build(['type' => 'authors'], basename(self::$script));
        $expected = "Notes for authors";
        $test = self::$apiProvider->getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNoteByTypeItem(): void
    {
        $request = Request::build(['type' => 'authors', 'item' => 3], basename(self::$script));
        $expected = "Note for authors #3";
        $test = self::$apiProvider->getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["resources"]);
    }

    public function testGetPreferences(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Preferences";
        $test = self::$apiProvider->getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 13;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetPreferenceByKey(): void
    {
        $request = Request::build([], basename(self::$script));
        $request->set('key', 'virtual_libraries');
        $expected = "Preference for virtual_libraries";
        $test = self::$apiProvider->getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 2;
        $this->assertCount($expected, $test["val"]);
    }

    public function testGetAnnotations(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Annotations";
        $test = self::$apiProvider->getAnnotations($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
        $expected = "Annotations for 17";
        $this->assertEquals($expected, $test["entries"][0]["title"]);
        $expected = "index.php/restapi/annotations/17";
        $this->assertStringEndsWith($expected, $test["entries"][0]["navlink"]);
        $expected = 5;
        $this->assertEquals($expected, $test["entries"][0]["number"]);
    }

    public function testGetAnnotationsByBookId(): void
    {
        $request = Request::build(['bookId' => 17], basename(self::$script));
        $expected = "Annotations for 17";
        $test = self::$apiProvider->getAnnotations($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 5;
        $this->assertCount($expected, $test["entries"]);
        $expected = "(17) Bookmark About #1";
        $this->assertEquals($expected, $test["entries"][0]["title"]);
        $expected = 'index.php/restapi/annotations/17/1';
        $this->assertStringEndsWith($expected, $test["entries"][0]["navlink"]);
    }

    public function testGetAnnotationById(): void
    {
        $request = Request::build(['bookId' => 17, 'id' => 1], basename(self::$script));
        $expected = "(17) Bookmark About #1";
        $test = self::$apiProvider->getAnnotations($request);
        $expected = "EPUB";
        $this->assertEquals($expected, $test["format"]);
        $expected = "viewer";
        $this->assertEquals($expected, $test["user"]);
        $expected = [
            'title' => 'About #1',
            'pos_type' => 'epubcfi',
            'pos' => 'epubcfi(/6/2/4/2/6/2:38)',
            'timestamp' => '2024-03-11T11:54:35.128396+00:00',
            'type' => 'bookmark',
        ];
        $this->assertEquals($expected, $test["data"]);
    }

    public function testGetMetadata(): void
    {
        $request = Request::build(['bookId' => 17], basename(self::$script));
        $expected = "Metadata for 17";
        $test = self::$apiProvider->getMetadata($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = Metadata::class;
        $this->assertEquals($expected, $test["entries"]::class);
        $expected = "2.0";
        $this->assertEquals($expected, $test["entries"]->version);
        $expected = 24;
        $this->assertCount($expected, $test["entries"]->metadata);
        $identifiers = $test["entries"]->getIdentifiers();
        $expected = 2;
        $this->assertCount($expected, $identifiers);
        $expected = [
            'scheme' => 'calibre',
            'id' => 'calibre_id',
            'value' => '17',
        ];
        $this->assertEquals($expected, $identifiers[0]);
        $annotations = $test["entries"]->getAnnotations();
        $expected = 5;
        $this->assertCount($expected, $annotations);
        $expected = "bookmark";
        $this->assertEquals($expected, $annotations[0]["annotation"]["type"]);
        $expected = "About #1";
        $this->assertEquals($expected, $annotations[0]["annotation"]["title"]);
    }

    public function testGetMetadataElement(): void
    {
        $element = "dc:title";
        $request = Request::build(['bookId' => 17, 'element' => $element], basename(self::$script));
        $expected = "Metadata for 17";
        $test = self::$apiProvider->getMetadata($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = $element;
        $this->assertEquals($expected, $test["element"]);
        $expected = "Alice's Adventures in Wonderland";
        $this->assertEquals($expected, $test["entries"][0]);
    }

    public function testGetMetadataElementName(): void
    {
        $element = "meta";
        $name = "calibre:annotation";
        $request = Request::build(['bookId' => 17, 'element' => $element, 'name' => $name], basename(self::$script));
        $expected = "Metadata for 17";
        $test = self::$apiProvider->getMetadata($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = $element;
        $this->assertEquals($expected, $test["element"]);
        $expected = $name;
        $this->assertEquals($expected, $test["name"]);
        $expected = 5;
        $this->assertCount($expected, $test["entries"]);
        $expected = "bookmark";
        $this->assertEquals($expected, $test["entries"][0]["annotation"]["type"]);
        $expected = "About #1";
        $this->assertEquals($expected, $test["entries"][0]["annotation"]["title"]);
    }

    public function testGetUserNoAuth(): void
    {
        $request = Framework::getRequest();
        $expected = "Invalid username";
        $test = self::$apiProvider->getUser($request);
        $this->assertEquals($expected, $test["error"]);
    }

    public function testGetUser(): void
    {
        $http_auth_user = Config::get('http_auth_user', 'PHP_AUTH_USER');
        $_SERVER[$http_auth_user] = "admin";
        $request = Framework::getRequest();
        $expected = "admin";
        $test = self::$apiProvider->getUser($request);
        $this->assertEquals($expected, $test["username"]);
        unset($_SERVER[$http_auth_user]);
    }

    public function testGetUserDetails(): void
    {
        Config::set('calibre_user_database', __DIR__ . "/BaseWithSomeBooks/users.db");
        $http_auth_user = Config::get('http_auth_user', 'PHP_AUTH_USER');
        $_SERVER[$http_auth_user] = 'admin';
        $_SERVER['PATH_INFO'] = '/restapi/user/details';
        $request = Framework::getRequest();

        $expected = "admin";
        $test = self::$apiProvider->getUser($request);
        $this->assertEquals($expected, $test["username"]);

        $expected = ['library_restrictions' => []];
        $this->assertEquals($expected, $test["restriction"]);

        unset($_SERVER[$http_auth_user]);
        unset($_SERVER['PATH_INFO']);
        Config::set('calibre_user_database', null);
    }

    public function testRunHandlerFalse(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');
        $_SERVER['PATH_INFO'] = '/zipfs/0/20/META-INF/container.xml';
        $request = Framework::getRequest();

        $apiProvider = new RestApiProvider($request);
        $apiProvider->doRunHandler = false;
        $expected = [
            Route::HANDLER_PARAM => Route::getHandler("zipfs"),
            "path" => "/zipfs/0/20/META-INF/container.xml",
            "params" => [
                "_route" => "zipfs",
                "db" => "0",
                "data" => "20",
                "comp" => "META-INF/container.xml",
            ],
        ];
        $expected = json_encode($expected, JSON_UNESCAPED_SLASHES);
        $test = $apiProvider->getOutput();
        $this->assertEquals($expected, $test);

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
        unset($_SERVER['PATH_INFO']);
    }

    public function testRunHandlerTrue(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');
        $_SERVER['PATH_INFO'] = '/calres/0/xxh64/7c301792c52eebf7';
        $request = Framework::getRequest();

        ob_start();
        $apiProvider = new RestApiProvider($request);
        $apiProvider->doRunHandler = true;
        $result = $apiProvider->getOutput();
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['calres'];
        $this->assertTrue($result instanceof Response);
        $this->assertEquals(0, actual: count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
        unset($_SERVER['PATH_INFO']);
    }

    public function testRunHandlerDifferentRoot(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');
        $_SERVER['PATH_INFO'] = '/thumbs/0/17/html.jpg';
        $request = Framework::getRequest();

        $apiProvider = new RestApiProvider($request);
        $apiProvider->doRunHandler = false;
        $expected = [
            Route::HANDLER_PARAM => Route::getHandler("fetch"),
            // check if the path starts with the handler param here
            "path" => "/thumbs/0/17/html.jpg",
            "params" => [
                "_route" => "fetch-thumb",
                "db" => "0",
                "id" => "17",
                "thumb" => "html",
            ],
        ];
        $expected = json_encode($expected, JSON_UNESCAPED_SLASHES);
        $test = $apiProvider->getOutput();
        $this->assertEquals($expected, $test);

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
        unset($_SERVER['PATH_INFO']);
    }

    /**
     * Summary of routeProvider
     * @return array<mixed>
     */
    public static function routeProvider()
    {
        // get restapi routes
        $defaults = [
            'any' => ['db' => 0, 'name' => 'books', 'type' => 'authors', 'item' => 3, 'title' => 'Lewis Carroll', 'key' => 'virtual_libraries', 'bookId' => 17, 'element' => 'dc:title'],
            'restapi-annotation' => ['bookId' => 17, 'id' => 1],
            'restapi-metadata-element-name' => ['element' => 'meta', 'name' => 'calibre:annotation'],
            'restapi-path' => ['path' => 'path'],
        ];
        $result = RouteTest::getRouteProvider(RestApiHandler::class, $defaults);
        // add page routes
        $defaults = [
            'any' => ['id' => 1, 'letter' => 'C', 'year' => 2006, 'author' => 'Author', 'title' => 'Title', 'query' => 'car', 'scope' => 'author', 'search' => 1, 'custom' => 3],
            'page-book' => ['id' => 17],
            'page-book-id' => ['id' => 17],
            'page-publisher' => ['id' => 2],
            'page-publisher-id' => ['id' => 2],
            'page-identifier' => ['id' => 'isbn'],
            'page-identifier-id' => ['id' => 'isbn'],
            'page-format' => ['id' => 'EPUB'],
        ];
        $extra = RouteTest::getRouteProvider(HtmlHandler::class, $defaults);
        // add other routes
        $defaults = [
            'any' => ['more' => 'more'],
        ];
        $extra = array_merge($extra, RouteTest::getRouteProvider(CheckHandler::class, $defaults));
        foreach ($extra as $info) {
            [$path, $name, $params] = $info;
            $fixed = $params;
            // handle via REST API with /restapi prefix
            $path = RestApiHandler::PREFIX . $path;
            $params[Route::HANDLER_PARAM] = RestApiHandler::class;
            // with or without route param
            $params[Route::ROUTE_PARAM] = $name;
            $name = 'restapi-path';
            $result[] = [$path, $name, $params, $fixed];
        }
        return $result;
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
        RouteTest::getRouteForParams($this, $expected, $route, $params);
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
        if ($route == 'restapi-path') {
            $this->markTestSkipped('Skip restapi-path for generate');
        }
        RouteTest::generateRoute($this, $routeUrl, $route, $params);
    }

    /**
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @param mixed $fixed
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("routeProvider")]
    public function testGetResult($routeUrl, $route, $params, $fixed = [])
    {
        $_SERVER['PATH_INFO'] = $routeUrl;
        //$request = Request::build($params, basename(self::$script));
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $result = $apiProvider->getOutput();
        if ($route == 'restapi-path' && !empty($params[Route::ROUTE_PARAM])) {
            $route = $params[Route::ROUTE_PARAM];
        }
        if ($result instanceof Response) {
            $output = $result->getContent();
            $resultFile = __DIR__ . '/restapi/' . $route . '.html';
        } else {
            $output = Format::json(json_decode($result, true));
            $resultFile = __DIR__ . '/restapi/' . $route . '.json';
        }
        if (!file_exists($resultFile)) {
            file_put_contents($resultFile, $output);
            $this->assertTrue(true);
        } else {
            $expexted = file_get_contents($resultFile);
            $this->assertEquals($expexted, $output);
        }

        unset($_SERVER["PATH_INFO"]);
    }
}
