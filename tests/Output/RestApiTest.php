<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Calibre\Metadata;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Framework\FrameworkTodo;
use SebLucas\Cops\Handlers\CheckHandler;
use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Handlers\RestApiHandler;
use SebLucas\Cops\Handlers\ZipFsHandler;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\RestApiProvider;
use SebLucas\Cops\Tests\Routing\RouteTest;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\TestHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Routing\UriGenerator;

class RestApiTest extends TestCase
{
    /** @var class-string<BaseHandler> */
    protected static string $handler;
    protected static RequestContext $context;
    protected static RestApiProvider $apiProvider;
    /** @var array<string, int> */
    protected static $expectedSize = [
        'calres' => 37341,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$handler = TestHandler::class;
        UriGenerator::setScriptName($_SERVER["SCRIPT_NAME"]);
        UriGenerator::setBaseUrl(null);
        $framework = new FrameworkTodo();
        self::$context = $framework->getContext();
        self::$apiProvider = new RestApiProvider(self::$context->getRequest());
        self::$apiProvider->setContext(self::$context);
    }

    public static function tearDownAfterClass(): void
    {
        // ...
    }

    public function testGetPathInfo(): void
    {
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $expected = "/index";
        $test = $apiProvider->getPathInfo();
        $this->assertEquals($expected, $test);

        $path = "/books/2";
        $request = Framework::getRequest($path);
        $apiProvider = new RestApiProvider($request);

        $expected = "/books/2";
        $test = $apiProvider->getPathInfo();
        $this->assertEquals($expected, $test);
    }

    public function testMatchPathInfo(): void
    {
        $path = "/books/2";
        $request = Framework::getRequest($path);
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $path = $apiProvider->getPathInfo();

        $expected = ["page" => PageId::BOOK_DETAIL, "id" => 2, "_route" => "page-book-id"];
        $test = $apiProvider->matchPathInfo($path);
        $this->assertEquals($expected, $test);

        $path = "/restapi/openapi";
        $request = Framework::getRequest($path);
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $path = $apiProvider->getPathInfo();

        $expected = self::$apiProvider->getOpenApi($request);
        $test = $apiProvider->matchPathInfo($path);
        $this->assertEquals($expected, $test);

        $expected = true;
        $test = $apiProvider->isExtra;
        $this->assertEquals($expected, $test);
    }

    public function testSetParams(): void
    {
        $path = "/books/2";
        $request = Framework::getRequest($path);
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $path = $apiProvider->getPathInfo();
        $params = $apiProvider->matchPathInfo($path);
        $request = $apiProvider->getRequest()->setParams($params);

        $expected = PageId::BOOK_DETAIL;
        $test = $request->get("page");
        $this->assertEquals($expected, $test);

        $expected = 2;
        $test = $request->getId();
        $this->assertEquals($expected, $test);
    }

    public function testGetJson(): void
    {
        $request = Framework::getRequest();
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $renderer = new JsonRenderer();
        $expected = $renderer->getJson($request);
        $test = $apiProvider->getJson();
        unset($expected['counters']);
        unset($test['counters']);
        $this->assertEquals($expected, $test);
    }

    public function testGetOutput(): void
    {
        $request = Request::build([], self::$handler);
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $expected = true;
        $test = $apiProvider->getOutput();
        $this->assertEquals($expected, str_starts_with($test, '{"title":"COPS",'));
    }

    public function testGetCustomColumns(): void
    {
        $request = Request::build([], self::$handler);
        $expected = "Custom Columns";
        $test = self::$apiProvider->getCustomColumns($request);
        $this->assertEquals($expected, $test["title"]);
    }

