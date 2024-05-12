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
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\EPubReader;
use SebLucas\EPubMeta\EPub;

class EpubFsTest extends TestCase
{
    private static EPub $book;
    /** @var array<mixed> */
    private static array $params;
    private static string $handler = 'epubfs';

    public static function setUpBeforeClass(): void
    {
        $idData = 20;
        self::$params = ["data" => $idData];
        $myBook = Book::getBookByDataId($idData);

        self::$book = new EPub($myBook->getFilePath("EPUB", $idData));
        self::$book->initSpineComponent();
    }

    public function testUrlImage(): void
    {
        $data = EPubReader::getComponentContent(self::$book, EPubReader::encode('cover.xml'), self::$params);

        $src = "";
        if (preg_match("/src\=\'(.*?)\'/", $data, $matches)) {
            $src = $matches [1];
        }
        $url = Route::link(self::$handler, null, ['data' => 20, 'comp' => EPubReader::encode('images/cover.png')], '&amp;');
        $this->assertEquals($url, $src);
    }

    public function testUrlHref(): void
    {
        $data = EPubReader::getComponentContent(self::$book, EPubReader::encode('title.xml'), self::$params);

        $src = "";
        if (preg_match("/src\=\'(.*?)\'/", $data, $matches)) {
            $src = $matches [1];
        }
        $url = Route::link(self::$handler, null, ['data' => 20, 'comp' => EPubReader::encode('images/logo-feedbooks-tiny.png')], '&amp;');
        $this->assertEquals($url, $src);

        $href = "";
        if (preg_match("/href\=\'(.*?)\'/", $data, $matches)) {
            $href = $matches [1];
        }
        $url = Route::link(self::$handler, null, ['data' => 20, 'comp' => EPubReader::encode('css/title.css')], '&amp;');
        $this->assertEquals($url, $href);
    }

    public function testImportCss(): void
    {
        $data = EPubReader::getComponentContent(self::$book, EPubReader::encode('css/title.css'), self::$params);

        $import = "";
        if (preg_match("/import \'(.*?)\'/", $data, $matches)) {
            $import = $matches [1];
        }
        $url = Route::link(self::$handler, null, ['data' => 20, 'comp' => EPubReader::encode('css/page.css')], '&amp;');
        $this->assertEquals($url, $import);
    }

    public function testUrlInCss(): void
    {
        $data = EPubReader::getComponentContent(self::$book, EPubReader::encode('css/main.css'), self::$params);

        $src = "";
        if (preg_match("/url\s*\(\'(.*?)\'\)/", $data, $matches)) {
            $src = $matches [1];
        }
        $url = Route::link(self::$handler, null, ['data' => 20, 'comp' => EPubReader::encode('fonts/times.ttf')]);
        $this->assertEquals($url, $src);
    }

    public function testDirectLink(): void
    {
        $data = EPubReader::getComponentContent(self::$book, EPubReader::encode('main10.xml'), self::$params);

        $src = "";
        if (preg_match("/href\='(.*?)' title=\"Direct Link\"/", $data, $matches)) {
            $src = $matches [1];
        }
        $url = Route::link(self::$handler, null, ['data' => 20, 'comp' => EPubReader::encode('main2.xml')], '&amp;');
        $this->assertEquals($url, $src);
    }

    public function testDirectLinkWithAnchor(): void
    {
        $data = EPubReader::getComponentContent(self::$book, EPubReader::encode('main10.xml'), self::$params);

        $src = "";
        if (preg_match("/href\='(.*?)' title=\"Direct Link with anchor\"/", $data, $matches)) {
            $src = $matches [1];
        }
        $url = Route::link(self::$handler, null, ['data' => 20, 'comp' => EPubReader::encode('main2.xml')], '&amp;');
        $this->assertEquals($url . '#anchor', $src);
    }

    public function testAnchorOnly(): void
    {
        $data = EPubReader::getComponentContent(self::$book, EPubReader::encode('main10.xml'), self::$params);

        $src = "";
        if (preg_match("/href\='(.*?)' title=\"Link to anchor\"/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals('#anchor', $src);
    }

    public function testEncodeDecode(): void
    {
        $decoded = 'images/logo-feedbooks-tiny.png';
        $encoded = 'images~SLASH~logo~DASH~feedbooks~DASH~tiny.png';
        $this->assertEquals($encoded, EPubReader::encode($decoded));
        $this->assertEquals($decoded, EPubReader::decode($encoded));
    }
}
