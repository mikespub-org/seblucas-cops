<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Calibre\Filter;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;

class FilterTest extends TestCase
{
    private static string $handler = 'phpunit';

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testAuthorFilters(): void
    {
        /** @var Author $author */
        $author = Author::getInstanceById(1);
        $this->assertEquals("1", $author->id);

        $books = $author->getBooks();
        $this->assertCount(8, $books);

        //$authors = $author->getAuthors();
        //$this->assertCount(5, $authors);

        $languages = $author->getLanguages();
        $this->assertCount(1, $languages);

        $publishers = $author->getPublishers();
        $this->assertCount(1, $publishers);

        $ratings = $author->getRatings();
        $this->assertCount(1, $ratings);

        $series = $author->getSeries();
        $this->assertCount(2, $series);

        $tags = $author->getTags();
        $this->assertCount(4, $tags);

        $identifiers = $author->getIdentifiers();
        $this->assertCount(2, $identifiers);
    }

    public function testLanguageFilters(): void
    {
        /** @var Language $language */
        $language = Language::getInstanceById(1);
        $this->assertEquals("1", $language->id);

        $books = $language->getBooks();
        $this->assertCount(14, $books);

        $authors = $language->getAuthors();
        $this->assertCount(5, $authors);

        //$languages = $language->getLanguages();
        //$this->assertCount(1, $languages);

        $publishers = $language->getPublishers();
        $this->assertCount(5, $publishers);

        $ratings = $language->getRatings();
        $this->assertCount(3, $ratings);

        $series = $language->getSeries();
        $this->assertCount(3, $series);

        $tags = $language->getTags();
        $this->assertCount(10, $tags);

        $identifiers = $language->getIdentifiers();
        $this->assertCount(4, $identifiers);
    }

    public function testPublisherFilters(): void
    {
        /** @var Publisher $publisher */
        $publisher = Publisher::getInstanceById(6);
        $this->assertEquals("6", $publisher->id);

        $books = $publisher->getBooks();
        $this->assertCount(8, $books);

        $authors = $publisher->getAuthors();
        $this->assertCount(1, $authors);

        $languages = $publisher->getLanguages();
        $this->assertCount(1, $languages);

        //$publishers = $publisher->getPublishers();
        //$this->assertCount(5, $publishers);

        $ratings = $publisher->getRatings();
        $this->assertCount(1, $ratings);

        $series = $publisher->getSeries();
        $this->assertCount(2, $series);

        $tags = $publisher->getTags();
        $this->assertCount(4, $tags);

        $identifiers = $publisher->getIdentifiers();
        $this->assertCount(2, $identifiers);
    }

    public function testRatingFilters(): void
    {
        /** @var Rating $rating */
        $rating = Rating::getInstanceById(1);
        $this->assertEquals("1", $rating->id);

        $books = $rating->getBooks();
        $this->assertCount(4, $books);

        $authors = $rating->getAuthors();
        $this->assertCount(1, $authors);

        $languages = $rating->getLanguages();
        $this->assertCount(1, $languages);

        $publishers = $rating->getPublishers();
        $this->assertCount(1, $publishers);

        //$ratings = $rating->getRatings();
        //$this->assertCount(3, $ratings);

        $series = $rating->getSeries();
        $this->assertCount(1, $series);

        $tags = $rating->getTags();
        $this->assertCount(3, $tags);

        $identifiers = $rating->getIdentifiers();
        $this->assertCount(2, $identifiers);
    }

    public function testSerieFilters(): void
    {
        /** @var Serie $serie */
        $serie = Serie::getInstanceById(1);
        $this->assertEquals("1", $serie->id);

        $books = $serie->getBooks();
        $this->assertCount(7, $books);

        $authors = $serie->getAuthors();
        $this->assertCount(1, $authors);

        $languages = $serie->getLanguages();
        $this->assertCount(1, $languages);

        $publishers = $serie->getPublishers();
        $this->assertCount(1, $publishers);

        $ratings = $serie->getRatings();
        $this->assertCount(1, $ratings);

        //$series = $serie->getSeries();
        //$this->assertCount(3, $series);

        $tags = $serie->getTags();
        $this->assertCount(3, $tags);

        $identifiers = $serie->getIdentifiers();
        $this->assertCount(2, $identifiers);
    }

