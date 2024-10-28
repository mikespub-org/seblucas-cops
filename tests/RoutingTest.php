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
        static::$routing = new Routing();
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
            ["index.php?page=index", "/index", "index", ["page" => "index"]],
            ["index.php?page=1", "/authors", "authors", ["page" => "1"]],
            ["index.php?page=1&letter=1", "/authors/letter", "authors-letter", ["page" => "1", "letter" => "1"]],
            ["index.php?page=2&id=D", "/authors/letter/D", "authors-letter-id", ["page" => "2", "id" => "D"]],
            ["index.php?page=3&id=1", "/authors/1", "authors-id", ["page" => "3", "id" => "1"]],
            ["index.php?page=4", "/books", "books", ["page" => "4"]],
            ["index.php?page=4&letter=1", "/books/letter", "books-letter", ["page" => "4", "letter" => "1"]],
            ["index.php?page=5&id=A", "/books/letter/A", "books-letter-id", ["page" => "5", "id" => "A"]],
            ["index.php?page=4&year=1", "/books/year", "books-year", ["page" => "4", "year" => "1"]],
            ["index.php?page=50&id=2006", "/books/year/2006", "books-year-id", ["page" => "50", "id" => "2006"]],
            ["index.php?page=6", "/series", "series", ["page" => "6"]],
            ["index.php?page=7&id=1", "/series/1", "series-id", ["page" => "7", "id" => "1"]],
            ["index.php?page=8", "/search", "search", ["page" => "8"]],
            ["index.php?page=9&query=alice", "/search/alice", "search-query", ["page" => "9", "query" => "alice"]],
            ["index.php?page=9&query=alice&scope=book", "/search/alice/book", "search-query-scope", ["page" => "9", "query" => "alice", "scope" => "book"]],
            ["index.php?page=9&search=1&query=alice", "/query/alice", "query-query", ["page" => "9", "search" => "1", "query" => "alice"]],
            ["index.php?page=9&search=1&query=alice&scope=book", "/query/alice/book", "query-query-scope", ["page" => "9", "search" => "1", "query" => "alice", "scope" => "book"]],
            ["index.php?page=10", "/recent", "recent", ["page" => "10"]],
            ["index.php?page=11", "/tags", "tags", ["page" => "11"]],
            ["index.php?page=12&id=1", "/tags/1", "tags-id", ["page" => "12", "id" => "1"]],
            ["index.php?page=13&id=2", "/books/2", "books-id", ["page" => "13", "id" => "2"]],
            ["index.php?page=14&custom=1", "/custom/1", "custom-custom", ["page" => "14", "custom" => "1"]],
            ["index.php?page=15&custom=1&id=2", "/custom/1/2", "custom-custom-id", ["page" => "15", "custom" => "1", "id" => "2"]],
            ["index.php?page=16", "/about", "about", ["page" => "16"]],
            ["index.php?page=17", "/languages", "languages", ["page" => "17"]],
            ["index.php?page=18&id=1", "/languages/1", "languages-id", ["page" => "18", "id" => "1"]],
            ["index.php?page=19", "/customize", "customize", ["page" => "19"]],
            ["index.php?page=20", "/publishers", "publishers", ["page" => "20"]],
            ["index.php?page=21&id=1", "/publishers/1", "publishers-id", ["page" => "21", "id" => "1"]],
            ["index.php?page=22", "/ratings", "ratings", ["page" => "22"]],
            ["index.php?page=23&id=1", "/ratings/1", "ratings-id", ["page" => "23", "id" => "1"]],
            ["index.php?page=22&a=1", "/ratings?a=1", "ratings", ["page" => "22", "a" => "1"]],
            ["index.php?page=23&id=1&a=1", "/ratings/1?a=1", "ratings-id", ["page" => "23", "id" => "1", "a" => "1"]],
            ["calres.php?db=0&alg=xxh64&digest=7c301792c52eebf7", "/calres/0/xxh64/7c301792c52eebf7", "calres-db-alg-digest", ["_handler" => "calres", "db" => "0", "alg" => "xxh64", "digest" => "7c301792c52eebf7"]],
            ["zipfs.php?db=0&data=20&comp=META-INF%2Fcontainer.xml", "/zipfs/0/20/META-INF/container.xml", "zipfs-db-data-comp", ["_handler" => "zipfs", "db" => "0", "data" => "20", "comp" => "META-INF/container.xml"]],
            ["loader.php?action=wd_author&dbNum=0&authorId=1&matchId=Q35610", "/loader/wd_author/0/1?matchId=Q35610", "loader-action-dbNum-authorId", ["_handler" => "loader", "action" => "wd_author", "dbNum" => "0", "authorId" => "1", "matchId" => "Q35610"]],
            ["checkconfig.php", "/check", "check", ["_handler" => "check"]],
            ["epubreader.php?db=0&data=20&title=Alice%27s_Adventures_in_Wonderland", "/read/0/20/Alice%27s_Adventures_in_Wonderland", "read-db-data-title", ["_handler" => "read", "db" => "0", "data" => "20", "title" => "Alice's_Adventures_in_Wonderland"]],
            ["epubreader.php?db=0&data=20", "/read/0/20", "read-db-data", ["_handler" => "read", "db" => "0", "data" => "20"]],
            ["sendtomail.php", "/mail", "mail", ["_handler" => "mail"]],
            ["fetch.php?thumb=html&db=0&id=17", "/thumbs/html/0/17.jpg", "thumbs-thumb-db-id", ["_handler" => "fetch", "thumb" => "html", "db" => "0", "id" => "17"]],
            ["fetch.php?thumb=opds&db=0&id=17", "/thumbs/opds/0/17.jpg", "thumbs-thumb-db-id", ["_handler" => "fetch", "thumb" => "opds", "db" => "0", "id" => "17"]],
            ["fetch.php?db=0&id=17", "/covers/0/17.jpg", "covers-db-id", ["_handler" => "fetch", "db" => "0", "id" => "17"]],
            ["fetch.php?view=1&db=0&data=20&type=epub", "/inline/0/20/ignore.epub", "inline-db-data-ignore.type", ["_handler" => "fetch", "view" => "1", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["fetch.php?db=0&data=20&type=epub", "/fetch/0/20/ignore.epub", "fetch-db-data-ignore.type", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["fetch.php?db=0&id=17&file=hello.txt", "/files/0/17/hello.txt", "files-db-id-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "hello.txt"]],
            ["fetch.php?db=0&id=17&file=zipped", "/files/0/17/zipped", "files-db-id-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "zipped"]],
            ["zipper.php?page=10&type=any", "/zipper/10/any", "zipper-page-type", ["_handler" => "zipper", "page" => "10", "type" => "any"]],
            ["feed.php?page=3&id=1&title=Arthur+Conan+Doyle", "/feed/3/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["feed.php?page=3&id=1", "/feed/3/1", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1"]],
            ["feed.php?page=10", "/feed/10", "feed-page", ["_handler" => "feed", "page" => "10"]],
            ["opds.php?page=3&id=1&title=Arthur+Conan+Doyle", "/opds/3/1?title=Arthur%20Conan%20Doyle", "opds-page-id", ["_handler" => "opds", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["opds.php?page=3&id=1", "/opds/3/1", "opds-page-id", ["_handler" => "opds", "page" => "3", "id" => "1"]],
            ["opds.php?page=10", "/opds/10", "opds-page", ["_handler" => "opds", "page" => "10"]],
            ["restapi.php?route=openapi", "/restapi/openapi", "restapi-route", ["_handler" => "restapi", "route" => "openapi"]],
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
        $result = static::$routing->match($path);
        if (!empty($query)) {
            $extra = [];
            parse_str($query, $extra);
            $result = array_merge($result, $extra);
        }
        // @todo handle ignore
        //unset($result["ignore"]);

        $expected = $route;
        $this->assertEquals($expected, $result["_route"]);
        unset($result["_route"]);
        $expected = $params;
        $this->assertEquals($expected, $result);

        // @todo check/add default route for each handler?
        $endpoint = parse_url((string) $queryUrl, PHP_URL_PATH);
        $flipped = array_flip(Config::ENDPOINT);
        $handler = "index";
        if (!empty($flipped[$endpoint])) {
            $handler = $flipped[$endpoint];
        }
        if ($handler == "index") {
            return;
        }
        $path = "/$handler";
        try {
            $result = static::$routing->match($path);
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
        unset($params["_handler"]);
        try {
            $result = static::$routing->generate($route, $params);
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
            ["/calres/0/xxh64/7c301792c52eebf7", "calres-db-alg-digest", ["_handler" => "calres", "db" => "0", "alg" => "xxh64", "digest" => "7c301792c52eebf7"]],
            ["/zipfs/0/20/META-INF/container.xml", "zipfs-db-data-comp", ["_handler" => "zipfs", "db" => "0", "data" => "20", "comp" => "META-INF/container.xml"]],
            [null, "zipfs-db-data-comp", ["_handler" => "zipfs", "db" => "x", "data" => "20","comp" => "META-INF/container.xml"]],
            ["/thumbs/html/0/17.jpg", "thumbs-thumb-db-id", ["_handler" => "fetch", "thumb" => "html", "db" => "0", "id" => "17"]],
            ["/thumbs/opds/0/17.jpg", "thumbs-thumb-db-id", ["_handler" => "fetch", "thumb" => "opds", "db" => "0", "id" => "17"]],
            ["/thumbs/html2/0/17.jpg", "thumbs-thumb-db-id", ["_handler" => "fetch", "thumb" => "html2", "db" => "0", "id" => "17"]],
            ["/thumbs/opds2/0/17.jpg", "thumbs-thumb-db-id", ["_handler" => "fetch", "thumb" => "opds2", "db" => "0", "id" => "17"]],
            ["/covers/0/17.jpg", "covers-db-id", ["_handler" => "fetch", "db" => "0", "id" => "17"]],
            ["/inline/0/20/ignore.epub", "inline-db-data-ignore.type", ["_handler" => "fetch", "view" => "1", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["/fetch/0/20/ignore.epub", "fetch-db-data-ignore.type", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["/files/0/17/hello.txt", "files-db-id-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "hello.txt"]],
            ["/zipper/3/any/3", "zipper-page-type-id", ["_handler" => "zipper", "page" => "3", "type" => "any", "id" => "3"]],
            ["/zipper/10/any", "zipper-page-type", ["_handler" => "zipper", "page" => "10", "type" => "any"]],
            ["/loader/wd_author/0/1?matchId=Q35610", "loader-action-dbNum-authorId", ["_handler" => "loader", "action" => "wd_author", "dbNum" => "0", "authorId" => "1", "matchId" => "Q35610"]],
            ["/check", "check", ["_handler" => "check"]],
            ["/read/0/20/Alice%27s_Adventures_in_Wonderland", "read-db-data-title", ["_handler" => "read", "db" => "0", "data" => "20", "title" => "Alice's_Adventures_in_Wonderland"]],
            ["/read/0/20", "read-db-data", ["_handler" => "read", "db" => "0", "data" => "20"]],
            ["/mail", "mail", ["_handler" => "mail"]],
            ["/feed/3/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["/feed/3/1", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1"]],
            ["/feed/10", "feed-page", ["_handler" => "feed", "page" => "10"]],
            ["/restapi/openapi", "restapi-route", ["_handler" => "restapi", "route" => "openapi"]],
            ["/graphql", "graphql", ["_handler" => "graphql"]],
            ["/view/20/ignore.epub", "view-data-ignore.type", ["_handler" => "fetch", "view" => "1", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["/download/20/ignore.epub", "download-data-ignore.type", ["_handler" => "fetch", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
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
        try {
            $result = static::$routing->match($path);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        if (!empty($query)) {
            $extra = [];
            parse_str($query, $extra);
            $result = array_merge($result, $extra);
        }
        $expected = $route;
        $this->assertEquals($expected, $result["_route"]);
        unset($result["_route"]);
        $expected = $params;
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
        unset($params["_handler"]);
        try {
            $result = static::$routing->generate($route, $params);
        } catch (Exception) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $result);
    }

    protected function todo(): void
    {
        $routes = [
            // 'name' => ['path', [ defaults ], [ requirements ], [ methods ], [ options ], [ ... ]],
            'index' => ['/index', ['page' => 'index']],
            'authors-letter-id' => ['/authors/letter/{id}', ['page' => '2']],
            'authors-letter' => ['/authors/letter', ['page' => '1',  'letter' => 1,]],
            'authors-id-title' => ['/authors/{id}/{title}', ['page' => '3'], ['id' => '\d+']],
            'authors-id' => ['/authors/{id}', ['page' => '3'], ['id' => '\d+']],
            'authors' => ['/authors', ['page' => '1']],
            'books-letter-id' => ['/books/letter/{id}', ['page' => '5'], ['id' => '\w']],
            'books-letter' => ['/books/letter', ['page' => '4',  'letter' => 1,]],
            'books-year-id' => ['/books/year/{id}', ['page' => '50'], ['id' => '\d+']],
            'books-year' => ['/books/year', ['page' => '4',  'year' => 1,]],
            'books-id-author-title' => ['/books/{id}/{author}/{title}', ['page' => '13'], ['id' => '\d+']],
            'books-id' => ['/books/{id}', ['page' => '13'], ['id' => '\d+']],
            'books' => ['/books', ['page' => '4']],
            'series-id-title' => ['/series/{id}/{title}', ['page' => '7'], ['id' => '\d+']],
            'series-id' => ['/series/{id}', ['page' => '7'], ['id' => '\d+']],
            'series' => ['/series', ['page' => '6']],
            'query-query-scope' => ['/query/{query}/{scope}', ['page' => '9',  'search' => 1,]],
            'query-query' => ['/query/{query}', ['page' => '9',  'search' => 1,]],
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
            'files-db-id-file' => ['/files/{db}/{id}/{file}', ['_handler' => 'fetch'], ['db' => '\d+',  'id' => '\d+',  'file' => '.+']],
            'thumbs-thumb-db-id' => ['/thumbs/{thumb}/{db}/{id}.jpg', ['_handler' => 'fetch'], ['db' => '\d+',  'id' => '\d+']],
            'covers-db-id' => ['/covers/{db}/{id}.jpg', ['_handler' => 'fetch'], ['db' => '\d+',  'id' => '\d+']],
            'inline-db-data-ignore.type' => ['/inline/{db}/{data}/{ignore}.{type}', ['_handler' => 'fetch',  'view' => 1,], ['db' => '\d+',  'data' => '\d+']],
            'fetch-db-data-ignore.type' => ['/fetch/{db}/{data}/{ignore}.{type}', ['_handler' => 'fetch'], ['db' => '\d+',  'data' => '\d+']],
            'view-data-db-ignore.type' => ['/view/{data}/{db}/{ignore}.{type}', ['_handler' => 'fetch',  'view' => 1,]],
            'view-data-ignore.type' => ['/view/{data}/{ignore}.{type}', ['_handler' => 'fetch',  'view' => 1,]],
            'download-data-db-ignore.type' => ['/download/{data}/{db}/{ignore}.{type}', ['_handler' => 'fetch']],
            'download-data-ignore.type' => ['/download/{data}/{ignore}.{type}', ['_handler' => 'fetch']],
            'read-db-data-title' => ['/read/{db}/{data}/{title}', ['_handler' => 'read'], ['db' => '\d+',  'data' => '\d+']],
            'read-db-data' => ['/read/{db}/{data}', ['_handler' => 'read'], ['db' => '\d+',  'data' => '\d+']],
            'epubfs-db-data-comp' => ['/epubfs/{db}/{data}/{comp}', ['_handler' => 'epubfs'], ['db' => '\d+',  'data' => '\d+',  'comp' => '.+']],
            'restapi-route' => ['/restapi/{route}', ['_handler' => 'restapi'], ['route' => '.*']],
            'restapi' => ['/restapi', ['_handler' => 'restapi']],
            'custom' => ['/custom', ['_handler' => 'restapi']],
            'databases-db-name' => ['/databases/{db}/{name}', ['_handler' => 'restapi']],
            'databases-db' => ['/databases/{db}', ['_handler' => 'restapi']],
            'databases' => ['/databases', ['_handler' => 'restapi']],
            'openapi' => ['/openapi', ['_handler' => 'restapi']],
            'routes' => ['/routes', ['_handler' => 'restapi']],
            'pages' => ['/pages', ['_handler' => 'restapi']],
            'notes-type-id-title' => ['/notes/{type}/{id}/{title}', ['_handler' => 'restapi']],
            'notes-type-id' => ['/notes/{type}/{id}', ['_handler' => 'restapi']],
            'notes-type' => ['/notes/{type}', ['_handler' => 'restapi']],
            'notes' => ['/notes', ['_handler' => 'restapi']],
            'preferences-key' => ['/preferences/{key}', ['_handler' => 'restapi']],
            'preferences' => ['/preferences', ['_handler' => 'restapi']],
            'annotations-bookId-id' => ['/annotations/{bookId}/{id}', ['_handler' => 'restapi']],
            'annotations-bookId' => ['/annotations/{bookId}', ['_handler' => 'restapi']],
            'annotations' => ['/annotations', ['_handler' => 'restapi']],
            'metadata-bookId-element-name' => ['/metadata/{bookId}/{element}/{name}', ['_handler' => 'restapi']],
            'metadata-bookId-element' => ['/metadata/{bookId}/{element}', ['_handler' => 'restapi']],
            'metadata-bookId' => ['/metadata/{bookId}', ['_handler' => 'restapi']],
            'user-details' => ['/user/details', ['_handler' => 'restapi']],
            'user' => ['/user', ['_handler' => 'restapi']],
            'check-more' => ['/check/{more}', ['_handler' => 'check'], ['more' => '.*']],
            'check' => ['/check', ['_handler' => 'check']],
            'opds-page-id' => ['/opds/{page}/{id}', ['_handler' => 'opds']],
            'opds-page' => ['/opds/{page}', ['_handler' => 'opds']],
            'opds' => ['/opds', ['_handler' => 'opds']],
            'loader-action-dbNum-authorId-urlPath' => ['/loader/{action}/{dbNum}/{authorId}/{urlPath}', ['_handler' => 'loader'], ['dbNum' => '\d+',  'authorId' => '\w+',  'urlPath' => '.*']],
            'loader-action-dbNum-authorId' => ['/loader/{action}/{dbNum}/{authorId}', ['_handler' => 'loader'], ['dbNum' => '\d+',  'authorId' => '\w*']],
            'loader-action-dbNum' => ['/loader/{action}/{dbNum}', ['_handler' => 'loader'], ['dbNum' => '\d+']],
            'loader-action-' => ['/loader/{action}/', ['_handler' => 'loader']],
            'loader-action' => ['/loader/{action}', ['_handler' => 'loader']],
            'loader' => ['/loader', ['_handler' => 'loader']],
            'zipper-page-type-id' => ['/zipper/{page}/{type}/{id}', ['_handler' => 'zipper']],
            'zipper-page-type' => ['/zipper/{page}/{type}', ['_handler' => 'zipper']],
            'zipper-page' => ['/zipper/{page}', ['_handler' => 'zipper']],
            'calres-db-alg-digest' => ['/calres/{db}/{alg}/{digest}', ['_handler' => 'calres'], ['db' => '\d+']],
            'zipfs-db-data-comp' => ['/zipfs/{db}/{data}/{comp}', ['_handler' => 'zipfs'], ['db' => '\d+',  'data' => '\d+',  'comp' => '.+']],
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
