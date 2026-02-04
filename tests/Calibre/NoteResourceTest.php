<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Calibre;

use SebLucas\Cops\Calibre\Note;
use SebLucas\Cops\Calibre\Resource;
use SebLucas\Cops\Handlers\CalResHandler;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\FileResponse;
use SebLucas\Cops\Routing\UriGenerator;

class NoteResourceTest extends TestCase
{
    private static Author $author;
    /** @var array<string, int> */
    protected static $expectedSize = [
        'note' => 434,
        'calres' => 37341,
    ];

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
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
        $expected = ["authors" => 3];
        $result = Note::getCountByType();
        $this->assertEquals($expected, $result);
    }

    public function testGetEntriesByType(): void
    {
        $expected = [];
        $expected[3] = [
            "item" => 3,
            "size" => 434,
            "mtime" => 1770217333.384,
            "title" => "Lewis Carroll",
        ];
        $result = Note::getEntriesByType("authors");
        $this->assertEquals($expected[3], $result[3]);
    }

    public function testGetNotesById(): void
    {
        $note = self::$author->getNote();
        $this->assertEquals(Note::class, $note !== null ? $note::class : self::class);
        $this->assertEquals(3, $note->item);
        $this->assertEquals("authors", $note->colname);
        $this->assertEquals(self::$expectedSize['note'], strlen($note->doc));
        $this->assertEquals(1770217333.384, $note->mtime);
    }

    public function testFixResourceLinks(): void
    {
        Config::set('full_url', '/cops/');
        UriGenerator::setBaseUrl(null);

        $note = self::$author->getNote();
        $html = Resource::fixResourceLinks($note->doc, $note->databaseId);
        $expected = '<img src="/cops/index.php/calres/0/xxh64/7c301792c52eebf7?placement=';
        $this->assertStringContainsString($expected, $html);

        Config::set('resources_cdn', 'https://fastly.site.com/cops/');
        $html = Resource::fixResourceLinks($note->doc, $note->databaseId);
        $expected = '<img src="https://fastly.site.com/cops/index.php/calres/0/xxh64/7c301792c52eebf7?placement=';
        $this->assertStringContainsString($expected, $html);

        Config::set('resources_cdn', '');
        Config::set('full_url', '');
        UriGenerator::setBaseUrl(null);
    }

    public function testGetResources(): void
    {
        $note = self::$author->getNote();
        $resources = $note->getResources();
        $this->assertCount(1, $resources);
        $hash = "xxh64:7c301792c52eebf7";
        $this->assertEquals(Resource::class, $resources[$hash]::class);
        $expected = $hash;
        $this->assertEquals($expected, $resources[$hash]->hash);
        $expected = "330px-LewisCarrollSelfPhoto.jpg";
        $this->assertEquals($expected, $resources[$hash]->name);
        $expected = "/.calnotes/resources/7c/xxh64-7c301792c52eebf7";
        $this->assertStringEndsWith($expected, Resource::getResourcePath($resources[$hash]->hash));
        $expected = CalResHandler::link() . "/calres/0/xxh64/7c301792c52eebf7";
        $this->assertEquals($expected, $resources[$hash]->getUri());
    }

    public function testSendImageResource(): void
    {
        $hash = "xxh64:7c301792c52eebf7";
        $name = null;
        $database = 0;
        $response = new FileResponse();

        ob_start();
        $result = Resource::sendImageResource($hash, $response, $name, $database);
        $result->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['calres'];
        $this->assertEquals(0, count($headers));
        $this->assertEquals($expected, strlen($output));
        $expected = FileResponse::class;
        $this->assertEquals($expected, $result::class);
    }

    public function testCalResHandler(): void
    {
        $request = Request::build(["db" => 0, "alg" => "xxh64", "digest" => "7c301792c52eebf7"]);
        $handler = Framework::createHandler('calres');

        ob_start();
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = self::$expectedSize['calres'];
        $this->assertEquals($expected, strlen($output));
    }
}
