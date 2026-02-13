<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Routing;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Routing\RouteCollection;
use SebLucas\Cops\Routing\UriGenerator;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use Throwable;

class RouteTest extends TestCase
{
    // @deprecated 3.5.7 remove old endpoints in uri generator
    public const OLD_ENDPOINT = [
        "html" => "index.php",
        "feed" => "feed.php",
        "json" => "getJSON.php",
        "fetch" => "fetch.php",
        "read" => "epubreader.php",
        "epubfs" => "epubfs.php",
        "restapi" => "restapi.php",
        "check" => "checkconfig.php",
        "opds" => "opds.php",
        "loader" => "loader.php",
        "zipper" => "zipper.php",
        "calibre" => "calibre.php",
        "calres" => "calres.php",
        "zipfs" => "zipfs.php",
        "mail" => "sendtomail.php",
        "graphql" => "graphql.php",
        "tables" => "tables.php",
    ];
    protected static HandlerManager $manager;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$manager = new HandlerManager();
    }

    public function testDump(): void
    {
        $routes = new RouteCollection(new HandlerManager());
        $expected = $routes->count();
        $routes->dump();
        $routes->load();
        $test = $routes->count();
        $this->assertEquals($expected, $test);
    }

    /**
     * Summary of linkProvider
     * @return array<mixed>
     */
    public static function linkProvider()
    {
        return [
            ["index.php?page=index", "/index", "page-index", ["page" => "index"]],
            ["index.php?page=authors", "/authors", "page-authors", ["page" => "authors"]],
            ["index.php?page=authors&letter=1", "/authors/letter", "page-authors-letters", ["page" => "authors", "letter" => "1"]],
            ["index.php?page=authors_letter&letter=D", "/authors/letter/D", "page-authors-letter", ["page" => "authors_letter", "letter" => "D"]],
            ["index.php?page=author&id=1", "/authors/1", "page-author-id", ["page" => "author", "id" => "1"]],
            ["index.php?page=author&id=1&title=Title", "/authors/1/Title", "page-author", ["page" => "author", "id" => "1", "title" => "Title"]],
            ["index.php?page=books", "/books", "page-books", ["page" => "books"]],
            ["index.php?page=books&letter=1", "/books/letter", "page-books-letters", ["page" => "books", "letter" => "1"]],
            ["index.php?page=books_letter&letter=A", "/books/letter/A", "page-books-letter", ["page" => "books_letter", "letter" => "A"]],
            ["index.php?page=books&year=1", "/books/year", "page-books-years", ["page" => "books", "year" => "1"]],
            ["index.php?page=books_year&year=2006", "/books/year/2006", "page-books-year", ["page" => "books_year", "year" => "2006"]],
            ["index.php?page=series", "/series", "page-series", ["page" => "series"]],
            ["index.php?page=serie&id=1", "/series/1", "page-serie-id", ["page" => "serie", "id" => "1"]],
            ["index.php?page=serie&id=1&title=Title", "/series/1/Title", "page-serie", ["page" => "serie", "id" => "1", "title" => "Title"]],
            ["index.php?page=opensearch", "/search", "page-search", ["page" => "opensearch"]],
            ["index.php?page=query&query=alice", "/search/alice", "page-query", ["page" => "query", "query" => "alice"]],
            ["index.php?page=query&query=alice&scope=book", "/search/alice/book", "page-query-scope", ["page" => "query", "query" => "alice", "scope" => "book"]],
            ["index.php?page=query&search=1&query=alice", "/typeahead?query=alice", "page-typeahead", ["page" => "query", "search" => "1", "query" => "alice"]],
            ["index.php?page=query&search=1&query=alice&scope=book", "/typeahead?query=alice&scope=book", "page-typeahead", ["page" => "query", "search" => "1", "query" => "alice", "scope" => "book"]],
            ["index.php?page=recent", "/recent", "page-recent", ["page" => "recent"]],
            ["index.php?page=tags", "/tags", "page-tags", ["page" => "tags"]],
            ["index.php?page=tag&id=1", "/tags/1", "page-tag-id", ["page" => "tag", "id" => "1"]],
            ["index.php?page=tag&id=1&title=Title", "/tags/1/Title", "page-tag", ["page" => "tag", "id" => "1", "title" => "Title"]],
            ["index.php?page=book&id=2", "/books/2", "page-book-id", ["page" => "book", "id" => "2"]],
            ["index.php?page=book&id=2&author=Author&title=Title", "/books/2/Author/Title", "page-book", ["page" => "book", "id" => "2", "author" => "Author", "title" => "Title"]],
            ["index.php?page=customtype&custom=1", "/custom/1", "page-customtype", ["page" => "customtype", "custom" => "1"]],
            ["index.php?page=custom&custom=1&id=2", "/custom/1/2", "page-custom", ["page" => "custom", "custom" => "1", "id" => "2"]],
            ["index.php?page=custom&custom=1&id=not_set", "/custom/1/not_set", "page-custom", ["page" => "custom", "custom" => "1", "id" => "not_set"]],  // @todo id = null
            ["index.php?page=about", "/about", "page-about", ["page" => "about"]],
            ["index.php?page=languages", "/languages", "page-languages", ["page" => "languages"]],
            ["index.php?page=language&id=1", "/languages/1", "page-language-id", ["page" => "language", "id" => "1"]],
            ["index.php?page=language&id=1&title=Title", "/languages/1/Title", "page-language", ["page" => "language", "id" => "1", "title" => "Title"]],
            ["index.php?page=customize", "/customize", "page-customize", ["page" => "customize"]],
            ["index.php?page=publishers", "/publishers", "page-publishers", ["page" => "publishers"]],
            ["index.php?page=publisher&id=1", "/publishers/1", "page-publisher-id", ["page" => "publisher", "id" => "1"]],
            ["index.php?page=publisher&id=1&title=Title", "/publishers/1/Title", "page-publisher", ["page" => "publisher", "id" => "1", "title" => "Title"]],
            ["index.php?page=ratings", "/ratings", "page-ratings", ["page" => "ratings"]],
            ["index.php?page=rating&id=1", "/ratings/1", "page-rating-id", ["page" => "rating", "id" => "1"]],
            ["index.php?page=rating&id=1&title=Title", "/ratings/1/Title", "page-rating", ["page" => "rating", "id" => "1", "title" => "Title"]],
            ["index.php?page=ratings&a=1", "/ratings?a=1", "page-ratings", ["page" => "ratings", "a" => "1"]],
            ["index.php?page=rating&id=1&a=1", "/ratings/1?a=1", "page-rating-id", ["page" => "rating", "id" => "1", "a" => "1"]],
            ["calibre.php?action=book-details&library=_hex_-4261736557697468536f6d65426f6f6b73&details=17", "/calibre/book-details/_hex_-4261736557697468536f6d65426f6f6b73/17", "calibre-details", ["_handler" => "calibre", "action" => "book-details", "library" => "_hex_-4261736557697468536f6d65426f6f6b73", "details" => "17"]],
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
            ["zipper.php?page=recent&type=any", "/zipper/recent/any.zip", "zipper-page-type", ["_handler" => "zipper", "page" => "recent", "type" => "any"]],
            // skip feed-page routes for handler::link() - use feed-path route with default page handler
            //["feed.php?page=author&id=1&title=Arthur%20Conan%20Doyle", "/feed/author/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "author", "id" => "1", "title" => "Arthur Conan Doyle"]],
            //["feed.php?page=author&id=1", "/feed/author/1", "feed-page-id", ["_handler" => "feed", "page" => "author", "id" => "1"]],
            ["feed.php?page=recent", "/feed/recent", "feed-page", ["_handler" => "feed", "page" => "recent"]],
            // skip opds-page routes for handler::link() - use opds-path route with default page handler
            //["opds.php?page=author&id=1&title=Arthur%20Conan%20Doyle", "/opds/author/1?title=Arthur%20Conan%20Doyle", "opds-page-id", ["_handler" => "opds", "page" => "author", "id" => "1", "title" => "Arthur Conan Doyle"]],
            //["opds.php?page=author&id=1", "/opds/author/1", "opds-page-id", ["_handler" => "opds", "page" => "author", "id" => "1"]],
            ["opds.php?page=recent", "/opds/recent", "opds-page", ["_handler" => "opds", "page" => "recent"]],
            // use default page handler with prefix for opds-path + use _route in params to generate path
            ["opds.php?page=author&id=1&title=Arthur%20Conan%20Doyle", "/opds/authors/1/Arthur_Conan_Doyle", "opds-path", ["_handler" => "opds", "page" => "author", "id" => "1", "title" => "Arthur Conan Doyle", "_route" => "page-author"]],
            ["opds.php?page=author&id=1", "/opds/authors/1", "opds-path", ["_handler" => "opds", "page" => "author", "id" => "1", "_route" => "page-author-id"]],
            // @todo check page vs path
            //["opds.php?page=recent", "/opds/recent", "opds-path", ["_handler" => "opds", "page" => "recent", "_route" => "page-recent"]],
            ["restapi.php?_resource=openapi", "/restapi/openapi", "restapi-openapi", ["_handler" => "restapi", "_resource" => "openapi"]],
            ["graphql.php", "/graphql", "graphql", ["_handler" => "graphql"]],
        ];
    }

    /**
     * Summary of routeProvider
     * @return array<mixed>
     */
    public static function routeProvider()
    {
        // @todo replace handler name with class-string
        return [
            ["/calibre/book-details/_hex_-4261736557697468536f6d65426f6f6b73/17", "calibre-details", ["_handler" => "calibre", "action" => "book-details", "library" => "_hex_-4261736557697468536f6d65426f6f6b73", "details" => "17"]],
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
            // @todo not supporting Ignore_Title.kepub.epub in URL here - match excludes \. in {ignore} by default for Symfony with {ignore}.{type}
            ["/fetch/0/20/Alice_s_Adventures_in_Wonderland_Lewis_Carroll_kepub.epub", "fetch-data", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "Alice's Adventures in Wonderland - Lewis Carroll.kepub", "type" => "epub"]],
            ["/files/0/17/hello.txt", "fetch-file", ["_handler" => "fetch", "db" => "0", "id" => "17", "file" => "hello.txt"]],
            ["/files/0/17/sub%20dir/hello%20world.txt", "fetch-file", ["_handler" => "fetch", "db" => 0, "id" => 17, "file" => "sub dir/hello world.txt"]],
            ["/zipper/author/3/any.zip", "zipper-page-id-type", ["_handler" => "zipper", "page" => "author", "type" => "any", "id" => "3"]],
            ["/zipper/recent/any.zip", "zipper-page-type", ["_handler" => "zipper", "page" => "recent", "type" => "any"]],
            ["/loader/wd_author/0/1?matchId=Q35610", "loader-action-dbNum-authorId", ["_handler" => "loader", "action" => "wd_author", "dbNum" => "0", "authorId" => "1", "matchId" => "Q35610"]],
            ["/check", "check", ["_handler" => "check"]],
            ["/read/0/20/Alice_s_Adventures_in_Wonderland", "read-title", ["_handler" => "read", "db" => "0", "data" => "20", "title" => "Alice's Adventures in Wonderland"]],
            ["/read/0/20", "read", ["_handler" => "read", "db" => "0", "data" => "20"]],
            ["/mail", "mail", ["_handler" => "mail", "_method" => "POST"]],  // fake _method to simulate POST
            // skip feed-page routes for UriGenerator::getRouteForParams() - use feed-path route with default page handler
            //["/feed/author/1?title=Arthur%20Conan%20Doyle", "feed-page-id", ["_handler" => "feed", "page" => "author", "id" => "1", "title" => "Arthur Conan Doyle"]],
            //["/feed/author/1", "feed-page-id", ["_handler" => "feed", "page" => "author", "id" => "1"]],
            ["/feed/recent", "feed-page", ["_handler" => "feed", "page" => "recent"]],
            // use default page handler with prefix for feed-path + use _route in params to generate path
            ["/feed/authors/1/Arthur_Conan_Doyle", "feed-path", ["_handler" => "feed", "page" => "author", "id" => "1", "title" => "Arthur Conan Doyle", "_route" => "page-author"]],
            ["/feed/authors/1", "feed-path", ["_handler" => "feed", "page" => "author", "id" => "1", "_route" => "page-author-id"]],
            // @todo check page vs path
            //["/feed/recent", "feed-path", ["_handler" => "feed", "page" => "recent", "_route" => "page-recent"]],
            ["/restapi/openapi", "restapi-openapi", ["_handler" => "restapi", "_resource" => "openapi"]],
            ["/graphql", "graphql", ["_handler" => "graphql"]],
            // @todo handle url rewriting if enabled separately - path parameters are different
            ["/view/20/0/ignore.epub", "fetch-view", ["_handler" => "fetch", "view" => "1", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub", "_route" => "fetch-view"]],
            ["/download/20/0/ignore.epub", "fetch-download", ["_handler" => "fetch", "db" => "0", "data" => "20", "ignore" => "ignore", "type" => "epub", "_route" => "fetch-download"]],
        ];
    }

    /**
     * Summary of getRouteProvider
     * @param class-string $handler
     * @param array<mixed> $defaults
     * @return array<mixed>
     */
    public static function getRouteProvider($handler, $defaults = [])
    {
        $missing = [];
        $result = [];
        $routes = $handler::getRoutes();
        foreach ($routes as $name => $route) {
            // Add params, methods and options if needed
            array_push($route, [], [], []);
            [$path, $params, $methods, $options] = $route;
            $fixed = $params;
            // Add ["_handler" => $handler] to params
            if ($handler::HANDLER !== HtmlHandler::class) {
                $params[Request::HANDLER_PARAM] ??= $handler;
            } else {
                // default routes can be used by html, json, phpunit, restapi without _resource, ...
            }
            // Add ["_route" => $name] to params
            if (empty($params[Request::ROUTE_PARAM]) && !str_starts_with($name, 'restapi-')) {
                $params[Request::ROUTE_PARAM] ??= $name;
            }
            $found = [];
            // check and replace path params + support custom patterns - see nikic/fast-route
            $count = preg_match_all("~\{(\w+(|:[^}]+))\}~", $path, $found);
            if (empty($count)) {
                $result[] = [$path, $name, $params, $fixed];
                continue;
            }
            foreach ($found[1] as $key => $match) {
                $pattern = '';
                if (str_contains($match, ':')) {
                    [$param, $pattern] = explode(':', $match);
                } else {
                    $param = $match;
                }
                if (!empty($defaults[$name]) && isset($defaults[$name][$param])) {
                    $value = (string) $defaults[$name][$param];
                } elseif (!empty($defaults['any']) && isset($defaults['any'][$param])) {
                    $value = (string) $defaults['any'][$param];
                } else {
                    $value = (string) $key;
                    $missing[$name] ??= [];
                    $missing[$name][$param] ??= $key;
                }
                if (in_array($param, ['author', 'title'])) {
                    $value = UriGenerator::getSlugger()->slug($value, '_');
                }
                if (in_array($param, ['file', 'path']) && !str_contains($value, '%')) {
                    $encoded = implode('/', array_map('rawurlencode', explode('/', $value)));
                    $path = str_replace('{' . $match . '}', $encoded, $path);
                } else {
                    $path = str_replace('{' . $match . '}', $value, $path);
                }
                // add dummy params here for tests
                $params[$param] ??= $value;
            }
            $result[] = [$path, $name, $params, $fixed];
        }
        if (!empty($missing)) {
            var_dump($missing);
        }
        return $result;
    }

    /**
     * @param TestCase $test
     * @param mixed $expected
     * @param mixed $route
     * @param mixed $params
     * @return void
     */
    public static function getRouteForParams($test, $expected, $route, $params)
    {
        // pass handler class-string as param here
        if (!empty($params[Request::HANDLER_PARAM])) {
            self::$manager ??= new HandlerManager();
            $params[Request::HANDLER_PARAM] = self::$manager->getHandlerClass($params[Request::HANDLER_PARAM]);
        }
        $test->assertEquals($expected, UriGenerator::getRouteForParams($params));
    }

    /**
     * @param TestCase $test
     * @param mixed $routeUrl
     * @param mixed $route
     * @param mixed $params
     * @param string $prefix
     * @return void
     */
    public static function generateRoute($test, $routeUrl, $route, $params, $prefix = "")
    {
        try {
            $result = UriGenerator::generate($route, $params);
        } catch (Throwable) {
            $test->assertNull($routeUrl);
            return;
        }
        $expected = $routeUrl;
        $test->assertEquals($expected, $prefix . $result);
    }
}
