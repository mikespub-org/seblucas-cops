<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Handler;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class CalibreHandlerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testSwitchLibrarySingle(): void
    {
        $request = Request::build(['action' => 'switch-library', 'library' => '_hex_-4261736557697468536f6d65426f6f6b73']);
        $handler = Framework::createHandler('calibre');

        $response = $handler->handle($request);

        $expected = 302;
        $this->assertEquals($expected, $response->getStatusCode());
        $expected = 'vendor/bin/index.php';
        $this->assertEquals($expected, $response->getHeader('Location'));
    }

    public function testSwitchLibraryMulti(): void
    {
        Config::set('calibre_directory', [
            'BaseWithOneBook' => dirname(__DIR__) . "/BaseWithOneBook/",
            'BaseWithSomeBooks' => dirname(__DIR__) . "/BaseWithSomeBooks/",
        ]);
        $request = Request::build(['action' => 'switch-library', 'library' => '_hex_-4261736557697468536f6d65426f6f6b73']);
        $handler = Framework::createHandler('calibre');

        $response = $handler->handle($request);
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");

        $expected = 302;
        $this->assertEquals($expected, $response->getStatusCode());
        $expected = 'vendor/bin/index.php?db=1';
        $this->assertEquals($expected, $response->getHeader('Location'));
    }

    public function testBookDetails(): void
    {
        $request = Request::build(['action' => 'book-details', 'library' => '_hex_-4261736557697468536f6d65426f6f6b73', 'details' => '17']);
        $handler = Framework::createHandler('calibre');

        $response = $handler->handle($request);

        $expected = 302;
        $this->assertEquals($expected, $response->getStatusCode());
        $expected = 'vendor/bin/index.php/books/17/Lewis_Carroll/Alice_s_Adventures_in_Wonderland';
        $this->assertEquals($expected, $response->getHeader('Location'));
    }

    public function testBookDetailsInvalid(): void
    {
        $request = Request::build(['action' => 'book-details', 'library' => '_hex_-4261736557697468536f6d65426f6f6b73', 'details' => '999']);
        $handler = Framework::createHandler('calibre');

        $response = $handler->handle($request);

        $expected = 404;
        $this->assertEquals($expected, $response->getStatusCode());
        $expected = 'Invalid Book';
        $this->assertStringContainsString($expected, $response->getContent());
    }

    public function testShowNoteById(): void
    {
        $request = Request::build(['action' => 'show-note', 'library' => '_hex_-4261736557697468536f6d65426f6f6b73', 'details' => 'authors/id_3']);
        $handler = Framework::createHandler('calibre');

        $response = $handler->handle($request);

        $expected = 302;
        $this->assertEquals($expected, $response->getStatusCode());
        $expected = 'vendor/bin/index.php/restapi/notes/authors/3';
        $this->assertEquals($expected, $response->getHeader('Location'));
    }

    public function testShowNoteByHex(): void
    {
        $hex = bin2hex('Lewis Carroll');
        $request = Request::build(['action' => 'show-note', 'library' => '_hex_-4261736557697468536f6d65426f6f6b73', 'details' => 'authors/hex_' . $hex]);
        $handler = Framework::createHandler('calibre');

        $response = $handler->handle($request);

        $expected = 302;
        $this->assertEquals($expected, $response->getStatusCode());
        $expected = 'vendor/bin/index.php/restapi/notes/authors/3';
        $this->assertEquals($expected, $response->getHeader('Location'));
    }

    public function testShowNoteByName(): void
    {
        $val = rawurlencode('Lewis Carroll');
        $request = Request::build(['action' => 'show-note', 'library' => '_hex_-4261736557697468536f6d65426f6f6b73', 'details' => 'authors/val_' . $val]);
        $handler = Framework::createHandler('calibre');

        $response = $handler->handle($request);

        $expected = 302;
        $this->assertEquals($expected, $response->getStatusCode());
        $expected = 'vendor/bin/index.php/restapi/notes/authors/3';
        $this->assertEquals($expected, $response->getHeader('Location'));
    }
}
