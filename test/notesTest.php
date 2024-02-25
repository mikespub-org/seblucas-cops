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
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class NotesTest extends TestCase
{
    private static string $endpoint = 'phpunit';
    private static Request $request;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        self::$request = new Request();
        Database::clearDb();
    }

    public static function tearDownAfterClass(): void
    {
        Database::clearDb();
    }

    public function testGetNotesDb(): void
    {
        $notesDb = Database::getNotesDb();
        $this->assertNotNull($notesDb);
    }

    public function testGetNotesById(): void
    {
        $note = Author::getNotesById(3);
        $this->assertEquals(3, $note->item);
        $this->assertEquals("authors", $note->colname);
        $this->assertEquals(227, strlen($note->doc));
        $this->assertEquals(1708880895.654, $note->mtime);
    }
}
