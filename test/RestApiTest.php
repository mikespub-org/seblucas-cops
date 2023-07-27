<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Output\RestApi;

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\JSONRenderer;
use SebLucas\Cops\Pages\Page;

class RestApiTest extends TestCase
{
    public static $script;

    public static function setUpBeforeClass(): void
    {
        global $config;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Base::clearDb();
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

        $expected = ["page" => Page::BOOK_DETAIL, "id" => 2];
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

        $expected = Page::BOOK_DETAIL;
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

    public function testReplaceLinks(): void
    {
        $script = $_SERVER["SCRIPT_NAME"];
        $_SERVER["SCRIPT_NAME"] =  "/" . RestApi::$endpoint;
        $request = new Request();
        $links = [
            "restapi.php?page=index" => "restapi.php/index",
            "restapi.php?page=1" => "restapi.php/authors",
            "restapi.php?page=2&id=D" => "restapi.php/authors_l/D",
            "restapi.php?page=3&id=1" => "restapi.php/authors/1",
            "restapi.php?page=4" => "restapi.php/books",
            "restapi.php?page=5&id=A" => "restapi.php/books_l/A",
            "restapi.php?page=6" => "restapi.php/series",
            "restapi.php?page=7&id=1" => "restapi.php/series/1",
            //"restapi.php?page=8" => "restapi.php/search,
            "restapi.php?page=9&query=alice" => "restapi.php/search/alice",  // @todo scope
            "restapi.php?page=10" => "restapi.php/recent",
            "restapi.php?page=11" => "restapi.php/tags",
            "restapi.php?page=12&id=1" => "restapi.php/tags/1",
            "restapi.php?page=13&id=2" => "restapi.php/books/2",
            "restapi.php?page=14&custom=1" => "restapi.php/custom/1",
            "restapi.php?page=15&custom=1&id=2" => "restapi.php?page=15&custom=1&id=2",  // @todo custom + id
            "restapi.php?page=16" => "restapi.php/about",
            "restapi.php?page=17" => "restapi.php/languages",
            "restapi.php?page=18&id=1" => "restapi.php/languages/1",
            "restapi.php?page=19" => "restapi.php/customize",
            "restapi.php?page=20" => "restapi.php/publishers",
            "restapi.php?page=21&id=1" => "restapi.php/publishers/1",
            "restapi.php?page=22" => "restapi.php/ratings",
            "restapi.php?page=23&id=1" => "restapi.php/ratings/1",
        ];
        $output = json_encode(array_keys($links));
        $endpoint = RestApi::getScriptName($request);

        $expected = json_encode(array_values($links), JSON_UNESCAPED_SLASHES);
        $test = RestApi::replaceLinks($output, $endpoint);
        $this->assertEquals($expected, $test);

        $_SERVER["SCRIPT_NAME"] = $script;
    }

    public function testGetOutput(): void
    {
        $request = new Request();
        $apiHandler = new RestApi($request);
        $expected = true;
        $test = $apiHandler->getOutput();
        $this->assertEquals($expected, str_starts_with($test, '{"title":"COPS",'));
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
        $expected = "3.1.0";
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
