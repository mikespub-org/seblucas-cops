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

    /**
     * Summary of getLinks
     * @return array<mixed>
     */
    public static function getLinks()
    {
        return [
            "index.php?page=index" => "index.php/index",
            "index.php?page=1" => "index.php/authors",
            "index.php?page=1&letter=1" => "index.php/authors/letter",
            "index.php?page=2&id=D" => "index.php/authors/letter/D",
            "index.php?page=3&id=1" => "index.php/authors/1",
            "index.php?page=4" => "index.php/books",
            "index.php?page=4&letter=1" => "index.php/books/letter",
            "index.php?page=5&id=A" => "index.php/books/letter/A",
            "index.php?page=4&year=1" => "index.php/books/year",
            "index.php?page=50&id=2006" => "index.php/books/year/2006",
            "index.php?page=6" => "index.php/series",
            "index.php?page=7&id=1" => "index.php/series/1",
            "index.php?page=8" => "index.php/search",
            "index.php?page=9&query=alice" => "index.php/search/alice",
            "index.php?page=9&query=alice&scope=book" => "index.php/search/alice/book",
            "index.php?page=9&search=1&query=alice" => "index.php/typeahead?query=alice",
            "index.php?page=9&search=1&query=alice&scope=book" => "index.php/typeahead?query=alice&scope=book",
            "index.php?page=10" => "index.php/recent",
            "index.php?page=11" => "index.php/tags",
            "index.php?page=12&id=1" => "index.php/tags/1",
            "index.php?page=13&id=2" => "index.php/books/2",
            "index.php?page=14&custom=1" => "index.php/custom/1",
            "index.php?page=15&custom=1&id=2" => "index.php/custom/1/2",
            "index.php?page=15&custom=1&id=not_set" => "index.php/custom/1/not_set",  // @todo id = null
            "index.php?page=16" => "index.php/about",
            "index.php?page=17" => "index.php/languages",
            "index.php?page=18&id=1" => "index.php/languages/1",
            "index.php?page=19" => "index.php/customize",
            "index.php?page=20" => "index.php/publishers",
            "index.php?page=21&id=1" => "index.php/publishers/1",
            "index.php?page=22" => "index.php/ratings",
            "index.php?page=23&id=1" => "index.php/ratings/1",
            "index.php?page=22&a=1" => "index.php/ratings?a=1",
            "index.php?page=23&id=1&a=1" => "index.php/ratings/1?a=1",
            "calres.php?db=0&alg=xxh64&digest=7c301792c52eebf7" => "index.php/calres/0/xxh64/7c301792c52eebf7",
            "zipfs.php?db=0&data=20&comp=META-INF%2Fcontainer.xml" => "index.php/zipfs/0/20/META-INF/container.xml",
            "loader.php?action=wd_author&dbNum=0&authorId=1&matchId=Q35610" => "index.php/loader/wd_author/0/1?matchId=Q35610",
            "checkconfig.php" => "index.php/check",
            "epubreader.php?db=0&data=20&title=Alice%27s_Adventures_in_Wonderland" => "index.php/read/0/20/Alice%27s_Adventures_in_Wonderland",
            "epubreader.php?db=0&data=20" => "index.php/read/0/20",
            "sendtomail.php" => "index.php/mail",
            "fetch.php?db=0&id=17&thumb=html" => "index.php/thumbs/0/17/html.jpg",
            "fetch.php?db=0&id=17&thumb=opds" => "index.php/thumbs/0/17/opds.jpg",
            "fetch.php?db=0&id=17" => "index.php/covers/0/17.jpg",
            "fetch.php?view=1&db=0&data=20&type=epub" => "index.php/inline/0/20/ignore.epub",
            "fetch.php?db=0&data=20&type=epub" => "index.php/fetch/0/20/ignore.epub",
            "fetch.php?db=0&id=17&file=hello.txt" => "index.php/files/0/17/hello.txt",
            "fetch.php?db=0&id=17&file=zipped" => "index.php/files/0/17/zipped",
            "zipper.php?page=10&type=any" => "index.php/zipper/10/any.zip",
            "feed.php?page=3&id=1&title=Arthur_Conan_Doyle" => "index.php/feed/authors/1/Arthur_Conan_Doyle",
            "feed.php?page=3&id=1" => "index.php/feed/authors/1",
            "feed.php?page=10" => "index.php/feed/recent",
            "opds.php?page=3&id=1&title=Arthur_Conan_Doyle" => "index.php/opds/authors/1/Arthur_Conan_Doyle",
            "opds.php?page=3&id=1" => "index.php/opds/authors/1",
            "opds.php?page=10" => "index.php/opds/recent",
            "restapi.php?_resource=openapi" => "index.php/restapi/openapi",
            "graphql.php" => "index.php/graphql",
        ];
    }

    /**
     * Summary of linkProvider
     * @return array<mixed>
     */
    public static function linkProvider()
    {
        $data = [];
        $links = self::getLinks();
        foreach ($links as $from => $to) {
            array_push($data, [$from, $to]);
        }
        return $data;
    }

    /**
     * @param mixed $link
     * @param mixed $expected
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('linkProvider')]
    public function testRouteLink($link, $expected)
    {
        $params = [];
        parse_str(parse_url((string) $link, PHP_URL_QUERY) ?? '', $params);
        $page = $params["page"] ?? null;
        unset($params["page"]);
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
        $test = $handler::page($page, $params);
        $this->assertStringEndsWith($expected, $test);
    }

    /**
     * @param mixed $expected
     * @param mixed $path
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('linkProvider')]
    public function testRouteMatch($expected, $path)
    {
        $query = parse_url((string) $path, PHP_URL_QUERY);
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
            $test .= '?' . Route::getQueryString($params);
        }
        if (!empty($query)) {
            $test .= '&' . $query;
        }
        $this->assertEquals($expected, $test);
    }

    /**
     * Summary of getRoutes
     * @return array<mixed>
     */
    public static function getRoutes()
    {
        // @todo replace handler name with class-string
        //$handlers = Framework::getHandlers();
        return [
            "/calres/0/xxh64/7c301792c52eebf7" => [Route::HANDLER_PARAM => "calres", "db" => 0, "alg" => "xxh64", "digest" => "7c301792c52eebf7"],
            "/zipfs/0/20/META-INF/container.xml" => [Route::HANDLER_PARAM => "zipfs", "db" => 0, "data" => 20, "comp" => "META-INF/container.xml"],
            null => [Route::HANDLER_PARAM => "zipfs", "db" => "x", "data" => 20, "comp" => "META-INF/container.xml"],
            "/thumbs/0/17/html.jpg" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "html"],
            "/thumbs/0/17/opds.jpg" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "opds"],
            "/thumbs/0/17/html2.jpg" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "html2"],
            "/thumbs/0/17/opds2.jpg" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "opds2"],
            "/covers/0/17.jpg" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17],
            "/inline/0/20/ignore.epub" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "data" => 20, "type" => "epub", "view" => 1],
            "/fetch/0/20/ignore.epub" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "data" => 20, "type" => "epub"],
            "/files/0/17/hello.txt" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "file" => "hello.txt"],
            "/files/0/17/sub%20dir/hello%20world.txt" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "file" => "sub dir/hello world.txt"],
            "/zipper/3/3/any.zip" => [Route::HANDLER_PARAM => "zipper", "page" => 3, "type" => "any", "id" => 3],
            "/zipper/10/any.zip" => [Route::HANDLER_PARAM => "zipper", "page" => 10, "type" => "any"],
            "/loader/wd_author/0/1?matchId=Q35610" => [Route::HANDLER_PARAM => "loader", "action" => "wd_author", "dbNum" => 0, "authorId" => 1, "matchId" => "Q35610"],
            "/check" => [Route::HANDLER_PARAM => "check"],
            "/read/0/20/Alice%27s_Adventures_in_Wonderland" => [Route::HANDLER_PARAM => "read", "db" => 0, "data" => 20, "title" => "Alice's Adventures in Wonderland"],
            "/read/0/20" => [Route::HANDLER_PARAM => "read", "db" => 0, "data" => 20],
            "/mail" => [Route::HANDLER_PARAM => "mail"],
            "/feed/authors/1/Arthur_Conan_Doyle" => [Route::HANDLER_PARAM => "feed", "page" => 3, "id" => 1, "title" => "Arthur Conan Doyle"],
            "/feed/authors/1" => [Route::HANDLER_PARAM => "feed", "page" => 3, "id" => 1],
            "/feed/recent" => [Route::HANDLER_PARAM => "feed", "page" => 10],
            "/restapi/openapi" => [Route::HANDLER_PARAM => "restapi", "path" => "openapi"],
            "/graphql" => [Route::HANDLER_PARAM => "graphql"],
            // @todo handle url rewriting if enabled separately - path parameters are different
            "/view/20/0/ignore.epub" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "data" => 20, "ignore" => "ignore", "type" => "epub", "view" => 1, Route::ROUTE_PARAM => "fetch-view"],
            "/download/20/0/ignore.epub" => [Route::HANDLER_PARAM => "fetch", "db" => 0, "data" => 20, "ignore" => "ignore", "type" => "epub", Route::ROUTE_PARAM => "fetch-download"],
        ];
    }

    /**
     * Summary of routeProvider
     * @return array<mixed>
     */
    public static function routeProvider()
    {
        $data = [];
        $routes = self::getRoutes();
        foreach ($routes as $from => $to) {
            array_push($data, [$from, $to]);
        }
        return $data;
    }

    /**
     * @param mixed $expected
     * @param mixed $params
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('routeProvider')]
    public function testGetRouteForParams($expected, $params)
    {
        // pass handler class-string as param here
        if (!empty($params[Route::HANDLER_PARAM])) {
            $params[Route::HANDLER_PARAM] = Route::getHandler($params[Route::HANDLER_PARAM]);
        }
        $this->assertEquals($expected, Route::getRouteForParams($params));
    }
}
