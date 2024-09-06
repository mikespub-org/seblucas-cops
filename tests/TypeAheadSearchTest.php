<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Pages\PageId;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class TypeAheadSearchTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testTag(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "fic");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("2 tags", $currentPage->entryArray[0]->content);
        $this->assertEquals("Fiction", $currentPage->entryArray[1]->title);
        $this->assertEquals("Science Fiction", $currentPage->entryArray[2]->title);
    }

    public function testBookAndAuthor(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "car");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray[1]->title);

        $this->assertEquals("1 author", $currentPage->entryArray[2]->content);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray[3]->title);
    }

    public function testAuthorAndSeries(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "art");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("1 author", $currentPage->entryArray[0]->content);
        $this->assertEquals("Arthur Conan Doyle", $currentPage->entryArray[1]->title);

        $this->assertEquals("2 series", $currentPage->entryArray[2]->content);
        $this->assertEquals("D'Artagnan Romances", $currentPage->entryArray[3]->title);
    }

    public function testPublisher(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "Macmillan");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("2 publishers", $currentPage->entryArray[0]->content);
        $this->assertEquals("Macmillan and Co. London", $currentPage->entryArray[1]->title);
        $this->assertEquals("Macmillan Publishers USA", $currentPage->entryArray[2]->title);
    }

    public function testWithIgnored_SingleCategory(): void
    {
        Config::set('ignored_categories', ["author"]);
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "car");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray[1]->title);

        Config::set('ignored_categories', []);
    }

    public function testWithIgnored_MultipleCategory(): void
    {
        Config::set('ignored_categories', ["series"]);
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "art");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("1 author", $currentPage->entryArray[0]->content);
        $this->assertEquals("Arthur Conan Doyle", $currentPage->entryArray[1]->title);

        Config::set('ignored_categories', []);
    }

    public function testMultiDatabase(): void
    {
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "art");
        $request->set('search', 1);
        $request->set('multi', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("Some books", $currentPage->entryArray[0]->title);
        $this->assertEquals("1 author", $currentPage->entryArray[1]->content);
        $this->assertEquals("2 series", $currentPage->entryArray[2]->content);
        $this->assertEquals("One book", $currentPage->entryArray[3]->title);
        $this->assertEquals("1 book", $currentPage->entryArray[4]->content);

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function tearDown(): void
    {
        Database::clearDb();
    }
}
