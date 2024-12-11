<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Route;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use Throwable;

class RouteTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testRoutePage(): void
    {
        $this->assertEquals("", Route::page(null, ['db' => null]));
        $this->assertEquals("?db=0", Route::page(null, ['db' => 0]));
        $this->assertEquals("?key=value", Route::page(null, ['key' => 'value', 'db' => null]));
        $this->assertEquals("?key=value&db=0", Route::page(null, ['key' => 'value', 'db' => 0]));
        $this->assertEquals("?key=value&db=0", Route::page(null, ['key' => 'value', 'otherKey' => null, 'db' => 0]));
        $this->assertEquals("?key=value&otherKey=other&db=0", Route::page(null, ['key' => 'value', 'otherKey' => 'other', 'db' => 0]));
        $this->assertEquals("/authors", Route::page(1, ['db' => null]));
        $this->assertEquals("/authors?db=0", Route::page(1, ['db' => 0]));
        $this->assertEquals("/authors?key=value", Route::page(1, ['key' => 'value', 'db' => null]));
        $this->assertEquals("/authors?key=value&db=0", Route::page(1, ['key' => 'value', 'db' => 0]));
        $this->assertEquals("/authors?key=value&db=0", Route::page(1, ['key' => 'value', 'otherKey' => null, 'db' => 0]));
        $this->assertEquals("/authors?key=value&otherKey=other&db=0", Route::page(1, ['key' => 'value', 'otherKey' => 'other', 'db' => 0]));
    }

    public function testFrontController(): void
    {
        $expected = '/recent';
        $uri = Route::page(10);
        $this->assertEquals($expected, $uri);

        Config::set('front_controller', 'index.php');
        $expected = Route::base() . 'recent';
        $test = Route::absolute(Route::page(10));
        $this->assertEquals($expected, $test);

        Config::set('front_controller', '');
        $expected = Route::base() . 'index.php/recent';
        $test = Route::absolute(Route::page(10));
        $this->assertEquals($expected, $test);
    }

    public function testGetGroups(): void
    {
        $groups = Route::getGroups();
        $names = [];
        foreach ($groups as $group => $items) {
            if (in_array($group::HANDLER, ['html', 'restapi'])) {
                foreach ($items as $subgroup => $routes) {
                    foreach ($routes as $name) {
                        $names[] = $name;
                    }
                }
                continue;
            }
            foreach ($items as $name) {
                $names[] = $name;
            }
        }
        $expected = Route::count();
        $this->assertCount($expected, $names);
    }

    public function testDump(): void
    {
        $expected = Route::count();
        Route::dump();
        Route::load();
        $test = Route::count();
        $this->assertEquals($expected, $test);
    }

    public function testProxyBaseUrl(): void
    {
        Route::setBaseUrl(null);
        Config::set('full_url', '');

        $expected = 'vendor/bin/';
        $base = Route::base();
        $this->assertEquals($expected, $base);
        Route::setBaseUrl(null);

        // @see https://github.com/mikespub-org/seblucas-cops/wiki/Reverse-proxy-configurations
        Config::set('trusted_proxies', 'private_ranges');
        Config::set('trusted_headers', ['x-forwarded-for', 'x-forwarded-host', 'x-forwarded-proto', 'x-forwarded-port', 'x-forwarded-prefix']);
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'www.example.com';
        $_SERVER['HTTP_X_FORWARDED_PORT'] = 8443;
        $_SERVER['HTTP_X_FORWARDED_PREFIX'] = '/books/';
        $_SERVER['REMOTE_ADDR'] = '::1';
        //$_SERVER['REQUEST_URI'] = '/index.php/check';

        $expected = 'https://www.example.com:8443/books/';
        $base = Route::base();
        $this->assertEquals($expected, $base);
        Route::setBaseUrl(null);
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
        unset($_SERVER['HTTP_X_FORWARDED_PORT']);
        unset($_SERVER['HTTP_X_FORWARDED_PREFIX']);
        unset($_SERVER['REMOTE_ADDR']);

        // this has priority over trusted proxies or script name
        Config::set('full_url', '/cops/');

        $expected = '/cops/';
        $base = Route::base();
        $this->assertEquals($expected, $base);
        Route::setBaseUrl(null);

        Config::set('trusted_proxies', '');
        Config::set('trusted_headers', []);
        Config::set('full_url', '');
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
            ["index.php?page=1", "/authors", "page-authors", ["page" => "1"]],
            ["index.php?page=1&letter=1", "/authors/letter", "page-1-letter", ["page" => "1", "letter" => "1"]],
            ["index.php?page=2&id=D", "/authors/letter/D", "page-authors-letter", ["page" => "2", "id" => "D"]],
            ["index.php?page=3&id=1", "/authors/1", "page-3-id", ["page" => "3", "id" => "1"]],
            ["index.php?page=3&id=1&title=Title", "/authors/1/Title", "page-author", ["page" => "3", "id" => "1", "title" => "Title"]],
            ["index.php?page=4", "/books", "page-books", ["page" => "4"]],
            ["index.php?page=4&letter=1", "/books/letter", "page-4-letter", ["page" => "4", "letter" => "1"]],
            ["index.php?page=5&id=A", "/books/letter/A", "page-books-letter", ["page" => "5", "id" => "A"]],
            ["index.php?page=4&year=1", "/books/year", "page-4-year", ["page" => "4", "year" => "1"]],
            ["index.php?page=50&id=2006", "/books/year/2006", "page-books-year", ["page" => "50", "id" => "2006"]],
            ["index.php?page=6", "/series", "page-series", ["page" => "6"]],
            ["index.php?page=7&id=1", "/series/1", "page-7-id", ["page" => "7", "id" => "1"]],
            ["index.php?page=7&id=1&title=Title", "/series/1/Title", "page-serie", ["page" => "7", "id" => "1", "title" => "Title"]],
            ["index.php?page=8", "/search", "page-search", ["page" => "8"]],
            ["index.php?page=9&query=alice", "/search/alice", "page-query", ["page" => "9", "query" => "alice"]],
            ["index.php?page=9&query=alice&scope=book", "/search/alice/book", "page-query-scope", ["page" => "9", "query" => "alice", "scope" => "book"]],
            ["index.php?page=9&search=1&query=alice", "/typeahead?query=alice", "page-typeahead", ["page" => "9", "search" => "1", "query" => "alice"]],
            ["index.php?page=9&search=1&query=alice&scope=book", "/typeahead?query=alice&scope=book", "page-typeahead", ["page" => "9", "search" => "1", "query" => "alice", "scope" => "book"]],
            ["index.php?page=10", "/recent", "page-recent", ["page" => "10"]],
            ["index.php?page=11", "/tags", "page-tags", ["page" => "11"]],
            ["index.php?page=12&id=1", "/tags/1", "page-12-id", ["page" => "12", "id" => "1"]],
            ["index.php?page=12&id=1&title=Title", "/tags/1/Title", "page-tag", ["page" => "12", "id" => "1", "title" => "Title"]],
            ["index.php?page=13&id=2", "/books/2", "page-13-id", ["page" => "13", "id" => "2"]],
            ["index.php?page=13&id=2&author=Author&title=Title", "/books/2/Author/Title", "page-book", ["page" => "13", "id" => "2", "author" => "Author", "title" => "Title"]],
            ["index.php?page=14&custom=1", "/custom/1", "page-customtype", ["page" => "14", "custom" => "1"]],
            ["index.php?page=15&custom=1&id=2", "/custom/1/2", "page-custom", ["page" => "15", "custom" => "1", "id" => "2"]],
            ["index.php?page=15&custom=1&id=not_set", "/custom/1/not_set", "page-custom", ["page" => "15", "custom" => "1", "id" => "not_set"]],  // @todo id = null
            ["index.php?page=16", "/about", "page-about", ["page" => "16"]],
            ["index.php?page=17", "/languages", "page-languages", ["page" => "17"]],
            ["index.php?page=18&id=1", "/languages/1", "page-18-id", ["page" => "18", "id" => "1"]],
            ["index.php?page=18&id=1&title=Title", "/languages/1/Title", "page-language", ["page" => "18", "id" => "1", "title" => "Title"]],
            ["index.php?page=19", "/customize", "page-customize", ["page" => "19"]],
            ["index.php?page=20", "/publishers", "page-publishers", ["page" => "20"]],
            ["index.php?page=21&id=1", "/publishers/1", "page-21-id", ["page" => "21", "id" => "1"]],
            ["index.php?page=21&id=1&title=Title", "/publishers/1/Title", "page-publisher", ["page" => "21", "id" => "1", "title" => "Title"]],
            ["index.php?page=22", "/ratings", "page-ratings", ["page" => "22"]],
            ["index.php?page=23&id=1", "/ratings/1", "page-23-id", ["page" => "23", "id" => "1"]],
            ["index.php?page=23&id=1&title=Title", "/ratings/1/Title", "page-rating", ["page" => "23", "id" => "1", "title" => "Title"]],
            ["index.php?page=22&a=1", "/ratings?a=1", "page-ratings", ["page" => "22", "a" => "1"]],
            ["index.php?page=23&id=1&a=1", "/ratings/1?a=1", "page-23-id", ["page" => "23", "id" => "1", "a" => "1"]],
            ["calres.php?db=0&alg=xxh64&digest=7c301792c52eebf7", "/calres/0/xxh64/7c301792c52eebf7", "calres", ["_handler" => "calres", "db" => "0", "alg" => "xxh64", "digest" => "7c301792c52eebf7"]],
            ["zipfs.php?db=0&data=20&comp=META-INF%2Fcontainer.xml", "/zipfs/0/20/META-INF/container.xml", "zipfs", ["_handler" => "zipfs", "db" => "0", "data" => "20", "comp" => "META-INF/container.xml"]],
            ["loader.php?action=wd_author&dbNum=0&authorId=1&matchId=Q35610", "/loader/wd_author/0/1?matchId=Q35610", "loader-action-dbNum-authorId", ["_handler" => "loader", "action" => "wd_author", "dbNum" => "0", "authorId" => "1", "matchId" => "Q35610"]],
            ["checkconfig.php", "/check", "check", ["_handler" => "check"]],
            ["epubreader.php?db=0&data=20&title=Alice%20s%20Adventures%20in%20Wonderland", "/read/0/20/Alice_s_Adventures_in_Wonderland", "read-title", ["_handler" => "read", "db" => "0", "data" => "20", "title" => "Alice's Adventures in Wonderland"]],
            ["epubreader.php?db=0&data=20", "/read/0/20", "read", ["_handler" => "read", "db" => "0", "data" => "20"]],
            ["sendtomail.php", "/mail", "mail", ["_handler" => "mail", "_method" => "POST"]],  // fake _method to simulate POST
            ["fetch.php?db=0&id=17&thumb=html", "/thumbs/0/17/html.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "html", "db" => "0", "id" => "17"]],
            ["fetch.php?db=0&id=17&thumb=opds", "/thumbs/0/17/opds.jpg", "fetch-thumb", ["_handler" => "fetch", "thumb" => "opds", "db" => "0", "id" => "17"]],
            ["fetch.php?db=0&id=17", "/covers/0/17.jpg", "fetch-cover", ["_handler" => "fetch", "db" => "0", "id" => "17"]],
            ["fetch.php?view=1&db=0&data=20&type=epub", "/inline/0/20/ignore.epub", "fetch-inline", ["_handler" => "fetch", "view" => "1", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["fetch.php?db=0&data=20&type=epub", "/fetch/0/20/ignore.epub", "fetch-data", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub"]],
            ["fetch.php?db=0&id=17&file=hello.txt", "/files/0/17/hello.txt", "fetch-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "hello.txt"]],
            ["fetch.php?db=0&id=17&file=zipped", "/files/0/17/zipped", "fetch-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "zipped"]],
            ["zipper.php?page=10&type=any", "/zipper/10/any.zip", "zipper-page-type", ["_handler" => "zipper", "page" => "10", "type" => "any"]],
            // skip feed-page routes for handler::page() - use feed-path route with default page handler
            ["feed.php?page=3&id=1&title=Arthur%20Conan%20Doyle", "/feed/3/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["feed.php?page=3&id=1", "/feed/3/1", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1"]],
            ["feed.php?page=10", "/feed/10", "feed-page", ["_handler" => "feed", "page" => "10"]],
            // skip opds-page routes for handler::page() - use opds-path route with default page handler
            ["opds.php?page=3&id=1&title=Arthur%20Conan%20Doyle", "/opds/3/1?title=Arthur%20Conan%20Doyle", "opds-page-id", ["_handler" => "opds", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["opds.php?page=3&id=1", "/opds/3/1", "opds-page-id", ["_handler" => "opds", "page" => "3", "id" => "1"]],
            ["opds.php?page=10", "/opds/10", "opds-page", ["_handler" => "opds", "page" => "10"]],
            // use default page handler with prefix for opds-path + use _route in params to generate path
            ["opds.php?page=3&id=1&title=Arthur%20Conan%20Doyle", "/opds/authors/1/Arthur_Conan_Doyle", "opds-path", ["_handler" => "opds", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle", "_route" => "page-author"]],
            ["opds.php?page=3&id=1", "/opds/authors/1", "opds-path", ["_handler" => "opds", "page" => "3", "id" => "1", "_route" => "page-3-id"]],
            ["opds.php?page=10", "/opds/recent", "opds-path", ["_handler" => "opds", "page" => "10", "_route" => "page-recent"]],
            ["restapi.php?_resource=openapi", "/restapi/openapi", "restapi-openapi", ["_handler" => "restapi", "_resource" => "openapi"]],
            ["graphql.php", "/graphql", "graphql", ["_handler" => "graphql"]],
        ];
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
        // skip feed-page routes for handler::page() - use feed-path route with default page handler
        if (str_starts_with($route, "feed-page")) {
            $this->markTestSkipped("Skip feed-page routes here");
        }
        // skip opds-page routes for handler::page() - use opds-path route with default page handler
        if (str_starts_with($route, "opds-page")) {
            $this->markTestSkipped("Skip opds-page routes here");
        }
        $expected = "index.php" . $expected;
        $params = [];
        parse_str(parse_url((string) $link, PHP_URL_QUERY) ?? '', $params);
        $endpoint = parse_url((string) $link, PHP_URL_PATH);
        // 1. this will find handler name based on old endpoints
        $handler = "html";
        if ($endpoint !== Config::ENDPOINT["html"]) {
            $testpoint = str_replace('.php', '', $endpoint);
            if (array_key_exists($testpoint, Config::ENDPOINT)) {
                $params[Route::HANDLER_PARAM] = $testpoint;
                $handler = $testpoint;
            } else {
                // for epubreader.php, checkconfig.php etc.
                $flipped = array_flip(Config::ENDPOINT);
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
     * @param mixed $expected
     * @param mixed $path
     * @param mixed $route
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('linkProvider')]
    public function testRouteMatch($expected, $path, $route)
    {
        $path = "index.php" . $path;
        $query = parse_url((string) $path, PHP_URL_QUERY);
        // parse_url() does not decode URL-encoded characters in the path
        $path = parse_url((string) $path, PHP_URL_PATH);
        $parts = explode('/', $path);
        $endpoint = array_shift($parts);
        $path = '/' . implode('/', $parts);
        $params = Route::match($path);
        if (is_null($params)) {
            $this->fail('Invalid params for path ' . $path);
        }
        // this contains handler class-string now
        if (!empty($params[Route::HANDLER_PARAM]) && in_array($params[Route::HANDLER_PARAM], Framework::getHandlers())) {
            $name = $params[Route::HANDLER_PARAM]::HANDLER;
            $endpoint = Config::ENDPOINT[$name];
            unset($params[Route::HANDLER_PARAM]);
            // parse path parameter
            if (in_array($name, ['feed', 'opds']) && !empty($params['path'])) {
                $params = Route::match('/' . $params['path']);
            }
            // un-slugify parameter (minimal) - not tested here
            foreach (['title', 'author'] as $param) {
                if (isset($params[$param])) {
                    $params[$param] = rawurldecode($params[$param]);
                }
            }
        }
        if (array_key_exists('ignore', $params)) {
            unset($params['ignore']);
        }
        $test = $endpoint;
        unset($params[Route::HANDLER_PARAM]);
        unset($params[Route::ROUTE_PARAM]);
        if (!empty($params)) {
            if (!empty($params['title'])) {
                $params['title'] = str_replace('_', ' ', $params['title']);
            }
            $test .= '?' . Route::getQueryString($params);
        }
        if (!empty($query)) {
            $test .= '&' . $query;
        }
        $this->assertEquals($expected, $test);
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
        if (!empty($routeUrl) && str_contains($routeUrl, '?')) {
            $this->markTestSkipped('Generate uri with FastRoute still has some issues - e.g. not knowing which params have been "consumed"');
        }
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
            $params['title'] = Route::slugify($params['title']);
        }
        if (!empty($params['file'])) {
            $params['file'] = implode('/', array_map('rawurlencode', explode('/', $params['file'])));
        }
        try {
            $result = Route::generate($route, $params);
        } catch (Throwable) {
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
        // @todo replace handler name with class-string
        //$handlers = Framework::getHandlers();
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
            ["/files/0/17/sub%20dir/hello%20world.txt", "fetch-file", ["_handler" => "fetch", "db" => 0, "id" => 17, "file" => "sub dir/hello world.txt"]],
            ["/zipper/3/3/any.zip", "zipper-page-id-type", ["_handler" => "zipper", "page" => "3", "type" => "any", "id" => "3"]],
            ["/zipper/10/any.zip", "zipper-page-type", ["_handler" => "zipper", "page" => "10", "type" => "any"]],
            ["/loader/wd_author/0/1?matchId=Q35610", "loader-action-dbNum-authorId", ["_handler" => "loader", "action" => "wd_author", "dbNum" => "0", "authorId" => "1", "matchId" => "Q35610"]],
            ["/check", "check", ["_handler" => "check"]],
            ["/read/0/20/Alice_s_Adventures_in_Wonderland", "read-title", ["_handler" => "read", "db" => "0", "data" => "20", "title" => "Alice's Adventures in Wonderland"]],
            ["/read/0/20", "read", ["_handler" => "read", "db" => "0", "data" => "20"]],
            ["/mail", "mail", ["_handler" => "mail", "_method" => "POST"]],  // fake _method to simulate POST
            // skip feed-page routes for Route::getRouteForParams() - use feed-path route with default page handler
            ["/feed/3/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle"]],
            ["/feed/3/1", "feed-page-id", ["_handler" => "feed", "page" => "3", "id" => "1"]],
            ["/feed/10", "feed-page", ["_handler" => "feed", "page" => "10"]],
            // use default page handler with prefix for feed-path + use _route in params to generate path
            ["/feed/authors/1/Arthur_Conan_Doyle", "feed-path", ["_handler" => "feed", "page" => "3", "id" => "1", "title" => "Arthur Conan Doyle", "_route" => "page-author"]],
            ["/feed/authors/1", "feed-path", ["_handler" => "feed", "page" => "3", "id" => "1", "_route" => "page-3-id"]],
            ["/feed/recent", "feed-path", ["_handler" => "feed", "page" => "10", "_route" => "page-recent"]],
            ["/restapi/openapi", "restapi-openapi", ["_handler" => "restapi", "_resource" => "openapi"]],
            ["/graphql", "graphql", ["_handler" => "graphql"]],
            // @todo handle url rewriting if enabled separately - path parameters are different
            ["/view/20/0/ignore.epub", "fetch-view", ["_handler" => "fetch", "view" => "1", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub", "_route" => "fetch-view"]],
            ["/download/20/0/ignore.epub", "fetch-download", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub", "_route" => "fetch-download"]],
        ];
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
        $this->assertEquals($expected, Route::getRouteForParams($params));
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
        if (!empty($routeUrl) && str_contains($routeUrl, '?')) {
            $this->markTestSkipped('Generate uri with FastRoute still has some issues - e.g. not knowing which params have been "consumed"');
        }
        unset($params[Route::HANDLER_PARAM]);
        unset($params["_method"]);
        $prefix = "";
        // use default page handler with prefix for feed-path + use _route in params to generate path
        if ($route == "feed-path" && !empty($params[Route::ROUTE_PARAM])) {
            $route = $params[Route::ROUTE_PARAM];
            $prefix = "/feed";
        }
        if (!empty($params['title']) && !in_array($route, ['feed-page-id', 'opds-page-id'])) {
            $params['title'] = Route::slugify($params['title']);
        }
        if (!empty($params['file'])) {
            $params['file'] = implode('/', array_map('rawurlencode', explode('/', $params['file'])));
        }
        try {
            $result = Route::generate($route, $params);
        } catch (Throwable) {
            $this->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $this->assertEquals($expected, $prefix . $result);
    }
}
