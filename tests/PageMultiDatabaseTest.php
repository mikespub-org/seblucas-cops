<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Pages\PageId;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class PageMultiDatabaseTest extends TestCase
{
    public function testPageIndex(): void
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::INDEX;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals(Config::get('title_default'), $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Some books", $currentPage->entryArray [0]->title);
        $this->assertEquals("16 books", $currentPage->entryArray [0]->content);
        $this->assertEquals(16, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("One book", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [1]->content);
        $this->assertEquals(1, $currentPage->entryArray [1]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    /**
     * @param int $maxItem
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerSearch')]
    public function testPageSearchXXX($maxItem)
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', "art");

        // Issue 124
        Config::set('max_item_per_page', $maxItem);
        $currentPage = PageId::getPage($page, $request);

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
    public static function providerSearch()
    {
        return [
            [2],
            [-1],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }
}
