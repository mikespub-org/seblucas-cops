<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Calibre;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Folder;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\LinkAcquisition;

class FolderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testFindBookFiles(): void
    {
        $root = dirname(__DIR__, 2);
        $folder = Folder::getRootFolder($root);
        $folder->setHandler(HtmlHandler::class);
        $books = $folder->findBookFiles();
        $expected = 3;
        $this->assertEquals($expected, $folder->count);
    }

    public function testFolderHierarchy(): void
    {
        /**
        tests/
        └── BaseWithSomeBooks
            ├── Lewis Carroll
            │   └── Alice's Adventures in Wonderland (17)
            │       └── Alice's Adventures in Wonderland - Lewis Carroll.epub
            └── Sun Wu
                └── Sun Zi Bing Fa (19)
                    └── Sun Zi Bing Fa - Sun Wu.epub
        */
        $root = dirname(__DIR__, 2) . '/tests';
        $folder = Folder::getRootFolder($root);
        $folder->setHandler(HtmlHandler::class);

        $books = $folder->findBookFiles();

        // Total books in tests is 2
        $this->assertEquals(2, $folder->count);

        // Direct children in tests
        $children = $folder->getChildFolders();
        $this->assertNotEmpty($children);
        $this->assertEquals(1, count($children));

        // Force re-scan of $child folder (no books loaded in root scan above)
        $child = reset($children);
        $child->scanned = false;
        $child->findBookFiles();

        // Recursive children in tests
        $children = $folder->getChildFolders(true);
        $this->assertNotEmpty($children);
        $this->assertEquals(5, count($children));

        // Find BaseWithSomeBooks folder by name in $folder
        $base = $folder->getChildFolderByName('BaseWithSomeBooks');
        $this->assertNotNull($base, 'BaseWithSomeBooks folder not found');
        $this->assertEquals(2, $base->count);

        // Direct children in BaseWithSomeBooks
        $children = $base->getChildFolders();
        $this->assertNotEmpty($children);
        $this->assertEquals(2, count($children));

        // Find Lewis Carroll folder by name in $base
        $lewis = $base->getChildFolderByName('Lewis Carroll');
        $this->assertNotNull($lewis, 'Lewis Carroll folder not found');
        $this->assertEquals(1, $lewis->getBookCount());

        // Find BaseWithSomeBooks/Lewis Carroll folder by id from $folder
        $carroll = $folder->getChildFolderById('BaseWithSomeBooks/Lewis Carroll');
        $this->assertNotNull($carroll, 'BaseWithSomeBooks/Lewis Carroll folder not found');
        $this->assertEquals($lewis, $carroll);

        // Find book entries in Lewis Carroll folder
        [$entries, $total] = Folder::getBooksByFolderOrChildren($carroll);
        $this->assertEquals(1, count($entries));
        $this->assertEquals(1, $total);

        $entry = reset($entries);
        $expected = "Alice's Adventures in Wonderland - Lewis Carroll";
        $this->assertEquals($expected, $entry->title);
        foreach ($entry->linkArray as $link) {
            if ($link instanceof LinkAcquisition) {
                $expected = str_replace('/folder/', '/format/', $carroll->getUri()) . "/" . rawurlencode("Alice's Adventures in Wonderland (17)") . "/" . rawurlencode($expected) . ".epub";
                $this->assertEquals($expected, $link->getUri());
                break;
            }
        }

        $datas = $entry->book->getDatas();
        $data = reset($datas);
        $expected = "Alice's Adventures in Wonderland - Lewis Carroll.epub";
        $this->assertEquals($expected, $data->getFilename());
        $expected = $carroll->id . "/Alice's Adventures in Wonderland (17)/" . $expected;
        $this->assertEquals($expected, $data->getFolderPath());

        // Find parent trail for $lewis
        $trail = $lewis->getParentTrail();
    }

    public function testParseGetFiles(): void
    {
        $root = '/volume1/calibre/';
        $files = dirname(__DIR__, 2) . '/tests/getfiles.json';
        $folder = Folder::getRootFolder($root);
        $result = $folder->parseGetFiles($files);
        $this->assertEmpty($result);
        $this->assertNotNull($result);
        $expected = 2;
        $this->assertEquals($expected, $folder->count);

        $folderName = 'BaseWithSomeBooks/Lewis Carroll';
        $result = $folder->parseGetFiles($files, $folderName);
        $expected = 1;
        $this->assertCount($expected, $result);
        $instance = $folder->getChildFolderById('BaseWithSomeBooks/Lewis Carroll');
        $this->assertNotNull($instance);
    }

    public function tearDown(): void
    {
        Database::clearDb();
    }
}
