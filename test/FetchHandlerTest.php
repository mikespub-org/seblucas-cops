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

class FetchHandlerTest extends TestCase
{
    protected static string $kepubifyPath = '/usr/local/bin/kepubify';

    /** @var array<string, int> */
    protected static $expectedSize = [
        'cover' => 200128,
        'thumb' => 15349,
        'original' => 1598906,
        'updated' => 1047437,
        'kepubify' => 1608245,
        'allinone' => 1055260,
        'file' => 12,
        'zipped' => 344,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    /**
     * Summary of testCover
     * @runInSeparateProcess
     * @return void
     */
    public function testCover(): void
    {
        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['id' => 17], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['cover'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }

    /**
     * Summary of testThumb
     * @runInSeparateProcess
     * @return void
     */
    public function testThumb(): void
    {
        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['id' => 17, 'thumb' => 'html'], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['thumb'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }

    /**
     * Summary of testView
     * @runInSeparateProcess
     * @return void
     */
    public function testView(): void
    {
        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['data' => 20, 'type' => 'epub', 'view' => 1], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['original'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }

    /**
     * Summary of testFetch
     * @runInSeparateProcess
     * @return void
     */
    public function testFetch(): void
    {
        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['data' => 20, 'type' => 'epub'], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['original'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        //file_put_contents(__DIR__ . '/file.original.epub', $output);
    }

    /**
     * Summary of testUpdated
     * @runInSeparateProcess
     * @return void
     */
    public function testUpdated(): void
    {
        Config::set('update_epub-metadata', '1');

        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['data' => 20, 'type' => 'epub'], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['updated'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        //file_put_contents(__DIR__ . '/file.updated.epub', $output);

        Config::set('update_epub-metadata', '0');
    }

    /**
     * Summary of testKepubify
     * @runInSeparateProcess
     * @return void
     */
    public function testKepubify(): void
    {
        Config::set('provide_kepub', '1');
        Config::set('kepubify_path', static::$kepubifyPath);
        $_SERVER['HTTP_USER_AGENT'] = "Kobo";

        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['data' => 20, 'type' => 'epub'], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['kepubify'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        //file_put_contents(__DIR__ . '/file.kepubify.epub', $output);

        unset($_SERVER['HTTP_USER_AGENT']);
        Config::set('kepubify_path', '');
        Config::set('provide_kepub', '0');
    }

    /**
     * Summary of testAllInOne
     * @runInSeparateProcess
     * @return void
     */
    public function testAllInOne(): void
    {
        Config::set('update_epub-metadata', '1');
        Config::set('provide_kepub', '1');
        Config::set('kepubify_path', static::$kepubifyPath);
        $_SERVER['HTTP_USER_AGENT'] = "Kobo";

        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['data' => 20, 'type' => 'epub'], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['allinone'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        //file_put_contents(__DIR__ . '/file.allinone.epub', $output);

        unset($_SERVER['HTTP_USER_AGENT']);
        Config::set('kepubify_path', '');
        Config::set('provide_kepub', '0');
        Config::set('update_epub-metadata', '0');
    }

    /**
     * Summary of testFile
     * @runInSeparateProcess
     * @return void
     */
    public function testFile(): void
    {
        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['id' => 17, 'file' => 'hello.txt'], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['file'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }

    /**
     * Summary of testZipped
     * @runInSeparateProcess
     * @return void
     */
    public function testZipped(): void
    {
        // set request handler to 'phpunit' to override output buffer check in handler
        $request = Request::build(['id' => 17, 'file' => 'zipped'], 'phpunit');
        $handler = Framework::getHandler('fetch');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['zipped'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }
}
