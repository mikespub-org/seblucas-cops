<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\EPubReader;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\ImageResponse;
use SebLucas\Cops\Output\Response;
use SebLucas\EPubMeta\EPub;
use DOMDocument;
use ZipArchive;

class EpubReaderTest extends TestCase
{
    private static EPub $book;

    public static function setUpBeforeClass(): void
    {
        $idData = 20;
        $myBook = Book::getBookByDataId($idData);

        self::$book = new EPub($myBook->getFilePath("EPUB", $idData));
        self::$book->initSpineComponent();
    }

    public function testGetReader(): void
    {
        $idData = 20;
        $request = new Request();
        $reader = new EPubReader();
        $version = null;

        ob_start();
        $data = $reader->getReader($idData, $version);
        $headers = headers_list();
        $output = ob_get_clean();

        $html = new DOMDocument();
        $html->loadHTML($data);

        $title = $html->getElementsByTagName('title')->item(0)->nodeValue;
        $expected = "COPS EPub Reader";
        $this->assertEquals($expected, $title);

        $script = $html->getElementsByTagName('script')->item(2)->nodeValue;
        $expected = 'title: "Alice\'s Adventures in Wonderland"';
        $this->assertStringContainsString($expected, $script);

        $expected = 'index.php/epubfs/0/20/';
        $this->assertStringContainsString($expected, $data);
    }

    public function testSendContent(): void
    {
        $idData = 20;
        $component = 'title.xml';
        $database = null;
        $request = new Request();
        $response = new Response();
        $reader = new EPubReader($request, $response);

        ob_start();
        $result = $reader->sendContent($idData, $component, $database);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = Response::class;
        $this->assertEquals($expected, $result::class);

        $html = new DOMDocument();
        $html->loadHTML($output);

        $title = $html->getElementsByTagName('title')->item(0)->nodeValue;
        $expected = "Title Page";
        $this->assertEquals($expected, $title);

        $h1 = $html->getElementsByTagName('h1')->item(0)->nodeValue;
        $expected = "Alice's Adventures in Wonderland";
        $this->assertStringContainsString($expected, $h1);
    }

    public function testComponents(): void
    {
        $data = self::$book->components();
        $expected = [
            "cover.xml",
            "title.xml",
            "about.xml",
            "main0.xml",
            "main1.xml",
            "main2.xml",
            "main3.xml",
            "main4.xml",
            "main5.xml",
            "main6.xml",
            "main7.xml",
            "main8.xml",
            "main9.xml",
            "main10.xml",
            "main11.xml",
            "similar.xml",
            "feedbooks.xml",
        ];

        $this->assertEquals($expected, $data);
    }

    public function testContents(): void
    {
        $data = self::$book->contents();
        $expected = [
            [ "title" => "Title", "src" => "title.xml" ],
            [ "title" => "About", "src" => "about.xml" ],
            [ "title" => "Chapter 1 - Down the Rabbit Hole", "src" => "main0.xml" ],
            [ "title" => "Chapter 2 - The Pool of Tears", "src" => "main1.xml" ],
            [ "title" => "Chapter 3 - A Caucus-Race and a Long Tale", "src" => "main2.xml" ],
            [ "title" => "Chapter 4 - The Rabbit Sends in a Little Bill", "src" => "main3.xml" ],
            [ "title" => "Chapter 5 - Advice from a Caterpillar", "src" => "main4.xml" ],
            [ "title" => "Chapter 6 - Pig and Pepper", "src" => "main5.xml" ],
            [ "title" => "Chapter 7 - A Mad Tea-Party", "src" => "main6.xml" ],
            [ "title" => "Chapter 8 - The Queen’s Croquet Ground", "src" => "main7.xml" ],
            [ "title" => "Chapter 9 - The Mock Turtle’s Story", "src" => "main8.xml" ],
            [ "title" => "Chapter 10 - The Lobster-Quadrille", "src" => "main9.xml" ],
            [ "title" => "Chapter 11 - Who Stole the Tarts?", "src" => "main10.xml" ],
            [ "title" => "Chapter 12 - Alice’s Evidence", "src" => "main11.xml" ],
            [ "title" => "Recommendations", "src" => "similar.xml" ],
        ];

        $this->assertEquals($expected, $data);
    }

