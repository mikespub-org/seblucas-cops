<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\Zipper;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Pages\PageId;

class ZipperTest extends TestCase
{
    /** @var array<string, int> */
    protected static $expectedSize = [
        'recent' => 1596561,
        'author' => 1594886,
        'zipped' => 368,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Config::set('calibre_custom_column', []);
        Config::set('calibre_custom_column_list', []);
        Config::set('calibre_custom_column_preview', []);
        Database::clearDb();
    }

    public function testDownloadPageRecent(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        Config::set('download_page', ['ANY']);

        $request = new Request();
        $request->set('page', $page);
        $request->set('type', 'any');

        $zipper = new Zipper($request);
        $valid = $zipper->isValidForDownload();
        $this->assertTrue($valid);

        ob_start();
        $zipper->download(null, false);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['recent'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('download_page', ['']);
    }

    public function testDownloadAuthorPage(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $authorId = 3;

        Config::set('download_page', ['ANY']);

        $request = new Request();
        $request->set('page', $page);
        $request->set('id', $authorId);
        $request->set('type', 'any');

        $zipper = new Zipper($request);
        $valid = $zipper->isValidForDownload();
        $this->assertTrue($valid);

        ob_start();
        $zipper->download(null, false);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['author'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('download_page', ['']);
    }

    public function testDownloadWrongSeries(): void
    {
        $page = PageId::SERIE_DETAIL;
        $seriesId = 1;

        Config::set('download_page', ['ANY']);

        $request = new Request();
        $request->set('page', $page);
        $request->set('id', $seriesId);
        $request->set('type', 'any');
        $expected = 'No files found';

        $zipper = new Zipper($request);
        $valid = $zipper->isValidForDownload();
        $this->assertFalse($valid);
        $this->assertEquals($expected, $zipper->getMessage());

        Config::set('download_page', ['']);
    }

    public function testDownloadWrongFormat(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        Config::set('download_page', ['ANY']);

        $request = new Request();
        $request->set('page', $page);
        $request->set('type', 'epub');
        $expected = 'Invalid format for page';

        $zipper = new Zipper($request);
        $valid = $zipper->isValidForDownload();
        $this->assertFalse($valid);
        $this->assertEquals($expected, $zipper->getMessage());

        Config::set('download_page', ['']);
    }

    public function testZipExtraFiles(): void
    {
        $request = new Request();
        $book = Book::getBookById(17);

        $zipper = new Zipper($request);
        $valid = $zipper->isValidForExtraFiles($book);
        $this->assertTrue($valid);

        ob_start();
        $zipper->download(null, false);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['zipped'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        $expected = [
            'hello.txt',
            'sub folder/copied file.txt',
        ];
        // make a temp file to analyze the zip file
        $tmpfile = FileResponse::getTempFile('zip');
        file_put_contents($tmpfile, $output);
        $zip = new \ZipArchive();
        $result = $zip->open($tmpfile, \ZipArchive::RDONLY);
        $this->assertTrue($result);
        $result = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $result[] = $zip->getNameIndex($i);
        }
        $this->assertEquals($expected, $result);
        $zip->close();
    }

    public function testZipperHandler(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        Config::set('download_page', ['ANY']);

        $request = new Request();
        $request->set('page', $page);
        $request->set('type', 'any');

        $handler = Framework::createHandler('zipper');

        ob_start();
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['recent'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('download_page', ['']);
    }
}