    public function testGetDatabases(): void
    {
        $request = Request::build([], self::$handler);
        $expected = "Databases";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabase(): void
    {
        $request = Request::build(['db' => 0], self::$handler);
        $expected = "Database Types";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 2;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabaseTable(): void
    {
        $request = Request::build(['db' => 0, 'type' => 'table'], self::$handler);
        $expected = "Database Type table";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 44;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetTable(): void
    {
        $request = Request::build(['db' => 0, 'name' => 'books'], self::$handler);
        $expected = "Database Table books";
        $test = self::$apiProvider->getDatabases($request);
        $this->assertEquals($expected, $test["title"]);

        $expected = "Invalid api key";
        $this->assertEquals($expected, $test["error"]);

        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $server = ['HTTP_X_API_KEY' => Config::get('api_key')];
        $request = Request::build(['db' => 0, 'name' => 'books'], self::$handler, $server);

        // less than self::$apiProvider->$numberPerPage;
        $expected = 16;
        $test = self::$apiProvider->getDatabases($request);
        $this->assertCount($expected, $test["entries"]);

        Config::set('api_key', null);
    }

    public function testGetOpenApi(): void
    {
        $request = Request::build([], self::$handler);
        $expected = "3.0.3";
        $test = self::$apiProvider->getOpenApi($request);
        $this->assertEquals($expected, $test["openapi"]);

        $cacheFile = dirname(__DIR__, 2) . '/resources/openapi.json';
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
        $request = Request::build([], self::$handler);
        $test = self::$apiProvider->getRoutes($request);
        $expected = "Routes";
        $this->assertEquals($expected, $test["title"]);
        $expected = count(self::$context->getHandlerManager()->getRoutes());
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetHandlers(): void
    {
        $request = Request::build([], self::$handler);
        $test = self::$apiProvider->getHandlers($request);
        $expected = "Handlers";
        $this->assertEquals($expected, $test["title"]);
        $expected = count(self::$context->getHandlerManager()->getHandlers());
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNotes(): void
    {
        $request = Request::build([], self::$handler);
        $expected = "Notes";
        $test = self::$apiProvider->getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNotesByType(): void
    {
        $request = Request::build(['type' => 'authors'], self::$handler);
        $expected = "Notes for authors";
        $test = self::$apiProvider->getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 3;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNoteByTypeItem(): void
    {
        $request = Request::build(['type' => 'authors', 'item' => 3], self::$handler);
        $expected = "Note for authors #3";
        $test = self::$apiProvider->getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["resources"]);
    }

    public function testGetPreferences(): void
    {
        $request = Request::build([], self::$handler);
        $expected = "Preferences";
        $test = self::$apiProvider->getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 14;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetPreferenceByKey(): void
    {
        $request = Request::build([], self::$handler);
        $request->set('key', 'virtual_libraries');
        $expected = "Preference for virtual_libraries";
        $test = self::$apiProvider->getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 2;
        $this->assertCount($expected, $test["val"]);
    }

    public function testGetAnnotations(): void
    {
        $request = Request::build([], self::$handler);
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
        $request = Request::build(['bookId' => 17], self::$handler);
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
        $request = Request::build(['bookId' => 17, 'id' => 1], self::$handler);
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
        $request = Request::build(['bookId' => 17], self::$handler);
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
        $request = Request::build(['bookId' => 17, 'element' => $element], self::$handler);
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
        $request = Request::build(['bookId' => 17, 'element' => $element, 'name' => $name], self::$handler);
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
        $request = Framework::getRequest();
        $request->serverParams[$http_auth_user] = "admin";
        $expected = "admin";
        $test = self::$apiProvider->getUser($request);
        $this->assertEquals($expected, $test["username"]);
    }

    public function testGetUserDetails(): void
    {
        Config::set('calibre_user_database', dirname(__DIR__) . "/BaseWithSomeBooks/users.db");
        $http_auth_user = Config::get('http_auth_user', 'PHP_AUTH_USER');
        $path = '/restapi/user/details';
        $request = Framework::getRequest($path);
        $request->serverParams[$http_auth_user] = "admin";

        $expected = "admin";
        $test = self::$apiProvider->getUser($request);
        $this->assertEquals($expected, $test["username"]);

        $expected = ['library_restrictions' => []];
        $this->assertEquals($expected, $test["restriction"]);

        Config::set('calibre_user_database', null);
    }

    public function testRunHandlerFalse(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $path = '/zipfs/0/20/META-INF/container.xml';
        $request = Framework::getRequest($path);
        $request->serverParams['HTTP_X_API_KEY'] = Config::get('api_key');

        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $apiProvider->doRunHandler = false;
        $expected = [
            Request::HANDLER_PARAM => ZipFsHandler::class,
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
    }

    public function testRunHandlerTrue(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $path = '/calres/0/xxh64/7c301792c52eebf7';
        $request = Framework::getRequest($path);
        $request->serverParams['HTTP_X_API_KEY'] = Config::get('api_key');

        ob_start();
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $apiProvider->doRunHandler = true;
        $result = $apiProvider->getOutput();
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['calres'];
        $this->assertTrue($result instanceof Response);
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('api_key', null);
    }

    public function testRunHandlerDifferentRoot(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $path = '/thumbs/0/17/html.jpg';
        $request = Framework::getRequest($path);
        $request->serverParams['HTTP_X_API_KEY'] = Config::get('api_key');

        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $apiProvider->doRunHandler = false;
        $expected = [
            Request::HANDLER_PARAM => FetchHandler::class,
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
            'page-authors-letter' => ['letter' => 'C'],
            'page-publishers-letter' => ['letter' => 'M'],
            'page-series-letter' => ['letter' => 'S'],
            'page-tags-letter' => ['letter' => 'F'],
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
            $params[Request::HANDLER_PARAM] = RestApiHandler::class;
            // with or without route param
            $params[Request::ROUTE_PARAM] = $name;
            $name = 'restapi-path';
            $result[] = [$path, $name, $params, $fixed];
        }
        return $result;
    }

    /**
     * @param mixed $expected
     * @param mixed $route
     * @param mixed $params
     * @param mixed $ignored
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('routeProvider')]
    public function testGetRouteForParams($expected, $route, $params, $ignored)
    {
        RouteTest::getRouteForParams($this, $expected, $route, $params);
    }

    /**
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @param mixed $ignored
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("routeProvider")]
    public function testGenerateRoute($routeUrl, $route, $params, $ignored)
    {
        $prefix = "";
        if ($route == 'restapi-path') {
            if (empty($params[Request::ROUTE_PARAM])) {
                $this->markTestSkipped('Skip restapi-path without route param for generate');
            }
            $prefix = "/restapi";
            $route = $params[Request::ROUTE_PARAM];
        }
        RouteTest::generateRoute($this, $routeUrl, $route, $params, $prefix);
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
        $path = $routeUrl;
        //$request = Request::build($params, self::$handler);
        $request = Framework::getRequest($path);
        $apiProvider = new RestApiProvider($request);
        $apiProvider->setContext(self::$context);
        $result = $apiProvider->getOutput();
        if ($route == 'restapi-path' && !empty($params[Request::ROUTE_PARAM])) {
            $route = $params[Request::ROUTE_PARAM];
        }
        if ($result instanceof Response) {
            $output = $result->getContent();
            $resultFile = dirname(__DIR__) . '/restapi/' . $route . '.html';
        } else {
            $output = Format::json(json_decode($result, true));
            $resultFile = dirname(__DIR__) . '/restapi/' . $route . '.json';
        }
        if (!file_exists($resultFile)) {
            file_put_contents($resultFile, $output);
        }
        $expected = file_get_contents($resultFile);
        if (str_contains($output ?? '', ' "mtime": ')) {
            $output = preg_replace('/ "mtime": \S+/', ' "mtime": "now"', $output);
            $expected = preg_replace('/ "mtime": \S+/', ' "mtime": "now"', $expected);
        }
        $this->assertEquals($expected, $output);
    }
}
