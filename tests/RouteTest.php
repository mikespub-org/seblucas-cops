<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Input\Route;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;

class RouteTest extends TestCase
{
    /** @var mixed */
    protected static $route;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        // try out route urls
        static::$route = Config::get('use_route_urls');
        Config::set('use_route_urls', true);
    }

    public static function tearDownAfterClass(): void
    {
        Config::set('use_route_urls', static::$route);
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
            "index.php?page=10" => "index.php/recent",
            "index.php?page=11" => "index.php/tags",
            "index.php?page=12&id=1" => "index.php/tags/1",
            "index.php?page=13&id=2" => "index.php/books/2",
            "index.php?page=14&custom=1" => "index.php/custom/1",
            "index.php?page=15&custom=1&id=2" => "index.php/custom/1/2",
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
            "epubreader.php?db=0&data=20" => "index.php/read/0/20",
            "sendtomail.php" => "index.php/mail",
            "fetch.php?thumb=html&db=0&id=17" => "index.php/thumbs/html/0/17.jpg",
            "fetch.php?thumb=opds&db=0&id=17" => "index.php/thumbs/opds/0/17.jpg",
            "fetch.php?db=0&id=17" => "index.php/covers/0/17.jpg",
            "fetch.php?view=1&db=0&data=20&type=epub" => "index.php/inline/0/20/ignore.epub",
            "fetch.php?db=0&data=20&type=epub" => "index.php/fetch/0/20/ignore.epub",
            "fetch.php?db=0&id=17&file=hello.txt" => "index.php/files/0/17/hello.txt",
            "fetch.php?db=0&id=17&file=zipped" => "index.php/files/0/17/zipped",
            "zipper.php?page=10&type=any" => "index.php/zipper/10/any",
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
        if ($endpoint !== Config::ENDPOINT["index"]) {
            $testpoint = str_replace('.php', '', $endpoint);
            if (array_key_exists($testpoint, Config::ENDPOINT)) {
                $params[Route::HANDLER_PARAM] = $testpoint;
            } else {
                // for epubreader.php, checkconfig.php etc.
                $flipped = array_flip(Config::ENDPOINT);
                $params[Route::HANDLER_PARAM] = $flipped[$endpoint];
            }
        }
        $test = Config::ENDPOINT["index"] . Route::page($page, $params);
        //$test = Route::link(RestApi::$handler, $page, $params);
        $this->assertEquals($expected, $test);
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
        if (!empty($params[Route::HANDLER_PARAM]) && array_key_exists($params[Route::HANDLER_PARAM], Config::ENDPOINT)) {
            $endpoint = Config::ENDPOINT[$params[Route::HANDLER_PARAM]];
            unset($params[Route::HANDLER_PARAM]);
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
        $this->assertEquals("/calres/0/xxh64/7c301792c52eebf7", Route::getPageRoute([Route::HANDLER_PARAM => "calres", "db" => 0, "alg" => "xxh64", "digest" => "7c301792c52eebf7"]));
        $this->assertEquals("/zipfs/0/20/META-INF/container.xml", Route::getPageRoute([Route::HANDLER_PARAM => "zipfs", "db" => 0, "data" => 20, "comp" => "META-INF/container.xml"]));
        $this->assertNull(Route::getPageRoute([Route::HANDLER_PARAM => "zipfs", "db" => "x", "data" => 20, "comp" => "META-INF/container.xml"]));
        $this->assertEquals("/loader/wd_author/0/1?matchId=Q35610", Route::getPageRoute([Route::HANDLER_PARAM => "loader", "action" => "wd_author", "dbNum" => 0, "authorId" => 1, "matchId" => "Q35610"]));
        $this->assertEquals("/thumbs/html/0/17.jpg", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "html"]));
        $this->assertEquals("/thumbs/opds/0/17.jpg", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "opds"]));
        $this->assertEquals("/thumbs/html2/0/17.jpg", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "html2"]));
        $this->assertEquals("/thumbs/opds2/0/17.jpg", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "thumb" => "opds2"]));
        $this->assertEquals("/covers/0/17.jpg", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17]));
        $this->assertEquals("/inline/0/20/ignore.epub", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "data" => 20, "type" => "epub", "view" => 1]));
        $this->assertEquals("/fetch/0/20/ignore.epub", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "data" => 20, "type" => "epub"]));
        $this->assertEquals("/files/0/17/hello.txt", Route::getPageRoute([Route::HANDLER_PARAM => "fetch", "db" => 0, "id" => 17, "file" => "hello.txt"]));
        $this->assertEquals("/zipper/3/any/3", Route::getPageRoute([Route::HANDLER_PARAM => "zipper", "page" => 3, "type" => "any", "id" => 3]));
        $this->assertEquals("/zipper/10/any", Route::getPageRoute([Route::HANDLER_PARAM => "zipper", "page" => 10, "type" => "any"]));
        $this->assertEquals("/loader/wd_author/0/1?matchId=Q35610", Route::getPageRoute([Route::HANDLER_PARAM => "loader", "action" => "wd_author", "dbNum" => 0, "authorId" => 1, "matchId" => "Q35610"]));
        $this->assertEquals("/check", Route::getPageRoute([Route::HANDLER_PARAM => "check"]));
        $this->assertEquals("/read/0/20", Route::getPageRoute([Route::HANDLER_PARAM => "read", "db" => 0, "data" => 20]));
        $this->assertEquals("/mail", Route::getPageRoute([Route::HANDLER_PARAM => "mail"]));
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
     * @param mixed $link
     * @param mixed $expected
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('rewriteProvider')]
    public function testRewriteLink($link, $expected)
    {
        $params = [];
        parse_str(parse_url((string) $link, PHP_URL_QUERY), $params);
        $endpoint = parse_url((string) $link, PHP_URL_PATH);
        $test = Route::getUrlRewrite($endpoint, $params);
        $this->assertEquals($expected, $test);
    }

    /**
     * @param mixed $expected
     * @param mixed $path
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('rewriteProvider')]
    public function testRewriteMatch($expected, $path)
    {
        $query = parse_url((string) $path, PHP_URL_QUERY);
        $path = parse_url((string) $path, PHP_URL_PATH);
        //$endpoint = parse_url($expected, PHP_URL_PATH);
        [$endpoint, $params] = Route::matchRewrite($path);
        $test = $endpoint . '?' . http_build_query($params);
        if (!empty($query)) {
            $test .= '&' . $query;
        }
        $this->assertEquals($expected, $test);
    }
}
