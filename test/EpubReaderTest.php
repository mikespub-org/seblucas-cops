<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Book;
use SebLucas\EPubMeta\EPub;

class EpubReaderTest extends TestCase
{
    private static $book;

    public static function setUpBeforeClass(): void
    {
        $idData = 20;
        $myBook = Book::getBookByDataId($idData);

        self::$book = new EPub($myBook->getFilePath("EPUB", $idData));
        self::$book->initSpineComponent();
    }

    public function testComponents()
    {
        $data = self::$book->components();
        $check = [
            "cover.xml",
            "title.xml",
            "about.xml",
            "main0.xml",
            "main1.xml",
            "main2.xml",
            "main3.xml",
            "main4.xml",
            "main5.xml",
            "main6.xml",
            "main7.xml",
            "main8.xml",
            "main9.xml",
            "main10.xml",
            "main11.xml",
            "similar.xml",
            "feedbooks.xml",
        ];

        $this->assertEquals($check, $data);
    }

    public function testContents()
    {
        $data = self::$book->contents();
        $check = [
            [ "title" => "Title", "src" => "title.xml" ],
            [ "title" => "About", "src" => "about.xml" ],
            [ "title" => "Chapter 1 - Down the Rabbit Hole", "src" => "main0.xml" ],
            [ "title" => "Chapter 2 - The Pool of Tears", "src" => "main1.xml" ],
            [ "title" => "Chapter 3 - A Caucus-Race and a Long Tale", "src" => "main2.xml" ],
            [ "title" => "Chapter 4 - The Rabbit Sends in a Little Bill", "src" => "main3.xml" ],
            [ "title" => "Chapter 5 - Advice from a Caterpillar", "src" => "main4.xml" ],
            [ "title" => "Chapter 6 - Pig and Pepper", "src" => "main5.xml" ],
            [ "title" => "Chapter 7 - A Mad Tea-Party", "src" => "main6.xml" ],
            [ "title" => "Chapter 8 - The Queen’s Croquet Ground", "src" => "main7.xml" ],
            [ "title" => "Chapter 9 - The Mock Turtle’s Story", "src" => "main8.xml" ],
            [ "title" => "Chapter 10 - The Lobster-Quadrille", "src" => "main9.xml" ],
            [ "title" => "Chapter 11 - Who Stole the Tarts?", "src" => "main10.xml" ],
            [ "title" => "Chapter 12 - Alice’s Evidence", "src" => "main11.xml" ],
            [ "title" => "Recommendations", "src" => "similar.xml" ],
        ];

        $this->assertEquals($check, $data);
    }

    public function testComponent($component='cover.xml')
    {
        $data = self::$book->component($component);
        $check = 532;
        $this->assertEquals($check, strlen($data));
    }

    public function testGetComponentName($component='cover.xml', $element='images/cover.png')
    {
        $data = self::$book->getComponentName($component, $element);
        $check = 'images~SLASH~cover.png';
        $this->assertEquals($check, $data);
    }

    public function testComponentContentType($component='cover.xml')
    {
        $data = self::$book->componentContentType($component);
        $check = 'application/xhtml+xml';
        $this->assertEquals($check, $data);
    }
}
