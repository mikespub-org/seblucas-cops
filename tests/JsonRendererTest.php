<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Output\JsonRenderer;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\PageId;

class JsonRendererTest extends TestCase
{
    private static string $handler = 'phpunit';

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Config::set('calibre_custom_column', []);
        Config::set('calibre_custom_column_list', []);
        Config::set('calibre_custom_column_preview', []);
        Database::clearDb();
        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "");
    }

    public function testCompleteArray(): void
    {
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $request = Request::build([], self::$handler);
        $renderer = new JsonRenderer();
        $renderer->setRequest($request);
        $test = [];
        $test["c"] = $renderer->getCompleteArray();
        $this->assertArrayHasKey("c", $test);
        $this->assertArrayHasKey("version", $test ["c"]);
        $this->assertArrayHasKey("i18n", $test ["c"]);
        $this->assertArrayHasKey("url", $test ["c"]);
        $this->assertArrayHasKey("config", $test ["c"]);

        $this->assertEquals(Route::link(self::$handler) . "?page=13&id={0}&db={1}", $test ["c"]["url"]["detailUrl"]);
        $this->assertEquals(Route::link("fetch") . "?thumb=html&id={0}&db={1}", $test ["c"]["url"]["thumbnailUrl"]);
        $this->assertFalse($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "1");
        $renderer->setRequest($request);
        $test = [];
        $test["c"] = $renderer->getCompleteArray();

        $this->assertEquals(Route::link("fetch") . "?id={0}&db={1}", $test ["c"]["url"]["thumbnailUrl"]);
        $this->assertTrue($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "/images.png");
        $renderer->setRequest($request);
        $test = [];
        $test["c"] = $renderer->getCompleteArray();

        $this->assertEquals("/images.png", $test ["c"]["url"]["thumbnailUrl"]);

        Config::set('thumbnail_handling', "");
    }

    public function testGetBookContentArrayWithoutSeries(): void
    {
        $book = Book::getBookById(17);
        $book->setHandler(self::$handler);

        $renderer = new JsonRenderer();
        $test = $renderer->getBookContentArray($book);

        $this->assertCount(2, $test ["preferedData"]);
        $this->assertEquals(Route::link("fetch") . "?id=17&type=epub&data=20", $test ["preferedData"][0]["url"]);
        $this->assertEquals(Route::link(self::$handler) . "?page=21&id=2", $test ["publisherurl"]);

        $this->assertEquals("", $test ["seriesName"]);
        $this->assertEquals("1.0", $test ["seriesIndex"]);
        $this->assertEquals("", $test ["seriesCompleteName"]);
        $this->assertEquals("", $test ["seriesurl"]);
    }

    public function testGetBookContentArrayWithSeries(): void
    {
        $book = Book::getBookById(2);
        $book->setHandler(self::$handler);

        $renderer = new JsonRenderer();
        $test = $renderer->getBookContentArray($book);

        $this->assertCount(1, $test ["preferedData"]);
        $this->assertEquals(Route::link("fetch") . "?id=2&type=epub&data=1", $test ["preferedData"][0]["url"]);
        $this->assertEquals(Route::link(self::$handler) . "?page=21&id=6", $test ["publisherurl"]);

        $this->assertEquals("Sherlock Holmes", $test ["seriesName"]);
        $this->assertEquals("6.0", $test ["seriesIndex"]);
        $this->assertEquals("Book 6 in the Sherlock Holmes series", $test ["seriesCompleteName"]);
        $this->assertEquals(Route::link(self::$handler) . "?page=7&id=1", $test ["seriesurl"]);
    }

    public function testGetFullBookContentArray(): void
    {
        $book = Book::getBookById(17);
        $book->setHandler(self::$handler);

        $renderer = new JsonRenderer();
        $test = $renderer->getFullBookContentArray($book);

        $this->assertEquals(Route::link("fetch") . "?id=17", $test ["coverurl"]);
        $this->assertEquals(Route::link("fetch") . "?id=17&thumb=html2", $test ["thumbnailurl"]);
        $this->assertCount(1, $test ["authors"]);
        $this->assertEquals(Route::link(self::$handler) . "?page=3&id=3", $test ["authors"][0]["url"]);
        $this->assertCount(3, $test ["tags"]);
        $this->assertEquals(Route::link(self::$handler) . "?page=12&id=5", $test ["tags"][0]["url"]);
        $this->assertCount(0, $test ["identifiers"]);
        $this->assertCount(3, $test ["datas"]);
        $this->assertEquals(Route::link("fetch") . "?id=17&type=epub&data=20", $test ["datas"][2]["url"]);
        $this->assertEquals(Route::link("fetch") . "?id=17&type=epub&data=20&view=1", $test ["datas"][2]["viewUrl"]);
        $this->assertEquals(Route::link("read") . "?data=20&db=0", $test ["datas"][2]["readerUrl"]);

        // use relative path for calibre directory
        Config::set('calibre_directory', "./tests/BaseWithSomeBooks/");
        Database::clearDb();
        $book = Book::getBookById(17);
        $book->setHandler(self::$handler);

        $renderer = new JsonRenderer();
        $test = $renderer->getFullBookContentArray($book);

        $this->assertEquals(Route::url("./tests/BaseWithSomeBooks/Lewis%20Carroll/Alice%27s%20Adventures%20in%20Wonderland%20%2817%29/cover.jpg"), $test ["coverurl"]);
        $this->assertEquals(Route::link("fetch") . "?id=17&thumb=html2", $test ["thumbnailurl"]);
        // see bookTest for more tests on data links
        $this->assertEquals(Route::url("./tests/BaseWithSomeBooks/Lewis%20Carroll/Alice%27s%20Adventures%20in%20Wonderland%20%2817%29/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.epub"), $test ["datas"][2]["url"]);

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testGetJson(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        $request = Request::build(['page' => $page], self::$handler);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(16, $test["entries"]);
        $this->assertEquals("La curée", $test["entries"][1]["title"]);
    }

    public function testGetJsonIsPaginated(): void
    {
        Config::set('max_item_per_page', 5);
        $page = PageId::AUTHOR_DETAIL;
        $id = 1;

        $request = Request::build(['page' => $page, 'id' => $id], self::$handler);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);

        $this->assertEquals("Authors > Arthur Conan Doyle", $test["title"]);
        $this->assertCount(5, $test["entries"]);
        $this->assertEquals("The Lost World", $test["entries"][0]["title"]);

        $this->assertEquals(1, $test["isPaginated"]);
        $this->assertStringEndsWith("?page=3&id=1&n=2", $test["nextLink"]);

        Config::set('max_item_per_page', 48);
    }

    public function testGetJsonHasFilter(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $a = 1;

        $request = Request::build(['page' => $page, 'a' => $a], self::$handler);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(8, $test["entries"]);
        $this->assertEquals("The Hound of the Baskervilles", $test["entries"][0]["title"]);

        $this->assertCount(1, $test["filters"]);
        $this->assertEquals("Arthur Conan Doyle", $test["filters"][0]["title"]);
        $this->assertStringEndsWith("?page=3&id=1&filter=1", $test["filters"][0]["navlink"]);
    }

    public function testGetJsonSearch(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $query = "fic";

        $request = Request::build(['page' => $page, 'query' => $query, 'search' => 1], self::$handler);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);
        $expected = [
            [
                'class' => 'tt-header',
                'title' => 'Search result for *fic* in tags',
                'navlink' => Route::link(self::$handler) . '?page=9&query=fic&scope=tag',
            ],
            [
                'class' => 'Tag',
                'title' => 'Fiction',
                'navlink' => Route::link(self::$handler) . '?page=12&id=1',
            ],
            [
                'class' => 'Tag',
                'title' => 'Science Fiction',
                'navlink' => Route::link(self::$handler) . '?page=12&id=7',
            ],
        ];
        $this->assertEquals($expected, $test);
    }

    public function testGetJsonComplete(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        $request = Request::build(['page' => $page], self::$handler);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request, true);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(16, $test["entries"]);
        $this->assertEquals("La curée", $test["entries"][1]["title"]);
        $this->assertCount(4, $test["c"]);
    }

    public function testGetDownloadLinks(): void
    {
        Config::set('download_page', ['ANY']);
        $page = PageId::ALL_RECENT_BOOKS;

        $request = Request::build(['page' => $page], self::$handler);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);

        $this->assertIsArray($test["download"]);
        $this->assertCount(1, $test["download"]);
        $this->assertStringEndsWith("zipper.php?page=10&type=any", $test["download"][0]["url"]);

        Config::set('download_page', ['']);
    }

    public function testJsonHandler(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);
        $handler = Framework::getHandler('json');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $result = json_decode($output, true);

        $expected = "Recent additions";
        $this->assertEquals($expected, $result['title']);
    }
}
