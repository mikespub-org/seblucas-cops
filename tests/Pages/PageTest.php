<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Pages;

use SebLucas\Cops\Pages\PageAuthorDetail;
use SebLucas\Cops\Pages\PageCustomize;
use SebLucas\Cops\Pages\PageId;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Handlers\JsonHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Session;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Language\Normalizer;
use SebLucas\Cops\Routing\UriGenerator;

class PageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Config::set('show_not_set_filter', ['custom', 'rating', 'series', 'tag', 'identifier', 'format', 'libraries']);
        Database::clearDb();
    }

    public function testPageIndex(): void
    {
        // reset icon images
        Entry::$images = [];

        $page = PageId::INDEX;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals(Config::get('title_default'), $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Alphabetical index of the 7 authors", $currentPage->entryArray [0]->content);
        if (Config::get('show_icons') == 1) {
            $this->assertEquals(UriGenerator::path("images/author.png", ["v" => Config::VERSION]), $currentPage->entryArray [0]->getThumbnail());
        } else {
            $this->assertNull($currentPage->entryArray [0]->getThumbnail());
        }
        $this->assertEquals(7, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Series", $currentPage->entryArray [1]->title);
        $this->assertEquals("Alphabetical index of the 4 series", $currentPage->entryArray [1]->content);
        $this->assertEquals(4, $currentPage->entryArray [1]->numberOfElement);
        $this->assertEquals("Publishers", $currentPage->entryArray [2]->title);
        $this->assertEquals("Alphabetical index of the 7 publishers", $currentPage->entryArray [2]->content);
        $this->assertEquals(7, $currentPage->entryArray [2]->numberOfElement);
        $this->assertEquals("Tags", $currentPage->entryArray [3]->title);
        $this->assertEquals("Alphabetical index of the 11 tags", $currentPage->entryArray [3]->content);
        $this->assertEquals(11, $currentPage->entryArray [3]->numberOfElement);
        $this->assertEquals("Ratings", $currentPage->entryArray [4]->title);
        $this->assertEquals("3 ratings", $currentPage->entryArray [4]->content);
        $this->assertEquals(3, $currentPage->entryArray [4]->numberOfElement);
        $this->assertEquals("Languages", $currentPage->entryArray [5]->title);
        $this->assertEquals("Alphabetical index of the 3 languages", $currentPage->entryArray [5]->content);
        $this->assertEquals(3, $currentPage->entryArray [5]->numberOfElement);
        $this->assertEquals("All books", $currentPage->entryArray [6]->title);
        $this->assertEquals("Alphabetical index of the 16 books", $currentPage->entryArray [6]->content);
        $this->assertEquals(16, $currentPage->entryArray [6]->numberOfElement);
        $this->assertEquals("Recent additions", $currentPage->entryArray [7]->title);
        $this->assertEquals("16 most recent books", $currentPage->entryArray [7]->content);
        $this->assertEquals(16, $currentPage->entryArray [7]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageIndexWithIgnored(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('ignored_categories', ["author", "series", "tag", "publisher", "language", "format", "identifier"]);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals(Config::get('title_default'), $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("Ratings", $currentPage->entryArray [0]->title);
        $this->assertEquals("All books", $currentPage->entryArray [1]->title);
        $this->assertEquals("Alphabetical index of the 16 books", $currentPage->entryArray [1]->content);
        $this->assertEquals("Recent additions", $currentPage->entryArray [2]->title);
        $this->assertEquals("16 most recent books", $currentPage->entryArray [2]->content);
        $this->assertFalse($currentPage->containsBook());

        Config::set('ignored_categories', ["format", "identifier"]);
    }

    public function testPageIndexWithCustomColumn_Type1(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_custom_column', ["type1"]);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Type1", $currentPage->entryArray [6]->title);
        $this->assertEquals("Custom column 'Type1'", $currentPage->entryArray [6]->content);
        $this->assertEquals(2, $currentPage->entryArray [6]->numberOfElement);

        Config::set('calibre_custom_column', []);
    }

    public function testPageIndexWithCustomColumn_Type2(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_custom_column', ["type2"]);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Type2", $currentPage->entryArray [6]->title);
        $this->assertEquals("Custom column 'Type2'", $currentPage->entryArray [6]->content);
        $this->assertEquals(3, $currentPage->entryArray [6]->numberOfElement);

        Config::set('calibre_custom_column', []);
    }

    public function testPageIndexWithCustomColumn_Type4(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_custom_column', ["type4"]);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Type4", $currentPage->entryArray [6]->title);
        $this->assertEquals("Alphabetical index of the 2 series", $currentPage->entryArray [6]->content);
        $this->assertEquals(2, $currentPage->entryArray [6]->numberOfElement);

        Config::set('calibre_custom_column', []);
    }

    public function testPageIndexWithCustomColumn_ManyTypes(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_custom_column', ["type1", "type2", "type4"]);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(11, $currentPage->entryArray);

        Config::set('calibre_custom_column', []);
    }

    public function testPageIndexWithCustomColumn_All(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_custom_column', ["*"]);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(11, $currentPage->entryArray);

        Config::set('calibre_custom_column', []);
    }

    public function testPageIndexWithVirtualLibrary(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_virtual_libraries', ["*"]);
        $request->set('vl', '2.Short_Stories_in_English');

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Series", $currentPage->entryArray [1]->title);
        $this->assertEquals(1, $currentPage->entryArray [1]->numberOfElement);
        $this->assertEquals("Publishers", $currentPage->entryArray [2]->title);
        $this->assertEquals(1, $currentPage->entryArray [2]->numberOfElement);
        $this->assertEquals("Tags", $currentPage->entryArray [3]->title);
        $this->assertEquals(1, $currentPage->entryArray [3]->numberOfElement);
        $this->assertEquals("Ratings", $currentPage->entryArray [4]->title);
        $this->assertEquals(1, $currentPage->entryArray [4]->numberOfElement);
        $this->assertEquals("Languages", $currentPage->entryArray [5]->title);
        $this->assertEquals(1, $currentPage->entryArray [5]->numberOfElement);
        $this->assertEquals("Virtual libraries", $currentPage->entryArray [6]->title);
        $this->assertEquals(2, $currentPage->entryArray [6]->numberOfElement);
        $this->assertEquals("All books", $currentPage->entryArray [7]->title);
        $this->assertEquals(4, $currentPage->entryArray [7]->numberOfElement);
        $this->assertEquals("Recent additions", $currentPage->entryArray [8]->title);
        $this->assertEquals(4, $currentPage->entryArray [8]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());

        $this->assertEquals(['vl' => '2.Short_Stories_in_English'], $currentPage->filterParams);
        $this->assertStringEndsWith("index.php/authors?vl=2.Short_Stories_in_English", $currentPage->entryArray [0]->getNavLink());
        $this->assertStringEndsWith("index.php/series?vl=2.Short_Stories_in_English", $currentPage->entryArray [1]->getNavLink());

        $request->set('a', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);

        $this->assertEquals(['a' => 1, 'vl' => '2.Short_Stories_in_English'], $currentPage->filterParams);
        $this->assertStringEndsWith("index.php/authors?vl=2.Short_Stories_in_English&a=1", $currentPage->entryArray [0]->getNavLink());
        $this->assertStringEndsWith("index.php/series?vl=2.Short_Stories_in_English&a=1", $currentPage->entryArray [1]->getNavLink());

        Config::set('calibre_virtual_libraries', []);
    }

    public function testPageAllCustom_Type4(): void
    {
        $page = PageId::ALL_CUSTOMS;
        $request = new Request();

        $request->set('custom', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Type4", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("SeriesLike", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Not Set", $currentPage->entryArray [2]->title);
        $this->assertEquals(13, $currentPage->entryArray [2]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllCustom_Type2(): void
    {
        $page = PageId::ALL_CUSTOMS;
        $request = new Request();

        $request->set('custom', 2);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Type2", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("tag1", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Not Set", $currentPage->entryArray [3]->title);
        $this->assertEquals(12, $currentPage->entryArray [3]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllCustom_Type1(): void
    {
        $page = PageId::ALL_CUSTOMS;
        $request = new Request();

        $request->set('custom', 3);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Type1", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("other", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Not Set", $currentPage->entryArray [2]->title);
        $this->assertEquals(13, $currentPage->entryArray [2]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageCustomDetail_Type4(): void
    {
        $page = PageId::CUSTOM_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $request->set('custom', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("SeriesLike", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageCustomDetail_Type2(): void
    {
        $page = PageId::CUSTOM_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $request->set('custom', 2);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("tag1", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Alice's Adventures in Wonderland", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageCustomDetail_Type1(): void
    {
        $page = PageId::CUSTOM_DETAIL;
        $request = new Request();

        $request->set('custom', 3);
        $request->set('id', "2");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("other", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageAllAuthors_WithFullName(): void
    {
        $page = PageId::ALL_AUTHORS;
        $request = new Request();

        Config::set('author_split_first_letter', "0");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Authors", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());

        Config::set('author_split_first_letter', "1");
    }

    public function testPageAllAuthors_SplitByFirstLetter(): void
    {
        $page = PageId::ALL_AUTHORS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Authors", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("C", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAuthorsFirstLetter(): void
    {
        $page = PageId::AUTHORS_FIRST_LETTER;
        $request = new Request();
        $request->set('letter', "C");

        // Author Lewis Carroll
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("1 author starting with C", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAuthorsDetail_FirstPage(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        Config::set('max_item_per_page', 2);

        // First page

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertEquals(4, $currentPage->getMaxPage());
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertTrue($currentPage->IsPaginated());
        $this->assertNull($currentPage->getPrevLink());

        Config::set('max_item_per_page', 48);
    }

    public function testPageAuthorsDetail_LastPage(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        // Last page
        Config::set('max_item_per_page', 5);
        $request->set('n', "2");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertEquals(2, $currentPage->getMaxPage());
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertTrue($currentPage->IsPaginated());
        $this->assertNull($currentPage->getNextLink());

        Config::set('max_item_per_page', 48);
    }

    public function testPageAuthorsDetail_NoPagination(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        // No pagination
        Config::set('max_item_per_page', -1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertFalse($currentPage->IsPaginated());

        Config::set('max_item_per_page', 48);
    }

    public function testPageAuthorsDetail_Filter(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("Languages", $currentPage->entryArray [0]->title);
        $this->assertEquals("English", $currentPage->entryArray [1]->title);
        $this->assertEquals("8 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());

        $this->assertEquals(['a' => 1], $currentPage->filterParams);
        $this->assertStringEndsWith("index.php/languages?a=1", $currentPage->entryArray [0]->getNavLink());
        $this->assertStringEndsWith("index.php/languages/1/English?a=1", $currentPage->entryArray [1]->getNavLink());
    }

    public function testPageAuthorsDetail_Instance(): void
    {
        $author = Author::getInstanceById(1);
        $author->setHandler(JsonHandler::class);
        $params = ['n' => 2];

        // navlink contains the paginated uri
        $entry = $author->getEntry(8, $params);
        $this->assertEquals($author->getUri($params), $entry->getNavLink());

        $currentPage = $author->getPage(8, $params);

        $this->assertEquals(PageAuthorDetail::class, $currentPage::class);
        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertCount(0, $currentPage->entryArray);
        // currentUri contains the unfiltered uri
        $this->assertEquals($author->getUri(), $currentPage->currentUri);
        $this->assertEquals("cops:authors:1", $currentPage->idPage);
        $this->assertEquals(8, $currentPage->totalNumber);
        $this->assertEquals(2, $currentPage->n);
    }

    public function testPageAllBooks_WithFullName(): void
    {
        $page = PageId::ALL_BOOKS;
        $request = new Request();

        Config::set('titles_split_first_letter', 0);
        Config::set('titles_split_publication_year', 0);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(16, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertEquals("Alice's Adventures in Wonderland", $currentPage->entryArray [1]->title);
        $this->assertTrue($currentPage->containsBook());

        Config::set('titles_split_first_letter', 1);
        Config::set('titles_split_publication_year', 0);
    }

    public function testPageAllBooks_SplitByFirstLetter(): void
    {
        $page = PageId::ALL_BOOKS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(10, $currentPage->entryArray);
        $this->assertEquals("A", $currentPage->entryArray [0]->title);
        $this->assertEquals("C", $currentPage->entryArray [1]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllBooksByLetter(): void
    {
        $page = PageId::ALL_BOOKS_LETTER;
        $request = new Request();
        $request->set('letter', "C");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("3 books starting with C", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("The Call of the Wild", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageAllBooks_SplitByPubYear(): void
    {
        $page = PageId::ALL_BOOKS;
        $request = new Request();

        Config::set('titles_split_first_letter', 0);
        Config::set('titles_split_publication_year', 1);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("1872", $currentPage->entryArray [0]->title);
        $this->assertEquals("1897", $currentPage->entryArray [1]->title);
        $this->assertFalse($currentPage->containsBook());

        Config::set('titles_split_first_letter', 1);
        Config::set('titles_split_publication_year', 0);
    }

    public function testPageAllBooksByYear(): void
    {
        $page = PageId::ALL_BOOKS_YEAR;
        $request = new Request();
        $request->set('year', "2006");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("9 books published in 2006", $currentPage->title);
        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("The Casebook of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageAllSeries(): void
    {
        $page = PageId::ALL_SERIES;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Series", $currentPage->title);
        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("D'Artagnan Romances", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("No series", $currentPage->entryArray [4]->title);
        $this->assertEquals(5, $currentPage->entryArray [4]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllSeries_SplitByFirstLetter(): void
    {
        $page = PageId::ALL_SERIES;
        $request = new Request();

        Config::set('series_split_first_letter', "1");

        $currentPage = PageId::getPage($page, $request);

        Config::set('series_split_first_letter', "0");

        $this->assertEquals("Series", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("D", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSeriesFirstLetter(): void
    {
        $page = PageId::SERIES_FIRST_LETTER;
        $request = new Request();
        $request->set('letter', "S");

        // Series Sherlock Holmes & Série des Rougon-Macquart
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("2 series starting with S", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSeriesDetail(): void
    {
        $page = PageId::SERIE_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Sherlock Holmes", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageSeriesDetail_Filter(): void
    {
        $page = PageId::SERIE_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Sherlock Holmes", $currentPage->title);
        $this->assertCount(12, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Arthur Conan Doyle", $currentPage->entryArray [1]->title);
        $this->assertEquals("7 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllPublishers(): void
    {
        $page = PageId::ALL_PUBLISHERS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Publishers", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("D. Appleton and Company", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllPublishers_SplitByFirstLetter(): void
    {
        $page = PageId::ALL_PUBLISHERS;
        $request = new Request();

        Config::set('publisher_split_first_letter', "1");

        $currentPage = PageId::getPage($page, $request);

        Config::set('publisher_split_first_letter', "0");

        $this->assertEquals("Publishers", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("D", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPagePublishersFirstLetter(): void
    {
        $page = PageId::PUBLISHERS_FIRST_LETTER;
        $request = new Request();
        $request->set('letter', "M");

        // Publisher Macmillan and Co. London & Macmillan Publishers USA
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("2 publishers starting with M", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPagePublishersDetail(): void
    {
        $page = PageId::PUBLISHER_DETAIL;
        $request = new Request();
        $request->set('id', "6");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Strand Magazine", $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPagePublishersDetail_Filter(): void
    {
        $page = PageId::PUBLISHER_DETAIL;
        $request = new Request();
        $request->set('id', "6");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Strand Magazine", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Arthur Conan Doyle", $currentPage->entryArray [1]->title);
        $this->assertEquals("8 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllTags(): void
    {
        $page = PageId::ALL_TAGS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Tags", $currentPage->title);
        $this->assertCount(12, $currentPage->entryArray);
        $this->assertEquals("Action & Adventure", $currentPage->entryArray [0]->title);
        $this->assertEquals(4, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("No tags", $currentPage->entryArray [11]->title);
        $this->assertEquals(1, $currentPage->entryArray [11]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllTags_SplitByFirstLetter(): void
    {
        $page = PageId::ALL_TAGS;
        $request = new Request();

        Config::set('tag_split_first_letter', "1");

        $currentPage = PageId::getPage($page, $request);

        Config::set('tag_split_first_letter', "0");

        $this->assertEquals("Tags", $currentPage->title);
        $this->assertCount(10, $currentPage->entryArray);
        $this->assertEquals("A", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageTagsFirstLetter(): void
    {
        $page = PageId::TAGS_FIRST_LETTER;
        $request = new Request();
        $request->set('letter', "F");

        // Tag Fantasy & Fiction
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("2 tags starting with F", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageTagDetail(): void
    {
        $page = PageId::TAG_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Fiction", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageTagDetail_Filter(): void
    {
        $page = PageId::TAG_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Fiction", $currentPage->title);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray [1]->title);
        $this->assertEquals("2 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllLanguages(): void
    {
        $page = PageId::ALL_LANGUAGES;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Languages", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("English", $currentPage->entryArray [0]->title);
        $this->assertEquals("French", $currentPage->entryArray [1]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageLanguageDetail(): void
    {
        $page = PageId::LANGUAGE_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("English", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageLanguageDetail_Filter(): void
    {
        $page = PageId::LANGUAGE_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("English", $currentPage->title);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray [1]->title);
        $this->assertEquals("2 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllRatings(): void
    {
        $page = PageId::ALL_RATINGS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Ratings", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("2 stars", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("No star", $currentPage->entryArray [3]->title);
        $this->assertEquals(9, $currentPage->entryArray [3]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageRatingDetail(): void
    {
        $page = PageId::RATING_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("5 stars", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageRatingDetail_Filter(): void
    {
        $page = PageId::RATING_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("5 stars", $currentPage->title);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Arthur Conan Doyle", $currentPage->entryArray [1]->title);
        $this->assertEquals("4 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllIdentifiers(): void
    {
        $page = PageId::ALL_IDENTIFIERS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Identifiers", $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertEquals("Amazon", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("uri", $currentPage->entryArray [4]->title);
        $this->assertEquals(13, $currentPage->entryArray [4]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageIdentifierDetail(): void
    {
        $page = PageId::IDENTIFIER_DETAIL;
        $request = new Request();
        $request->set('id', "isbn");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("ISBN", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("La curée", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageIdentifierDetail_Filter(): void
    {
        Config::set('html_filter_links', ['author', 'language', 'publisher', 'rating', 'series', 'tag', 'identifier']);
        $page = PageId::IDENTIFIER_DETAIL;
        $request = new Request();
        $request->set('id', "isbn");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("ISBN", $currentPage->title);
        $this->assertCount(15, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Émile Zola", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
        Config::set('html_filter_links', ['author', 'language', 'publisher', 'rating', 'series', 'tag']);
    }

    public function testPageAllFormats(): void
    {
        $page = PageId::ALL_FORMATS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Formats", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("EPUB", $currentPage->entryArray [0]->title);
        $this->assertEquals(16, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("PDF", $currentPage->entryArray [2]->title);
        $this->assertEquals(1, $currentPage->entryArray [2]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageFormatDetail(): void
    {
        $page = PageId::FORMAT_DETAIL;
        $request = new Request();
        $request->set('id', "EPUB");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("EPUB", $currentPage->title);
        $this->assertCount(16, $currentPage->entryArray);
        $this->assertEquals("The Adventures of Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageFormatDetail_Filter(): void
    {
        Config::set('html_filter_links', ['author', 'language', 'publisher', 'rating', 'series', 'tag', 'format']);
        $page = PageId::FORMAT_DETAIL;
        $request = new Request();
        $request->set('id', "EPUB");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("EPUB", $currentPage->title);
        $this->assertCount(42, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray [1]->title);
        $this->assertEquals("2 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
        Config::set('html_filter_links', ['author', 'language', 'publisher', 'rating', 'series', 'tag']);
    }

    public function testPageAllLibraries(): void
    {
        $page = PageId::ALL_LIBRARIES;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Virtual libraries", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("Action Fiction from this Century (TODO)", $currentPage->entryArray [0]->title);
        $this->assertEquals(0, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("No virtual libraries", $currentPage->entryArray [2]->title);
        $this->assertEquals(16, $currentPage->entryArray [2]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageRecent(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(16, $currentPage->entryArray);
        $this->assertEquals("孙子兵法", $currentPage->entryArray [0]->title);
        $this->assertEquals("La curée", $currentPage->entryArray [1]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageRecent_WithFacets_IncludedTag(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = new Request();

        $request->set('tag', "Historical");
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Twenty Years After", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageRecent_WithFacets_ExcludedTag(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = new Request();

        $request->set('tag', "!Romance");
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("孙子兵法", $currentPage->entryArray [0]->title);
        $this->assertEquals("La curée", $currentPage->entryArray [1]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageBookDetail(): void
    {
        $page = PageId::BOOK_DETAIL;
        $request = new Request();
        $request->set('id', "2");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("The Return of Sherlock Holmes", $currentPage->title);
        $this->assertCount(0, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSearch_WithOnlyBooksReturned(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', "alice");

        // Only books returned
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *alice*", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Search result for *alice* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("2 books", $currentPage->entryArray [0]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSearch_WithAuthorsIgnored(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        // Match Lewis Caroll & Scarlet
        $request = new Request();
        $request->set('query', "car");

        Config::set('ignored_categories', ["author", "format", "identifier"]);
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *car*", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [0]->content);
        $this->assertFalse($currentPage->containsBook());

        Config::set('ignored_categories', ["format", "identifier"]);
    }

    public function testPageSearch_WithTwoCategories(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        // Match Lewis Caroll & Scarlet
        $request = new Request();
        $request->set('query', "car");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *car*", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [0]->content);
        $this->assertEquals("Search result for *car* in authors", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 author", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    /**
     * @param string $query
     * @param int $count
     * @param string $content
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerAccentuatedCharacters')]
    public function testPageSearch_WithAccentuatedCharacters($query, $count, $content)
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', $query);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *$query*", $currentPage->title);
        $this->assertCount($count, $currentPage->entryArray);
        if ($count > 0) {
            $this->assertEquals($content, $currentPage->entryArray [0]->content);
        }
        $this->assertFalse($currentPage->containsBook());
    }

    /**
     * Summary of providerAccentuatedCharacters
     * @return array<mixed>
     */
    public static function providerAccentuatedCharacters()
    {
        return [
            ["curée", 1, "1 book"],
            ["Émile zola", 1, "1 author"],
            ["émile zola", 1, " "], // With standard search upper does not work with diacritics
            ["Littérature", 1, "1 tag"],
            ["Eugène Fasquelle", 1, "1 publisher"],
        ];
    }

    /**
     * @param string $query
     * @param int $count
     * @param string $content
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerNormalizedSearch')]
    public function testPageSearch_WithNormalizedSearch_Book($query, $count, $content)
    {
        $page = PageId::OPENSEARCH_QUERY;
        Config::set('normalized_search', "1");
        Database::clearDb();
        if (!Normalizer::useNormAndUp()) {
            $this->markTestIncomplete();
        }
        $request = new Request();
        $request->set('query', $query);

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *$query*", $currentPage->title);
        $this->assertCount($count, $currentPage->entryArray);
        if ($count > 0) {
            $this->assertEquals($content, $currentPage->entryArray [0]->content);
        }
        $this->assertFalse($currentPage->containsBook());

        Config::set('normalized_search', "0");
        Database::clearDb();
    }

    /**
     * Summary of providerNormalizedSearch
     * @return array<mixed>
     */
    public static function providerNormalizedSearch()
    {
        return [
            ["curee", 1, "1 book"],
            ["emile zola", 1, "1 author"],
            ["émile zola", 1, "1 author"],
            ["Litterature", 1, "1 tag"],
            ["Litterâture", 1, "1 tag"],
            ["Serie des Rougon", 1, "1 series"],
            ["Eugene Fasquelle", 1, "1 publisher"],
        ];
    }

    public function testAuthorSearch_ByName(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', "Lewis Carroll");
        $request->set('scope', "author");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *Lewis Carroll* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testAuthorSearch_BySort(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', "Carroll, Lewis");
        $request->set('scope', "author");

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *Carroll, Lewis* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSearchScopeAuthors(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('scope', "author");

        // Match Lewis Carroll
        $request->set('query', "car");
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *car* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Lewis Carroll", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSearchScopeSeries(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('scope', "series");

        // Match Holmes
        $request->set('query', "hol");
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *hol* in series", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Sherlock Holmes", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSearchScopeBooks(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('scope', "book");

        // Match Holmes
        $request->set('query', "hol");
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *hol* in books", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageSearchScopePublishers(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('scope', "publisher");

        // Match Holmes
        $request->set('query', "millan");
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *millan* in publishers", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Macmillan and Co. London", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSearchScopeTags(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('scope', "tag");

        // Match Holmes
        $request->set('query', "fic");
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Search result for *fic* in tags", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAbout(): void
    {
        $page = PageId::ABOUT;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("About COPS", $currentPage->title);
        $this->assertCount(0, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageCustomize(): void
    {
        $page = PageId::CUSTOMIZE;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Customize COPS UI", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("Template", $currentPage->entryArray [0]->title);
        $this->assertEquals("Virtual library", $currentPage->entryArray [6]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testPageCustomizeSessionGet(): void
    {
        $session = new Session();
        $session->start();
        $custom = [
            'template' => "default",
        ];
        $session->set('custom', $custom);
        $page = PageId::CUSTOMIZE;
        $server = ['REQUEST_METHOD' => "GET"];
        $request = Request::build(['page' => $page], null, $server);
        $request->setSession($session);

        /** @var PageCustomize $currentPage */
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Customize COPS UI", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("Template", $currentPage->entryArray [0]->title);
        $this->assertEquals("Virtual library", $currentPage->entryArray [6]->title);
        $this->assertFalse($currentPage->containsBook());

        $expected = $custom;
        $this->assertEquals($expected, $currentPage->custom);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testPageCustomizeSessionPost(): void
    {
        $session = new Session();
        $session->start();
        $custom = [
            'template' => "default",
        ];
        $session->set('custom', $custom);
        $page = PageId::CUSTOMIZE;
        $server = ['REQUEST_METHOD' => "POST"];
        $post = [
            'template' => "twigged",
            'style' => "default",
            'max_item_per_page' => "48",
            'email' => "test@example.com",
            'ignored_categories' => ["format", "identifier"],
            'virtual_library' => "2.Short_Stories_in_English",
        ];
        $request = Request::build(['page' => $page], null, $server, $post);
        $request->setSession($session);

        /** @var PageCustomize $currentPage */
        $currentPage = PageId::getPage($page, $request);

        $this->assertEquals("Customize COPS UI", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("Template", $currentPage->entryArray [0]->title);
        $this->assertEquals("Virtual library", $currentPage->entryArray [6]->title);
        $this->assertFalse($currentPage->containsBook());

        $expected = $post;
        $this->assertEquals($expected, $currentPage->custom);
    }
}
