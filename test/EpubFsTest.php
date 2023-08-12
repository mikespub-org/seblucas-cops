<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require(dirname(__FILE__) . "/../epubfs.php");
require(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\EPubReader;
use SebLucas\EPubMeta\EPub;

class EpubFsTest extends TestCase
{
    private static $book;
    private static $add;
    private static $endpoint;


    public static function setUpBeforeClass(): void
    {
        $idData = 20;
        self::$add = "data=$idData";
        $myBook = Book::getBookByDataId($idData);

        self::$book = new EPub($myBook->getFilePath("EPUB", $idData));
        self::$book->initSpineComponent();

        self::$endpoint = Config::ENDPOINT["epubfs"];
    }

    public function testUrlImage()
    {
        $data = EPubReader::getComponentContent(self::$book, "cover.xml", self::$add);

        $src = "";
        if (preg_match("/src\=\'(.*?)\'/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals(self::$endpoint . '?data=20&amp;comp=images~SLASH~cover.png', $src);
    }

    public function testUrlHref()
    {
        $data = EPubReader::getComponentContent(self::$book, "title.xml", self::$add);

        $src = "";
        if (preg_match("/src\=\'(.*?)\'/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals(self::$endpoint . '?data=20&amp;comp=images~SLASH~logo~DASH~feedbooks~DASH~tiny.png', $src);

        $href = "";
        if (preg_match("/href\=\'(.*?)\'/", $data, $matches)) {
            $href = $matches [1];
        }
        $this->assertEquals(self::$endpoint . '?data=20&amp;comp=css~SLASH~title.css', $href);
    }

    public function testImportCss()
    {
        $data = EPubReader::getComponentContent(self::$book, "css~SLASH~title.css", self::$add);

        $import = "";
        if (preg_match("/import \'(.*?)\'/", $data, $matches)) {
            $import = $matches [1];
        }
        $this->assertEquals(self::$endpoint . '?data=20&amp;comp=css~SLASH~page.css', $import);
    }

    public function testUrlInCss()
    {
        $data = EPubReader::getComponentContent(self::$book, "css~SLASH~main.css", self::$add);

        $src = "";
        if (preg_match("/url\s*\(\'(.*?)\'\)/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals(self::$endpoint . '?data=20&comp=fonts~SLASH~times.ttf', $src);
    }

    public function testDirectLink()
    {
        $data = EPubReader::getComponentContent(self::$book, "main10.xml", self::$add);

        $src = "";
        if (preg_match("/href\='(.*?)' title=\"Direct Link\"/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals(self::$endpoint . '?data=20&amp;comp=main2.xml', $src);
    }

    public function testDirectLinkWithAnchor()
    {
        $data = EPubReader::getComponentContent(self::$book, "main10.xml", self::$add);

        $src = "";
        if (preg_match("/href\='(.*?)' title=\"Direct Link with anchor\"/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals(self::$endpoint . '?data=20&amp;comp=main2.xml#anchor', $src);
    }

    public function testAnchorOnly()
    {
        $data = EPubReader::getComponentContent(self::$book, "main10.xml", self::$add);

        $src = "";
        if (preg_match("/href\='(.*?)' title=\"Link to anchor\"/", $data, $matches)) {
            $src = $matches [1];
        }
        $this->assertEquals('#anchor', $src);
    }
}
