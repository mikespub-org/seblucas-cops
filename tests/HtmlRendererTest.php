<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Output\HtmlRenderer;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\PageId;

class HtmlRendererTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        $_GET = [];
    }

    public function testHtmlRenderer(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        if (Config::get('use_route_urls')) {
            $expected = "getJSON.php/recent?complete=1";
        } else {
            $expected = "getJSON.php?page=10&complete=1";
        }
        $this->assertStringContainsString($expected, $output);
    }

    public function testHtmlRendererServerSide(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = "Kindle/1.0";

        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "Alice&#039;s Adventures in Wonderland";
        $this->assertStringContainsString($expected, $output);

        unset($_SERVER['HTTP_USER_AGENT']);
    }

    public function testHtmlRendererTwig(): void
    {
        $_COOKIE['template'] = "twigged";

        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        if (Config::get('use_route_urls')) {
            $expected = "getJSON.php/recent?complete=1";
        } else {
            $expected = "getJSON.php?page=10&complete=1";
        }
        $this->assertStringContainsString($expected, $output);

        unset($_COOKIE['template']);
    }

    public function testHtmlRendererTwigServerSide(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = "Kindle/1.0";
        $_COOKIE['template'] = "twigged";

        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "Alice&#039;s Adventures in Wonderland";
        $this->assertStringContainsString($expected, $output);

        unset($_COOKIE['template']);
        unset($_SERVER['HTTP_USER_AGENT']);
    }
}
