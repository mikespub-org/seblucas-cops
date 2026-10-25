<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Pages;

require_once dirname(__DIR__, 2) . '/config/test.php';

use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\BookShelf;
use SebLucas\Cops\Database\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\PageShelves;

class PageShelvesTest extends TestCase
{
    private static string $tempDir;

    public static function setUpBeforeClass(): void
    {
        self::$tempDir = sys_get_temp_dir() . '/cops_test_page_shelves_' . bin2hex(random_bytes(4)) . '/';
        mkdir(self::$tempDir, 0o750, true);
        Config::set('calibre_directory', dirname(__DIR__) . '/BaseWithSomeBooks/');
        Config::set('shelves_directory', self::$tempDir);
        Database::clearDb();
    }

    public static function tearDownAfterClass(): void
    {
        if (is_dir(self::$tempDir)) {
            $files = glob(self::$tempDir . '{,.}*', GLOB_BRACE) ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir(self::$tempDir);
        }
        Config::set('use_server_shelves', '');
    }

    protected function tearDown(): void
    {
        // Clean json files between tests
        $files = glob(self::$tempDir . '*.json') ?: [];
        foreach ($files as $file) {
            unlink($file);
        }
        Config::set('use_server_shelves', '');
    }

    public function testGetPageI18n(): void
    {
        $request = new Request();
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $this->assertInstanceOf(PageShelves::class, $page);

        $i18n = $page->getPageI18n();

        // all JS-only camelCase shelf* keys from tools/i18n-inventory.csv
        $expectedKeys = array_keys(PageShelves::getPageI18nMap());
        $this->assertEquals($expectedKeys, array_keys($i18n));

        // spot-check localized values (en) incl. {0} placeholders
        $this->assertEquals('Add to Shelf', $i18n['shelfAddTitle']);
        $this->assertEquals('Delete shelf "{0}"?', $i18n['shelfDeleteConfirm']);
        $this->assertEquals('Books in "{0}"', $i18n['shelfBooksInTitle']);
        $this->assertEquals('Shelves', $i18n['shelvesTitle']);

        // no untranslated dot-key leakage (value must not equal its dot key)
        $map = PageShelves::getPageI18nMap();
        foreach ($i18n as $camelKey => $value) {
            $this->assertNotEquals($map[$camelKey], $value, "untranslated value for {$camelKey}");
            $this->assertNotEmpty($value, "empty value for {$camelKey}");
        }
    }

    public function testSortedDefault(): void
    {
        $request = new Request();
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $this->assertEquals('sort', $page->sorted);
    }

    public function testSortedFromRequest(): void
    {
        // browsers decode '+' to space, so the server sees 'title desc'
        $request = new Request();
        $request->set('sort', 'title desc');
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $this->assertEquals('title desc', $page->sorted);
    }

    public function testSortedFromRequestInvalid(): void
    {
        $request = new Request();
        $request->set('sort', 'title;drop');
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        // fails the getSorted() pattern -> default
        $this->assertEquals('sort', $page->sorted);
    }

    public function testGetSortOptionsOnlyClientSortable(): void
    {
        $request = new Request();
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $options = $page->getSortOptions();
        $this->assertArrayHasKey('title', $options);
        $this->assertArrayHasKey('author', $options);
        $this->assertArrayHasKey('timestamp', $options);
        $this->assertArrayNotHasKey('pubdate', $options);
        $this->assertArrayNotHasKey('rating', $options);
    }

    public function testPageDoesNotHandleShelfActions(): void
    {
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);

