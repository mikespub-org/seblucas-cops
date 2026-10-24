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
use SebLucas\Cops\Calibre\BookShelf;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Session;

class BookShelfTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/cops_test_shelves_' . bin2hex(random_bytes(4)) . '/';
        mkdir($this->tempDir, 0o750, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '{,.}*', GLOB_BRACE) ?: [];
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->tempDir);
        }
    }

    public function testStorageModesAndFilenames(): void
    {
        // 1. Shared mode
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);
        Config::set('server_id', 'test_srv');
        $shelf = new BookShelf();
        $this->assertTrue($shelf->isEnabled());
        $this->assertEquals(BookShelf::MODE_SHARED, $shelf->getStorageMode());
        $this->assertEquals('shared.json', $shelf->getStorageFilename());

        // 2. User mode
        Config::set('use_server_shelves', BookShelf::MODE_USER);
        $request = Request::build([], null, ['PHP_AUTH_USER' => 'alice']);
        $shelf = new BookShelf($request);
        $this->assertTrue($shelf->isEnabled());
        $this->assertEquals('alice', $shelf->getUserIdentifier());
        $expectedUserFile = md5('alice' . 'test_srv') . '.json';
        $this->assertEquals($expectedUserFile, $shelf->getStorageFilename());

        // 3. Session mode
        Config::set('use_server_shelves', BookShelf::MODE_SESSION);
        $sessionStub = $this->createStub(Session::class);
        $sessionStub->method('getId')->willReturn('sess_12345');
        $request = new Request();
        $request->setSession($sessionStub);
        $shelf = new BookShelf($request);
        $this->assertTrue($shelf->isEnabled());
        $this->assertEquals('sess_12345', $shelf->getSessionIdentifier());
        $expectedSessFile = md5('sess_12345' . 'test_srv') . '.json';
        $this->assertEquals($expectedSessFile, $shelf->getStorageFilename());

        // 4. Disabled mode
        Config::set('use_server_shelves', '');
        $shelf = new BookShelf();
        $this->assertFalse($shelf->isEnabled());
        $this->assertNull($shelf->getStorageFilename());

        // Static check
        Config::set('use_server_shelves', 'shared');
        $this->assertTrue(BookShelf::isServerShelvesEnabled());
        Config::set('use_server_shelves', '');
        $this->assertFalse(BookShelf::isServerShelvesEnabled());
    }

    public function testLoadAndSaveDefaultFavorites(): void
    {
        $filePath = $this->tempDir . 'test_shelf.json';
        $shelf = new BookShelf();
        $shelf->setFilePath($filePath);

        $loaded = $shelf->load();
        $this->assertFalse($loaded); // File did not exist initially
        $this->assertEquals('Favorites', $shelf->getActiveShelfName());
        $this->assertEquals(['Favorites'], $shelf->getShelfNames());
        $this->assertEmpty($shelf->getBooksInShelf());

        $saved = $shelf->save();
        $this->assertTrue($saved);
        $this->assertFileExists($filePath);
        $this->assertFileExists($this->tempDir . '.htaccess');

        // Re-load from saved file
        $shelf2 = new BookShelf();
        $shelf2->setFilePath($filePath);
        $this->assertTrue($shelf2->load());
        $this->assertEquals('Favorites', $shelf2->getActiveShelfName());
        $this->assertEquals(['Favorites'], $shelf2->getShelfNames());
    }

    public function testShelfCrud(): void
    {
        $filePath = $this->tempDir . 'crud_shelf.json';
        $shelf = new BookShelf();
        $shelf->setFilePath($filePath);
        $shelf->load();

        // Create
        $this->assertTrue($shelf->createShelf('To Read'));
        $this->assertFalse($shelf->createShelf('To Read')); // Duplicate
        $this->assertFalse($shelf->createShelf('   '));     // Empty name
        $this->assertTrue($shelf->hasShelf('To Read'));
        $this->assertEquals(['Favorites', 'To Read'], $shelf->getShelfNames());

        // Switch active
        $this->assertTrue($shelf->setActiveShelf('To Read'));
        $this->assertEquals('To Read', $shelf->getActiveShelfName());
        $this->assertFalse($shelf->setActiveShelf('Nonexistent'));

        // Rename
        $this->assertTrue($shelf->renameShelf('To Read', 'Reading'));
        $this->assertEquals('Reading', $shelf->getActiveShelfName()); // Active updated
        $this->assertTrue($shelf->hasShelf('Reading'));
        $this->assertFalse($shelf->hasShelf('To Read'));
        $this->assertFalse($shelf->renameShelf('Reading', 'Favorites')); // Collision

        // Delete
        $this->assertTrue($shelf->deleteShelf('Reading'));
        $this->assertFalse($shelf->hasShelf('Reading'));
        $this->assertEquals('Favorites', $shelf->getActiveShelfName()); // Reset to first remaining
        $this->assertFalse($shelf->deleteShelf('Favorites')); // Cannot delete last shelf
    }

    public function testBookOperations(): void
    {
        $filePath = $this->tempDir . 'books_shelf.json';
        $shelf = new BookShelf();
        $shelf->setFilePath($filePath);
        $shelf->load();

        // Add
        $this->assertTrue($shelf->addBook(42, 0, 'Favorites', ['title' => 'The Hobbit']));
        $this->assertFalse($shelf->addBook(42, 0)); // Duplicate
        $this->assertTrue($shelf->addBook(42, 1));  // Same book ID, different DB -> distinct identity!

        $this->assertTrue($shelf->isInShelf(42, 0));
        $this->assertTrue($shelf->isInShelf(42, 1));
        $this->assertFalse($shelf->isInShelf(43, 0));
        $this->assertFalse($shelf->isInShelf(42, 2));

        $books = $shelf->getBooksInShelf('Favorites');
        $this->assertCount(2, $books);
        $this->assertEquals('The Hobbit', $books[0]['title']);

        // Toggle: remove if present
        $toggledOff = $shelf->toggleBook(42, 0);
        $this->assertFalse($toggledOff);
        $this->assertFalse($shelf->isInShelf(42, 0));

        // Toggle: re-add if absent
        $toggledOn = $shelf->toggleBook(42, 0);
        $this->assertTrue($toggledOn);
        $this->assertTrue($shelf->isInShelf(42, 0));

        // Remove
        $this->assertTrue($shelf->removeBook(42, 0));
        $this->assertFalse($shelf->removeBook(42, 0)); // Already removed
        $this->assertFalse($shelf->isInShelf(42, 0));
    }

    public function testBookKeyNormalization(): void
    {
        $this->assertEquals('', BookShelf::normalizeDb(null));
        $this->assertEquals('', BookShelf::normalizeDb(0));
        $this->assertEquals('', BookShelf::normalizeDb('0'));
        $this->assertEquals('', BookShelf::normalizeDb(''));
        $this->assertEquals('1', BookShelf::normalizeDb(1));
        $this->assertEquals('2', BookShelf::normalizeDb('2'));

        $this->assertEquals(':42', BookShelf::makeBookKey(42, 0));
        $this->assertEquals(':42', BookShelf::makeBookKey(42, null));
        $this->assertEquals('1:42', BookShelf::makeBookKey(42, 1));

        $this->assertEquals(':42', BookShelf::getBookKey(['id' => 42, 'db' => 0]));
        $this->assertEquals('2:100', BookShelf::getBookKey(['id' => 100, 'db' => '2']));
        $this->assertEquals('3:200', BookShelf::getBookKey((object) ['id' => 200, 'db' => 3]));
    }

    public function testGetIdListGroupedAndResolveShelf(): void
    {
        $filePath = $this->tempDir . 'idlist_shelf.json';
        $shelf = new BookShelf();
        $shelf->setFilePath($filePath);
        $shelf->load();

        $shelf->createShelf('SciFi');
        $shelf->addBook(10, 0, 'Favorites');
        $shelf->addBook(11, 0, 'Favorites');
        $shelf->addBook(50, 1, 'Favorites');

        $shelf->addBook(99, 2, 'SciFi');

        // Active shelf is Favorites
        $idlistFavorites = $shelf->getIdListGrouped();
        $this->assertEquals([
            0 => [10, 11],
            1 => [50],
        ], $idlistFavorites);

        // Resolve by name
        $idlistSciFi = $shelf->getIdListGrouped('SciFi');
        $this->assertEquals([
            2 => [99],
        ], $idlistSciFi);

        // Resolve by MD5 hash of shelf name
        $md5SciFi = md5('SciFi');
        $this->assertEquals('SciFi', $shelf->resolveShelfName($md5SciFi));
        $idlistByMd5 = $shelf->getIdListGrouped($md5SciFi);
        $this->assertEquals([
            2 => [99],
        ], $idlistByMd5);
    }

    public function testExportAndImport(): void
    {
        $filePath = $this->tempDir . 'export_import.json';
        $shelf = new BookShelf();
        $shelf->setFilePath($filePath);
        $shelf->load();

        $shelf->addBook(1, 0, 'Favorites', ['title' => 'Book 1']);
        $shelf->addBook(2, 0, 'Favorites', ['title' => 'Book 2']);

        // Export
        $exportedJson = $shelf->exportData('Favorites');
        $this->assertJson($exportedJson);
        $decoded = json_decode($exportedJson, true);
        $this->assertEquals('Favorites', $decoded['activeShelf']);
        $this->assertCount(2, $decoded['shelves']['Favorites']);

        // Import 'new'
        $shelf->importData($exportedJson, 'new');
        $this->assertEquals('Imported', $shelf->getActiveShelfName());
        $this->assertCount(2, $shelf->getBooksInShelf('Imported'));

        // Import second 'new' -> suffix 'Imported 1'
        $shelf->importData($exportedJson, 'new');
        $this->assertEquals('Imported 1', $shelf->getActiveShelfName());
        $this->assertCount(2, $shelf->getBooksInShelf('Imported 1'));

        // Import 'replace'
        $shelf->setActiveShelf('Favorites');
        $replacePayload = [
            'activeShelf' => 'ExportedShelf',
            'shelves' => [
                'ExportedShelf' => [
                    ['id' => 999, 'db' => 0, 'title' => 'Sole Book'],
                ],
            ],
        ];
        $shelf->importData($replacePayload, 'replace');
        $booksFavorites = $shelf->getBooksInShelf('Favorites');
        $this->assertCount(1, $booksFavorites);
        $this->assertEquals(999, $booksFavorites[0]['id']);

        // Import 'merge'
        $mergePayload = [
            'activeShelf' => 'ExportedShelf',
            'shelves' => [
                'ExportedShelf' => [
                    ['id' => 999, 'db' => 0], // Duplicate, should not be added again
                    ['id' => 1000, 'db' => 0, 'title' => 'New Book'],
                ],
            ],
        ];
        $shelf->importData($mergePayload, 'merge');
        $booksFavoritesMerged = $shelf->getBooksInShelf('Favorites');
        $this->assertCount(2, $booksFavoritesMerged);
        $this->assertEquals(999, $booksFavoritesMerged[0]['id']);
        $this->assertEquals(1000, $booksFavoritesMerged[1]['id']);
    }
}
