<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Calibre\Metadata;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Output\RestApi;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Pages\PageId;

class RestApiTest extends TestCase
{
    protected static string $script;
    /** @var array<string, int> */
    protected static $expectedSize = [
        'calres' => 37341,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$script = $_SERVER["SCRIPT_NAME"];
    }

    public static function tearDownAfterClass(): void
    {
        $_SERVER["SCRIPT_NAME"] = self::$script;
    }

    public function testGetPathInfo(): void
    {
        $request = new Request();
        $apiHandler = new RestApi($request);
        $expected = "/index";
        $test = $apiHandler->getPathInfo();
        $this->assertEquals($expected, $test);

        $_SERVER["PATH_INFO"] = "/books/2";
        $request = new Request();
        $apiHandler = new RestApi($request);

        $expected = "/books/2";
        $test = $apiHandler->getPathInfo();
        $this->assertEquals($expected, $test);

        unset($_SERVER["PATH_INFO"]);
    }

    public function testMatchPathInfo(): void
    {
        $_SERVER["PATH_INFO"] = "/books/2";
        $request = new Request();
        $apiHandler = new RestApi($request);
        $path = $apiHandler->getPathInfo();

        $expected = ["page" => PageId::BOOK_DETAIL, "id" => 2];
        $test = $apiHandler->matchPathInfo($path);
        $this->assertEquals($expected, $test);

        $_SERVER["PATH_INFO"] = "/openapi";
        $request = new Request();
        $apiHandler = new RestApi($request);
        $path = $apiHandler->getPathInfo();

        $expected = RestApi::getOpenApi($request);
        $test = $apiHandler->matchPathInfo($path);
        $this->assertEquals($expected, $test);

        $expected = true;
        $test = $apiHandler->isExtra;
        $this->assertEquals($expected, $test);

        unset($_SERVER["PATH_INFO"]);
    }

    public function testSetParams(): void
    {
        $_SERVER["PATH_INFO"] = "/books/2";
        $request = new Request();
        $apiHandler = new RestApi($request);
        $path = $apiHandler->getPathInfo();
        $params = $apiHandler->matchPathInfo($path);
        $request = $apiHandler->setParams($params);

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
        $request = new Request();
        $apiHandler = new RestApi($request);
        $renderer = new JsonRenderer();
        $expected = $renderer->getJson($request);
        $test = $apiHandler->getJson();
        $this->assertEquals($expected, $test);
    }

    public function testGetScriptName(): void
    {
        $script = $_SERVER["SCRIPT_NAME"];
        $_SERVER["SCRIPT_NAME"] = "/" . Config::ENDPOINT["restapi"];
        $request = new Request();

        $expected = "restapi.php";
        $test = RestApi::getScriptName($request);
        $this->assertEquals($expected, $test);

        $_SERVER["SCRIPT_NAME"] = $script;
    }

    public function testGetOutput(): void
    {
        $request = Request::build([], basename(self::$script));
        $apiHandler = new RestApi($request);
        $expected = true;
        $test = $apiHandler->getOutput();
        $this->assertEquals($expected, str_starts_with($test, '{"title":"COPS",'));
    }

    public function testGetCustomColumns(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Custom Columns";
        $test = RestApi::getCustomColumns($request);
        $this->assertEquals($expected, $test["title"]);
    }

