<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;

class FilterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $config;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Database::clearDb();
    }

    public function testAuthorFilters()
    {
        $author = Author::getAuthorById(1);
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

    public function testLanguageFilters()
    {
        $language = Language::getLanguageById(1);
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

    public function testPublisherFilters()
    {
        $publisher = Publisher::getPublisherById(6);
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

    public function testRatingFilters()
    {
        $rating = Rating::getRatingById(1);
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

    public function testSerieFilters()
    {
        $serie = Serie::getSerieById(1);
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

    public function testTagFilters()
    {
        $tag = Tag::getTagById(1);
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

        //$tags = $tag->getTags();
        //$this->assertCount(1, $tags);
    }
}
