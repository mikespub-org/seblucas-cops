<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\Response;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Exception\InvalidArgumentException;

class ResponseTest extends TestCase
{
    public function testResponseNullFile(): void
    {
        $filename = null;
        $response = new Response();
        $response->setContentDisposition($filename);
        $headers = $response->getHeaders();

        $expected = [];
        $this->assertEquals($expected, $headers);
    }

    public function testResponseEmptyFile(): void
    {
        $filename = '';
        $response = new Response();
        $response->setContentDisposition($filename);
        $headers = $response->getHeaders();

        $expected = ['Content-Disposition' => 'inline'];
        $this->assertEquals($expected, $headers);
    }

    public function testResponseAsciiFile(): void
    {
        $filename = "Alice's Adventures in Wonderland - Lewis Carroll.epub";
        $response = new Response();
        $response->setContentDisposition($filename);
        $headers = $response->getHeaders();

        $expected = ['Content-Disposition' => 'attachment; filename="Alice\'s Adventures in Wonderland - Lewis Carroll.epub"'];
        $this->assertEquals($expected, $headers);
    }

    public function testResponseFileSlashes(): void
    {
        $filename = 'hello \n"world.epub';
        $response = new Response();
        $response->setContentDisposition($filename);
        $headers = $response->getHeaders();

        $backslash = '\\';
        $expected = ['Content-Disposition' => 'attachment; filename="hello ' . $backslash . $backslash . 'n' . $backslash . '"world.epub"'];
        $this->assertEquals($expected, $headers);
    }

    public function testResponseFilePath(): void
    {
        $filename = "./tests/BaseWithSomeBooks/Lewis Carroll/Alice's Adventures in Wonderland (17)/Alice's Adventures in Wonderland - Lewis Carroll.epub";
        $response = new Response();
        $response->setContentDisposition($filename);
        $headers = $response->getHeaders();

        $expected = ['Content-Disposition' => 'attachment; filename="Alice\'s Adventures in Wonderland - Lewis Carroll.epub"'];
        $this->assertEquals($expected, $headers);
    }

    public function testResponseUtf8File(): void
    {
        $filename = 'Émile Zola - Série des Rougon-Macquart #1 - La curée.epub';
        $response = new Response();
        $response->setContentDisposition($filename);
        $headers = $response->getHeaders();

        $charset = "utf-8''";
        $expected = ['Content-Disposition' => 'attachment; filename="Emile Zola - Serie des Rougon-Macquart #1 - La curee.epub"; filename*=' . $charset . '%C3%89mile%20Zola%20-%20S%C3%A9rie%20des%20Rougon-Macquart%20%231%20-%20La%20cur%C3%A9e.epub'];
        $this->assertEquals($expected, $headers);
    }

    public function testResponseIso88591File(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UTF-8 string.');

        $filename = 'Émile Zola - Série des Rougon-Macquart #1 - La curée.epub';
        $isofile = mb_convert_encoding($filename, 'ISO-8859-1', 'UTF-8');
        $response = new Response();
        $response->setContentDisposition($isofile);
        $headers = $response->getHeaders();

        // in theory we could send plain ISO-8859-1 filename here, but COPS doesn't support it
        $expected = ['Content-Disposition' => 'attachment; filename="' . $isofile . '"'];
        $this->assertEquals($expected, $headers);
    }
}
