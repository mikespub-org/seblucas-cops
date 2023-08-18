<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\PageId;

class PageMultiDatabaseTest extends TestCase
{
    public function testPageIndex(): void
    {
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
                                              "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::INDEX;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals(Config::get('title_default'), $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Some books", $currentPage->entryArray [0]->title);
        $this->assertEquals("15 books", $currentPage->entryArray [0]->content);
        $this->assertEquals(15, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("One book", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [1]->content);
        $this->assertEquals(1, $currentPage->entryArray [1]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    /**
     * @dataProvider providerSearch
     * @param int $maxItem
     * @return void
     */
    public function testPageSearchXXX($maxItem)
    {
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
                                              "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', "art");

        // Issue 124
        Config::set('max_item_per_page', $maxItem);
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *art*", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Some books", $currentPage->entryArray [0]->title);
        $this->assertEquals("11 books", $currentPage->entryArray [0]->content);
        $this->assertEquals("One book", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());

        Config::set('max_item_per_page', -1);
    }

    /**
     * Summary of providerSearch
     * @return array<mixed>
     */
    public function providerSearch()
    {
        return [
            [2],
            [-1],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        Database::clearDb();
    }
}
