<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use Exception;
use SebLucas\Cops\Input\RouteLoader;
use SebLucas\Cops\Input\Routing;

require_once dirname(__DIR__) . "/config/test.php";
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;

class RoutingTest extends TestCase
{
    /** @var Routing */
    protected static $routing;

    public static function setUpBeforeClass(): void
    {
        Config::set("calibre_directory", __DIR__ . "/BaseWithSomeBooks/");
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
        //return RouteTest::linkProvider();
        return [
            ["index.php?page=index", "/index", "page-index", ["page" => "index"]],
            ["index.php?page=1", "/authors", "page-1", ["page" => "1"]],
            ["index.php?page=1&letter=1", "/authors/letter", "page-1-letter", ["page" => "1", "letter" => "1"]],
            ["index.php?page=2&id=D", "/authors/letter/D", "page-2-id", ["page" => "2", "id" => "D"]],
            ["index.php?page=3&id=1", "/authors/1", "page-3-id", ["page" => "3", "id" => "1"]],
            ["index.php?page=4", "/books", "page-4", ["page" => "4"]],
            ["index.php?page=4&letter=1", "/books/letter", "page-4-letter", ["page" => "4", "letter" => "1"]],
            ["index.php?page=5&id=A", "/books/letter/A", "page-5-id", ["page" => "5", "id" => "A"]],
            ["index.php?page=4&year=1", "/books/year", "page-4-year", ["page" => "4", "year" => "1"]],
            ["index.php?page=50&id=2006", "/books/year/2006", "page-50-id", ["page" => "50", "id" => "2006"]],
            ["index.php?page=6", "/series", "page-6", ["page" => "6"]],
            ["index.php?page=7&id=1", "/series/1", "page-7-id", ["page" => "7", "id" => "1"]],
            ["index.php?page=8", "/search", "page-8", ["page" => "8"]],
            ["index.php?page=9&query=alice", "/search/alice", "page-9-search", ["page" => "9", "query" => "alice"]],
            ["index.php?page=9&query=alice&scope=book", "/search/alice/book", "page-9-search-scope", ["page" => "9", "query" => "alice", "scope" => "book"]],
            ["index.php?page=9&search=1&query=alice", "/query/alice", "page-9-query", ["page" => "9", "search" => "1", "query" => "alice"]],
            ["index.php?page=9&search=1&query=alice&scope=book", "/query/alice/book", "page-9-query-scope", ["page" => "9", "search" => "1", "query" => "alice", "scope" => "book"]],
            ["index.php?page=10", "/recent", "page-10", ["page" => "10"]],
            ["index.php?page=11", "/tags", "page-11", ["page" => "11"]],
            ["index.php?page=12&id=1", "/tags/1", "page-12-id", ["page" => "12", "id" => "1"]],
            ["index.php?page=13&id=2", "/books/2", "page-13-id", ["page" => "13", "id" => "2"]],
            ["index.php?page=14&custom=1", "/custom/1", "page-14-custom", ["page" => "14", "custom" => "1"]],
            ["index.php?page=15&custom=1&id=2", "/custom/1/2", "page-15-custom-id", ["page" => "15", "custom" => "1", "id" => "2"]],
            ["index.php?page=16", "/about", "page-16", ["page" => "16"]],
            ["index.php?page=17", "/languages", "page-17", ["page" => "17"]],
            ["index.php?page=18&id=1", "/languages/1", "page-18-id", ["page" => "18", "id" => "1"]],
            ["index.php?page=19", "/customize", "page-19", ["page" => "19"]],
            ["index.php?page=20", "/publishers", "page-20", ["page" => "20"]],
            ["index.php?page=21&id=1", "/publishers/1", "page-21-id", ["page" => "21", "id" => "1"]],
            ["index.php?page=22", "/ratings", "page-22", ["page" => "22"]],
            ["index.php?page=23&id=1", "/ratings/1", "page-23-id", ["page" => "23", "id" => "1"]],
            ["index.php?page=22&a=1", "/ratings?a=1", "page-22", ["page" => "22", "a" => "1"]],
            ["index.php?page=23&id=1&a=1", "/ratings/1?a=1", "page-23-id", ["page" => "23", "id" => "1", "a" => "1"]],
            ["calres.php?db=0&alg=xxh64&digest=7c301792c52eebf7", "/calres/0/xxh64/7c301792c52eebf7", "calres", ["_handler" => "calres", "db" => "0", "alg" => "xxh64", "digest" => "7c301792c52eebf7"]],
            ["zipfs.php?db=0&data=20&comp=META-INF%2Fcontainer.xml", "/zipfs/0/20/META-INF/container.xml", "zipfs", ["_handler" => "zipfs", "db" => "0", "data" => "20", "comp" => "META-INF/container.xml"]],
            ["loader.php?action=wd_author&dbNum=0&authorId=1&matchId=Q35610", "/loader/wd_author/0/1?matchId=Q35610", "loader-action-dbNum-authorId", ["_handler" => "loader", "action" => "wd_author", "dbNum" => "0", "authorId" => "1", "matchId" => "Q35610"]],
            ["checkconfig.php", "/check", "check", ["_handler" => "check"]],
            ["epubreader.php?db=0&data=20&title=Alice%27s_Adventures_in_Wonderland", "/read/0/20/Alice%27s_Adventures_in_Wonderland", "read-title", ["_handler" => "read", "db" => "0", "data" => "20", "title" => "Alice's_Adventures_in_Wonderland"]],
            ["epubreader.php?db=0&data=20", "/read/0/20", "read", ["_handler" => "read", "db" => "0", "data" => "20"]],
            ["sendtomail.php", "/mail", "mail", ["_handler" => "mail", "_method" => "POST"]],  // fake _method to simulate POST
            ["fetch.php?thumb=html&db=0&id=17", "/thumbs/0/17/html.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "html", "db" => "0", "id" => "17"]],
            ["fetch.php?thumb=opds&db=0&id=17", "/thumbs/0/17/opds.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "opds", "db" => "0", "id" => "17"]],
            ["fetch.php?db=0&id=17", "/covers/0/17.jpg", "fetch-cover", ["_handler" => "fetch", "db" => "0", "id" => "17"]],
            ["fetch.php?view=1&db=0&data=20&type=epub", "/inline/0/20/ignore.epub", "fetch-inline", ["_handler" => "fetch", "view" => "1", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["fetch.php?db=0&data=20&type=epub", "/fetch/0/20/ignore.epub", "fetch-data", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["fetch.php?db=0&id=17&file=hello.txt", "/files/0/17/hello.txt", "fetch-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "hello.txt"]],
            ["fetch.php?db=0&id=17&file=zipped", "/files/0/17/zipped", "fetch-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "zipped"]],
            ["zipper.php?page=10&type=any", "/zipper/10/any.zip", "zipper-page-type", ["_handler" => "zipper", "page" => "10", "type" => "any"]],
            ["feed.php?page=3&id=1&title=Arthur%20Conan%20Doyle", "/feed/3/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["feed.php?page=3&id=1", "/feed/3/1", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1"]],
            ["feed.php?page=10", "/feed/10", "feed-page", ["_handler" => "feed", "page" => "10"]],
            ["opds.php?page=3&id=1&title=Arthur%20Conan%20Doyle", "/opds/3/1?title=Arthur%20Conan%20Doyle", "opds-page-id", ["_handler" => "opds", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["opds.php?page=3&id=1", "/opds/3/1", "opds-page-id", ["_handler" => "opds", "page" => "3", "id" => "1"]],
            ["opds.php?page=10", "/opds/10", "opds-page", ["_handler" => "opds", "page" => "10"]],
            ["restapi.php?route=openapi", "/restapi/openapi", "restapi-openapi", ["_handler" => "restapi", "_resource" => "openapi"]],
            ["graphql.php", "/graphql", "graphql", ["_handler" => "graphql"]],
        ];
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
        $path = parse_url((string) $routeUrl, PHP_URL_PATH);
        // handle POST method for mail
        $method = null;
        if (!empty($params["_method"])) {
            $method = $params["_method"];
            unset($params["_method"]);
        }
        try {
            $result = self::$routing->match($path, $method);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->assertNull($routeUrl);
            return;
        }
        if (!empty($query)) {
            $extra = [];
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
        $this->assertEquals($expected, $result);

        // @todo check/add default route for each handler?
        $endpoint = parse_url((string) $queryUrl, PHP_URL_PATH);
        $flipped = array_flip(Config::ENDPOINT);
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
        try {
            $result = self::$routing->generate($route, $params);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $result);
    }

    /**
     * Summary of routeProvider
     * @return array<mixed>
     */
    public static function routeProvider()
    {
        //return RouteTest::routeProvider();
        return [
            ["/calres/0/xxh64/7c301792c52eebf7", "calres", ["_handler" => "calres", "db" => "0", "alg" => "xxh64", "digest" => "7c301792c52eebf7"]],
            ["/zipfs/0/20/META-INF/container.xml", "zipfs", ["_handler" => "zipfs", "db" => "0", "data" => "20", "comp" => "META-INF/container.xml"]],
            [null, "zipfs", ["_handler" => "zipfs", "db" => "x", "data" => "20", "comp" => "META-INF/container.xml"]],
            ["/thumbs/0/17/html.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "html", "db" => "0", "id" => "17"]],
            ["/thumbs/0/17/opds.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "opds", "db" => "0", "id" => "17"]],
            ["/thumbs/0/17/html2.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "html2", "db" => "0", "id" => "17"]],
            ["/thumbs/0/17/opds2.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "opds2", "db" => "0", "id" => "17"]],
            ["/covers/0/17.jpg", "fetch-cover", ["_handler" => "fetch", "db" => "0", "id" => "17"]],
            ["/inline/0/20/ignore.epub", "fetch-inline", ["_handler" => "fetch", "view" => "1", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["/fetch/0/20/ignore.epub", "fetch-data", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["/files/0/17/hello.txt", "fetch-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "hello.txt"]],
            ["/zipper/3/3/any.zip", "zipper-page-id-type", ["_handler" => "zipper", "page" => "3", "type" => "any", "id" => "3"]],
            ["/zipper/10/any.zip", "zipper-page-type", ["_handler" => "zipper", "page" => "10", "type" => "any"]],
            ["/loader/wd_author/0/1?matchId=Q35610", "loader-action-dbNum-authorId", ["_handler" => "loader", "action" => "wd_author", "dbNum" => "0", "authorId" => "1", "matchId" => "Q35610"]],
            ["/check", "check", ["_handler" => "check"]],
            ["/read/0/20/Alice%27s_Adventures_in_Wonderland", "read-title", ["_handler" => "read", "db" => "0", "data" => "20", "title" => "Alice's_Adventures_in_Wonderland"]],
            ["/read/0/20", "read", ["_handler" => "read", "db" => "0", "data" => "20"]],
            ["/mail", "mail", ["_handler" => "mail", "_method" => "POST"]],  // fake _method to simulate POST
            ["/feed/3/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["/feed/3/1", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1"]],
            ["/feed/10", "feed-page", ["_handler" => "feed", "page" => "10"]],
            ["/restapi/openapi", "restapi-openapi", ["_handler" => "restapi", "_resource" => "openapi"]],
            ["/graphql", "graphql", ["_handler" => "graphql"]],
            ["/view/20/0/ignore.epub", "fetch-view", ["_handler" => "fetch", "view" => "1", "db" => 0, "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["/download/20/0/ignore.epub", "fetch-download", ["_handler" => "fetch", "db" => 0, "data" => "20", "ignore" => "ignore", "type" => "epub"]],
        ];
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
        $path = parse_url((string) $routeUrl, PHP_URL_PATH);
        // handle POST method for mail
        $method = null;
        if (!empty($params["_method"])) {
            $method = $params["_method"];
            unset($params["_method"]);
        }
        try {
            $result = self::$routing->match($path, $method);
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->assertNull($routeUrl);
            return;
        }
        if (!empty($query)) {
            $extra = [];
            parse_str($query, $extra);
            $result = array_merge($result, $extra);
        }
        $expected = $route;
        $this->assertEquals($expected, $result[Route::ROUTE_PARAM]);
        unset($result[Route::ROUTE_PARAM]);
        unset($result[Route::HANDLER_PARAM]);
        $expected = $params;
        unset($expected[Route::HANDLER_PARAM]);
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
        unset($params[Route::HANDLER_PARAM]);
        unset($params["_method"]);
        try {
            $result = self::$routing->generate($route, $params);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array<mixed>
     */
    protected function getRoutes()
    {
        return [
            // 'name' => ['path', [ defaults ], [ requirements ], [ methods ], [ options ], [ ... ]],
            'index' => ['/index', ['page' => 'index']],
            'authors-letter-id' => ['/authors/letter/{id}', ['page' => '2']],
            'authors-letter' => ['/authors/letter', ['page' => '1', 'letter' => 1]],
            'authors-id-title' => ['/authors/{id}/{title}', ['page' => '3'], ['id' => '\d+']],
            'authors-id' => ['/authors/{id}', ['page' => '3'], ['id' => '\d+']],
            'authors' => ['/authors', ['page' => '1']],
            'books-letter-id' => ['/books/letter/{id}', ['page' => '5'], ['id' => '\w']],
            'books-letter' => ['/books/letter', ['page' => '4', 'letter' => 1]],
            'books-year-id' => ['/books/year/{id}', ['page' => '50'], ['id' => '\d+']],
            'books-year' => ['/books/year', ['page' => '4', 'year' => 1]],
            'books-id-author-title' => ['/books/{id}/{author}/{title}', ['page' => '13'], ['id' => '\d+']],
            'books-id' => ['/books/{id}', ['page' => '13'], ['id' => '\d+']],
            'books' => ['/books', ['page' => '4']],
            'series-id-title' => ['/series/{id}/{title}', ['page' => '7'], ['id' => '\d+']],
            'series-id' => ['/series/{id}', ['page' => '7'], ['id' => '\d+']],
            'series' => ['/series', ['page' => '6']],
            'query-query-scope' => ['/query/{query}/{scope}', ['page' => '9', 'search' => 1]],
            'query-query' => ['/query/{query}', ['page' => '9', 'search' => 1]],
            'search-query-scope' => ['/search/{query}/{scope}', ['page' => '9']],
            'search-query' => ['/search/{query}', ['page' => '9']],
            'search' => ['/search', ['page' => '8']],
            'recent' => ['/recent', ['page' => '10']],
            'tags-id-title' => ['/tags/{id}/{title}', ['page' => '12'], ['id' => '\d+']],
            'tags-id' => ['/tags/{id}', ['page' => '12'], ['id' => '\d+']],
            'tags' => ['/tags', ['page' => '11']],
            'custom-custom-id' => ['/custom/{custom}/{id}', ['page' => '15'], ['custom' => '\d+']],
            'custom-custom' => ['/custom/{custom}', ['page' => '14'], ['custom' => '\d+']],
            'about' => ['/about', ['page' => '16']],
            'languages-id-title' => ['/languages/{id}/{title}', ['page' => '18'], ['id' => '\d+']],
            'languages-id' => ['/languages/{id}', ['page' => '18'], ['id' => '\d+']],
            'languages' => ['/languages', ['page' => '17']],
            'customize' => ['/customize', ['page' => '19']],
            'publishers-id-title' => ['/publishers/{id}/{title}', ['page' => '21'], ['id' => '\d+']],
            'publishers-id' => ['/publishers/{id}', ['page' => '21'], ['id' => '\d+']],
            'publishers' => ['/publishers', ['page' => '20']],
            'ratings-id-title' => ['/ratings/{id}/{title}', ['page' => '23'], ['id' => '\d+']],
            'ratings-id' => ['/ratings/{id}', ['page' => '23'], ['id' => '\d+']],
            'ratings' => ['/ratings', ['page' => '22']],
            'identifiers-id-title' => ['/identifiers/{id}/{title}', ['page' => '42'], ['id' => '\w+']],
            'identifiers-id' => ['/identifiers/{id}', ['page' => '42'], ['id' => '\w+']],
            'identifiers' => ['/identifiers', ['page' => '41']],
            'libraries' => ['/libraries', ['page' => '43']],
            'feed-page-id' => ['/feed/{page}/{id}', ['_handler' => 'feed']],
            'feed-page' => ['/feed/{page}', ['_handler' => 'feed']],
            'feed' => ['/feed', ['_handler' => 'feed']],
            'files-db-id-file' => ['/files/{db}/{id}/{file}', ['_handler' => 'fetch'], ['db' => '\d+', 'id' => '\d+', 'file' => '.+']],
            'thumbs-db-id-thumb' => ['/thumbs/{db}/{id}/{thumb}.jpg', ['_handler' => 'fetch'], ['db' => '\d+', 'id' => '\d+']],
            'covers-db-id' => ['/covers/{db}/{id}.jpg', ['_handler' => 'fetch'], ['db' => '\d+', 'id' => '\d+']],
            'inline-db-data-ignore.type' => ['/inline/{db}/{data}/{ignore}.{type}', ['_handler' => 'fetch', 'view' => 1], ['db' => '\d+', 'data' => '\d+']],
            'fetch-db-data-ignore.type' => ['/fetch/{db}/{data}/{ignore}.{type}', ['_handler' => 'fetch'], ['db' => '\d+', 'data' => '\d+']],
            'view-data-db-ignore.type' => ['/view/{data}/{db}/{ignore}.{type}', ['_handler' => 'fetch', 'view' => 1]],
            'view-data-ignore.type' => ['/view/{data}/{ignore}.{type}', ['_handler' => 'fetch', 'view' => 1]],
            'download-data-db-ignore.type' => ['/download/{data}/{db}/{ignore}.{type}', ['_handler' => 'fetch']],
            'download-data-ignore.type' => ['/download/{data}/{ignore}.{type}', ['_handler' => 'fetch']],
            'read-db-data-title' => ['/read/{db}/{data}/{title}', ['_handler' => 'read'], ['db' => '\d+', 'data' => '\d+']],
            'read-db-data' => ['/read/{db}/{data}', ['_handler' => 'read'], ['db' => '\d+', 'data' => '\d+']],
            'epubfs-db-data-comp' => ['/epubfs/{db}/{data}/{comp}', ['_handler' => 'epubfs'], ['db' => '\d+', 'data' => '\d+', 'comp' => '.+']],
            'restapi-custom' => ['/restapi/custom', ['_handler' => 'restapi', '_resource' => 'CustomColumnType']],
            'restapi-databases-db-name' => ['/restapi/databases/{db}/{name}', ['_handler' => 'restapi', '_resource' => 'Database']],
            'restapi-databases-db' => ['/restapi/databases/{db}', ['_handler' => 'restapi', '_resource' => 'Database']],
            'restapi-databases' => ['/restapi/databases', ['_handler' => 'restapi', '_resource' => 'Database']],
            'restapi-openapi' => ['/restapi/openapi', ['_handler' => 'restapi', '_resource' => 'openapi']],
            'restapi-routes' => ['/restapi/routes', ['_handler' => 'restapi', '_resource' => 'route']],
            'restapi-groups' => ['/restapi/groups', ['_handler' => 'restapi', '_resource' => 'group']],
            'restapi-notes-type-id-title' => ['/restapi/notes/{type}/{id}/{title}', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-notes-type-id' => ['/restapi/notes/{type}/{id}', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-notes-type' => ['/restapi/notes/{type}', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-notes' => ['/restapi/notes', ['_handler' => 'restapi', '_resource' => 'Note']],
            'restapi-preferences-key' => ['/restapi/preferences/{key}', ['_handler' => 'restapi', '_resource' => 'Preference']],
            'restapi-preferences' => ['/restapi/preferences', ['_handler' => 'restapi', '_resource' => 'Preference']],
            'restapi-annotations-bookId-id' => ['/restapi/annotations/{bookId}/{id}', ['_handler' => 'restapi', '_resource' => 'Annotation']],
            'restapi-annotations-bookId' => ['/restapi/annotations/{bookId}', ['_handler' => 'restapi', '_resource' => 'Annotation']],
            'restapi-annotations' => ['/restapi/annotations', ['_handler' => 'restapi', '_resource' => 'Annotation']],
            'restapi-metadata-bookId-element-name' => ['/restapi/metadata/{bookId}/{element}/{name}', ['_handler' => 'restapi', '_resource' => 'Metadata']],
            'restapi-metadata-bookId-element' => ['/restapi/metadata/{bookId}/{element}', ['_handler' => 'restapi', '_resource' => 'Metadata']],
            'restapi-metadata-bookId' => ['/restapi/metadata/{bookId}', ['_handler' => 'restapi', '_resource' => 'Metadata']],
            'restapi-user-details' => ['/restapi/user/details', ['_handler' => 'restapi', '_resource' => 'User']],
            'restapi-user' => ['/restapi/user', ['_handler' => 'restapi', '_resource' => 'User']],
            'restapi-route' => ['/restapi/{route}', ['_handler' => 'restapi'], ['route' => '.*']],
            'check-more' => ['/check/{more}', ['_handler' => 'check'], ['more' => '.*']],
            'check' => ['/check', ['_handler' => 'check']],
            'opds-page-id' => ['/opds/{page}/{id}', ['_handler' => 'opds']],
            'opds-page' => ['/opds/{page}', ['_handler' => 'opds']],
            'opds' => ['/opds', ['_handler' => 'opds']],
            'loader-action-dbNum-authorId-urlPath' => ['/loader/{action}/{dbNum}/{authorId}/{urlPath}', ['_handler' => 'loader'], ['dbNum' => '\d+', 'authorId' => '\w+', 'urlPath' => '.*']],
            'loader-action-dbNum-authorId' => ['/loader/{action}/{dbNum}/{authorId}', ['_handler' => 'loader'], ['dbNum' => '\d+', 'authorId' => '\w*']],
            'loader-action-dbNum' => ['/loader/{action}/{dbNum}', ['_handler' => 'loader'], ['dbNum' => '\d+']],
            'loader-action-' => ['/loader/{action}/', ['_handler' => 'loader']],
            'loader-action' => ['/loader/{action}', ['_handler' => 'loader']],
            'loader' => ['/loader', ['_handler' => 'loader']],
            'zipper-page-id-type' => ['/zipper/{page}/{id}/{type}.zip', ['_handler' => 'zipper']],
            'zipper-page-type' => ['/zipper/{page}/{type}.zip', ['_handler' => 'zipper']],
            'calres-db-alg-digest' => ['/calres/{db}/{alg}/{digest}', ['_handler' => 'calres'], ['db' => '\d+']],
            'zipfs-db-data-comp' => ['/zipfs/{db}/{data}/{comp}', ['_handler' => 'zipfs'], ['db' => '\d+', 'data' => '\d+', 'comp' => '.+']],
            // @todo 'name' => ['path', [ defaults ], [ requirements ], [ methods ], [ options ], [ ... ]],
            'mail' => ['/mail', ['_handler' => 'mail'], [], ['POST']],
            'graphql' => ['/graphql', ['_handler' => 'graphql'], [], ['GET', 'POST']],
            'tables' => ['/tables', ['_handler' => 'tables']],
            // @todo handle cors options
            'cors' => ['/{route}', ['_handler' => 'TODO'], ['route' => '.*'], ['OPTIONS']],
            // @todo handle unicode!?
            //'authors-letter-id' => ['/authors/letter/{id}', ['page' => '2'], ['id' => '\w'], ['GET], ['utf8' => true]],
        ];
        //$routes->add('article_show', '/articles/{_locale}/search.{_format}')
        //->controller([ArticleController::class, 'search'])
        //->methods(['GET', 'HEAD'])
        //->locale('en')
        //->format('html')
        //->defaults([
        //    'page'  => 1,
        //    'title' => 'Hello world!',
        //])
        //->requirements(['page' => '\d+'])
        //->schemes(['https'])
        //->utf8(true)
        //->options([...])
        // this is added to the beginning of all imported route URLs
        //->prefix('/blog')
        // this is added to the beginning of all imported route names
        //->namePrefix('blog_')

        //$routes->alias('new_route_name', 'original_route_name');
        //$routes->import(...)
        //$framework->router()->defaultUri('https://example.org/my/path/');
    }
}
