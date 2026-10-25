<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestConfig;
use SebLucas\Cops\Pages\PageId;

/**
 * Server-side virtual shelves and generic book shelf model
 */
class BookShelf extends Base
{
    public const PAGE_ID = PageId::ALL_SHELVES_ID;
    public const PAGE_ALL = PageId::ALL_SHELVES;
    public const PAGE_DETAIL = PageId::SHELF_DETAIL;
    public const ROUTE_ALL = "page-shelves";
    public const ROUTE_DETAIL = "page-shelves";
    public const ROUTE_ACTION = "shelves-action";
    public const URL_PARAM = "shelf";

    public const DEFAULT_SHELF = "Favorites";
    public const DEFAULT_SORT = "title+asc";

    public const MODE_SESSION = "session";
    public const MODE_USER = "user";
    public const MODE_SHARED = "shared";

    /** @var ?string */
    public $id;
    /** @var ?array<int, array<int>> */
    public $bookIdList = null;
    public string $activeShelf = self::DEFAULT_SHELF;
    public string $sortKey = self::DEFAULT_SORT;

    /** @var array<string, array<int, array<string, mixed>>> */
    public array $shelves = [
        self::DEFAULT_SHELF => [],
    ];

    protected ?Request $request = null;
    protected ?string $filePath = null;
    protected bool $loaded = false;

