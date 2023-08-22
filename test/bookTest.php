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
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Cover;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Language;
use SebLucas\Cops\Calibre\Publisher;
use SebLucas\Cops\Calibre\Rating;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\LinkEntry;
use SebLucas\Cops\Pages\PageId;

/*
Publishers:
id:2 (2 books)   Macmillan and Co. London:   Lewis Caroll
id:3 (2 books)   D. Appleton and Company     Alexander Dumas
id:4 (1 book)    Macmillan Publishers USA:   Jack London
id:5 (1 book)    Pierson's Magazine:         H. G. Wells
id:6 (8 books)   Strand Magazine:            Arthur Conan Doyle
*/

class BookTest extends TestCase
{
    private const TEST_THUMBNAIL = __DIR__ . "/thumbnail.jpg";
    private const COVER_WIDTH = 400;
    private const COVER_HEIGHT = 600;

    private static string $endpoint = 'phpunit';
    private static Request $request;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        self::$request = new Request();
        Database::clearDb();

        $book = Book::getBookById(2);
        if (!is_dir($book->path)) {
            mkdir($book->path, 0777, true);
        }
        $im = imagecreatetruecolor(self::COVER_WIDTH, self::COVER_HEIGHT);
        $text_color = imagecolorallocate($im, 255, 0, 0);
        imagestring($im, 1, 5, 5, 'Book cover', $text_color);
        imagejpeg($im, $book->path . "/cover.jpg", 80);
    }

    public static function tearDownAfterClass(): void
    {
        $book = Book::getBookById(2);
        if (!file_exists($book->path . "/cover.jpg")) {
            return;
        }
        unlink($book->path . "/cover.jpg");
        rmdir($book->path);
        rmdir(dirname($book->path));
    }

    public function testGetBookCount(): void
    {
        $booklist = new BookList(self::$request);
        $this->assertEquals(15, $booklist->getBookCount());
    }

    public function testGetCount(): void
    {
        $booklist = new BookList(self::$request);

        $entryArray = $booklist->getCount();
        $this->assertEquals(2, count($entryArray));

        $entryAllBooks = $entryArray [0];
        $this->assertEquals("Alphabetical index of the 15 books", $entryAllBooks->content);

        $entryRecentBooks = $entryArray [1];
        $this->assertEquals("50 most recent books", $entryRecentBooks->content);
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
        // All book by Arthur Conan Doyle
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

        Config::set('max_item_per_page', -1);
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

        // All book from the Sherlock Holmes series
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($series, -1);
        $this->assertEquals(7, count($entryArray));
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

        // All book with the Fiction tag
        [$entryArray, $totalNumber] = $booklist->getBooksByInstance($tag, -1);
        $this->assertEquals(14, count($entryArray));
        $this->assertEquals(-1, $totalNumber);
    }

    public function testGetBooksByLanguage(): void
    {
        $booklist = new BookList(self::$request);
        /** @var Language $language */
        $language = Language::getInstanceById(1);

        // All english book (= all books)
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

    public function testGetCountByFirstLetter(): void
    {
        $booklist = new BookList(self::$request);

        // All books by first letter
        $entryArray = $booklist->getCountByFirstLetter();
        $this->assertCount(9, $entryArray);
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
        $this->assertCount(5, $entryArray);
    }

    public function testGetBooksByPubYear(): void
    {
        $booklist = new BookList(self::$request);

        // All books by publication year
        [$entryArray, $totalNumber] = $booklist->getBooksByPubYear(2006, -1);
        $this->assertEquals(-1, $totalNumber);
        $this->assertCount(9, $entryArray);
    }

    public function testGetBookByDataId(): void
    {
        $book = Book::getBookByDataId(17);

        $this->assertEquals("Alice's Adventures in Wonderland", $book->getTitle());
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
        $this->assertCount(15, $entryArray);
    }

    /**
     * @dataProvider providerPublicationDate
     * @param mixed $pubdate
     * @param mixed $expectedYear
     * @return void
     */
    public function testGetPubDate($pubdate, $expectedYear)
    {
        $book = Book::getBookById(2);
        $book->pubdate = $pubdate;
        $this->assertEquals($expectedYear, $book->getPubDate());
    }

    /**
     * Summary of providerPublicationDate
     * @return array<mixed>
     */
    public function providerPublicationDate()
    {
        return [
            ['2010-10-05 22:00:00+00:00', '2010'],
            ['1982-11-15 13:05:29.908657+00:00', '1982'],
            ['1562-10-05 00:00:00+00:00', '1562'],
            ['0100-12-31 23:00:00+00:00', ''],
            ['', ''],
            [null, ''],
            ];
    }

    public function testGetBookById(): void
    {
        // also check most of book's class methods
        $book = Book::getBookById(2);

        $linkArray = $book->getLinkArray();
        $this->assertCount(5, $linkArray);

        $this->assertEquals("The Return of Sherlock Holmes", $book->getTitle());
        $this->assertEquals("urn:uuid:87ddbdeb-1e27-4d06-b79b-4b2a3bfc6a5f", $book->getEntryId());
        $this->assertEquals(Config::ENDPOINT["index"] . "?page=13&id=2", $book->getDetailUrl());
        $this->assertEquals("Arthur Conan Doyle", $book->getAuthorsName());
        $this->assertEquals("Fiction, Mystery & Detective, Short Stories", $book->getTagsName());
        $this->assertEquals('<p class="description">The Return of Sherlock Holmes is a collection of 13 Sherlock Holmes stories, originally published in 1903-1904, by Arthur Conan Doyle.<br />The book was first published on March 7, 1905 by Georges Newnes, Ltd and in a Colonial edition by Longmans. 30,000 copies were made of the initial print run. The US edition by McClure, Phillips &amp; Co. added another 28,000 to the run.<br />This was the first Holmes collection since 1893, when Holmes had "died" in "The Adventure of the Final Problem". Having published The Hound of the Baskervilles in 1901–1902 (although setting it before Holmes\' death) Doyle came under intense pressure to revive his famous character.</p>', $book->getComment(false));
        $this->assertEquals("English", $book->getLanguages());
        $this->assertEquals("Strand Magazine", $book->getPublisher()->name);
    }

    public function testGetBookById_NotFound(): void
    {
        $book = Book::getBookById(666);

        $this->assertNull($book);
    }

    public function testGetRating_FiveStars(): void
    {
        $book = Book::getBookById(2);

        $this->assertEquals("&#9733;&#9733;&#9733;&#9733;&#9733;", $book->getRating());
    }

    public function testGetRating_FourStars(): void
    {
        $book = Book::getBookById(2);
        $book->rating = 8;

        // 4 filled stars and one empty
        $this->assertEquals("&#9733;&#9733;&#9733;&#9733;&#9734;", $book->getRating());
    }

    public function testGetRating_NoStars_Zero(): void
    {
        $book = Book::getBookById(2);
        $book->rating = 0;

        $this->assertEquals("", $book->getRating());
    }

    public function testGetRating_NoStars_Null(): void
    {
        $book = Book::getBookById(2);
        $book->rating = null;

        $this->assertEquals("", $book->getRating());
    }

    public function testGetIdentifiers_Uri(): void
    {
        $book = Book::getBookById(2);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(2, $identifiers);
        $this->assertEquals("uri", $identifiers[0]->type);
        $this->assertEquals("http|//www.feedbooks.com/book/63", $identifiers[0]->val);
        $this->assertEquals("", $identifiers[0]->getUri());
    }

    public function testGetIdentifiers_Isbn(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(1, $identifiers);
        $this->assertEquals("isbn", $identifiers[0]->type);
        $this->assertEquals("9782253003663", $identifiers[0]->val);
        $this->assertEquals("https://www.worldcat.org/isbn/9782253003663", $identifiers[0]->getUri());
    }

    public function testBookGetLinkArrayWithUrlRewriting(): void
    {
        Config::set('use_url_rewriting', "1");
        $book = Book::getBookById(2);

        $linkArray = $book->getLinkArray();
        foreach ($linkArray as $link) {
            if ($link->rel == LinkEntry::OPDS_ACQUISITION_TYPE && $link->title == "EPUB") {
                $this->assertEquals("download/1/The%20Return%20of%20Sherlock%20Holmes%20-%20Arthur%20Conan%20Doyle.epub", $link->href);
                return;
            }
        }
        $this->fail();
    }

    public function testBookGetLinkArrayWithoutUrlRewriting(): void
    {
        Config::set('use_url_rewriting', "0");
        $book = Book::getBookById(2);

        $linkArray = $book->getLinkArray();
        foreach ($linkArray as $link) {
            if ($link->rel == LinkEntry::OPDS_ACQUISITION_TYPE && $link->title == "EPUB") {
                $this->assertEquals(Config::ENDPOINT["fetch"] . "?id=2&type=epub&data=1", $link->href);
                return;
            }
        }
        $this->fail();
    }

    public function testGetThumbnailNotNeeded(): void
    {
        $book = Book::getBookById(2);
        $cover = new Cover($book);

        $this->assertFalse($cover->getThumbnail(null, null, null));

        // Current cover is 400*600
        $this->assertFalse($cover->getThumbnail(self::COVER_WIDTH, null, null));
        $this->assertFalse($cover->getThumbnail(self::COVER_WIDTH + 1, null, null));
        $this->assertFalse($cover->getThumbnail(null, self::COVER_HEIGHT, null));
        $this->assertFalse($cover->getThumbnail(null, self::COVER_HEIGHT + 1, null));
    }

    /**
     * @dataProvider providerThumbnail
     * @param mixed $width
     * @param mixed $height
     * @param mixed $expectedWidth
     * @param mixed $expectedHeight
     * @return void
     */
    public function testGetThumbnailByWidth($width, $height, $expectedWidth, $expectedHeight)
    {
        $book = Book::getBookById(2);
        $cover = new Cover($book);

        $this->assertTrue($cover->getThumbnail($width, $height, self::TEST_THUMBNAIL));

        $size = GetImageSize(self::TEST_THUMBNAIL);
        $this->assertEquals($expectedWidth, $size [0]);
        $this->assertEquals($expectedHeight, $size [1]);

        unlink(self::TEST_THUMBNAIL);
    }

    /**
     * Summary of providerThumbnail
     * @return array<mixed>
     */
    public function providerThumbnail()
    {
        return [
            [164, null, 164, 246],
            [null, 164, 109, 164],
        ];
    }

    public function testGetThumbnailUri(): void
    {
        $book = Book::getBookById(2);

        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "1");
        $entry = $book->getEntry();
        $thumbnailurl = $entry->getThumbnail(self::$endpoint);
        $this->assertEquals("fetch.php?id=2", $thumbnailurl);

        // The thumbnails should be the same as the handling
        Config::set('thumbnail_handling', "/images.png");
        $entry = $book->getEntry();
        $thumbnailurl = $entry->getThumbnail(self::$endpoint);
        $this->assertEquals("/images.png", $thumbnailurl);

        Config::set('thumbnail_handling', "");
        $entry = $book->getEntry();
        $thumbnailurl = $entry->getThumbnail(self::$endpoint);
        $this->assertEquals("fetch.php?id=2&height=225", $thumbnailurl);
    }

    /**
     * @dataProvider providerThumbnailCachePath
     * @param mixed $width
     * @param mixed $height
     * @param mixed $type
     * @param mixed $expectedCachePath
     * @return void
     */
    public function testGetThumbnailCachePath($width, $height, $type, $expectedCachePath): void
    {
        $book = Book::getBookById(2);
        $cover = new Cover($book);

        Config::set('thumbnail_cache_directory', __DIR__ . '/cache/');
        $cachePath = $cover->getThumbnailCachePath($width, $height, $type);
        $this->assertEquals($expectedCachePath, $cachePath);

        rmdir(dirname($cachePath));
        rmdir(dirname(dirname($cachePath)));

        Config::set('thumbnail_cache_directory', '');
    }

    /**
     * Summary of providerThumbnail
     * @return array<mixed>
     */
    public function providerThumbnailCachePath()
    {
        return [
            [164, null, 'jpg', __DIR__ . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-164x.jpg'],
            [null, 164, 'jpg', __DIR__ . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-x164.jpg'],
            [164, null, 'png', __DIR__ . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-164x.png'],
            [null, 164, 'png', __DIR__ . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-x164.png'],
        ];
    }

    public function testSendThumbnailOriginal(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $request = Request::build([]);
        $expected = filesize($cover->coverFileName);

        // no thumbnail resizing
        ob_start();
        $cover->sendThumbnail($request, false);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }

    public function testSendThumbnailResize(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $request = Request::build(['height' => Config::get('html_thumbnail_height')]);
        $expected = 15349;

        // no thumbnail cache
        ob_start();
        $cover->sendThumbnail($request, false);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }

    public function testSendThumbnailCacheMiss(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $width = null;
        $height = Config::get('html_thumbnail_height');
        $type = 'jpg';
        $request = Request::build(['height' => $height]);
        $expected = 15349;

        // use thumbnail cache
        Config::set('thumbnail_cache_directory', __DIR__ . '/cache/');
        $cachePath = $cover->getThumbnailCachePath($width, $height, $type);
        if (file_exists($cachePath)) {
            unlink($cachePath);
            rmdir(dirname($cachePath));
            rmdir(dirname(dirname($cachePath)));
        }

        // 1. cache miss
        ob_start();
        $cover->sendThumbnail($request, false);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('thumbnail_cache_directory', '');
    }

    public function testSendThumbnailCacheHit(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $width = null;
        $height = Config::get('html_thumbnail_height');
        $type = 'jpg';
        $request = Request::build(['height' => $height]);
        $expected = 15349;

        // use thumbnail cache
        Config::set('thumbnail_cache_directory', __DIR__ . '/cache/');

        // 2. cache hit
        ob_start();
        $cover->sendThumbnail($request, false);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        $cachePath = $cover->getThumbnailCachePath($width, $height, $type);
        if (file_exists($cachePath)) {
            unlink($cachePath);
            rmdir(dirname($cachePath));
            rmdir(dirname(dirname($cachePath)));
        }

        Config::set('thumbnail_cache_directory', '');
    }

    public function testCheckDatabaseFieldCover(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);

        // full path
        $fileName = $cover->checkDatabaseFieldCover($book->path . '/cover.jpg');
        $expected = $book->path . '/cover.jpg';
        $this->assertEquals($expected, $fileName);

        // relative path to image_directory
        Config::set('image_directory', $book->path . '/');
        $fileName = $cover->checkDatabaseFieldCover('cover.jpg');
        $expected = $book->path . '/cover.jpg';
        $this->assertEquals($expected, $fileName);

        // relative path to image_directory . epub->name
        // this won't work for Calibre directories due to missing (book->id) in path here
        Config::set('image_directory', dirname($book->path) . '/');
        $fileName = $cover->checkDatabaseFieldCover('cover.jpg');
        $expected = null;
        $this->assertEquals($expected, $fileName);

        // unknown path
        Config::set('image_directory', '');
        $fileName = $cover->checkDatabaseFieldCover('thumbnail.png');
        $expected = null;
        $this->assertEquals($expected, $fileName);

        // based on book path works for Calibre directories
        $fileName = $cover->checkCoverFilePath();
        $expected = $book->path . '/cover.jpg';
        $this->assertEquals($expected, $fileName);
    }

    public function testGetMostInterestingDataToSendToKindle_WithEPUB(): void
    {
        // Get Alice (available as MOBI, PDF, EPUB in that order)
        $book = Book::getBookById(17);
        $data = $book->GetMostInterestingDataToSendToKindle();
        $this->assertEquals("EPUB", $data->format);
    }

    public function testGetMostInterestingDataToSendToKindle_WithMOBI(): void
    {
        // Get Alice (available as MOBI, PDF, EPUB in that order)
        $book = Book::getBookById(17);
        $book->GetMostInterestingDataToSendToKindle();
        array_pop($book->datas);
        $data = $book->GetMostInterestingDataToSendToKindle();
        $this->assertEquals("MOBI", $data->format);
    }

    public function testGetMostInterestingDataToSendToKindle_WithPDF(): void
    {
        // Get Alice (available as MOBI, PDF, EPUB in that order)
        $book = Book::getBookById(17);
        $book->GetMostInterestingDataToSendToKindle();
        array_pop($book->datas);
        array_shift($book->datas);
        $data = $book->GetMostInterestingDataToSendToKindle();
        $this->assertEquals("PDF", $data->format);
    }

    public function testGetAllCustomColumnValues(): void
    {
        $book = Book::getBookById(17);
        $data = $book->getCustomColumnValues(["*"], true);

        $this->assertCount(3, $data);
    }

    public function testGetDataById(): void
    {
        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $book = Book::getBookById(17);
        $mobi = $book->getDataById(17);
        $this->assertEquals("MOBI", $mobi->format);
        $epub = $book->getDataById(20);
        $this->assertEquals("EPUB", $epub->format);
        $this->assertEquals("Carroll, Lewis - Alice's Adventures in Wonderland.epub", $epub->getUpdatedFilenameEpub());
        $this->assertEquals("Carroll, Lewis - Alice's Adventures in Wonderland.kepub.epub", $epub->getUpdatedFilenameKepub());
        $this->assertEquals(__DIR__ . "/BaseWithSomeBooks/Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.epub", $epub->getLocalPath());

        Config::set('use_url_rewriting', "1");
        Config::set('provide_kepub', "1");
        $_SERVER["HTTP_USER_AGENT"] = "Kobo";
        $book = Book::getBookById(17);
        $book->updateForKepub = true;
        $epub = $book->getDataById(20);
        $this->assertEquals("download/20/Carroll%2C%20Lewis%20-%20Alice%27s%20Adventures%20in%20Wonderland.kepub.epub", $epub->getHtmlLink());
        $this->assertEquals("download/17/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.mobi", $mobi->getHtmlLink());

        Config::set('provide_kepub', "0");
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $book = Book::getBookById(17);
        $book->updateForKepub = false;
        $epub = $book->getDataById(20);
        $this->assertEquals("download/20/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.epub", $epub->getHtmlLink());

        Config::set('use_url_rewriting', "0");
        $this->assertEquals(Config::ENDPOINT["fetch"] . "?id=17&type=epub&data=20", $epub->getHtmlLink());
    }

    public function testGetUpdatedEpub(): void
    {
        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $book = Book::getBookById(17);

        ob_start();
        $book->getUpdatedEpub(20, false);
        $headers = headers_list();
        $output = ob_get_clean();
        //$this->assertStringStartsWith("Exception : Cannot modify header information", $output);
        $this->assertEquals(0, count($headers));
        $this->assertEquals(1794249, strlen($output));
    }

    public function testGetFilePath_Cover(): void
    {
        $book = Book::getBookById(17);

        $this->assertEquals(Database::getDbDirectory(null) . "Lewis Carroll/Alice's Adventures in Wonderland (17)/cover.jpg", $book->getFilePath("jpg", null));
    }

    public function testGetFilePath_Epub(): void
    {
        $book = Book::getBookById(17);

        $this->assertEquals(Database::getDbDirectory(null) . "Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.epub", $book->getFilePath("epub", 20));
    }

    public function testGetFilePath_Mobi(): void
    {
        $book = Book::getBookById(17);

        $this->assertEquals(Database::getDbDirectory(null) . "Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.mobi", $book->getFilePath("mobi", 17));
    }

    public function testGetDataFormat_EPUB(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("EPUB");
        $this->assertEquals(20, $data->id);
    }

    public function testGetDataFormat_MOBI(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("MOBI");
        $this->assertEquals(17, $data->id);
    }

    public function testGetDataFormat_PDF(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("PDF");
        $this->assertEquals(19, $data->id);
    }

    public function testGetDataFormat_NonAvailable(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $this->assertFalse($book->getDataFormat("FB2"));
    }

    public function testGetMimeType_EPUB(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("EPUB");
        $this->assertEquals("application/epub+zip", $data->getMimeType());
    }

    public function testGetMimeType_MOBI(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("MOBI");
        $this->assertEquals("application/x-mobipocket-ebook", $data->getMimeType());
    }

    public function testGetMimeType_PDF(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("PDF");
        $this->assertEquals("application/pdf", $data->getMimeType());
    }

    public function testGetMimeType_Finfo(): void
    {
        $book = Book::getBookById(17);

        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $data = $book->getDataFormat("PDF");
        $this->assertEquals("application/pdf", $data->getMimeType());

        // Alter a data to make a test for finfo_file if enabled
        $data->extension = "ico";
        $data->format = "ICO";
        $data->name = "favicon";
        $data->book->path = realpath(__DIR__ . "/../");
        if (function_exists('finfo_open') === true) {
            //$this->assertEquals("image/x-icon", $data->getMimeType());
            $this->assertEquals("image/vnd.microsoft.icon", $data->getMimeType());
        } else {
            $this->assertEquals("application/octet-stream", $data->getMimeType());
        }
    }

    public function testTypeaheadSearch_Tag(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "fic");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("2 tags", $currentPage->entryArray[0]->content);
        $this->assertEquals("Fiction", $currentPage->entryArray[1]->title);
        $this->assertEquals("Science Fiction", $currentPage->entryArray[2]->title);
    }

    public function testTypeaheadSearch_BookAndAuthor(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "car");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray[1]->title);

        $this->assertEquals("1 author", $currentPage->entryArray[2]->content);
        $this->assertEquals("Carroll, Lewis", $currentPage->entryArray[3]->title);
    }

    public function testTypeaheadSearch_AuthorAndSeries(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "art");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("1 author", $currentPage->entryArray[0]->content);
        $this->assertEquals("Doyle, Arthur Conan", $currentPage->entryArray[1]->title);

        $this->assertEquals("2 series", $currentPage->entryArray[2]->content);
        $this->assertEquals("D'Artagnan Romances", $currentPage->entryArray[3]->title);
    }

    public function testTypeaheadSearch_Publisher(): void
    {
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "Macmillan");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("2 publishers", $currentPage->entryArray[0]->content);
        $this->assertEquals("Macmillan and Co. London", $currentPage->entryArray[1]->title);
        $this->assertEquals("Macmillan Publishers USA", $currentPage->entryArray[2]->title);
    }

    public function testTypeaheadSearchWithIgnored_SingleCategory(): void
    {
        Config::set('ignored_categories', ["author"]);
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "car");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("A Study in Scarlet", $currentPage->entryArray[1]->title);

        Config::set('ignored_categories', []);
    }

    public function testTypeaheadSearchWithIgnored_MultipleCategory(): void
    {
        Config::set('ignored_categories', ["series"]);
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "art");
        $request->set('search', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("1 author", $currentPage->entryArray[0]->content);
        $this->assertEquals("Doyle, Arthur Conan", $currentPage->entryArray[1]->title);

        Config::set('ignored_categories', []);
    }

    public function testTypeaheadSearchMultiDatabase(): void
    {
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        $request = new Request();
        $page = PageId::OPENSEARCH_QUERY;
        $request->set('query', "art");
        $request->set('search', 1);
        $request->set('multi', 1);

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

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
