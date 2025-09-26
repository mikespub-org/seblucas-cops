<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\JsonRenderer;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Handlers\ReadHandler;
use SebLucas\Cops\Handlers\JsonHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Routing\UriGenerator;

class JsonRendererTest extends TestCase
{
    /** @var class-string */
    private static $handler = JsonHandler::class;
    /** @var class-string */
    private static $fetcher = FetchHandler::class;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Config::set('calibre_custom_column', []);
        Config::set('calibre_custom_column_list', []);
        Config::set('calibre_custom_column_preview', []);
        Database::clearDb();
        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "");
    }

    public function testCompleteArray(): void
    {
        $request = self::$handler::request([]);
        $renderer = new JsonRenderer();
        $renderer->setRequest($request);
        $test = [];
        $test["c"] = $renderer->getCompleteArray();
        $this->assertArrayHasKey("c", $test);
        $this->assertArrayHasKey("version", $test ["c"]);
        $this->assertArrayHasKey("i18n", $test ["c"]);
        $this->assertArrayHasKey("url", $test ["c"]);
        $this->assertArrayHasKey("config", $test ["c"]);

        $this->assertEquals(self::$handler::link() . "/books/{0}?db={1}", $test ["c"]["url"]["detailUrl"]);
        $this->assertEquals(self::$fetcher::link() . "/thumbs/{1}/{0}/html.jpg", $test ["c"]["url"]["thumbnailUrl"]);
        $this->assertFalse($test ["c"]["url"]["thumbnailUrl"] == $test ["c"]["url"]["coverUrl"]);

        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "1");
        $renderer->setRequest($request);
        $test = [];
        $test["c"] = $renderer->getCompleteArray();

        $this->assertEquals(self::$fetcher::link() . "/covers/{1}/{0}.jpg", $test ["c"]["url"]["thumbnailUrl"]);
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
        $this->assertEquals(self::$fetcher::link() . "/fetch/0/20/Alice_s_Adventures_in_Wonderland_Lewis_Carroll.epub", $test ["preferedData"][0]["url"]);
        $this->assertEquals(self::$handler::link() . "/publishers/2/Macmillan_and_Co_London", $test ["publisherurl"]);

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
        $this->assertEquals(self::$fetcher::link() . "/fetch/0/1/The_Return_of_Sherlock_Holmes_Arthur_Conan_Doyle.epub", $test ["preferedData"][0]["url"]);
        $this->assertEquals(self::$handler::link() . "/publishers/6/Strand_Magazine", $test ["publisherurl"]);

        $this->assertEquals("Sherlock Holmes", $test ["seriesName"]);
        $this->assertEquals("6.0", $test ["seriesIndex"]);
        $this->assertEquals("Book 6 in the Sherlock Holmes series", $test ["seriesCompleteName"]);
        $this->assertEquals(self::$handler::link() . "/series/1/Sherlock_Holmes", $test ["seriesurl"]);
    }

    public function testGetFullBookContentArray(): void
    {
        $book = Book::getBookById(17);
        $book->setHandler(self::$handler);

        $renderer = new JsonRenderer();
        $test = $renderer->getFullBookContentArray($book);

        $this->assertEquals(self::$fetcher::link() . "/covers/0/17.jpg", $test ["coverurl"]);
        $this->assertEquals(self::$fetcher::link() . "/thumbs/0/17/html2.jpg", $test ["thumbnailurl"]);
        $this->assertCount(1, $test ["authors"]);
        $this->assertEquals(self::$handler::link() . "/authors/3/Lewis_Carroll", $test ["authors"][0]["url"]);
        $this->assertCount(3, $test ["tags"]);
        $this->assertEquals(self::$handler::link() . "/tags/5/Fantasy", $test ["tags"][0]["url"]);
        $this->assertCount(0, $test ["identifiers"]);
        $this->assertCount(3, $test ["datas"]);
        $this->assertEquals(self::$fetcher::link() . "/fetch/0/20/Alice_s_Adventures_in_Wonderland_Lewis_Carroll.epub", $test ["datas"][2]["url"]);
        $this->assertEquals(self::$fetcher::link() . "/inline/0/20/Alice_s_Adventures_in_Wonderland_Lewis_Carroll.epub", $test ["datas"][2]["viewUrl"]);
        $this->assertEquals(ReadHandler::link() . "/read/0/20/Alice_s_Adventures_in_Wonderland", $test ["datas"][2]["readerUrl"]);

        // use relative path for calibre directory
        Config::set('calibre_directory', "./tests/BaseWithSomeBooks/");
        Database::clearDb();
        $book = Book::getBookById(17);
        $book->setHandler(self::$handler);

        $renderer = new JsonRenderer();
        $test = $renderer->getFullBookContentArray($book);

        $this->assertEquals(UriGenerator::path("./tests/BaseWithSomeBooks/Lewis%20Carroll/Alice%27s%20Adventures%20in%20Wonderland%20%2817%29/cover.jpg"), $test ["coverurl"]);
        $this->assertEquals(self::$fetcher::link() . "/thumbs/0/17/html2.jpg", $test ["thumbnailurl"]);
        // see bookTest for more tests on data links
        $this->assertEquals(UriGenerator::path("./tests/BaseWithSomeBooks/Lewis%20Carroll/Alice%27s%20Adventures%20in%20Wonderland%20%2817%29/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.epub"), $test ["datas"][2]["url"]);

        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testGetJson(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        $request = self::$handler::request(['page' => $page]);
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

        $request = self::$handler::request(['page' => $page, 'id' => $id]);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);

        $this->assertEquals("Authors > Arthur Conan Doyle", $test["title"]);
        $this->assertCount(5, $test["entries"]);
        $this->assertEquals("The Lost World", $test["entries"][0]["title"]);

        $this->assertEquals(1, $test["isPaginated"]);
        $this->assertStringEndsWith("/authors/1?n=2", $test["nextLink"]);

        Config::set('max_item_per_page', 48);
    }

    public function testGetJsonHasFilter(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $a = 1;

        $request = self::$handler::request(['page' => $page, 'a' => $a]);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);

        $this->assertEquals("Recent additions", $test["title"]);
        $this->assertCount(8, $test["entries"]);
        $this->assertEquals("The Hound of the Baskervilles", $test["entries"][0]["title"]);

        $this->assertCount(1, $test["filters"]);
        $this->assertEquals("Arthur Conan Doyle", $test["filters"][0]["title"]);
        $this->assertStringEndsWith("/authors/1/Arthur_Conan_Doyle?filter=1", $test["filters"][0]["navlink"]);
    }

    public function testGetJsonSearch(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $query = "fic";

        $request = self::$handler::request(['page' => $page, 'query' => $query, 'search' => 1]);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);
        $expected = [
            [
                'class' => 'tt-header',
                'title' => 'Search result for *fic* in tags',
                'navlink' => self::$handler::link() . '/search/fic/tag',
            ],
            [
                'class' => 'Tag',
                'title' => 'Fiction',
                'navlink' => self::$handler::link() . '/tags/1/Fiction',
            ],
            [
                'class' => 'Tag',
                'title' => 'Science Fiction',
                'navlink' => self::$handler::link() . '/tags/7/Science_Fiction',
            ],
        ];
        $this->assertEquals($expected, $test);
    }

    public function testGetJsonComplete(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        $request = self::$handler::request(['page' => $page]);
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

        $request = self::$handler::request(['page' => $page]);
        $renderer = new JsonRenderer();
        $test = $renderer->getJson($request);

        $this->assertIsArray($test["download"]);
        $this->assertCount(1, $test["download"]);
        $this->assertStringEndsWith("/zipper/recent/any.zip", $test["download"][0]["url"]);

        Config::set('download_page', ['']);
    }

    public function testJsonHandler(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);
        $handler = Framework::createHandler('json');

        ob_start();
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $result = json_decode($output, true);

        $expected = "Recent additions";
        $this->assertEquals($expected, $result['title']);
    }
}
