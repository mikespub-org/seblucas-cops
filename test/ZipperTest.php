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
use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Zipper;
use SebLucas\Cops\Pages\PageId;

class ZipperTest extends TestCase
{
    /** @var array<string, int> */
    protected static $expectedSize = [
        'recent' => 1596525,
        'author' => 1594886,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Config::set('calibre_custom_column', []);
        Config::set('calibre_custom_column_list', []);
        Config::set('calibre_custom_column_preview', []);
        Database::clearDb();
    }

    /**
     * Summary of testDownloadPageRecent
     * @runInSeparateProcess
     * @return void
     */
    public function testDownloadPageRecent(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        Config::set('download_page', ['ANY']);

        $request = new Request();
        $request->set('page', $page);
        $request->set('type', 'any');

        $zipper = new Zipper($request);
        $valid = $zipper->isValid();
        $this->assertTrue($valid);

        ob_start();
        $zipper->download();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['recent'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('download_page', ['']);
    }

    /**
     * Summary of testDownloadAuthor
     * @runInSeparateProcess
     * @return void
     */
    public function testDownloadAuthor(): void
    {
        $authorId = 3;

        Config::set('download_author', ['ANY']);

        $request = new Request();
        $request->set('author', $authorId);
        $request->set('type', 'any');

        $zipper = new Zipper($request);
        $valid = $zipper->isValid();
        $this->assertTrue($valid);

        ob_start();
        $zipper->download();
        $headers = headers_list();
        $output = ob_get_clean();
        $headers = headers_list();

        $expected = self::$expectedSize['author'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('download_author', ['']);
    }

    public function testDownloadWrongSeries(): void
    {
        $seriesId = 1;

        Config::set('download_series', ['ANY']);

        $request = new Request();
        $request->set('series', $seriesId);
        $request->set('type', 'any');
        $expected = 'No files found';

        $zipper = new Zipper($request);
        $valid = $zipper->isValid();
        $this->assertFalse($valid);
        $this->assertEquals($expected, $zipper->getMessage());

        Config::set('download_series', ['']);
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
        $valid = $zipper->isValid();
        $this->assertFalse($valid);
        $this->assertEquals($expected, $zipper->getMessage());

        Config::set('download_page', ['']);
    }

    /**
     * Summary of testZipperHandler
     * @runInSeparateProcess
     * @return void
     */
    public function testZipperHandler(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;

        Config::set('download_page', ['ANY']);

        $request = new Request();
        $request->set('page', $page);
        $request->set('type', 'any');

        $handler = Framework::getHandler('zipper');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['recent'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));

        Config::set('download_page', ['']);
    }
}
