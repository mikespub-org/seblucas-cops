<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Calibre;

use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestConfig;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/config/test.php';

class SerieTest extends TestCase
{
    private static Request $request;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        self::$request = new Request();
        self::$request->locale = 'en';
        self::$request->setConfig(new RequestConfig());
    }

    public function testGetPrevNextBooks(): void
    {
        $series = Serie::getInstanceById(1);
        $series->setHandler(self::$request->getHandler());
        $series->setLocale(self::$request->locale);
        $series->setConfig(self::$request->getConfig());

        // Get all books ordered by series_index
        $booklist = new BookList(self::$request);
        [$allBooks, $totalNumber] = $booklist->getBooksByInstance($series, -1);
        $this->assertNotEmpty($allBooks);

        // Build a map: series_index => EntryBook
        $indexMap = [];
        foreach ($allBooks as $entryBook) {
            $indexMap[$entryBook->book->seriesIndex] = $entryBook;
        }

        // Verify we have known books with these indexes (from Sherlock Holmes series id=1)
        // Key detail: PHP converts float array keys like `1.0` to integers (`1`) when used as array keys, so `assertArrayHasKey(1.0, ...)` fails while `array_key_exists(1.0, ...)` succeeds.
        $this->assertTrue(array_key_exists(1, $indexMap));
        $this->assertTrue(array_key_exists(2, $indexMap));
        $this->assertTrue(array_key_exists(3, $indexMap));
        $this->assertTrue(array_key_exists(5, $indexMap));
        $this->assertTrue(array_key_exists(6, $indexMap));
        $this->assertTrue(array_key_exists(8, $indexMap));
        $this->assertTrue(array_key_exists(9, $indexMap));
        $this->assertCount(7, $indexMap);

        // Test at first index - prev should be null
        [$prev, $next] = $series->getPrevNextBooks(1.0);
        $this->assertNull($prev);
        $this->assertNotNull($next);
        $this->assertEquals($indexMap[2]->book->id, $next->book->id);

        // Test at last index - next should be null
        [$prev, $next] = $series->getPrevNextBooks(9.0);
        $this->assertNotNull($prev);
        $this->assertNull($next);
        $this->assertEquals($indexMap[8]->book->id, $prev->book->id);

        // Test at middle index
        [$prev, $next] = $series->getPrevNextBooks(5.0);
        $this->assertNotNull($prev);
        $this->assertNotNull($next);
        $this->assertEquals($indexMap[3]->book->id, $prev->book->id);
        $this->assertEquals($indexMap[6]->book->id, $next->book->id);

        // Test at non-existent index between 2.0 and 3.0
        [$prev, $next] = $series->getPrevNextBooks(2.5);
        $this->assertNotNull($prev);
        $this->assertNotNull($next);
        $this->assertEquals($indexMap[2]->book->id, $prev->book->id);
        $this->assertEquals($indexMap[3]->book->id, $next->book->id);

        // Test at non-existent index before first
        [$prev, $next] = $series->getPrevNextBooks(0.5);
        $this->assertNull($prev);
        $this->assertNotNull($next);
        $this->assertEquals($indexMap[1]->book->id, $next->book->id);

        // Test at non-existent index after last
        [$prev, $next] = $series->getPrevNextBooks(10.0);
        $this->assertNotNull($prev);
        $this->assertNull($next);
        $this->assertEquals($indexMap[9]->book->id, $prev->book->id);
    }
}