    public function testTagFilters(): void
    {
        /** @var Tag $tag */
        $tag = Tag::getInstanceById(1);
        $this->assertEquals("1", $tag->id);

        $books = $tag->getBooks();
        $this->assertCount(14, $books);

        $authors = $tag->getAuthors();
        $this->assertCount(5, $authors);

        $languages = $tag->getLanguages();
        $this->assertCount(1, $languages);

        $publishers = $tag->getPublishers();
        $this->assertCount(5, $publishers);

        $ratings = $tag->getRatings();
        $this->assertCount(3, $ratings);

        $series = $tag->getSeries();
        $this->assertCount(3, $series);

        // special case if we want to find other tags applied to books where this tag applies
        $tag->limitSelf = false;
        $tags = $tag->getTags();
        $this->assertCount(9, $tags);

        $identifiers = $tag->getIdentifiers();
        $this->assertCount(4, $identifiers);
    }

    public function testTagHierarchy(): void
    {
        // for hierarchical tags like Fiction, Fiction.Historical, Fiction.Romance etc.
        Config::set('calibre_categories_using_hierarchy', ['tags']);

        /** @var Tag $tag */
        $tag = Tag::getInstanceById(1);
        $this->assertEquals("1", $tag->id);

        // @todo add hierarchical tags for proper testing
        $children = $tag->getChildEntries();
        $this->assertCount(0, $children);

        $siblings = $tag->getSiblingEntries();
        $this->assertCount(0, $siblings);

        $request = Request::build();
        $booklist = new BookList($request);
        $entries = $booklist->getBooksByInstanceOrChildren($tag, 1);
        $this->assertCount(2, $entries);

        $baselist = new BaseList(Tag::class, $request);
        $entries = $baselist->browseAllEntries();
        $this->assertCount(11, $entries);

        Config::set('calibre_categories_using_hierarchy', []);
    }

    public function testTagWithout(): void
    {
        /** @var Tag $tag */
        $tag = Tag::getInstanceById(null);
        $this->assertEquals(0, $tag->id);

        $books = $tag->getBooks();
        $this->assertCount(1, $books);

        $authors = $tag->getAuthors();
        $this->assertCount(1, $authors);

        $languages = $tag->getLanguages();
        $this->assertCount(1, $languages);

        $publishers = $tag->getPublishers();
        $this->assertCount(1, $publishers);

        $ratings = $tag->getRatings();
        $this->assertCount(1, $ratings);

        $series = $tag->getSeries();
        $this->assertCount(0, $series);

        // special case if we want to find other tags applied to books where this tag applies - not relevant here
        $tag->limitSelf = false;
        $tags = $tag->getTags();
        $this->assertCount(0, $tags);

        $identifiers = $tag->getIdentifiers();
        $this->assertCount(1, $identifiers);
    }

    public function testIdentifierFilters(): void
    {
        /** @var Identifier $identifier */
        $identifier = Identifier::getInstanceById('uri');
        $this->assertEquals("uri", $identifier->id);

        $books = $identifier->getBooks();
        $this->assertCount(13, $books);

        $authors = $identifier->getAuthors();
        $this->assertCount(5, $authors);

        $languages = $identifier->getLanguages();
        $this->assertCount(1, $languages);

        $publishers = $identifier->getPublishers();
        $this->assertCount(5, $publishers);

        $ratings = $identifier->getRatings();
        $this->assertCount(2, $ratings);

        $series = $identifier->getSeries();
        $this->assertCount(3, $series);

        $tags = $identifier->getTags();
        $this->assertCount(10, $tags);

        // special case if we want to find other identifiers applied to books where this identifier applies
        $identifier->limitSelf = false;
        $identifiers = $identifier->getIdentifiers();
        $this->assertCount(3, $identifiers);
    }