    /**
     * Summary of testComponent
     * @param mixed $component
     * @return void
     */
    public function testComponent($component = 'cover.xml')
    {
        $data = self::$book->component($component);
        $expected = 532;
        $this->assertEquals($expected, strlen($data));
    }

    /**
     * Summary of testGetComponentName
     * @param mixed $component
     * @param mixed $element
     * @return void
     */
    public function testGetComponentName($component = 'cover.xml', $element = 'images/cover.png')
    {
        $data = self::$book->getComponentName($component, $element);
        $expected = 'images~SLASH~cover.png';
        $this->assertEquals($expected, $data);
    }

    /**
     * Summary of testComponentContentType
     * @param mixed $component
     * @return void
     */
    public function testComponentContentType($component = 'cover.xml')
    {
        $data = self::$book->componentContentType($component);
        $expected = 'application/xhtml+xml';
        $this->assertEquals($expected, $data);
    }

    public function testGetEpubjsReader(): void
    {
        $idData = 20;
        $request = new Request();
        $request->set('version', 'epubjs');
        $reader = new EPubReader();
        $version = 'epubjs';

        ob_start();
        $data = $reader->getReader($idData, $version);
        $headers = headers_list();
        $output = ob_get_clean();

        $html = new DOMDocument();
        $html->loadHTML($data);

        $title = $html->getElementsByTagName('title')->item(0)->nodeValue;
        $expected = "Alice's Adventures in Wonderland";
        $this->assertEquals($expected, $title);

        $script = $html->getElementsByTagName('script')->item(2)->getAttribute('src');
        $expected = 'dist/js/libs/epub.min.js';
        $this->assertStringContainsString($expected, $script);

        $expected = 'index.php/zipfs/0/20/';
        $this->assertStringContainsString($expected, $data);
    }

    public function testSendZipContent(): void
    {
        $idData = 20;
        $component = 'OPS/title.xml';
        $database = null;
        $request = new Request();
        $response = new Response();
        $reader = new EPubReader($request, $response);

        ob_start();
        $result = $reader->sendZipContent($idData, $component, $database);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = Response::class;
        $this->assertEquals($expected, $result::class);

        $html = new DOMDocument();
        $html->loadHTML($output);

        $title = $html->getElementsByTagName('title')->item(0)->nodeValue;
        $expected = "Title Page";
        $this->assertEquals($expected, $title);

        $h1 = $html->getElementsByTagName('h1')->item(0)->nodeValue;
        $expected = "Alice's Adventures in Wonderland";
        $this->assertStringContainsString($expected, $h1);
    }

    public function testListContentFiles(): void
    {
        $filePath = self::$book->file();
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);
        // Avoid phpunit notices about mock objects without expectations
        $reader = new class extends EPubReader {
            public function getDataLink()
            {
                return 'vendor/bin/index.php/zipfs/test.epub?comp=';
            }
        };

