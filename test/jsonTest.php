<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

use SebLucas\Cops\Output\JSONRenderer;

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\Page;

class JsonTest extends TestCase
{
    private static $endpoint = 'phpunit';

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__FILE__) . "/BaseWithSomeBooks/");
        Config::set('calibre_custom_column', []);
        Config::set('calibre_custom_column_list', []);
        Config::set('calibre_custom_column_preview', []);
        Database::clearDb();
    }

    public function testCompleteArray()
    {
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $test = [];
        $request = new Request();
        $test = JSONRenderer::addCompleteArray($test, $request, self::$endpoint);
        $this->assertArrayHasKey("c", $test);
        $this->assertArrayHasKey("version", $test ["c"]);
        $this->assertArrayHasKey("i18n", $test ["c"]);
        $this->assertArrayHasKey("url", $test ["c"]);
        $this->assertArrayHasKey("config", $test ["c"]);

        $this->assertEquals("phpunit?page=13&id={0}&db={1}", $test ["c"]["url"]["detailUrl"]);
        $this->assertEquals("fetch.php?height=225&id={0}&db={1}", $test ["c"]["url"]["thumbnailUrl"]);
        $this->assertFalse($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "1");
        $test = [];
        $test = JSONRenderer::addCompleteArray($test, $request, self::$endpoint);

        $this->assertEquals("fetch.php?id={0}&db={1}", $test ["c"]["url"]["thumbnailUrl"]);
        $this->assertTrue($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "/images.png");
        $test = [];
        $test = JSONRenderer::addCompleteArray($test, $request, self::$endpoint);

        $this->assertEquals("/images.png", $test ["c"]["url"]["thumbnailUrl"]);

        Config::set('thumbnail_handling', "");
    }

    public function testGetBookContentArrayWithoutSeries()
    {
        $book = Book::getBookById(17);
        $test = JSONRenderer::getBookContentArray($book, self::$endpoint);

        $this->assertCount(2, $test ["preferedData"]);
        $this->assertEquals("fetch.php?data=20&type=epub&id=17", $test ["preferedData"][0]["url"]);
        $this->assertEquals("phpunit?page=21&id=2", $test ["publisherurl"]);

        $this->assertEquals("", $test ["seriesName"]);
        $this->assertEquals("1.0", $test ["seriesIndex"]);
        $this->assertEquals("", $test ["seriesCompleteName"]);
        $this->assertEquals("", $test ["seriesurl"]);
    }

    public function testGetBookContentArrayWithSeries()
    {
        $book = Book::getBookById(2);

        $test = JSONRenderer::getBookContentArray($book, self::$endpoint);

        $this->assertCount(1, $test ["preferedData"]);
        $this->assertEquals("fetch.php?data=1&type=epub&id=2", $test ["preferedData"][0]["url"]);
        $this->assertEquals("phpunit?page=21&id=6", $test ["publisherurl"]);

        $this->assertEquals("Sherlock Holmes", $test ["seriesName"]);
        $this->assertEquals("6.0", $test ["seriesIndex"]);
        $this->assertEquals("Book 6 in the Sherlock Holmes series", $test ["seriesCompleteName"]);
        $this->assertEquals("phpunit?page=7&id=1", $test ["seriesurl"]);
    }

    public function testGetFullBookContentArray()
    {
        $book = Book::getBookById(17);

        $test = JSONRenderer::getFullBookContentArray($book, self::$endpoint);

        $this->assertEquals("fetch.php?id=17", $test ["coverurl"]);
        $this->assertEquals("fetch.php?height=450&id=17", $test ["thumbnailurl"]);
        $this->assertCount(1, $test ["authors"]);
        $this->assertEquals("phpunit?page=3&id=3", $test ["authors"][0]["url"]);
        $this->assertCount(3, $test ["tags"]);
        $this->assertEquals("phpunit?page=12&id=5", $test ["tags"][0]["url"]);
        $this->assertCount(0, $test ["identifiers"]);
        $this->assertCount(3, $test ["datas"]);
        $this->assertEquals("fetch.php?data=20&type=epub&id=17", $test ["datas"][2]["url"]);
        $this->assertEquals("fetch.php?data=20&view=1&type=epub&id=17", $test ["datas"][2]["viewUrl"]);
        $this->assertEquals("epubreader.php?data=20&db=", $test ["datas"][2]["readerUrl"]);

        // use relative path for calibre directory
        Config::set('calibre_directory', "./test/BaseWithSomeBooks/");
        Database::clearDb();
        $book = Book::getBookById(17);

        $test = JSONRenderer::getFullBookContentArray($book, self::$endpoint);

        $this->assertEquals("./test/BaseWithSomeBooks/Lewis%20Carroll/Alice%27s%20Adventures%20in%20Wonderland%20%2817%29/cover.jpg", $test ["coverurl"]);
        $this->assertEquals("fetch.php?height=450&id=17", $test ["thumbnailurl"]);
        // see bookTest for more tests on data links
        $this->assertEquals("./test/BaseWithSomeBooks/Lewis%20Carroll/Alice%27s%20Adventures%20in%20Wonderland%20%2817%29/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.epub", $test ["datas"][2]["url"]);

        Config::set('calibre_directory', dirname(__FILE__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testGetJson()
    {
        $page = Page::ALL_RECENT_BOOKS;

        $request = new Request();
        $request->set('page', $page);
        $test = JSONRenderer::getJson($request);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(15, $test["entries"]);
        $this->assertEquals("La curée", $test["entries"][0]["title"]);
    }

    public function testGetJsonSearch()
    {
        $page = Page::OPENSEARCH_QUERY;
        $query = "fic";

        $request = new Request();
        $request->set('page', $page);
        $request->set('query', $query);
        $request->set('search', 1);
        $test = JSONRenderer::getJson($request);
        $check = [
            [
                'class' => 'tt-header',
                'title' => 'Search result for *fic* in tags',
                'navlink' => 'phpunit?page=9&query=fic&db=&scope=tag',
            ],
            [
                'class' => 'Tag',
                'title' => 'Fiction',
                'navlink' => 'phpunit?page=12&id=1',
            ],
            [
                'class' => 'Tag',
                'title' => 'Science Fiction',
                'navlink' => 'phpunit?page=12&id=7',
            ],
        ];
        $this->assertEquals($check, $test);
    }

    public function testGetJsonComplete()
    {
        $page = Page::ALL_RECENT_BOOKS;

        $request = new Request();
        $request->set('page', $page);
        $test = JSONRenderer::getJson($request, true);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(15, $test["entries"]);
        $this->assertEquals("La curée", $test["entries"][0]["title"]);
        $this->assertCount(4, $test["c"]);
    }
}