    public function testCustomFilters(): void
    {
        $custom = CustomColumn::createCustom(1, 1);
        $this->assertEquals("Type4", $custom->customColumnType->getTitle());
        $this->assertEquals("SeriesLike", $custom->getTitle());

        $books = $custom->getBooks();
        $this->assertCount(2, $books);

        $authors = $custom->getAuthors();
        $this->assertCount(2, $authors);

        $languages = $custom->getLanguages();
        $this->assertCount(1, $languages);

        $publishers = $custom->getPublishers();
        $this->assertCount(2, $publishers);

        $ratings = $custom->getRatings();
        $this->assertCount(2, $ratings);

        $series = $custom->getSeries();
        $this->assertCount(0, $series);

        $tags = $custom->getTags();
        $this->assertCount(4, $tags);

        $identifiers = $custom->getIdentifiers();
        $this->assertCount(3, $identifiers);
    }

    public function testCustomHierarchy(): void
    {
        // for hierarchical custom columns like Fiction, Fiction.Historical, Fiction.Romance etc.
        Config::set('calibre_categories_using_hierarchy', ['Type2']);

        /**
         * Assuming you add Type2 entries:
         * 4: Tree
         * 5: Tree.More
         * and rename existing tags:
         * 1: Tree.Tag1
         * 2: Tree.More.Tag2
         * 3: Tree.More.Tag3
         *
         * See tests/BaseWithSomeBooks/hierarchical_type2.sql
         */
        $custom = CustomColumn::createCustom(2, 4);
        $this->assertEquals("Type2", $custom->customColumnType->getTitle());
        $this->assertEquals("Tree", $custom->getTitle());

        $children = $custom->getChildEntries();
        $this->assertCount(2, $children);
        $this->assertEquals("cops:custom:2:5", $children[0]->id);

        $children = $custom->getChildEntries(true);
        $this->assertCount(4, $children);
        $this->assertEquals("cops:custom:2:5", $children[0]->id);

        $custom = CustomColumn::createCustom(2, 3);
        $this->assertEquals("Type2", $custom->customColumnType->getTitle());
        $this->assertEquals("Tree.More.Tag3", $custom->getTitle());

        $parent = $custom->getParentEntry();
        $this->assertEquals("cops:custom:2:5", $parent->id);
        $this->assertEquals("Tree.More", $parent->title);

        Config::set('calibre_categories_using_hierarchy', []);
    }

