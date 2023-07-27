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
use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\Page;

class JsonTest extends TestCase
{
    private static $request;

    public static function setUpBeforeClass(): void
    {
        global $config;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        $config['cops_calibre_custom_column'] = [];
        $config['cops_calibre_custom_column_list'] = [];
        $config['cops_calibre_custom_column_preview'] = [];
        Base::clearDb();
        self::$request = new Request();
    }

    public function testCompleteArray()
    {
        global $config;

        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $test = [];
        $request = new Request();
        $test = JSONRenderer::addCompleteArray($test, $request);
        $this->assertArrayHasKey("c", $test);
        $this->assertArrayHasKey("version", $test ["c"]);
        $this->assertArrayHasKey("i18n", $test ["c"]);
        $this->assertArrayHasKey("url", $test ["c"]);
        $this->assertArrayHasKey("config", $test ["c"]);

        $this->assertFalse($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        $config['cops_thumbnail_handling'] = "1";
        $test = [];
        $test = JSONRenderer::addCompleteArray($test, $request);

        $this->assertTrue($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        $config['cops_thumbnail_handling'] = "/images.png";
        $test = [];
        $test = JSONRenderer::addCompleteArray($test, $request);

        $this->assertEquals("/images.png", $test ["c"]["url"]["thumbnailUrl"]);
    }

    public function testGetBookContentArrayWithoutSeries()
    {
        $book = Book::getBookById(17);
        $test = JSONRenderer::getBookContentArray($book);

        $this->assertEquals("", $test ["seriesName"]);
        $this->assertEquals("1.0", $test ["seriesIndex"]);
        $this->assertEquals("", $test ["seriesCompleteName"]);
        $this->assertEquals("", $test ["seriesurl"]);
    }

    public function testGetBookContentArrayWithSeries()
    {
        $book = Book::getBookById(2);

        $test = JSONRenderer::getBookContentArray($book);

        $this->assertEquals("Sherlock Holmes", $test ["seriesName"]);
        $this->assertEquals("6.0", $test ["seriesIndex"]);
        $this->assertEquals("Book 6 in the Sherlock Holmes series", $test ["seriesCompleteName"]);
        $this->assertStringEndsWith("?page=7&id=1", $test ["seriesurl"]);
    }

    public function testGetFullBookContentArray()
    {
        $book = Book::getBookById(17);

        $test = JSONRenderer::getFullBookContentArray($book);

        $this->assertCount(1, $test ["authors"]);
        $this->assertCount(3, $test ["tags"]);
        $this->assertCount(3, $test ["datas"]);
    }

    public function testGetJson()
    {
        $page = Page::ALL_RECENT_BOOKS;

        self::$request->set('page', $page);
        $test = JSONRenderer::getJson(self::$request);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(15, $test["entries"]);
        $this->assertEquals("La curée", $test["entries"][0]["title"]);

        self::$request->set('page', null);
    }

    public function testGetJsonSearch()
    {
        $page = Page::OPENSEARCH_QUERY;
        $query = "fic";

        self::$request->set('page', $page);
        self::$request->set('query', $query);
        self::$request->set('search', 1);
        $test = JSONRenderer::getJson(self::$request);
        $check = [
            [
                'class' => 'tt-header',
                'title' => 'Search result for *fic* in tags',
                'navlink' => 'phpunit?page=9&query=fic&db=&scope=tag',
            ],
            [
                'class' => 'SebLucas\Cops\Calibre\Tag',
                'title' => 'Fiction',
                'navlink' => 'phpunit?page=12&id=1',
            ],
            [
                'class' => 'SebLucas\Cops\Calibre\Tag',
                'title' => 'Science Fiction',
                'navlink' => 'phpunit?page=12&id=7',
            ],
        ];
        $this->assertEquals($check, $test);

        self::$request->set('page', null);
        self::$request->set('query', null);
        self::$request->set('search', null);
    }

    public function testGetJsonComplete()
    {
        $page = Page::ALL_RECENT_BOOKS;

        self::$request->set('page', $page);
        $test = JSONRenderer::getJson(self::$request, true);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(15, $test["entries"]);
        $this->assertEquals("La curée", $test["entries"][0]["title"]);
        $this->assertCount(4, $test["c"]);

        self::$request->set('page', null);
    }
}
