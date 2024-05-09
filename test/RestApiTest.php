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
use SebLucas\Cops\Output\RestApi;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Pages\PageId;

class RestApiTest extends TestCase
{
    public static string $script;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$script = $_SERVER["SCRIPT_NAME"];
        // try out route urls
        Config::set('use_route_urls', true);
    }

    public static function tearDownAfterClass(): void
    {
        $_SERVER["SCRIPT_NAME"] = self::$script;
        Config::set('use_route_urls', null);
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
        $expected = JsonRenderer::getJson($request);
        $test = $apiHandler->getJson();
        $this->assertEquals($expected, $test);
    }

    public function testGetScriptName(): void
    {
        $script = $_SERVER["SCRIPT_NAME"];
        $_SERVER["SCRIPT_NAME"] = "/" . RestApi::$endpoint;
        $request = new Request();

        $expected = "restapi.php";
        $test = RestApi::getScriptName($request);
        $this->assertEquals($expected, $test);

        $_SERVER["SCRIPT_NAME"] = $script;
    }

    /**
     * Summary of getLinks
     * @return array<mixed>
     */
    public static function getLinks()
    {
        return [
            "restapi.php?page=index" => "restapi.php/index",
            "restapi.php?page=1" => "restapi.php/authors",
            "restapi.php?page=1&letter=1" => "restapi.php/authors/letter",
            "restapi.php?page=2&id=D" => "restapi.php/authors/letter/D",
            "restapi.php?page=3&id=1" => "restapi.php/authors/1",
            "restapi.php?page=4" => "restapi.php/books",
            "restapi.php?page=4&letter=1" => "restapi.php/books/letter",
            "restapi.php?page=5&id=A" => "restapi.php/books/letter/A",
            "restapi.php?page=4&year=1" => "restapi.php/books/year",
            "restapi.php?page=50&id=2006" => "restapi.php/books/year/2006",
            "restapi.php?page=6" => "restapi.php/series",
            "restapi.php?page=7&id=1" => "restapi.php/series/1",
            "restapi.php?page=8" => "restapi.php/search",
            "restapi.php?page=9&query=alice" => "restapi.php/search/alice",
            "restapi.php?page=9&query=alice&scope=book" => "restapi.php/search/alice/book",
            "restapi.php?page=10" => "restapi.php/recent",
            "restapi.php?page=11" => "restapi.php/tags",
            "restapi.php?page=12&id=1" => "restapi.php/tags/1",
            "restapi.php?page=13&id=2" => "restapi.php/books/2",
            "restapi.php?page=14&custom=1" => "restapi.php/custom/1",
            "restapi.php?page=15&custom=1&id=2" => "restapi.php/custom/1/2",
            "restapi.php?page=16" => "restapi.php/about",
            "restapi.php?page=17" => "restapi.php/languages",
            "restapi.php?page=18&id=1" => "restapi.php/languages/1",
            "restapi.php?page=19" => "restapi.php/customize",
            "restapi.php?page=20" => "restapi.php/publishers",
            "restapi.php?page=21&id=1" => "restapi.php/publishers/1",
            "restapi.php?page=22" => "restapi.php/ratings",
            "restapi.php?page=23&id=1" => "restapi.php/ratings/1",
            "restapi.php?page=22&a=1" => "restapi.php/ratings?a=1",
            "restapi.php?page=23&id=1&a=1" => "restapi.php/ratings/1?a=1",
            "calres.php?db=0&alg=xxh64&digest=7c301792c52eebf7" => "restapi.php/calres/0/xxh64/7c301792c52eebf7",
            "zipfs.php?db=0&idData=20&component=META-INF%2Fcontainer.xml" => "restapi.php/zipfs/0/20/META-INF/container.xml",
            "loader.php?action=wd_author&dbNum=0&authorId=1&matchId=Q35610" => "restapi.php/loader/wd_author/0/1?matchId=Q35610",
            "checkconfig.php" => "restapi.php/check",
            "epubreader.php?db=0&data=20" => "restapi.php/read/0/20",
            "fetch.php?thumb=html&db=0&id=17" => "restapi.php/thumbs/html/0/17.jpg",
            "fetch.php?thumb=opds&db=0&id=17" => "restapi.php/thumbs/opds/0/17.jpg",
            "fetch.php?db=0&id=17" => "restapi.php/covers/0/17.jpg",
            "fetch.php?view=1&db=0&data=20&type=epub" => "restapi.php/view/0/20/ignore.epub",
            "fetch.php?db=0&data=20&type=epub" => "restapi.php/fetch/0/20/ignore.epub",
            "download.php?page=10&type=any" => "restapi.php/download/10/any",
        ];
    }

    /**
     * Summary of linkProvider
     * @return array<mixed>
     */
    public static function linkProvider()
    {
        $data = [];
        $links = static::getLinks();
        foreach ($links as $from => $to) {
            array_push($data, [$from, $to]);
        }
        return $data;
    }

    /**
     * @dataProvider linkProvider
     * @param mixed $link
     * @param mixed $expected
     * @return void
     */
    public function testRouteLink($link, $expected)
    {
        $params = [];
        parse_str(parse_url($link, PHP_URL_QUERY) ?? '', $params);
        $page = $params["page"] ?? null;
        unset($params["page"]);
        $endpoint = parse_url($link, PHP_URL_PATH);
        if ($endpoint !== RestApi::$endpoint) {
            $testpoint = str_replace('.php', '', $endpoint);
            if (array_key_exists($testpoint, Config::ENDPOINT)) {
                $params[Route::ENDPOINT_PARAM] = $testpoint;
            } else {
                // for epubreader.php, checkconfig.php etc.
                $flipped = array_flip(Config::ENDPOINT);
                $params[Route::ENDPOINT_PARAM] = $flipped[$endpoint];
            }
        }
        $test = RestApi::$endpoint . Route::page($page, $params);
        $this->assertEquals($expected, $test);
    }

    /**
     * @dataProvider linkProvider
     * @param mixed $expected
     * @param mixed $path
     * @return void
     */
    public function testRouteMatch($expected, $path)
    {
        $query = parse_url($path, PHP_URL_QUERY);
        $path = parse_url($path, PHP_URL_PATH);
        $parts = explode('/', $path);
        $endpoint = array_shift($parts);
        $path = '/' . implode('/', $parts);
        $params = Route::match($path);
        if (is_null($params)) {
            $this->fail('Invalid params for path ' . $path);
        }
        if (!empty($params[Route::ENDPOINT_PARAM]) && array_key_exists($params[Route::ENDPOINT_PARAM], Config::ENDPOINT)) {
            $endpoint = Config::ENDPOINT[$params[Route::ENDPOINT_PARAM]];
            unset($params[Route::ENDPOINT_PARAM]);
        }
        if (array_key_exists('ignore', $params)) {
            unset($params['ignore']);
        }
        $test = $endpoint;
        if (!empty($params)) {
            $test .= '?' . http_build_query($params);
        }
        if (!empty($query)) {
            $test .= '&' . $query;
        }
        $this->assertEquals($expected, $test);
    }

    public function testRouteGetPageRoute(): void
    {
        $this->assertEquals("/calres/0/xxh64/7c301792c52eebf7", Route::getPageRoute([Route::ENDPOINT_PARAM => "calres", "db" => 0, "alg" => "xxh64", "digest" => "7c301792c52eebf7"]));
        $this->assertEquals("/zipfs/0/20/META-INF/container.xml", Route::getPageRoute([Route::ENDPOINT_PARAM => "zipfs", "db" => 0, "idData" => 20, "component" => "META-INF/container.xml"]));
        $this->assertNull(Route::getPageRoute([Route::ENDPOINT_PARAM => "zipfs", "db" => "x", "idData" => 20, "component" => "META-INF/container.xml"]));
        $this->assertEquals("/loader/wd_author/0/1?matchId=Q35610", Route::getPageRoute([Route::ENDPOINT_PARAM => "loader", "action" => "wd_author", "dbNum" => 0, "authorId" => 1, "matchId" => "Q35610"]));
        $this->assertEquals("/thumbs/html/0/17.jpg", Route::getPageRoute([Route::ENDPOINT_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "html"]));
        $this->assertEquals("/thumbs/opds/0/17.jpg", Route::getPageRoute([Route::ENDPOINT_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "opds"]));
        $this->assertEquals("/thumbs/html2/0/17.jpg", Route::getPageRoute([Route::ENDPOINT_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "html2"]));
        $this->assertEquals("/thumbs/opds2/0/17.jpg", Route::getPageRoute([Route::ENDPOINT_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "opds2"]));
        $this->assertEquals("/covers/0/17.jpg", Route::getPageRoute([Route::ENDPOINT_PARAM => "fetch", "db" => 0, "id" => 17]));
        $this->assertEquals("/view/0/20/ignore.epub", Route::getPageRoute([Route::ENDPOINT_PARAM => "fetch", "db" => 0, "data" => 20, "type" => "epub", "view" => 1]));
        $this->assertEquals("/fetch/0/20/ignore.epub", Route::getPageRoute([Route::ENDPOINT_PARAM => "fetch", "db" => 0, "data" => 20, "type" => "epub"]));
        $this->assertEquals("/download/10/any", Route::getPageRoute([Route::ENDPOINT_PARAM => "download", "page" => 10, "type" => "any"]));
    }

    /**
     * Summary of getRewrites
     * @return array<mixed>
     */
    public static function getRewrites()
    {
        return [
            "fetch.php?data=1&db=0&type=epub&view=1" => "/view/1/0/ignore.epub",
            "fetch.php?data=1&type=epub&view=1" => "/view/1/ignore.epub",
            "fetch.php?data=1&db=0&type=epub" => "/download/1/0/ignore.epub",
            "fetch.php?data=1&type=epub" => "/download/1/ignore.epub",
        ];
    }

    /**
     * Summary of rewriteProvider
     * @return array<mixed>
     */
    public static function rewriteProvider()
    {
        $data = [];
        $links = static::getRewrites();
        foreach ($links as $from => $to) {
            array_push($data, [$from, $to]);
        }
        return $data;
    }

    /**
     * @dataProvider rewriteProvider
     * @param mixed $link
     * @param mixed $expected
     * @return void
     */
    public function testRewriteLink($link, $expected)
    {
        $params = [];
        parse_str(parse_url($link, PHP_URL_QUERY), $params);
        $endpoint = parse_url($link, PHP_URL_PATH);
        $test = Route::getUrlRewrite($endpoint, $params);
        $this->assertEquals($expected, $test);
    }

    /**
     * @dataProvider rewriteProvider
     * @param mixed $expected
     * @param mixed $path
     * @return void
     */
    public function testRewriteMatch($expected, $path)
    {
        $query = parse_url($path, PHP_URL_QUERY);
        $path = parse_url($path, PHP_URL_PATH);
        //$endpoint = parse_url($expected, PHP_URL_PATH);
        [$endpoint, $params] = Route::matchRewrite($path);
        $test = $endpoint . '?' . http_build_query($params);
        if (!empty($query)) {
            $test .= '&' . $query;
        }
        $this->assertEquals($expected, $test);
    }

    public function testGetOutput(): void
    {
        $request = new Request();
        $apiHandler = new RestApi($request);
        $expected = true;
        $test = $apiHandler->getOutput();
        $this->assertEquals($expected, strncmp($test, '{"title":"COPS",', strlen('{"title":"COPS",')) === 0);
    }

    public function testGetCustomColumns(): void
    {
        $request = new Request();
        $expected = "Custom Columns";
        $test = RestApi::getCustomColumns($request);
        $this->assertEquals($expected, $test["title"]);
    }

    public function testGetDatabases(): void
    {
        $request = new Request();
        $expected = "Databases";
        $test = RestApi::getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabase(): void
    {
        $request = new Request();
        $request->set('db', 0);
        $expected = "Database Types";
        $test = RestApi::getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 2;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetDatabaseTable(): void
    {
        $request = new Request();
        $request->set('db', 0);
        $request->set('type', 'table');
        $expected = "Database Type table";
        $test = RestApi::getDatabases($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 43;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetTable(): void
    {
        $request = new Request();
        $request->set('db', 0);
        $request->set('name', 'books');
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
        $expected = 15;
        $test = RestApi::getDatabases($request);
        $this->assertCount($expected, $test["entries"]);

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
    }

    public function testGetOpenApi(): void
    {
        $request = new Request();
        $expected = "3.0.3";
        $test = RestApi::getOpenApi($request);
        $this->assertEquals($expected, $test["openapi"]);
    }

    public function testGetRoutes(): void
    {
        $request = new Request();
        $expected = "Routes";
        $test = RestApi::getRoutes($request);
        $this->assertEquals($expected, $test["title"]);
    }

    public function testGetNotes(): void
    {
        $request = new Request();
        $expected = "Notes";
        $test = RestApi::getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNotesByType(): void
    {
        $request = new Request();
        $request->set('type', 'authors');
        $expected = "Notes for authors";
        $test = RestApi::getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetNoteByTypeId(): void
    {
        $request = new Request();
        $request->set('type', 'authors');
        $request->set('id', 3);
        $expected = "Note for authors #3";
        $test = RestApi::getNotes($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["resources"]);
    }

    public function testGetPreferences(): void
    {
        $request = new Request();
        $expected = "Preferences";
        $test = RestApi::getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 13;
        $this->assertCount($expected, $test["entries"]);
    }

    public function testGetPreferenceByKey(): void
    {
        $request = new Request();
        $request->set('key', 'saved_searches');
        $expected = "Preference for saved_searches";
        $test = RestApi::getPreferences($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["val"]);
    }

    public function testGetAnnotations(): void
    {
        $request = new Request();
        $expected = "Annotations";
        $test = RestApi::getAnnotations($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 1;
        $this->assertCount($expected, $test["entries"]);
        $expected = "Annotations for 17";
        $this->assertEquals($expected, $test["entries"][0]["title"]);
        $expected = static::$script . "/annotations/17";
        $this->assertEquals($expected, $test["entries"][0]["navlink"]);
        $expected = 5;
        $this->assertEquals($expected, $test["entries"][0]["number"]);
    }

    public function testGetAnnotationsByBookId(): void
    {
        $request = new Request();
        $request->set('bookId', 17);
        $expected = "Annotations for 17";
        $test = RestApi::getAnnotations($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = 5;
        $this->assertCount($expected, $test["entries"]);
        $expected = "(17) Bookmark About #1";
        $this->assertEquals($expected, $test["entries"][0]["title"]);
        $expected = static::$script . '/annotations/17/1';
        $this->assertEquals($expected, $test["entries"][0]["navlink"]);
    }

    public function testGetAnnotationById(): void
    {
        $request = new Request();
        $request->set('bookId', 17);
        $request->set('id', 1);
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
        $request = new Request();
        $request->set('bookId', 17);
        $expected = "Metadata for 17";
        $test = RestApi::getMetadata($request);
        $this->assertEquals($expected, $test["title"]);
        $expected = Metadata::class;
        $this->assertEquals($expected, get_class($test["entries"]));
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

    public function testRunEndpointFalse(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');
        $_SERVER['PATH_INFO'] = '/zipfs/0/20/META-INF/container.xml';
        $request = new Request();

        $apiHandler = new RestApi($request);
        $expected = [
            "endpoint" => "zipfs.php",
            "path" => "/0/20/META-INF/container.xml",
            "params" => [
                "db" => "0",
                "idData" => "20",
                "component" => "META-INF/container.xml",
            ],
        ];
        $expected = json_encode($expected, JSON_UNESCAPED_SLASHES);
        $test = $apiHandler->getOutput();
        $this->assertEquals($expected, $test);

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
        unset($_SERVER['PATH_INFO']);
    }

    /**
     * Summary of testRunEndpointTrue
     * @runInSeparateProcess
     * @return void
     */
    public function testRunEndpointTrue(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');
        $_SERVER['PATH_INFO'] = '/calres/0/xxh64/7c301792c52eebf7';
        $request = new Request();
        RestApi::$doRunEndpoint = true;

        ob_start();
        $apiHandler = new RestApi($request);
        $test = $apiHandler->getOutput();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = 37341;
        $this->assertEquals('', $test);
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        RestApi::$doRunEndpoint = false;
        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
        unset($_SERVER['PATH_INFO']);
    }

    public function testRunEndpointDifferentRoot(): void
    {
        // generate api key and pass along in request
        $apiKey = bin2hex(random_bytes(20));
        Config::set('api_key', $apiKey);
        $_SERVER['HTTP_X_API_KEY'] = Config::get('api_key');
        $_SERVER['PATH_INFO'] = '/thumbs/html/0/17.jpg';
        $request = new Request();

        $apiHandler = new RestApi($request);
        $expected = [
            "endpoint" => "fetch.php",
            // check if the path starts with the endpoint param here
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

        Config::set('api_key', null);
        unset($_SERVER['HTTP_X_API_KEY']);
        unset($_SERVER['PATH_INFO']);
    }
}