    public function testGetEntryArray(): void
    {
        $request = Request::build([], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertCount(0, $entries);

        $request = Request::build(['a' => '1'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Author", $entries[0]->className);
        $this->assertEquals("Arthur Conan Doyle", $entries[0]->title);
        $this->assertEquals("8 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/authors/1/Arthur_Conan_Doyle", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=3&id=1", $entries[0]->getNavLink());
        }

        $request = Request::build(['l' => '1'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Language", $entries[0]->className);
        $this->assertEquals("English", $entries[0]->title);
        $this->assertEquals("14 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/languages/1/English", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=18&id=1", $entries[0]->getNavLink());
        }

        $request = Request::build(['p' => '2'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Publisher", $entries[0]->className);
        $this->assertEquals("Macmillan and Co. London", $entries[0]->title);
        $this->assertEquals("2 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/publishers/2/Macmillan_and_Co._London", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=21&id=2", $entries[0]->getNavLink());
        }

        $request = Request::build(['r' => '1'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Rating", $entries[0]->className);
        $this->assertEquals("5 stars", $entries[0]->title);
        $this->assertEquals("4 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/ratings/1/5_stars", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=23&id=1", $entries[0]->getNavLink());
        }

        $request = Request::build(['s' => '1'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Serie", $entries[0]->className);
        $this->assertEquals("Sherlock Holmes", $entries[0]->title);
        $this->assertEquals("7 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/series/1/Sherlock_Holmes", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=7&id=1", $entries[0]->getNavLink());
        }

        $request = Request::build(['t' => '1'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Tag", $entries[0]->className);
        $this->assertEquals("Fiction", $entries[0]->title);
        $this->assertEquals("14 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/tags/1/Fiction", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=12&id=1", $entries[0]->getNavLink());
        }

        $request = Request::build(['i' => 'uri'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Identifier", $entries[0]->className);
        $this->assertEquals("uri", $entries[0]->title);
        $this->assertEquals("13 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/identifiers/uri/uri", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=42&id=uri", $entries[0]->getNavLink());
        }

        $request = Request::build(['c' => [1 => 1]], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Type4", $entries[0]->className);
        $this->assertEquals("SeriesLike", $entries[0]->title);
        $this->assertEquals("2 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/custom/1/1", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=15&custom=1&id=1", $entries[0]->getNavLink());
        }

        $request = Request::build(['f' => 'C'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Letter", $entries[0]->className);
        $this->assertEquals("C", $entries[0]->title);
        $this->assertEquals("3 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/books/letter/C", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=5&id=C", $entries[0]->getNavLink());
        }

        $request = Request::build(['y' => '2006'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Year", $entries[0]->className);
        $this->assertEquals("2006", $entries[0]->title);
        $this->assertEquals("9 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/books/year/2006", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=50&id=2006", $entries[0]->getNavLink());
        }

        // @todo remove negative flag for filter entry here
        $request = Request::build(['t' => '!2'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Tag", $entries[0]->className);
        $this->assertEquals("Short Stories", $entries[0]->title);
        $this->assertEquals("4 books", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/tags/2/Short_Stories", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=12&id=2", $entries[0]->getNavLink());
        }

        // apply Not Set filters here but skip other entries
        $request = Request::build(['t' => '0'], self::$handler);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Tag", $entries[0]->className);
        $this->assertEquals("No tags", $entries[0]->title);
        $this->assertEquals("1 book", $entries[0]->content);
        if (Config::get('use_route_urls')) {
            $this->assertEquals(Route::link(self::$handler) . "/tags/0/No_tags", $entries[0]->getNavLink());
        } else {
            $this->assertEquals(Route::link(self::$handler) . "?page=12&id=0", $entries[0]->getNavLink());
        }
    }

    public function testCheckForFilters(): void
    {
        $request = Request::build();
        $filter = new Filter($request);
        $expected = [];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = "";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['a' => '1']);
        $filter = new Filter($request);
        $expected = ['1'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_authors_link where books_authors_link.book = books.id and books_authors_link.author = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['l' => '1']);
        $filter = new Filter($request);
        $expected = ['1'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_languages_link where books_languages_link.book = books.id and books_languages_link.lang_code = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['p' => '2']);
        $filter = new Filter($request);
        $expected = ['2'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_publishers_link where books_publishers_link.book = books.id and books_publishers_link.publisher = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['r' => '1']);
        $filter = new Filter($request);
        $expected = ['1'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_ratings_link where books_ratings_link.book = books.id and books_ratings_link.rating = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['s' => '1']);
        $filter = new Filter($request);
        $expected = ['1'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_series_link where books_series_link.book = books.id and books_series_link.series = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['t' => '1']);
        $filter = new Filter($request);
        $expected = ['1'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_tags_link where books_tags_link.book = books.id and books_tags_link.tag = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['i' => 'uri']);
        $filter = new Filter($request);
        $expected = ['uri'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from identifiers where identifiers.book = books.id and identifiers.type = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['c' => [1 => 1]]);
        $filter = new Filter($request);
        $expected = [1];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_custom_column_1_link where books_custom_column_1_link.book = books.id and books_custom_column_1_link.value = ?))";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['f' => 'C']);
        $filter = new Filter($request);
        $expected = ['C'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (substr(upper(books.sort), 1, 1) = ?)";
        $this->assertEquals($expected, $filter->getFilterString());

        $request = Request::build(['y' => '2006']);
        $filter = new Filter($request);
        $expected = ['2006'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (substr(date(books.pubdate), 1, 4) = ?)";
        $this->assertEquals($expected, $filter->getFilterString());
    }

    public function testCheckForNegativeFilters(): void
    {
        $request = Request::build(['t' => '!1']);
        $filter = new Filter($request);
        $expected = ['1'];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (not exists (select null from books_tags_link where books_tags_link.book = books.id and books_tags_link.tag = ?))";
        $this->assertEquals($expected, $filter->getFilterString());
    }

    public function testCheckForFiltersWithout(): void
    {
        $request = Request::build(['t' => '0']);
        $filter = new Filter($request);
        $expected = [];
        $this->assertEquals($expected, $filter->getQueryParams());
        $expected = " and (exists (select null from books_tags_link where books.id not in (select book from books_tags_link)))";
        $this->assertEquals($expected, $filter->getFilterString());
    }
}
