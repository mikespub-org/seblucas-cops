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
use SebLucas\Cops\Output\ImageResponse;

/*
Publishers:
id:2 (2 books)   Macmillan and Co. London:   Lewis Caroll
id:3 (2 books)   D. Appleton and Company     Alexander Dumas
id:4 (1 book)    Macmillan Publishers USA:   Jack London
id:5 (1 book)    Pierson's Magazine:         H. G. Wells
id:6 (8 books)   Strand Magazine:            Arthur Conan Doyle
*/

class CoverTest extends TestCase
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

    public function testGetThumbnailNotNeeded(): void
    {
        $book = Book::getBookById(2);
        $cover = new Cover($book);
        $file = $cover->coverFileName;
        $image = new ImageResponse();

        $image->width = null;
        $image->height = null;
        $this->assertFalse($image->generateThumbnail($file));

        // Current cover is 400*600
        $image->width = self::COVER_WIDTH;
        $image->height = null;
        $this->assertFalse($image->generateThumbnail($file));

        $image->width = self::COVER_WIDTH + 1;
        $image->height = null;
        $this->assertFalse($image->generateThumbnail($file));

        $image->width = null;
        $image->height = self::COVER_HEIGHT;
        $this->assertFalse($image->generateThumbnail($file));

        $image->width = null;
        $image->height = self::COVER_HEIGHT + 1;
        $this->assertFalse($image->generateThumbnail($file));
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
        $file = $cover->coverFileName;
        $image = new ImageResponse();

        $image->width = $width;
        $image->height = $height;
        $this->assertTrue($image->generateThumbnail($file, self::TEST_THUMBNAIL));

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
        $uuid = $book->uuid;

        Config::set('thumbnail_cache_directory', dirname(__DIR__) . '/cache/');
        $cachePath = ImageResponse::getCachePath($uuid, $width, $height, $type);
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

        // send cover image
        ob_start();
        $result = $cover->sendImage();
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['cover'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = ImageResponse::class;
        $this->assertEquals($expected, $result::class);
    }

    public function testSendThumbnailOriginal(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $request = Request::build();

        // no thumbnail resizing
        ob_start();
        $result = $cover->sendThumbnail($request);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['cover'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = ImageResponse::class;
        $this->assertEquals($expected, $result::class);
    }

    public function testSendThumbnailResize(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $thumb = 'html';
        $request = Request::build(['thumb' => $thumb]);

        // no thumbnail cache
        ob_start();
        $result = $cover->sendThumbnail($request);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['thumb'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = ImageResponse::class;
        $this->assertEquals($expected, $result::class);
    }

    #[Depends('testSendThumbnailResize')]
    public function testSendThumbnailCacheMiss(): void
    {
        $book = Book::getBookById(17);
        $cover = new Cover($book);
        $uuid = $book->uuid;
        $width = null;
        $height = Config::get('html_thumbnail_height');
        $type = 'jpg';
        $thumb = 'html';
        $request = Request::build(['thumb' => $thumb]);

        // use thumbnail cache
        Config::set('thumbnail_cache_directory', dirname(__DIR__) . '/cache/');
        $cachePath = ImageResponse::getCachePath($uuid, $width, $height, $type);
        if (file_exists($cachePath)) {
            unlink($cachePath);
            rmdir(dirname((string) $cachePath));
            rmdir(dirname((string) $cachePath, 2));
        }

        // 1. cache miss
        ob_start();
        $result = $cover->sendThumbnail($request);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['thumb'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = ImageResponse::class;
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
        $uuid = $book->uuid;
        $width = null;
        $height = Config::get('html_thumbnail_height');
        $type = 'jpg';
        $thumb = 'html';
        $request = Request::build(['thumb' => $thumb]);

        // use thumbnail cache
        Config::set('thumbnail_cache_directory', dirname(__DIR__) . '/cache/');

        // 2. cache hit
        ob_start();
        $result = $cover->sendThumbnail($request);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['thumb'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = ImageResponse::class;
        $this->assertEquals($expected, $result::class);

        $cachePath = ImageResponse::getCachePath($uuid, $width, $height, $type);
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

        ob_start();
        $result = $cover->sendThumbnail($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEmpty($output);
        $expected = 200;
        $this->assertSame($expected, $result->getStatusCode());
        $this->assertNotEmpty($result->getContent());

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
        $result = $cover->sendThumbnail($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $this->assertEmpty($output);
        $expected = 304;
        $this->assertSame($expected, $result->getStatusCode());

        // check with Last-Modified
        $request = Request::build(['thumb' => $thumb]);
        $request->serverParams['HTTP_IF_MODIFIED_SINCE'] = $modified;

        ob_start();
        $result = $cover->sendThumbnail($request);
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

    public function tearDown(): void
    {
        Database::clearDb();
    }
}
