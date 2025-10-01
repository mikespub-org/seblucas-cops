<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Calibre;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Cover;
use SebLucas\Cops\Model\LinkImage;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Handlers\FetchHandler;
use SebLucas\Cops\Handlers\JsonHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\LinkAcquisition;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Routing\UriGenerator;

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
    private const TEST_THUMBNAIL = __DIR__ . "/../thumbnail.jpg";
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
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
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
        $this->assertEquals(self::$handler::link() . "/books/2/Arthur_Conan_Doyle/The_Return_of_Sherlock_Holmes", $book->getUri());
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
        $this->assertEquals("", $identifiers[0]->getValueUri());
    }

    public function testGetIdentifiers_Google(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("google", $identifiers[0]->type);
        $this->assertEquals("yr9EAAAAYAAJ", $identifiers[0]->val);
        $this->assertEquals("https://books.google.com/books?id=yr9EAAAAYAAJ", $identifiers[0]->getValueUri());
    }

    public function testGetIdentifiers_Isbn(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("isbn", $identifiers[1]->type);
        $this->assertEquals("9782253003663", $identifiers[1]->val);
        $this->assertEquals("https://www.worldcat.org/isbn/9782253003663", $identifiers[1]->getValueUri());
    }

    public function testGetIdentifiers_OpenLibrary(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("olid", $identifiers[2]->type);
        $this->assertEquals("OL118974W", $identifiers[2]->val);
        $this->assertEquals("https://openlibrary.org/works/OL118974W", $identifiers[2]->getValueUri());
    }

    public function testGetIdentifiers_WikiData(): void
    {
        $book = Book::getBookById(18);

        $identifiers = $book->getIdentifiers();
        $this->assertCount(4, $identifiers);
        $this->assertEquals("wd", $identifiers[3]->type);
        $this->assertEquals("Q962265", $identifiers[3]->val);
        $this->assertEquals("https://www.wikidata.org/entity/Q962265", $identifiers[3]->getValueUri());
    }

    public function testBookGetLinkArray(): void
    {
        $book = Book::getBookById(2);
        $book->setHandler(self::$handler);

        $linkArray = $book->getLinkArray();
        foreach ($linkArray as $link) {
            if ($link instanceof LinkAcquisition && $link->title == "EPUB") {
                $this->assertEquals(self::$fetcher::link() . "/fetch/0/1/The_Return_of_Sherlock_Holmes_Arthur_Conan_Doyle.epub", $link->getUri());
                return;
            }
        }
        $this->fail();
    }

    public function testBookGetLinkArrayWithCDN(): void
    {
        Config::set('full_url', '/cops/');
        UriGenerator::setBaseUrl(null);
        Config::set('resources_cdn', 'https://fastly.site.com/cops/');

        $book = Book::getBookById(17);
        $book->setHandler(self::$handler);

        $linkArray = $book->getLinkArray();
        $found = 0;
        foreach ($linkArray as $link) {
            if ($link instanceof LinkAcquisition && $link->title == "EPUB") {
                $this->assertEquals("https://fastly.site.com/cops/index.php/fetch/0/20/Alice_s_Adventures_in_Wonderland_Lewis_Carroll.epub", $link->getUri());
                $found += 1;
                continue;
            }
            if ($link instanceof LinkImage && $link->rel == "http://opds-spec.org/image/thumbnail") {
                $this->assertEquals("https://fastly.site.com/cops/index.php/thumbs/0/17/html.jpg", $link->getUri());
                $found += 1;
                continue;
            }
        }

        foreach ($book->getExtraFiles() as $fileName) {
            $link = $book->getExtraFileLink($fileName);
            $this->assertEquals("https://fastly.site.com/cops/index.php/files/0/17/hello.txt", $link->getUri());
            $found += 1;
            break;
        }

        Config::set('resources_cdn', '');
        Config::set('full_url', '');
        UriGenerator::setBaseUrl(null);
        if ($found < 3) {
            $this->fail();
        }
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

        $size = getimagesize(self::TEST_THUMBNAIL);
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

        Config::set('thumbnail_cache_directory', dirname(__DIR__) . '/cache/');
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
            [164, null, 'jpg', dirname(__DIR__) . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-164x.jpg'],
            [null, 164, 'jpg', dirname(__DIR__) . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-x164.jpg'],
            [164, null, 'png', dirname(__DIR__) . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-164x.png'],
            [null, 164, 'png', dirname(__DIR__) . '/cache/8/7d/dbdeb-1e27-4d06-b79b-4b2a3bfc6a5f-x164.png'],
        ];
    }

    public function testSendImage(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $request = Request::build();
        $response = new FileResponse();

        // send cover image
        ob_start();
        $result = $cover->sendImage($response);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['cover'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = FileResponse::class;
        $this->assertEquals($expected, $result::class);
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

    #[Depends('testSendThumbnailResize')]
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
        Config::set('thumbnail_cache_directory', dirname(__DIR__) . '/cache/');
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

    #[Depends('testSendThumbnailCacheMiss')]
    public function testGetLinkImageSize(): void
    {
        $book = Book::getBookById(17);
        $book->setHandler(self::$handler);

        // use thumbnail cache
        Config::set('thumbnail_cache_directory', dirname(__DIR__) . '/cache/');

        $entry = $book->getEntry();
        foreach ($entry->linkArray as $link) {
            if ($link instanceof LinkImage && $link->hasFileInfo()) {
                $width = $link->getWidth();
                $height = $link->getHeight();
                $size = getimagesize($link->filepath);
                $this->assertSame($size[0], $width);
                $this->assertSame($size[1], $height);
            }
        }

        Config::set('thumbnail_cache_directory', '');
    }

    #[Depends('testGetLinkImageSize')]
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
        Config::set('thumbnail_cache_directory', dirname(__DIR__) . '/cache/');

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

    #[Depends('testSendThumbnailCacheHit')]
    public function testSendThumbnailNotModified(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $thumb = 'html';

        // resize without cache
        $request = Request::build(['thumb' => $thumb]);
        $response = new FileResponse();

        ob_start();
        $result = $cover->sendThumbnail($request, $response);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $expected = 200;
        $this->assertSame($expected, $result->getStatusCode());

        // get current ETag and Last-Modified
        $etag = $result->getHeader('ETag');
        $modified = $result->getHeader('Last-Modified');

        $this->assertNotEmpty($etag);
        $expected = time();
        $this->assertLessThan($expected, strtotime($modified));

        // check with ETag
        $request = Request::build(['thumb' => $thumb]);
        $request->serverParams['HTTP_IF_NONE_MATCH'] = $etag;

        ob_start();
        $result = $cover->sendThumbnail($request, $response);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEmpty($output);
        $expected = 304;
        $this->assertSame($expected, $result->getStatusCode());

        // check with Last-Modified
        $request = Request::build(['thumb' => $thumb]);
        $request->serverParams['HTTP_IF_MODIFIED_SINCE'] = $modified;

        ob_start();
        $result = $cover->sendThumbnail($request, $response);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEmpty($output);
        $expected = 304;
        $this->assertSame($expected, $result->getStatusCode());
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
        // handle case where we have several values, e.g. array of text for type 2 (csv)
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
        $this->assertEquals(dirname(__DIR__) . "/BaseWithSomeBooks/Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.epub", $epub->getLocalPath());

        Config::set('provide_kepub', "1");
        $book = Book::getBookById(17);
        $book->updateForKepub = true;
        $epub = $book->getDataById(20);
        $this->assertEquals(self::$fetcher::link() . "/fetch/0/20/Carroll_Lewis_Alice_s_Adventures_in_Wonderland_kepub.epub", $epub->getHtmlLink());
        $this->assertEquals(self::$fetcher::link() . "/fetch/0/17/Alice_s_Adventures_in_Wonderland_Lewis_Carroll.mobi", $mobi->getHtmlLink());

        Config::set('provide_kepub', "0");
        $book = Book::getBookById(17);
        $book->updateForKepub = false;
        $epub = $book->getDataById(20);
        $this->assertEquals(self::$fetcher::link() . "/fetch/0/20/Alice_s_Adventures_in_Wonderland_Lewis_Carroll.epub", $epub->getHtmlLink());
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
        $data->book->path = dirname(__DIR__, 2);
        if (function_exists('finfo_open') === true) {
            //$this->assertEquals("image/x-icon", $data->getMimeType());
            $this->assertEquals("image/vnd.microsoft.icon", $data->getMimeType());
        } else {
            $this->assertEquals("application/octet-stream", $data->getMimeType());
        }
    }

    public function testReplaceTemplateFields(): void
    {
        $template = "{author_sort}{series:| - | #}{series_index} - {title}";
        Config::set('download_filename', $template);

        $book = Book::getBookById(17);
        $result = Book::replaceTemplateFields($template, $book);
        $expected = "Carroll, Lewis - Alice's Adventures in Wonderland";
        $this->assertEquals($expected, $result);

        $book = Book::getBookById(18);
        $result = Book::replaceTemplateFields($template, $book);
        $expected = "Zola, Émile - Série des Rougon-Macquart #1 - La curée";
        $this->assertEquals($expected, $result);

        Config::set('download_filename', '');
    }

    public function testDataWithDownloadFilename(): void
    {
        $template = "{author_sort}{series:| - | #}{series_index} - {title}";
        Config::set('download_filename', $template);

        $book = Book::getBookById(17);
        $data = $book->getDataFormat("EPUB");

        $result = $data->getDownloadFilename();
        $expected = "Carroll, Lewis - Alice's Adventures in Wonderland";
        $this->assertEquals($expected, $result);

        $result = $data->getUpdatedFilenameEpub();
        $expected = "Carroll, Lewis - Alice's Adventures in Wonderland.epub";
        $this->assertEquals($expected, $result);

        $result = $data->getUpdatedFilenameKepub();
        $expected = "Carroll, Lewis - Alice's Adventures in Wonderland.kepub.epub";
        $this->assertEquals($expected, $result);

        $book = Book::getBookById(18);
        $data = $book->getDataFormat("EPUB");

        $result = $data->getDownloadFilename();
        $expected = "Zola, Émile - Série des Rougon-Macquart #1 - La curée";
        $this->assertEquals($expected, $result);

        $result = $data->getUpdatedFilenameEpub();
        $expected = "Zola, Émile - Série des Rougon-Macquart #1 - La curée.epub";
        $this->assertEquals($expected, $result);

        Config::set('download_filename', '');

        $book = Book::getBookById(17);
        $data = $book->getDataFormat("EPUB");

        $result = $data->getDownloadFilename();
        $expected = "Alice's Adventures in Wonderland - Lewis Carroll";
        $this->assertEquals($expected, $result);

        $result = $data->getUpdatedFilenameEpub();
        $expected = "Carroll, Lewis - Alice's Adventures in Wonderland.epub";
        $this->assertEquals($expected, $result);

        $book = Book::getBookById(18);
        $data = $book->getDataFormat("EPUB");

        $result = $data->getDownloadFilename();
        $expected = "La curee - Emile Zola";
        $this->assertEquals($expected, $result);

        $result = $data->getUpdatedFilenameEpub();
        $expected = "Zola, Émile - La curée.epub";
        $this->assertEquals($expected, $result);
    }

    public function tearDown(): void
    {
        Database::clearDb();
    }
}
