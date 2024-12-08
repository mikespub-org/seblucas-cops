<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Cover;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Handlers\JsonHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\LinkEntry;
use SebLucas\Cops\Output\FileResponse;

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

    /** @var array<string, int> */
    protected static $expectedSize = [
        'cover' => 200128,
        'thumb' => 15349,
        'original' => 1598906,
        'updated' => 1047437,
    ];
    /** @var class-string */
    private static $handler = JsonHandler::class;
    /** @var class-string */
    private static $fetcher = FetchHandler::class;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();

        $book = Book::getBookById(2);
        if (!is_dir($book->path)) {
            mkdir($book->path, 0o777, true);
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

    public function testGetBookByDataId(): void
    {
        $book = Book::getBookByDataId(17);

        $this->assertEquals("Alice's Adventures in Wonderland", $book->getTitle());
    }

    /**
     * @param mixed $pubdate
     * @param mixed $expectedYear
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerPublicationDate')]
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
    public static function providerPublicationDate()
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
        $book->setHandler(self::$handler);

        $linkArray = $book->getLinkArray();
        $this->assertCount(5, $linkArray);

        $this->assertEquals("The Return of Sherlock Holmes", $book->getTitle());
        $this->assertEquals("urn:uuid:87ddbdeb-1e27-4d06-b79b-4b2a3bfc6a5f", $book->getEntryId());
        $this->assertEquals(self::$handler::link() . "/books/2/Arthur_Conan_Doyle/The_Return_of_Sherlock_Holmes", $book->getDetailUrl(self::$handler));
        $this->assertEquals("Arthur Conan Doyle", $book->getAuthorsName());
        $this->assertEquals("Fiction, Mystery & Detective, Short Stories", $book->getTagsName());
        $this->assertEquals('<p class="description">The Return of Sherlock Holmes is a collection of 13 Sherlock Holmes stories, originally published in 1903-1904, by Arthur Conan Doyle.<br />The book was first published on March 7, 1905 by Georges Newnes, Ltd and in a Colonial edition by Longmans. 30,000 copies were made of the initial print run. The US edition by McClure, Phillips &amp; Co. added another 28,000 to the run.<br />This was the first Holmes collection since 1893, when Holmes had "died" in "The Adventure of the Final Problem". Having published The Hound of the Baskervilles in 1901–1902 (although setting it before Holmes\' death) Doyle came under intense pressure to revive his famous character.</p>', $book->getComment(false));
        $this->assertEquals("English", $book->getLanguages());
        $this->assertEquals("Strand Magazine", $book->getPublisher()->name);
        $author = $book->getAuthors()[0];
        $this->assertEquals("http://www.wikidata.org/entity/Q35610", $author->link);
        $publisher = $book->getPublisher();
        if (Database::getUserVersion() > 25) {
            $this->assertNotNull($publisher->link);
        } else {
            $this->assertNull($publisher->link);
        }
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
        $this->assertEquals("", $identifiers[0]->getLink());
    }

    public function testGetIdentifiers_Google(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("google", $identifiers[0]->type);
        $this->assertEquals("yr9EAAAAYAAJ", $identifiers[0]->val);
        $this->assertEquals("https://books.google.com/books?id=yr9EAAAAYAAJ", $identifiers[0]->getLink());
    }

    public function testGetIdentifiers_Isbn(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("isbn", $identifiers[1]->type);
        $this->assertEquals("9782253003663", $identifiers[1]->val);
        $this->assertEquals("https://www.worldcat.org/isbn/9782253003663", $identifiers[1]->getLink());
    }

    public function testGetIdentifiers_OpenLibrary(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("olid", $identifiers[2]->type);
        $this->assertEquals("OL118974W", $identifiers[2]->val);
        $this->assertEquals("https://openlibrary.org/works/OL118974W", $identifiers[2]->getLink());
    }

    public function testGetIdentifiers_WikiData(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("wd", $identifiers[3]->type);
        $this->assertEquals("Q962265", $identifiers[3]->val);
        $this->assertEquals("https://www.wikidata.org/entity/Q962265", $identifiers[3]->getLink());
    }

    public function testBookGetLinkArrayWithUrlRewriting(): void
    {
        Config::set('use_url_rewriting', "1");
        $book = Book::getBookById(2);
        $book->setHandler(self::$handler);

        $linkArray = $book->getLinkArray();
        foreach ($linkArray as $link) {
            if ($link->rel == LinkEntry::OPDS_ACQUISITION_TYPE && $link->title == "EPUB") {
                $this->assertEquals(Route::path("download/1/0/The%20Return%20of%20Sherlock%20Holmes%20-%20Arthur%20Conan%20Doyle.epub"), $link->href);
                return;
            }
        }
        $this->fail();
    }

    public function testBookGetLinkArrayWithoutUrlRewriting(): void
    {
        Config::set('use_url_rewriting', "0");
        $book = Book::getBookById(2);
        $book->setHandler(self::$handler);

        $linkArray = $book->getLinkArray();
        foreach ($linkArray as $link) {
            if ($link->rel == LinkEntry::OPDS_ACQUISITION_TYPE && $link->title == "EPUB") {
                $this->assertEquals(self::$fetcher::link() . "/fetch/0/1/ignore.epub", $link->href);
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
     * @param mixed $width
     * @param mixed $height
     * @param mixed $expectedWidth
     * @param mixed $expectedHeight
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerThumbnail')]
    public function testGetThumbnailBySize($width, $height, $expectedWidth, $expectedHeight)
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
    public static function providerThumbnail()
    {
        return [
            [164, null, 164, 246],
            [null, 164, 109, 164],
        ];
    }

    public function testGetThumbnailUri(): void
    {
        $book = Book::getBookById(2);
        $book->setHandler(self::$handler);

        // The thumbnails should be the same as the covers
        Config::set('thumbnail_handling', "1");
        $entry = $book->getEntry();
        $thumbnailurl = $entry->getThumbnail();
        $this->assertEquals(self::$fetcher::link() . "/covers/0/2.jpg", $thumbnailurl);

        // The thumbnails should be the same as the handling
        Config::set('thumbnail_handling', "/images.png");
        $entry = $book->getEntry();
        $thumbnailurl = $entry->getThumbnail();
        $this->assertEquals("/images.png", $thumbnailurl);

        Config::set('thumbnail_handling', "");
        $entry = $book->getEntry();
        $thumbnailurl = $entry->getThumbnail();
        $this->assertEquals(self::$fetcher::link() . "/thumbs/0/2/html.jpg", $thumbnailurl);
    }

    /**
     * @param mixed $width
     * @param mixed $height
     * @param mixed $type
     * @param mixed $expectedCachePath
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerThumbnailCachePath')]
    public function testGetThumbnailCachePath($width, $height, $type, $expectedCachePath): void
    {
        $book = Book::getBookById(2);
        $cover = new Cover($book);

        Config::set('thumbnail_cache_directory', __DIR__ . '/cache/');
        $cachePath = $cover->getThumbnailCachePath($width, $height, $type);
        $this->assertEquals($expectedCachePath, $cachePath);

        rmdir(dirname((string) $cachePath));
        rmdir(dirname((string) $cachePath, 2));

        Config::set('thumbnail_cache_directory', '');
    }

    /**
     * Summary of providerThumbnail
     * @return array<mixed>
     */
    public static function providerThumbnailCachePath()
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
        $request = Request::build();
        $response = new FileResponse();

        // no thumbnail resizing
        ob_start();
        $result = $cover->sendThumbnail($request, $response);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['cover'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = FileResponse::class;
        $this->assertEquals($expected, $result::class);
    }

    public function testSendThumbnailResize(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $thumb = 'html';
        $request = Request::build(['thumb' => $thumb]);
        $response = new FileResponse();

        // no thumbnail cache
        ob_start();
        $result = $cover->sendThumbnail($request, $response);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['thumb'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = FileResponse::class;
        $this->assertEquals($expected, $result::class);
    }

    public function testSendThumbnailCacheMiss(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $width = null;
        $height = Config::get('html_thumbnail_height');
        $type = 'jpg';
        $thumb = 'html';
        $request = Request::build(['thumb' => $thumb]);
        $response = new FileResponse();

        // use thumbnail cache
        Config::set('thumbnail_cache_directory', __DIR__ . '/cache/');
        $cachePath = $cover->getThumbnailCachePath($width, $height, $type);
        if (file_exists($cachePath)) {
            unlink($cachePath);
            rmdir(dirname((string) $cachePath));
            rmdir(dirname((string) $cachePath, 2));
        }

        // 1. cache miss
        ob_start();
        $result = $cover->sendThumbnail($request, $response);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['thumb'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = FileResponse::class;
        $this->assertEquals($expected, $result::class);

        Config::set('thumbnail_cache_directory', '');
    }

    public function testSendThumbnailCacheHit(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $width = null;
        $height = Config::get('html_thumbnail_height');
        $type = 'jpg';
        $thumb = 'html';
        $request = Request::build(['thumb' => $thumb]);
        $response = new FileResponse();

        // use thumbnail cache
        Config::set('thumbnail_cache_directory', __DIR__ . '/cache/');

        // 2. cache hit
        ob_start();
        $result = $cover->sendThumbnail($request, $response);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['thumb'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = FileResponse::class;
        $this->assertEquals($expected, $result::class);

        $cachePath = $cover->getThumbnailCachePath($width, $height, $type);
        if (file_exists($cachePath)) {
            unlink($cachePath);
            rmdir(dirname((string) $cachePath));
            rmdir(dirname((string) $cachePath, 2));
        }

        Config::set('thumbnail_cache_directory', '');
    }

    public function testCheckDatabaseFieldCover(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);

        // full url
        $fileName = $cover->checkDatabaseFieldCover('http://localhost/' . $book->path . '/cover.jpg');
        $expected = 'http://localhost/' . $book->path . '/cover.jpg';
        $this->assertEquals($expected, $fileName);

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
        $book->setHandler(self::$handler);

        $data = $book->getCustomColumnValues(["*"], true);

        $this->assertCount(3, $data);

        $this->assertEquals("SeriesLike [1]", $data[0]['htmlvalue']);
        $this->assertEquals(self::$handler::link() . "/custom/1/1", $data[0]['url']);
        // @todo handle case where we have several values, e.g. array of text for type 2 (csv)
        $this->assertEquals("tag1,tag2", $data[1]['htmlvalue']);
        $this->assertEquals(self::$handler::link() . "/custom/2/1,2", $data[1]['url']);
        $this->assertEquals("text", $data[2]['htmlvalue']);
        $this->assertEquals(self::$handler::link() . "/custom/3/1", $data[2]['url']);
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
        $this->assertEquals(Route::path("download/20/0/Carroll%2C%20Lewis%20-%20Alice%27s%20Adventures%20in%20Wonderland.kepub.epub"), $epub->getHtmlLink());
        $this->assertEquals(Route::path("download/17/0/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.mobi"), $mobi->getHtmlLink());

        Config::set('provide_kepub', "0");
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $book = Book::getBookById(17);
        $book->updateForKepub = false;
        $epub = $book->getDataById(20);
        $this->assertEquals(Route::path("download/20/0/Alice%27s%20Adventures%20in%20Wonderland%20-%20Lewis%20Carroll.epub"), $epub->getHtmlLink());

        Config::set('use_url_rewriting', "0");
        $this->assertEquals(self::$fetcher::link() . "/fetch/0/20/ignore.epub", $epub->getHtmlLink());
    }

    public function testGetUpdatedEpub(): void
    {
        // Get Alice MOBI=>17, PDF=>19, EPUB=>20
        $book = Book::getBookById(17);
        $response = new FileResponse();

        ob_start();
        $result = $book->sendUpdatedEpub(20, $response);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['updated'];
        //$this->assertStringStartsWith("Exception : Cannot modify header information", $output);
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = FileResponse::class;
        $this->assertEquals($expected, $result::class);
    }

    public function testGetCoverFilePath(): void
    {
        $book = Book::getBookById(17);

        $this->assertEquals(Database::getDbDirectory(null) . "Lewis Carroll/Alice's Adventures in Wonderland (17)/cover.jpg", $book->getCoverFilePath("jpg"));
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

    public function tearDown(): void
    {
        Database::clearDb();
    }
}
