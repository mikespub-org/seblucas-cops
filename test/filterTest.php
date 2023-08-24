<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class FilterTest extends TestCase
{
    private static string $endpoint = 'phpunit';

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
    }

    public function testTagHierarchy(): void
    {
        // for hierarchical tags like Fiction, Fiction.Historical, Fiction.Romance etc.
        Config::set('calibre_categories_using_hierarchy', ['tags']);

        /** @var Tag $tag */
        $tag = Tag::getInstanceById(1);
        $this->assertEquals("1", $tag->id);

        $children = $tag->getChildEntries();
        $this->assertCount(0, $children);

        //$siblings = $tag->getSiblingEntries();
        //$this->assertCount(0, $siblings);

        Config::set('calibre_categories_using_hierarchy', []);
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
         * See test/BaseWithSomeBooks/hierarchical_type2.sql
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
        $request = Request::build([]);
        $entries = Filter::getEntryArray($request);
        $this->assertCount(0, $entries);

        $request = Request::build(['a' => '1']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Author", $entries[0]->className);
        $this->assertEquals("Doyle, Arthur Conan", $entries[0]->title);
        $this->assertEquals("8 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=3&id=1", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['l' => '1']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Language", $entries[0]->className);
        $this->assertEquals("English", $entries[0]->title);
        $this->assertEquals("14 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=18&id=1", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['p' => '2']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Publisher", $entries[0]->className);
        $this->assertEquals("Macmillan and Co. London", $entries[0]->title);
        $this->assertEquals("2 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=21&id=2", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['r' => '1']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Rating", $entries[0]->className);
        $this->assertEquals("5 stars", $entries[0]->title);
        $this->assertEquals("4 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=23&id=1", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['s' => '1']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Serie", $entries[0]->className);
        $this->assertEquals("Sherlock Holmes", $entries[0]->title);
        $this->assertEquals("7 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=7&id=1", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['t' => '1']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Tag", $entries[0]->className);
        $this->assertEquals("Fiction", $entries[0]->title);
        $this->assertEquals("14 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=12&id=1", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['c' => [1 => 1]]);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Type4", $entries[0]->className);
        $this->assertEquals("SeriesLike", $entries[0]->title);
        $this->assertEquals("2 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=15&custom=1&id=1", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['f' => 'C']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Letter", $entries[0]->className);
        $this->assertEquals("C", $entries[0]->title);
        $this->assertEquals("3 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=5&id=C", $entries[0]->getNavLink(self::$endpoint));

        $request = Request::build(['y' => '2006']);
        $entries = Filter::getEntryArray($request);
        $this->assertEquals("Year", $entries[0]->className);
        $this->assertEquals("2006", $entries[0]->title);
        $this->assertEquals("9 books", $entries[0]->content);
        $this->assertEquals("phpunit?page=50&id=2006", $entries[0]->getNavLink(self::$endpoint));
    }
}