    /**
     * Summary of __construct
     * @param Request|\stdClass|null $requestOrPost
     * @param ?DatabaseContext $dbContext
     */
    public function __construct($requestOrPost = null, $dbContext = null)
    {
        if ($requestOrPost instanceof Request) {
            $this->request = $requestOrPost;
            $this->config = $requestOrPost->getConfig();
            $this->dbContext = $dbContext;
            $this->databaseId = $dbContext?->getDatabase() ?? $this->request->database();
        } elseif ($requestOrPost instanceof \stdClass) {
            parent::__construct($requestOrPost, $dbContext);
        } else {
            $this->dbContext = $dbContext;
            $this->databaseId = $dbContext?->getDatabase() ?? null;
            $this->config = $dbContext?->getConfig() ?? null;
        }
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $params['shelf'] = $this->activeShelf;
        return $this->getRoute(static::ROUTE_DETAIL, $params);
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
        if ($request->getConfig() !== null) {
            $this->setConfig($request->getConfig());
        }
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * Check if server shelves feature is enabled
     */
    public function isEnabled(): bool
    {
        $mode = $this->getStorageMode();
        return in_array($mode, [self::MODE_SESSION, self::MODE_USER, self::MODE_SHARED], true);
    }

    /**
     * Static check if server shelves feature is enabled
     * @param ?RequestConfig $config
     */
    public static function isServerShelvesEnabled($config = null): bool
    {
        $mode = Config::getFrom($config, 'use_server_shelves');
        return is_string($mode) && in_array(trim($mode), [self::MODE_SESSION, self::MODE_USER, self::MODE_SHARED], true);
    }

    /**
     * Get storage mode ('session', 'user', 'shared', or '')
     */
    public function getStorageMode(): string
    {
        $mode = $this->config('use_server_shelves');
        return is_string($mode) ? trim($mode) : '';
    }

    /**
     * Get the configured shelves directory (ends with /)
     */
    public function getShelvesDirectory(): string
    {
        $dir = $this->config('shelves_directory')
            ?? (dirname(__DIR__, 2) . '/data/shelves/');

        return rtrim($dir, '/') . '/';
    }

    /**
     * Get server identifier used as salt for user/session filename obfuscation
     */
    public function getServerId(): string
    {
        return $this->config('server_id')
            ?? $this->request?->server('SERVER_NAME')
            ?? 'cops';
    }

    /**
     * Get current user identifier
     */
    public function getUserIdentifier(): ?string
    {
        $user = $this->request?->getUserName()
            ?? $this->request?->getSession()?->get('user')
            ?? null;

        return !empty($user) ? (string) $user : null;
    }

    /**
     * Get current session identifier
     */
    public function getSessionIdentifier(): ?string
    {
        $session = $this->request?->getSession();
        if ($session) {
            return $session->getId();
        }
        return null;
    }

    /**
     * Resolve the JSON storage filename
     */
    public function getStorageFilename(): ?string
    {
        $mode = $this->getStorageMode();
        if (empty($mode)) {
            return null;
        }

        if ($mode === self::MODE_SHARED) {
            return 'shared.json';
        }

        $serverId = $this->getServerId();

        if ($mode === self::MODE_USER) {
            $user = $this->getUserIdentifier();
            if (empty($user)) {
                return null;
            }
            return md5($user . $serverId) . '.json';
        }

        if ($mode === self::MODE_SESSION) {
            $sessionId = $this->getSessionIdentifier();
            if (empty($sessionId)) {
                return null;
            }
            return md5($sessionId . $serverId) . '.json';
        }

        return null;
    }

    /**
     * Get full filesystem path to the storage file
     */
    public function getFilePath(): ?string
    {
        if ($this->filePath !== null) {
            return $this->filePath;
        }

        $filename = $this->getStorageFilename();
        if (empty($filename)) {
            return null;
        }

        return $this->getShelvesDirectory() . $filename;
    }

    /**
     * Load shelves from file storage
     */
    public function load(bool $force = false): bool
    {
        if ($this->loaded && !$force) {
            return true;
        }

        $filePath = $this->getFilePath();
        if (empty($filePath) || !file_exists($filePath)) {
            $this->activeShelf = self::DEFAULT_SHELF;
            $this->sortKey = self::DEFAULT_SORT;
            $this->shelves = [self::DEFAULT_SHELF => []];
            $this->loaded = true;
            return false;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return false;
        }

        $this->activeShelf = !empty($data['activeShelf']) ? (string) $data['activeShelf'] : self::DEFAULT_SHELF;
        $this->sortKey = !empty($data['sortKey']) ? (string) $data['sortKey'] : self::DEFAULT_SORT;
        $this->shelves = (isset($data['shelves']) && is_array($data['shelves'])) ? $data['shelves'] : [];

        if (empty($this->shelves)) {
            $this->shelves[self::DEFAULT_SHELF] = [];
        }

        if (!array_key_exists($this->activeShelf, $this->shelves)) {
            $this->activeShelf = (string) array_key_first($this->shelves);
        }

        $this->loaded = true;
        return true;
    }

    /**
     * Save shelves to file storage with atomic rename
     */
    public function save(): bool
    {
        $filePath = $this->getFilePath();
        if (empty($filePath)) {
            return false;
        }

        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0o750, true) && !is_dir($dir)) {
                return false;
            }
        }

        $htaccess = rtrim($dir, '/') . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Deny from all\n");
        }

        $data = [
            'activeShelf' => $this->activeShelf,
            'sortKey' => $this->sortKey,
            'shelves' => $this->shelves,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $tempFile = $filePath . '.' . bin2hex(random_bytes(6)) . '.tmp';
        if (file_put_contents($tempFile, $json, LOCK_EX) === false) {
            return false;
        }

        return rename($tempFile, $filePath);
    }

    /**
     * Normalize database identifier: null, '', 0 and '0' are equivalent to ''
     */
    public static function normalizeDb(mixed $db): string
    {
        if ($db === null || $db === '' || $db === 0 || $db === '0') {
            return '';
        }
        return (string) $db;
    }

    /**
     * Generate unique book key "db:id"
     */
    public static function makeBookKey(mixed $bookId, mixed $db = 0): string
    {
        return self::normalizeDb($db) . ':' . $bookId;
    }

    /**
     * Extract unique book key "db:id" from a book array or object
     */
    public static function getBookKey(mixed $book): string
    {
        if (is_array($book)) {
            $id = $book['id'] ?? '';
            $db = $book['db'] ?? '';
        } elseif (is_object($book)) {
            $id = $book->id ?? '';
            $db = method_exists($book, 'getDatabaseId') ? $book->getDatabaseId() : ($book->db ?? '');
        } else {
            return '';
        }
        return self::normalizeDb($db) . ':' . $id;
    }

    /**
     * Get all shelf names
     * @return array<string>
     */
    public function getShelfNames(): array
    {
        $this->load();
        return array_keys($this->shelves);
    }

    /**
     * Get shelves map with book counts: [shelfName => count, ...]
     * @return array<string, int>
     */
    public function getShelves(): array
    {
        $this->load();
        $result = [];
        foreach ($this->shelves as $name => $books) {
            $result[$name] = count($books);
        }
        return $result;
    }

    /**
     * Check if a shelf exists
     */
    public function hasShelf(string $name): bool
    {
        $this->load();
        return array_key_exists($name, $this->shelves);
    }

    /**
     * Get active shelf name
     */
    public function getActiveShelfName(): string
    {
        $this->load();
        return $this->activeShelf;
    }

    /**
     * Set active shelf by name
     */
    public function setActiveShelf(string $name): bool
    {
        $this->load();
        if (array_key_exists($name, $this->shelves)) {
            $this->activeShelf = $name;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Create a new empty shelf
     */
    public function createShelf(string $name): bool
    {
        $this->load();
        $name = trim($name);
        if ($name === '' || array_key_exists($name, $this->shelves)) {
            return false;
        }
        $this->shelves[$name] = [];
        $this->save();
        return true;
    }

    /**
     * Rename a shelf
     */
    public function renameShelf(string $oldName, string $newName): bool
    {
        $this->load();
        $newName = trim($newName);
        if ($newName === '' || !array_key_exists($oldName, $this->shelves) || array_key_exists($newName, $this->shelves)) {
            return false;
        }

        $this->shelves[$newName] = $this->shelves[$oldName];
        unset($this->shelves[$oldName]);

        if ($this->activeShelf === $oldName) {
            $this->activeShelf = $newName;
        }

        $this->save();
        return true;
    }

    /**
     * Delete a shelf (cannot delete if it is the only shelf)
     */
    public function deleteShelf(string $name): bool
    {
        $this->load();
        if (!array_key_exists($name, $this->shelves) || count($this->shelves) <= 1) {
            return false;
        }

        unset($this->shelves[$name]);

        if ($this->activeShelf === $name) {
            $this->activeShelf = (string) array_key_first($this->shelves);
        }

        $this->save();
        return true;
    }

    /**
     * Resolve shelf name from either exact name or md5 hash
     */
    public function resolveShelfName(?string $shelfId): ?string
    {
        $this->load();
        if (empty($shelfId)) {
            return $this->activeShelf;
        }
        if (array_key_exists($shelfId, $this->shelves)) {
            return $shelfId;
        }
        foreach (array_keys($this->shelves) as $name) {
            if (md5($name) === $shelfId) {
                return $name;
            }
        }
        return null;
    }

    /**
     * Get books in a shelf (or active shelf if null)
     * @return array<int, array<string, mixed>>
     */
    public function getBooksInShelf(?string $shelfName = null): array
    {
        $this->load();
        $name = $shelfName ?? $this->activeShelf;
        return $this->shelves[$name] ?? [];
    }

    /**
     * Check if a book is in a shelf (or active shelf if null)
     */
    public function isInShelf(mixed $bookId, mixed $db = 0, ?string $shelfName = null): bool
    {
        $books = $this->getBooksInShelf($shelfName);
        $targetKey = self::makeBookKey($bookId, $db);

        foreach ($books as $book) {
            if (self::getBookKey($book) === $targetKey) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a book to a shelf (or active shelf if null)
     * @param array<string, mixed> $extra
     */
    public function addBook(mixed $bookId, mixed $db = 0, ?string $shelfName = null, array $extra = []): bool
    {
        $this->load();
        $name = $shelfName ?? $this->activeShelf;
        if (!array_key_exists($name, $this->shelves)) {
            $this->shelves[$name] = [];
        }

        $targetKey = self::makeBookKey($bookId, $db);
        foreach ($this->shelves[$name] as $book) {
            if (self::getBookKey($book) === $targetKey) {
                return false;
            }
        }

        $entry = array_merge([
            'id' => (int) $bookId,
            'db' => (int) $db,
            'added' => time(),
        ], $extra);

        $this->shelves[$name][] = $entry;
        $this->save();
        return true;
    }

    /**
     * Remove a book from a shelf (or active shelf if null)
     */
    public function removeBook(mixed $bookId, mixed $db = 0, ?string $shelfName = null): bool
    {
        $this->load();
        $name = $shelfName ?? $this->activeShelf;
        if (!array_key_exists($name, $this->shelves)) {
            return false;
        }

        $targetKey = self::makeBookKey($bookId, $db);
        $foundIndex = -1;

        foreach ($this->shelves[$name] as $index => $book) {
            if (self::getBookKey($book) === $targetKey) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex > -1) {
            array_splice($this->shelves[$name], $foundIndex, 1);
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Toggle a book in a shelf (or active shelf if null).
     * Returns true if book was added, false if removed.
     * @param array<string, mixed> $extra
     */
    public function toggleBook(mixed $bookId, mixed $db = 0, ?string $shelfName = null, array $extra = []): bool
    {
        if ($this->isInShelf($bookId, $db, $shelfName)) {
            $this->removeBook($bookId, $db, $shelfName);
            return false;
        }

        $this->addBook($bookId, $db, $shelfName, $extra);
        return true;
    }

    /**
     * Get book IDs grouped by database id: [dbId => [bookId, ...]]
     * @return array<int, array<int>>
     */
    public function getIdListGrouped(?string $shelfId = null): array
    {
        $this->load();
        $name = $this->resolveShelfName($shelfId) ?? $this->activeShelf;
        $books = $this->getBooksInShelf($name);

        $idlist = [];
        foreach ($books as $book) {
            $db = (int) ($book['db'] ?? 0);
            $id = (int) ($book['id'] ?? 0);
            if ($id > 0) {
                $idlist[$db][] = $id;
            }
        }

        return $idlist;
    }

    /**
     * Static helper to get idlist for a shelf (resolves shelf name or md5 hash)
     * @param ?string $shelfId
     * @param ?Request $request
     * @return array<int, array<int>>
     */
    public static function getIdListForShelf($shelfId = null, $request = null)
    {
        $className = static::class;
        $instance = new $className($request);
        return $instance->getIdListGrouped($shelfId);
    }

    /**
     * Export shelf data as a JSON string matching client-side export format
     */
    public function exportData(?string $shelfName = null): string
    {
        $this->load();
        $name = $shelfName ?? $this->activeShelf;
        $exportData = [
            'activeShelf' => $name,
            'shelves' => [
                $name => $this->getBooksInShelf($name),
            ],
        ];

        return json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    /**
     * Import shelf data from a JSON string or array.
     * Modes:
     * - 'replace': replaces books in active shelf
     * - 'new': creates a new "Imported" shelf (with numeric suffix if needed)
     * - 'merge': adds books to active shelf skipping duplicates (default)
     * @param string|array<mixed> $jsonOrData
     */
    public function importData(string|array $jsonOrData, string $mode = 'merge'): bool
    {
        $this->load();

        $data = is_array($jsonOrData) ? $jsonOrData : json_decode($jsonOrData, true);
        if (!is_array($data) || empty($data['shelves']) || !is_array($data['shelves'])) {
            return false;
        }

        $sourceShelf = !empty($data['activeShelf']) ? (string) $data['activeShelf'] : (string) array_key_first($data['shelves']);
        $importedBooks = $data['shelves'][$sourceShelf] ?? [];
        if (!is_array($importedBooks)) {
            return false;
        }

        if ($mode === 'new') {
            $newName = 'Imported';
            $counter = 1;
            while (array_key_exists($newName, $this->shelves)) {
                $newName = 'Imported ' . $counter;
                $counter++;
            }
            $this->shelves[$newName] = $importedBooks;
            $this->activeShelf = $newName;
        } elseif ($mode === 'merge') {
            $active = $this->activeShelf;
            if (!isset($this->shelves[$active])) {
                $this->shelves[$active] = [];
            }
            foreach ($importedBooks as $book) {
                $key = self::getBookKey($book);
                $exists = false;
                foreach ($this->shelves[$active] as $existing) {
                    if (self::getBookKey($existing) === $key) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $this->shelves[$active][] = $book;
                }
            }
        } else {
            // 'replace'
            $active = $this->activeShelf;
            $this->shelves[$active] = $importedBooks;
        }

        return $this->save();
    }
}
