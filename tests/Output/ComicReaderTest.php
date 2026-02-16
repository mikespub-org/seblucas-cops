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
        $result = ComicReader::isComicFile($filePath);
        $this->assertTrue($result);

        $filePath = dirname(__DIR__) . '/cba-cbam.epub';
        $result = ComicReader::isComicFile($filePath);
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
}
