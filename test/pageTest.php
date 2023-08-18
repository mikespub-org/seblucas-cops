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
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\PageId;

class PageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Config::set('show_not_set_filter', ['custom', 'rating', 'series', 'tag']);
        Database::clearDb();
        $_GET = [];
    }

    public function testPageIndex(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals(Config::get('title_default'), $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Alphabetical index of the 6 authors", $currentPage->entryArray [0]->content);
        if (Config::get('show_icons') == 1) {
            $this->assertEquals(Format::addVersion("images/author.png"), $currentPage->entryArray [0]->getThumbnail());
        } else {
            $this->assertNull($currentPage->entryArray [0]->getThumbnail());
        }
        $this->assertEquals(6, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Series", $currentPage->entryArray [1]->title);
        $this->assertEquals("Alphabetical index of the 4 series", $currentPage->entryArray [1]->content);
        $this->assertEquals(4, $currentPage->entryArray [1]->numberOfElement);
        $this->assertEquals("Publishers", $currentPage->entryArray [2]->title);
        $this->assertEquals("Alphabetical index of the 6 publishers", $currentPage->entryArray [2]->content);
        $this->assertEquals(6, $currentPage->entryArray [2]->numberOfElement);
        $this->assertEquals("Tags", $currentPage->entryArray [3]->title);
        $this->assertEquals("Alphabetical index of the 11 tags", $currentPage->entryArray [3]->content);
        $this->assertEquals(11, $currentPage->entryArray [3]->numberOfElement);
        $this->assertEquals("Ratings", $currentPage->entryArray [4]->title);
        $this->assertEquals("3 ratings", $currentPage->entryArray [4]->content);
        $this->assertEquals(3, $currentPage->entryArray [4]->numberOfElement);
        $this->assertEquals("Languages", $currentPage->entryArray [5]->title);
        $this->assertEquals("Alphabetical index of the 2 languages", $currentPage->entryArray [5]->content);
        $this->assertEquals(2, $currentPage->entryArray [5]->numberOfElement);
        $this->assertEquals("All books", $currentPage->entryArray [6]->title);
        $this->assertEquals("Alphabetical index of the 15 books", $currentPage->entryArray [6]->content);
        $this->assertEquals(15, $currentPage->entryArray [6]->numberOfElement);
        $this->assertEquals("Recent additions", $currentPage->entryArray [7]->title);
        $this->assertEquals("50 most recent books", $currentPage->entryArray [7]->content);
        $this->assertEquals(50, $currentPage->entryArray [7]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageIndexWithIgnored(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('ignored_categories', ["author", "series", "tag", "publisher", "language"]);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals(Config::get('title_default'), $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("Ratings", $currentPage->entryArray [0]->title);
        $this->assertEquals("All books", $currentPage->entryArray [1]->title);
        $this->assertEquals("Alphabetical index of the 15 books", $currentPage->entryArray [1]->content);
        $this->assertEquals("Recent additions", $currentPage->entryArray [2]->title);
        $this->assertEquals("50 most recent books", $currentPage->entryArray [2]->content);
        $this->assertFalse($currentPage->containsBook());

        Config::set('ignored_categories', []);
    }

    public function testPageIndexWithCustomColumn_Type1(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_custom_column', ["type1"]);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertCount(11, $currentPage->entryArray);

        Config::set('calibre_custom_column', []);
    }

    public function testPageIndexWithCustomColumn_All(): void
    {
        $page = PageId::INDEX;
        $request = new Request();

        Config::set('calibre_custom_column', ["*"]);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertCount(11, $currentPage->entryArray);

        Config::set('calibre_custom_column', []);
    }

    public function testPageAllCustom_Type4(): void
    {
        $page = PageId::ALL_CUSTOMS;
        $request = new Request();

        $request->set('custom', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Type4", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("SeriesLike", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Not Set", $currentPage->entryArray [2]->title);
        $this->assertEquals(12, $currentPage->entryArray [2]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllCustom_Type2(): void
    {
        $page = PageId::ALL_CUSTOMS;
        $request = new Request();

        $request->set('custom', 2);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Type2", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("tag1", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Not Set", $currentPage->entryArray [3]->title);
        $this->assertEquals(11, $currentPage->entryArray [3]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllCustom_Type1(): void
    {
        $page = PageId::ALL_CUSTOMS;
        $request = new Request();

        $request->set('custom', 3);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Type1", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("other", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("Not Set", $currentPage->entryArray [2]->title);
        $this->assertEquals(12, $currentPage->entryArray [2]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageCustomDetail_Type4(): void
    {
        $page = PageId::CUSTOM_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $request->set('custom', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Authors", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());

        Config::set('author_split_first_letter', "1");
    }

    public function testPageAllAuthors_SplittedByFirstLetter(): void
    {
        $page = PageId::ALL_AUTHORS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Authors", $currentPage->title);
        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("C", $currentPage->entryArray [0]->title);
        $this->assertEquals(1, $currentPage->entryArray [0]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAuthorsFirstLetter(): void
    {
        $page = PageId::AUTHORS_FIRST_LETTER;
        $request = new Request();
        $request->set('id', "C");

        // Author Lewis Carroll
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertEquals(4, $currentPage->getMaxPage());
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertTrue($currentPage->IsPaginated());
        $this->assertNull($currentPage->getPrevLink());

        Config::set('max_item_per_page', -1);
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
        $currentPage->InitializeContent();

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertEquals(2, $currentPage->getMaxPage());
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertTrue($currentPage->IsPaginated());
        $this->assertNull($currentPage->getNextLink());

        // No pagination
        Config::set('max_item_per_page', -1);
    }

    public function testPageAuthorsDetail_NoPagination(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        // No pagination
        Config::set('max_item_per_page', -1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertCount(8, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertFalse($currentPage->IsPaginated());
    }

    public function testPageAuthorsDetail_Filter(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('filter', "1");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Arthur Conan Doyle", $currentPage->title);
        $this->assertCount(12, $currentPage->entryArray);
        $this->assertEquals("Languages", $currentPage->entryArray [0]->title);
        $this->assertEquals("English", $currentPage->entryArray [1]->title);
        $this->assertEquals("8 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllBooks_WithFullName(): void
    {
        $page = PageId::ALL_BOOKS;
        $request = new Request();

        Config::set('titles_split_first_letter', 0);
        Config::set('titles_split_publication_year', 0);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(15, $currentPage->entryArray);
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
        $currentPage->InitializeContent();

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(9, $currentPage->entryArray);
        $this->assertEquals("A", $currentPage->entryArray [0]->title);
        $this->assertEquals("C", $currentPage->entryArray [1]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllBooksByLetter(): void
    {
        $page = PageId::ALL_BOOKS_LETTER;
        $request = new Request();
        $request->set('id', "C");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("All books", $currentPage->title);
        $this->assertCount(5, $currentPage->entryArray);
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
        $request->set('id', "2006");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Series", $currentPage->title);
        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("D'Artagnan Romances", $currentPage->entryArray [0]->title);
        $this->assertEquals(2, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("No series", $currentPage->entryArray [4]->title);
        $this->assertEquals(4, $currentPage->entryArray [4]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageSeriesDetail(): void
    {
        $page = PageId::SERIE_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Sherlock Holmes", $currentPage->title);
        $this->assertCount(10, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Doyle, Arthur Conan", $currentPage->entryArray [1]->title);
        $this->assertEquals("7 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllPublishers(): void
    {
        $page = PageId::ALL_PUBLISHERS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Publishers", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("D. Appleton and Company", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPagePublishersDetail(): void
    {
        $page = PageId::PUBLISHER_DETAIL;
        $request = new Request();
        $request->set('id', "6");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Strand Magazine", $currentPage->title);
        $this->assertCount(14, $currentPage->entryArray);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Doyle, Arthur Conan", $currentPage->entryArray [1]->title);
        $this->assertEquals("8 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllTags(): void
    {
        $page = PageId::ALL_TAGS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Tags", $currentPage->title);
        $this->assertCount(12, $currentPage->entryArray);
        $this->assertEquals("Action & Adventure", $currentPage->entryArray [0]->title);
        $this->assertEquals(4, $currentPage->entryArray [0]->numberOfElement);
        $this->assertEquals("No tags", $currentPage->entryArray [11]->title);
        $this->assertEquals(0, $currentPage->entryArray [11]->numberOfElement);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageTagDetail(): void
    {
        $page = PageId::TAG_DETAIL;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Fiction", $currentPage->title);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [1]->title);
        $this->assertEquals("2 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllLanguages(): void
    {
        $page = PageId::ALL_LANGUAGES;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Languages", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("English", $currentPage->title);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [1]->title);
        $this->assertEquals("2 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAllRatings(): void
    {
        $page = PageId::ALL_RATINGS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("5 stars", $currentPage->title);
        $this->assertEquals("Authors", $currentPage->entryArray [0]->title);
        $this->assertEquals("Doyle, Arthur Conan", $currentPage->entryArray [1]->title);
        $this->assertEquals("4 books", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageRecent(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(15, $currentPage->entryArray);
        $this->assertEquals("La curée", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageRecent_WithFacets_IncludedTag(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = new Request();

        $request->set('tag', "Historical");
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Recent additions", $currentPage->title);
        $this->assertCount(13, $currentPage->entryArray);
        $this->assertEquals("La curée", $currentPage->entryArray [0]->title);
        $this->assertTrue($currentPage->containsBook());
    }

    public function testPageBookDetail(): void
    {
        $page = PageId::BOOK_DETAIL;
        $request = new Request();
        $request->set('id', "2");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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

        Config::set('ignored_categories', ["author"]);
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *car*", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [0]->content);
        $this->assertFalse($currentPage->containsBook());

        Config::set('ignored_categories', []);
    }

    public function testPageSearch_WithTwoCategories(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        // Match Lewis Caroll & Scarlet
        $request = new Request();
        $request->set('query', "car");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *car*", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("Search result for *car* in books", $currentPage->entryArray [0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray [0]->content);
        $this->assertEquals("Search result for *car* in authors", $currentPage->entryArray [1]->title);
        $this->assertEquals("1 author", $currentPage->entryArray [1]->content);
        $this->assertFalse($currentPage->containsBook());
    }

    /**
     * @dataProvider providerAccentuatedCharacters
     * @param mixed $query
     * @param mixed $count
     * @param mixed $content
     * @return void
     */
    public function testPageSearch_WithAccentuatedCharacters($query, $count, $content)
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', $query);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
    public function providerAccentuatedCharacters()
    {
        return [
            ["curée", 1, "1 book"],
            ["Émile zola", 1, "1 author"],
            ["émile zola", 0, null], // With standard search upper does not work with diacritics
            ["Littérature", 1, "1 tag"],
            ["Eugène Fasquelle", 1, "1 publisher"],
        ];
    }

    /**
     * @dataProvider providerNormalizedSearch
     * @param mixed $query
     * @param mixed $count
     * @param mixed $content
     * @return void
     */
    public function testPageSearch_WithNormalizedSearch_Book($query, $count, $content)
    {
        $page = PageId::OPENSEARCH_QUERY;
        Config::set('normalized_search', "1");
        Database::clearDb();
        if (!Translation::useNormAndUp()) {
            $this->markTestIncomplete();
        }
        $request = new Request();
        $request->set('query', $query);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
    public function providerNormalizedSearch()
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
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *Lewis Carroll* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testAuthorSearch_BySort(): void
    {
        $page = PageId::OPENSEARCH_QUERY;
        $request = new Request();
        $request->set('query', "Carroll, Lewis");
        $request->set('scope', "author");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *Carroll, Lewis* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
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
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *car* in authors", $currentPage->title);
        $this->assertCount(1, $currentPage->entryArray);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray [0]->title);
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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

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
        $currentPage->InitializeContent();

        $this->assertEquals("Search result for *fic* in tags", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageAbout(): void
    {
        $page = PageId::ABOUT;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("About COPS", $currentPage->title);
        $this->assertCount(0, $currentPage->entryArray);
        $this->assertFalse($currentPage->containsBook());
    }

    public function testPageCustomize(): void
    {
        $page = PageId::CUSTOMIZE;
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("Customize COPS UI", $currentPage->title);
        $this->assertCount(7, $currentPage->entryArray);
        $this->assertEquals("Template", $currentPage->entryArray [0]->title);
        $this->assertFalse($currentPage->containsBook());
    }
}
