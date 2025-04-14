<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Calibre\BookList;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Format;
use SebLucas\Cops\Calibre\Identifier;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class BookListTest extends TestCase
{
    private static Request $request;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        self::$request = new Request();
        Database::clearDb();
    }

    public function testGetBookCount(): void
    {
        $booklist = new BookList(self::$request);
        $this->assertEquals(16, $booklist->getBookCount());
    }

    public function testGetCount(): void
    {
        $booklist = new BookList(self::$request);

        $entryArray = $booklist->getCount();
        $this->assertEquals(2, count($entryArray));

        $entryAllBooks = $entryArray [0];
        $this->assertEquals("Alphabetical index of the 16 books", $entryAllBooks->content);

        $entryRecentBooks = $entryArray [1];
        $this->assertEquals("16 most recent books", $entryRecentBooks->content);
    }

    public function testGetCountRecent(): void
    {
        Config::set('recentbooks_limit', 0);
        $request = new Request();
        $booklist = new BookList($request);

        $entryArray = $booklist->getCount();
        $this->assertEquals(1, count($entryArray));

        Config::set('recentbooks_limit', 2);
        $request = new Request();
        $booklist = new BookList($request);

        $entryArray = $booklist->getCount();
        $entryRecentBooks = $entryArray [1];
        $this->assertEquals("2 most recent books", $entryRecentBooks->content);

        Config::set('recentbooks_limit', 50);
    }

    public function testGetBooksByAuthor(): void
    {
        // All books by Arthur Conan Doyle
        Config::set('max_item_per_page', 5);
        $request = new Request();
        $booklist = new BookList($request);
        /** @var Author $author */
        $author = Author::getInstanceById(1);

        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($author, 1);
        $this->assertEquals(5, count($entryArray));
        $this->assertEquals(8, $totalNumber);

        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($author, 2);
        $this->assertEquals(3, count($entryArray));
        $this->assertEquals(8, $totalNumber);

        Config::set('max_item_per_page', 48);
        $request = new Request();
        $booklist = new BookList($request);

        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($author, -1);
        $this->assertEquals(8, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksBySeries(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Serie $series */
        $series = Serie::getInstanceById(1);

        // All books from the Sherlock Holmes series
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($series, -1);
        $this->assertEquals(7, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksWithoutSeries(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Serie $series */
        $series = Serie::getInstanceById(null);

        // All books without series
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($series, -1);
        $this->assertEquals(5, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByPublisher(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Publisher $publisher */
        $publisher = Publisher::getInstanceById(6);

        // All books from Strand Magazine
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($publisher, -1);
        $this->assertEquals(8, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByTag(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Tag $tag */
        $tag = Tag::getInstanceById(1);

        // All books with the Fiction tag
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($tag, -1);
        $this->assertEquals(14, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksWithoutTag(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Tag $tag */
        $tag = Tag::getInstanceById(null);

        // All books without tag
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($tag, -1);
        $this->assertEquals(1, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByLanguage(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Language $language */
        $language = Language::getInstanceById(1);

        // All english books (= all books)
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($language, -1);
        $this->assertEquals(14, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByRating(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Rating $rating */
        $rating = Rating::getInstanceById(1);

        // All books with 4 stars
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($rating, -1);
        $this->assertEquals(4, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksWithoutRating(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Rating $rating */
        $rating = Rating::getInstanceById(null);

        // All books with no stars
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($rating, -1);
        $this->assertEquals(9, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByIdentifier(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Identifier $identifier */
        $identifier = Identifier::getInstanceById("wd");

        // All books with Wikidata identifier
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($identifier, -1);
        $this->assertEquals(2, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksWithoutIdentifier(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Identifier $identifier */
        $identifier = Identifier::getInstanceById(null);

        // All books without identifier
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($identifier, -1);
        $this->assertEquals(1, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByFormat(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Format $format */
        $format = Format::getInstanceById("EPUB");

        // All books with EPUB format
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($format, -1);
        $this->assertEquals(16, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksWithoutFormat(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Format $format */
        $format = Format::getInstanceById(null);

        // All books without format
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($format, -1);
        $this->assertEquals(0, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetCountByFirstLetter(): void
    {
        $booklist = new BookList(self::$request);

        // All books by first letter
        $entryArray = $booklist->getCountByFirstLetter();
        $this->assertCount(10, $entryArray);
    }

    public function testGetBooksByFirstLetter(): void
    {
        $booklist = new BookList(self::$request);

        // All books by first letter
        [$entryArray, $totalNumber] = $booklist->getBooksByFirstLetter("T", -1);
        $this->assertEquals(-1, $totalNumber);
        $this->assertCount(3, $entryArray);
    }

    public function testGetCountByPubYear(): void
    {
        $booklist = new BookList(self::$request);

        // All books by publication year
        $entryArray = $booklist->getCountByPubYear();
        $this->assertCount(6, $entryArray);
    }

    public function testGetBooksByPubYear(): void
    {
        $booklist = new BookList(self::$request);

        // All books by publication year
        [$entryArray, $totalNumber] = $booklist->getBooksByPubYear(2006, -1);
        $this->assertEquals(-1, $totalNumber);
        $this->assertCount(9, $entryArray);
    }

    public function testGetBooksByIdList(): void
    {
        $booklist = new BookList(self::$request);

        // All books in idlist
        [$entryArray, $totalNumber] = $booklist->getBooksByIdList([17, 19, 42]);
        $this->assertEquals(-1, $totalNumber);
        $this->assertCount(2, $entryArray);

        // All books sorted by id
        [$entryArray, $totalNumber] = $booklist->getBooksByIdList([]);
        $this->assertEquals(16, $totalNumber);
        $this->assertCount(16, $entryArray);
        $this->assertEquals('id', $booklist->orderBy);
        $this->assertEquals(2, $entryArray[0]->book->id);
    }

    public function testGetBatchQuery(): void
    {
        // All recent books
        $request = new Request();
        // Use anonymous class to override class constant
        //$booklist = new class ($request) extends BookList {
        //    public const BATCH_QUERY = true;
        //};
        $booklist = new BookList($request);

        $entryArray = $booklist->getAllRecentBooks();
        $this->assertCount(16, $entryArray);
        foreach ($entryArray as $entry) {
            $booklist->bookList[$entry->book->id] = $entry->book;
        }
        $booklist->setAuthors();
        $booklist->setSerie();
        $booklist->setPublisher();
        $booklist->setTags();
        $booklist->setLanguages();
        $booklist->setDatas();
    }

    public function testGetAllRecentBooks(): void
    {
        // All recent books
        Config::set('recentbooks_limit', 2);
        $request = new Request();
        $booklist = new BookList($request);

        $entryArray = $booklist->getAllRecentBooks();
        $this->assertCount(2, $entryArray);

        Config::set('recentbooks_limit', 50);
        $request = new Request();
        $booklist = new BookList($request);

        $entryArray = $booklist->getAllRecentBooks();
        $this->assertCount(16, $entryArray);
    }

    public function testGetBookIdList(): void
    {
        $request = new Request();
        $request->set('idlist', [19, 17]);
        $booklist = new BookList($request);

        [$entryArray, $totalNumber] = $booklist->getAllBooks();
        $this->assertCount(2, $entryArray);
        $this->assertEquals(2, $totalNumber);

        $request = new Request();
        $request->set('idlist', '19,17');
        $booklist = new BookList($request);

        [$entryArray, $totalNumber] = $booklist->getAllBooks();
        $this->assertCount(2, $entryArray);
        $this->assertEquals(2, $totalNumber);
    }

    public function tearDown(): void
    {
        Database::clearDb();
    }
}