        $json = $reader->listContentFiles($zip, $filePath);
        $expected = [
            'contents' => [
                [
                    'title' => 'Title',
                    'src' => 'vendor/bin/index.php/zipfs/test.epub?comp=title.xml',
                ],
            ],
            'components' => [
                'vendor/bin/index.php/zipfs/test.epub?comp=title.xml',
            ],
        ];
        $data = json_decode($json, true);
        $this->assertEquals($expected['contents'][0], $data['contents'][0]);
    }

    public function testGetZipFileContentIndexJson(): void
    {
        $filePath = self::$book->file();
        // Avoid phpunit notices about mock objects without expectations
        $reader = new class extends EPubReader {
            public function getDataLink()
            {
                return 'vendor/bin/index.php/zipfs/test.epub?comp=';
            }
        };

        $json = $reader->getZipFileContent($filePath, 'index.json');
        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $expected = [
            'contents' => [
                [
                    'title' => 'Title',
                    'src' => 'vendor/bin/index.php/zipfs/test.epub?comp=title.xml',
                ],
            ],
            'components' => [
                'vendor/bin/index.php/zipfs/test.epub?comp=title.xml',
            ],
        ];
        $this->assertEquals($expected['contents'][0], $data['contents'][0]);
    }

    public function testGetCoverPath(): void
    {
        $path = self::$book->getCoverPath();
        $expected = 'images/cover.png';
        $this->assertEquals($expected, $path);

        $meta = self::$book->meta();
        $expected = 'OPS/fb.opf';
        $this->assertEquals($expected, $meta);

        // see EPub::getFullPath()
        $path = dirname('/' . $meta) . '/' . $path; // image path is relative to meta file
        $path = ltrim($path, '/');
        $expected = 'OPS/images/cover.png';
        $this->assertEquals($expected, $path);
    }

    public function testGetCoverInfo(): void
    {
        $hasCover = self::$book->hasCover();
        $this->assertTrue($hasCover);

        $info = self::$book->getCoverInfo();
        $expected = [
            'mime' => "image/png",
            'data' => 763176,
            'found' => "OPS/images/cover.png",
        ];
        $this->assertEquals($expected['mime'], $info['mime']);
        $this->assertEquals($expected['found'], $info['found']);
        $this->assertEquals($expected['data'], strlen($info['data']));
    }

    public function testGetZipFileContentCoverJpg(): void
    {
        $filePath = self::$book->file();
        $request = new Request();
        $reader = new EPubReader($request);

        $response = $reader->getZipFileContent($filePath, 'cover.jpg');
        $this->assertInstanceOf(ImageResponse::class, $response);
        $expected = "png";
        $this->assertEquals($expected, $response->type);
        $expected = 763176;
        $this->assertEquals($expected, strlen($response->getContent()));
    }

    public function testFindCoverInfo(): void
    {
        $filePath = self::$book->file();

        $reader = new EPubReader();
        $info = $reader->findCoverInfo($filePath);
        $expected = [
            'mime' => "image/png",
            'data' => 763176,
            'found' => "OPS/images/cover.png",
        ];
        $this->assertEquals($expected['mime'], $info['mime']);
        $this->assertEquals($expected['found'], $info['found']);
        $this->assertEquals($expected['data'], strlen($info['data']));
    }

    public function testSendCoverImage(): void
    {
        $filePath = self::$book->file();
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        $request = new Request();
        $reader = new EPubReader($request);

        $response = $reader->sendCoverImage($zip, $filePath);
        $this->assertInstanceOf(ImageResponse::class, $response);
        $expected = "png";
        $this->assertEquals($expected, $response->type);
        $expected = 763176;
        $this->assertEquals($expected, strlen($response->getContent()));
    }

    public function testSendCoverImageNoCover(): void
    {
        $filePath = self::$book->file();
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        // Avoid phpunit notices about mock objects without expectations
        $reader = new class extends EPubReader {
            public function findCoverInfo($filePath)
            {
                return false;
            }
        };
        $reader->setRequest(new Request());

        if (!Config::get('thumbnail_default')) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage("Unknown cover for Alice's Adventures in Wonderland - Lewis Carroll.epub");
        }
        $response = $reader->sendCoverImage($zip, $filePath);
        $this->assertInstanceOf(Response::class, $response);
        $status = 302;
        $this->assertEquals($status, $response->getStatusCode());
        $location = 'vendor/bin/images/icons/icon144.png';
        $this->assertEquals($location, $response->getHeader('Location'));
    }

    public function testSendCoverImageWithSize(): void
    {
        $filePath = self::$book->file();
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        $request = new Request();
        $request->set('size', 'html');
        $reader = new EPubReader($request);

        $response = $reader->sendCoverImage($zip, $filePath);
        $this->assertInstanceOf(ImageResponse::class, $response);
        $this->assertEquals('html', $request->get('thumb'));

        $content = $response->getContent();
        $this->assertNotEmpty($content);
        $size = getimagesizefromstring($content);
        $this->assertIsArray($size);
        $height = ImageResponse::getThumbnailHeight('html');
        $this->assertEquals($height, $size[1]);
    }

    public function testReadHandler(): void
    {
        $request = Request::build(['data' => 20]);
        $handler = Framework::createHandler('read');

        ob_start();
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "{title: 'Chapter 1 - Down the Rabbit Hole', src: 'main0.xml'}";
        $this->assertStringContainsString($expected, $output);
    }
}