        $request = new Request();
        $request->set('action', 'create');
        $request->set('name', 'SciFi');
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $this->assertFalse($page->getShelfModel()->hasShelf('SciFi'));
        $this->assertSame('Favorites', $page->getShelfModel()->getActiveShelfName());
    }

    public function testGetEntriesPopulatesFromDatabase(): void
    {
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);

        // Put books 17 and 6 on Favorites shelf
        $shelf = new BookShelf();
        $shelf->load();
        $shelf->addBook(17, 0, 'Favorites');
        $shelf->addBook(6, 0, 'Favorites');

        $request = new Request();
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $this->assertCount(2, $page->entryArray);
        $this->assertEquals(2, $page->totalNumber);
        $this->assertInstanceOf(EntryBook::class, $page->entryArray[0]);
        $this->assertInstanceOf(EntryBook::class, $page->entryArray[1]);

        // Alice's Adventures in Wonderland (17)
        $this->assertStringContainsString("Wonderland", $page->entryArray[0]->title);
        // Through the Looking Glass (6)
        $this->assertStringContainsString("Looking Glass", $page->entryArray[1]->title);
    }

    public function testSortingAndPagination(): void
    {
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);

        // Put books 17 and 6 on Favorites
        $shelf = new BookShelf();
        $shelf->load();
        $shelf->addBook(17, 0, 'Favorites');
        $shelf->addBook(6, 0, 'Favorites');

        // 1. Sort title asc: Alice (17) then Looking Glass (6)
        $reqAsc = new Request();
        $reqAsc->set('sort', 'title asc');
        /** @var PageShelves $pageAsc */
        $pageAsc = PageId::getPage(PageId::SHELVES, $reqAsc);
        $this->assertStringContainsString("Alice", $pageAsc->entryArray[0]->title);
        $this->assertStringContainsString("Looking Glass", $pageAsc->entryArray[1]->title);

        // 2. Sort title desc: Looking Glass (6) then Alice (17)
        $reqDesc = new Request();
        $reqDesc->set('sort', 'title desc');
        /** @var PageShelves $pageDesc */
        $pageDesc = PageId::getPage(PageId::SHELVES, $reqDesc);
        $this->assertStringContainsString("Looking Glass", $pageDesc->entryArray[0]->title);
        $this->assertStringContainsString("Alice", $pageDesc->entryArray[1]->title);

        // 3. Pagination with max_item_per_page = 1
        Config::set('max_item_per_page', 1);

        $reqPage1 = new Request();
        $reqPage1->set('n', 1);
        /** @var PageShelves $page1 */
        $page1 = PageId::getPage(PageId::SHELVES, $reqPage1);
        $this->assertTrue($page1->isPaginated());
        $this->assertEquals(2, $page1->totalNumber);
        $this->assertCount(1, $page1->entryArray);
        $this->assertNotNull($page1->getNextLink());

        $reqPage2 = new Request();
        $reqPage2->set('n', 2);
        /** @var PageShelves $page2 */
        $page2 = PageId::getPage(PageId::SHELVES, $reqPage2);
        $this->assertCount(1, $page2->entryArray);
        $this->assertNotNull($page2->getPrevLink());

        Config::set('max_item_per_page', -1);
    }

    public function testAuthorAndTimestampSorting(): void
    {
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);

        $shelf = new BookShelf();
        $shelf->load();
        $shelf->addBook(17, 0, 'Favorites');
        $shelf->addBook(6, 0, 'Favorites');
        $shelf->addBook(2, 0, 'Favorites');

        $authorRequest = new Request();
        $authorRequest->set('sort', 'author asc');
        /** @var PageShelves $authorPage */
        $authorPage = PageId::getPage(PageId::SHELVES, $authorRequest);
        // Lewis Carroll vs Arthur Conan Doyle (sorted by last name)
        $this->assertStringContainsString('Wonderland', $authorPage->entryArray[0]->title);
        $this->assertStringContainsString('Looking Glass', $authorPage->entryArray[1]->title);
        $this->assertStringContainsString('Sherlock', $authorPage->entryArray[2]->title);

        $timestampRequest = new Request();
        $timestampRequest->set('sort', 'timestamp asc');
        /** @var PageShelves $timestampPage */
        $timestampPage = PageId::getPage(PageId::SHELVES, $timestampRequest);
        $this->assertStringContainsString('Alice', $timestampPage->entryArray[0]->title);
        $this->assertStringContainsString('Looking Glass', $timestampPage->entryArray[1]->title);
        $this->assertStringContainsString('Sherlock', $timestampPage->entryArray[2]->title);
    }

    public function testGetEntriesFromMultipleDatabaseContexts(): void
    {
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);
        $library = dirname(__DIR__) . '/BaseWithSomeBooks/';
        Config::set('calibre_directory', [0 => $library, 1 => $library]);
        Database::clearDb();

        $shelf = new BookShelf();
        $shelf->load();
        $shelf->addBook(6, 1, 'Favorites');
        $shelf->addBook(17, 0, 'Favorites');

        $request = new Request();
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $this->assertCount(2, $page->entryArray);
        /** @var EntryBook $firstEntry */
        $firstEntry = $page->entryArray[0];
        /** @var EntryBook $secondEntry */
        $secondEntry = $page->entryArray[1];
        $this->assertSame(1, $firstEntry->book->getDatabaseId());
        $this->assertSame(0, $secondEntry->book->getDatabaseId());

        Config::set('calibre_directory', dirname(__DIR__) . '/BaseWithSomeBooks/');
        Database::clearDb();
    }
}
