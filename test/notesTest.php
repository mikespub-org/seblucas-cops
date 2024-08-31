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
use SebLucas\Cops\Calibre\Note;
use SebLucas\Cops\Calibre\Resource;
use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;

class NotesTest extends TestCase
{
    private static Author $author;
    /** @var array<string, int> */
    protected static $expectedSize = [
        'note' => 227,
        'calres' => 37341,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        self::$author = Author::getInstanceById(3);
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

    public function testGetCountByType(): void
    {
        $expected = ["authors" => 1];
        $result = Note::getCountByType();
        $this->assertEquals($expected, $result);
    }

    public function testGetEntriesByType(): void
    {
        $expected = [];
        $expected[3] = [
            "item" => 3,
            "size" => 227,
            "mtime" => 1708880895.654,
            "title" => "Lewis Carroll",
        ];
        $result = Note::getEntriesByType("authors");
        $this->assertEquals($expected, $result);
    }

    public function testGetNotesById(): void
    {
        $note = self::$author->getNote();
        $this->assertEquals(Note::class, get_class($note));
        $this->assertEquals(3, $note->item);
        $this->assertEquals("authors", $note->colname);
        $this->assertEquals(self::$expectedSize['note'], strlen($note->doc));
        $this->assertEquals(1708880895.654, $note->mtime);
    }

    public function testFixResourceLinks(): void
    {
        Config::set('full_url', '/cops/');
        Route::setBaseUrl(null);

        $note = self::$author->getNote();
        $html = Resource::fixResourceLinks($note->doc, $note->databaseId);
        $expected = '<img src="/cops/calres.php/0/xxh64/7c301792c52eebf7?placement=';
        $this->assertStringContainsString($expected, $html);

        Config::set('full_url', '');
        Route::setBaseUrl(null);
    }

    public function testGetResources(): void
    {
        $note = self::$author->getNote();
        $resources = $note->getResources();
        $this->assertCount(1, $resources);
        $hash = "xxh64:7c301792c52eebf7";
        $this->assertEquals(Resource::class, get_class($resources[$hash]));
        $expected = $hash;
        $this->assertEquals($expected, $resources[$hash]->hash);
        $expected = "330px-LewisCarrollSelfPhoto.jpg";
        $this->assertEquals($expected, $resources[$hash]->name);
        $expected = "/.calnotes/resources/7c/xxh64-7c301792c52eebf7";
        $this->assertStringEndsWith($expected, Resource::getResourcePath($resources[$hash]->hash));
        $expected = Route::link("calres") . "/0/xxh64/7c301792c52eebf7";
        $this->assertEquals($expected, $resources[$hash]->getUri());
    }

    /**
     * Summary of testSendImageResource
     * @runInSeparateProcess
     * @return void
     */
    public function testSendImageResource(): void
    {
        $hash = "xxh64:7c301792c52eebf7";
        $name = null;
        $database = 0;

        ob_start();
        $result = Resource::sendImageResource($hash, $name, $database);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['calres'];
        $this->assertTrue($result);
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
    }

    /**
     * Summary of testCalResHandler
     * @runInSeparateProcess
     * @return void
     */
    public function testCalResHandler(): void
    {
        $request = Request::build(["db" => 0, "alg" => "xxh64", "digest" => "7c301792c52eebf7"]);
        $handler = Framework::getHandler('calres');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['calres'];
        $this->assertEquals($expected, strlen($output));
    }
}
