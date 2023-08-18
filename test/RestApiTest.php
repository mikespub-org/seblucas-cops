<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Output\RestApi;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\JSONRenderer;
use SebLucas\Cops\Pages\PageId;

class RestApiTest extends TestCase
{
    public static string $script;

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
        $test = $request->get("id");
        $this->assertEquals($expected, $test);

        unset($_SERVER["PATH_INFO"]);
    }

    public function testGetJson(): void
    {
        $request = new Request();
        $apiHandler = new RestApi($request);
        $expected = JSONRenderer::getJson($request);
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
    public function getLinks()
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
        ];
    }

    /**
     * Summary of linkProvider
     * @return array<mixed>
     */
    public function linkProvider()
    {
        $data = [];
        $links = $this->getLinks();
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
        parse_str(parse_url($link, PHP_URL_QUERY), $params);
        $page = $params["page"];
        unset($params["page"]);
        $test = RestApi::$endpoint . Route::link($page, $params);
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
        $test = $endpoint . '?' . http_build_query($params);
        if (!empty($query)) {
            $test .= '&' . $query;
        }
        $this->assertEquals($expected, $test);
    }

    public function testReplaceLinks(): void
    {
        $script = $_SERVER["SCRIPT_NAME"];
        $_SERVER["SCRIPT_NAME"] =  "/" . RestApi::$endpoint;
        $request = new Request();

        $links = $this->getLinks();
        // Note: this does not replace rewrite rules, as they are already generated in code when use_url_rewriting == 1

        $output = json_encode(array_keys($links));
        $endpoint = RestApi::getScriptName($request);

        $expected = json_encode(array_values($links), JSON_UNESCAPED_SLASHES);
        $test = RestApi::replaceLinks($output, $endpoint);
        $this->assertEquals($expected, $test);

        $_SERVER["SCRIPT_NAME"] = $script;
    }

    /**
     * Summary of getRewrites
     * @return array<mixed>
     */
    public function getRewrites()
    {
        return [
            "fetch.php?data=1&type=epub" => "/download/1/ignore.epub",
            "fetch.php?data=1&type=epub&view=1" => "/view/1/ignore.epub",
            "fetch.php?data=1&type=png&height=225" => "/download/1/ignore.png?height=225",
            "fetch.php?data=1&db=0&type=epub" => "/download/1/0/ignore.epub",
            "fetch.php?data=1&db=0&type=epub&view=1" => "/view/1/0/ignore.epub",
            "fetch.php?data=1&db=0&type=png&height=225" => "/download/1/0/ignore.png?height=225",
        ];
    }

    /**
     * Summary of rewriteProvider
     * @return array<mixed>
     */
    public function rewriteProvider()
    {
        $data = [];
        $links = $this->getRewrites();
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
        $test = Route::linkRewrite($endpoint, $params);
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
}
