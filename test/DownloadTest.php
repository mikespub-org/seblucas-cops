<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Output\JSONRenderer;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Downloader;
use SebLucas\Cops\Pages\PageId;

class DownloadTest extends TestCase
{
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
        $expected = 1594886;

        $downloader = new Downloader($request);
        $valid = $downloader->isValid();
        $this->assertTrue($valid);

        ob_start();
        $downloader->download();
        $headers = headers_list();
        $output = ob_get_clean();

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
        $expected = 1594886;

        $downloader = new Downloader($request);
        $valid = $downloader->isValid();
        $this->assertTrue($valid);

        ob_start();
        $downloader->download();
        $headers = headers_list();
        $output = ob_get_clean();
        $headers = headers_list();

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

        $downloader = new Downloader($request);
        $valid = $downloader->isValid();
        $this->assertFalse($valid);
        $this->assertEquals($expected, $downloader->getMessage());

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

        $downloader = new Downloader($request);
        $valid = $downloader->isValid();
        $this->assertFalse($valid);
        $this->assertEquals($expected, $downloader->getMessage());

        Config::set('download_page', ['']);
    }
}