    public function testGetDatabases(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Databases";
        $test = RestApi::getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabase(): void
    {
        $request = Request::build(['db' => 0], basename(self::$script));
        $expected = "Database Types";
        $test = RestApi::getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 2;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabaseTable(): void
    {
        $request = Request::build(['db' => 0, 'type' => 'table'], basename(self::$script));
        $expected = "Database Type table";
        $test = RestApi::getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 43;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetTable(): void
    {
        $request = Request::build(['db' => 0, 'name' => 'books'], basename(self::$script));
        $expected = "Database Table books";
        $test = RestApi::getDatabases($request);
        $this->assertEquals($expected, $test["title"]);

        $expected = "Invalid api key";
        $this->assertEquals($expected, $test["error"]);

        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');

        // less than RestApi::$numberPerPage;
        $expected = 16;
        $test = RestApi::getDatabases($request);
        $this->assertCount($expected, $test["entries"]);

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
    }

    public function testGetOpenApi(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "3.0.3";
        $test = RestApi::getOpenApi($request);
        $this->assertEquals($expected, $test["openapi"]);
    }

    public function testGetRoutes(): void
    {
        $request = Request::build([], basename(self::$script));
        $test = RestApi::getRoutes($request);
        $expected = "Routes";
        $this->assertEquals($expected, $test["title"]);
        $expected = 97;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetPages(): void
    {
        $request = Request::build([], basename(self::$script));
        $test = RestApi::getPages($request);
        $expected = "Pages";
        $this->assertEquals($expected, $test["title"]);
        $expected = 41;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNotes(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Notes";
        $test = RestApi::getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNotesByType(): void
    {
        $request = Request::build(['type' => 'authors'], basename(self::$script));
        $expected = "Notes for authors";
        $test = RestApi::getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNoteByTypeId(): void
    {
        $request = Request::build(['type' => 'authors', 'id' => 3], basename(self::$script));
        $expected = "Note for authors #3";
        $test = RestApi::getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["resources"]);
    }

    public function testGetPreferences(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Preferences";
        $test = RestApi::getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 13;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetPreferenceByKey(): void
    {
        $request = Request::build([], basename(self::$script));
        $request->set('key', 'saved_searches');
        $expected = "Preference for saved_searches";
        $test = RestApi::getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["val"]);
    }

    public function testGetAnnotations(): void
    {
        $request = Request::build([], basename(self::$script));
        $expected = "Annotations";
        $test = RestApi::getAnnotations($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
        $expected = "Annotations for 17";
        $this->assertEquals($expected, $test["entries"][0]["title"]);
        if (Config::get('use_route_urls')) {
            $expected = "index.php/restapi/annotations/17";
        } else {
            $expected = "index.php/annotations/17";
        }
        $this->assertStringEndsWith($expected, $test["entries"][0]["navlink"]);
        $expected = 5;
        $this->assertEquals($expected, $test["entries"][0]["number"]);
    }

    public function testGetAnnotationsByBookId(): void
    {
        $request = Request::build(['bookId' => 17], basename(self::$script));
        $expected = "Annotations for 17";
        $test = RestApi::getAnnotations($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 5;
        $this->assertCount($expected, $test["entries"]);
        $expected = "(17) Bookmark About #1";
        $this->assertEquals($expected, $test["entries"][0]["title"]);
        if (Config::get('use_route_urls')) {
            $expected = 'index.php/restapi/annotations/17/1';
        } else {
            $expected = 'index.php?page=63&bookId=17&id=1';
        }
        $this->assertStringEndsWith($expected, $test["entries"][0]["navlink"]);
    }

    public function testGetAnnotationById(): void
    {
        $request = Request::build(['bookId' => 17, 'id' => 1], basename(self::$script));
        $expected = "(17) Bookmark About #1";
        $test = RestApi::getAnnotations($request);
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
        $test = RestApi::getMetadata($request);
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
        $request = new Request();
        $request->set('bookId', 17);
        $request->set('element', $element);
        $expected = "Metadata for 17";
        $test = RestApi::getMetadata($request);
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
        $request = new Request();
        $request->set('bookId', 17);
        $request->set('element', $element);
        $request->set('name', $name);
        $expected = "Metadata for 17";
        $test = RestApi::getMetadata($request);
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
        $request = new Request();
        $expected = "Invalid username";
        $test = RestApi::getUser($request);
        $this->assertEquals($expected, $test["error"]);
    }

    public function testGetUser(): void
    {
        $http_auth_user = Config::get('http_auth_user', 'PHP_AUTH_USER');
        $_SERVER[$http_auth_user] = "admin";
        $request = new Request();
        $expected = "admin";
        $test = RestApi::getUser($request);
        $this->assertEquals($expected, $test["username"]);
        unset($_SERVER[$http_auth_user]);
    }

    public function testGetUserDetails(): void
    {
        Config::set('calibre_user_database', __DIR__ . "/BaseWithSomeBooks/users.db");
        $http_auth_user = Config::get('http_auth_user', 'PHP_AUTH_USER');
        $_SERVER[$http_auth_user] = 'admin';
        $_SERVER['PATH_INFO'] = '/user/details';
        $request = new Request();

        $expected = "admin";
        $test = RestApi::getUser($request);
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
        $request = new Request();
        RestApi::$doRunHandler = false;

        $apiHandler = new RestApi($request);
        $expected = [
            Route::HANDLER_PARAM => "zipfs",
            "path" => "/zipfs/0/20/META-INF/container.xml",
            "params" => [
                "db" => "0",
                "data" => "20",
                "comp" => "META-INF/container.xml",
            ],
        ];
        $expected = json_encode($expected, JSON_UNESCAPED_SLASHES);
        $test = $apiHandler->getOutput();
        $this->assertEquals($expected, $test);

        RestApi::$doRunHandler = true;
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
        $request = new Request();
        RestApi::$doRunHandler = true;

        ob_start();
        $apiHandler = new RestApi($request);
        $result = $apiHandler->getOutput();
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['calres'];
        $this->assertTrue($result instanceof Response);
        $this->assertEquals(0, count($headers));
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
        $_SERVER['PATH_INFO'] = '/thumbs/html/0/17.jpg';
        $request = new Request();
        RestApi::$doRunHandler = false;

        $apiHandler = new RestApi($request);
        $expected = [
            Route::HANDLER_PARAM => "fetch",
            // check if the path starts with the handler param here
            "path" => "/thumbs/html/0/17.jpg",
            "params" => [
                "thumb" => "html",
                "db" => "0",
                "id" => "17",
            ],
        ];
        $expected = json_encode($expected, JSON_UNESCAPED_SLASHES);
        $test = $apiHandler->getOutput();
        $this->assertEquals($expected, $test);

        RestApi::$doRunHandler = true;
        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
        unset($_SERVER['PATH_INFO']);
    }
}
