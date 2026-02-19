<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\ComicReader;
use SebLucas\Cops\Output\ImageResponse;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Input\Request;
use ZipArchive;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;

class ComicReaderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // ...
    }

    public function testIsComicFile(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $result = ComicReader::isValidFile($filePath);
        $this->assertTrue($result);

        $filePath = dirname(__DIR__) . '/cba-cbam.epub';
        $result = ComicReader::isValidFile($filePath);
        $this->assertFalse($result);
    }

    public function testGetMetadata(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $reader = new ComicReader();
        $metadata = $reader->getMetadata($filePath);
        $this->assertNotNull($metadata);

        $title = $metadata->getElement('Title');
        $expected = 'You Had One Job';
        $this->assertEquals($expected, $title[0]);
    }

    public function testGetMetadataInvalid(): void
    {
        $filePath = __FILE__; // Not a zip file
        $reader = new ComicReader();
        $this->assertFalse($reader->getMetadata($filePath));
    }

    public function testGetImageFiles(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        $reader = new ComicReader();
        $images = $reader->getImageFiles($zip);
        $expected = ["cba-cbam 2/01.jpg"];
        $this->assertEquals($expected, $images);
    }

    public function testListContentFiles(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);
        // Avoid phpunit notices about mock objects without expectations
        $reader = new class extends ComicReader {
            public function getDataLink()
            {
                return 'vendor/bin/index.php/zipfs/test.epub?comp=';
            }
        };

        $data = $reader->listContentFiles($zip, $filePath);
        $expected = [
            [
                'name' => 'cba-cbam 2/01.jpg',
                'type' => 'image',
                'href' => 'vendor/bin/index.php/zipfs/test.epub?comp=cba-cbam%202%2F01.jpg',
            ],
        ];
        $images = json_decode($data, true);
        $this->assertEquals($expected, $images);
    }

    public function testGetZipFileContentInvalidFile(): void
    {
        $reader = new ComicReader();
        $this->expectException(\InvalidArgumentException::class);
        $reader->getZipFileContent(__FILE__, 'content');
    }

    public function testGetZipFileContentUnknownComponent(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $reader = new ComicReader();
        $this->expectException(\InvalidArgumentException::class);
        $reader->getZipFileContent($filePath, 'unknown.xml');
    }

    public function testGetZipFileContentIndexJson(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        // Avoid phpunit notices about mock objects without expectations
        $reader = new class extends ComicReader {
            public function getDataLink()
            {
                return '/cops/index.php/zipfs/cba-cbam.cbz?comp=';
            }
        };

        $json = $reader->getZipFileContent($filePath, 'index.json');
        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $expected = [
            'name' => "cba-cbam 2/01.jpg",
            'type' => "image",
            'href' => "/cops/index.php/zipfs/cba-cbam.cbz?comp=cba-cbam%202%2F01.jpg",
        ];
        $this->assertEquals($expected['name'], $data[0]['name']);
        $this->assertEquals($expected['href'], $data[0]['href']);
    }

    public function testGetZipFileContentCoverJpg(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $request = new Request();
        $reader = new ComicReader($request);

        $response = $reader->getZipFileContent($filePath, 'cover.jpg');
        $this->assertInstanceOf(ImageResponse::class, $response);
        $expected = "jpg";
        $this->assertEquals($expected, $response->type);
        $expected = 153544;
        $this->assertEquals($expected, strlen($response->getContent()));
    }

    public function testFindCoverImage(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        $reader = new ComicReader();
        $index = $reader->findCoverImage($zip);
        $expected = 6;
        $this->assertEquals($expected, $index);
    }

    public function testSendCoverImage(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        $request = new Request();
        $reader = new ComicReader($request);

        $response = $reader->sendCoverImage($zip, $filePath);
        $this->assertInstanceOf(ImageResponse::class, $response);
        $expected = "jpg";
        $this->assertEquals($expected, $response->type);
        $expected = 153544;
        $this->assertEquals($expected, strlen($response->getContent()));
    }

    public function testSendCoverImageNoCover(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        // Avoid phpunit notices about mock objects without expectations
        $reader = new class extends ComicReader {
            public function findCoverImage($zip)
            {
                return false;
            }
        };
        $reader->setRequest(new Request());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown cover for cba-cbam.cbz');
        $reader->sendCoverImage($zip, $filePath);
    }

    public function testSendCoverImageWithSize(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);
        $this->assertNotFalse($result);

        $request = new Request();
        $request->set('size', 'html');
        $reader = new ComicReader($request);

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
}
