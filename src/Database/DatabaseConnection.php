<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Database;

use SebLucas\Cops\Input\HasConfigTrait;
use SebLucas\Cops\Input\RequestConfig;
use SebLucas\Cops\Language\Normalizer;
use Exception;
use Pdo\Sqlite;

/**
 * Move actual database methods from static Database methods
 * to DatabaseConnection instance methods for each sqlite db
 */
class DatabaseConnection
{
    use HasConfigTrait;

    protected string $dbFileName;
    protected ?int $database;
    protected ?Sqlite $db = null;
    protected bool $functions = false;
    protected ?bool $notesDb = null;

    public function __construct(string $dbFileName, ?RequestConfig $config = null, ?int $database = null)
    {
        $this->dbFileName = $dbFileName;
        $this->config = $config;
        $this->database = $database;
    }

    /**
     * Summary of getDb
     * @throws \SebLucas\Cops\Database\DatabaseException
     */
    public function getDb(): Sqlite
    {
        if ($this->db === null) {
            try {
                if (!is_readable($this->dbFileName)) {
                    throw new DatabaseException("Database <{$this->database}> not found or not readable.");
                }
                $this->db = new Sqlite('sqlite:' . $this->dbFileName);
                $this->createSqliteFunctions();
            } catch (Exception) {
                throw new DatabaseException("Database <{$this->database}> not found.");
            }
        }
        return $this->db;
    }

    /**
     * Summary of getDbFileName
     */
    public function getDbFileName(): string
    {
        return $this->dbFileName;
    }

    /**
     * Summary of clear
     */
    public function clear(): void
    {
        $this->db = null;
        $this->functions = false;
        $this->notesDb = null;
    }

    /**
     * Summary of addSqliteFunctions
     */
    public function addSqliteFunctions(): void
    {
        if ($this->functions) {
            return;
        }
        $db = $this->getDb();
        if (!in_array('series', $this->config('calibre_categories_using_hierarchy', []))) {
            $db->createFunction('title_sort', fn($s) => Normalizer::getTitleSort($s), 1);
        }
        $db->createFunction('books_list_filter', fn($s) => 1, 1);
        $db->createAggregate('concat', function ($context, $string) {
            $context ??= [];
            $context[] = $string;
            return $context;
        }, function ($context) {
            $context ??= [];
            return implode(',', $context);
        }, 1);
        $db->createAggregate('sortconcat', function ($context, $id, $string) {
            $context ??= [];
            $context[$id] = $string;
            return $context;
        }, function ($context) {
            $context ??= [];
            sort($context);
            return implode(',', $context);
        }, 2);
        $this->functions = true;
    }

    /**
     * Summary of createSqliteFunctions
     */
    protected function createSqliteFunctions(): void
    {
        $db = $this->db;
        if (!$db) {
            return;
        }

        if (Normalizer::useNormAndUp($this->config)) {
            $db->createFunction('normAndUp', fn($s) => Normalizer::normAndUp($s), 1);
        }
        if (in_array('series', $this->config('calibre_categories_using_hierarchy', []))) {
            $db->createFunction('title_sort', fn($s) => Normalizer::getTitleSort($s), 1);
        }

        if (!empty($this->config('search_strip_html', 0))) {
            $db->createFunction('strip_html', fn($s) => preg_replace('/<[^>]*>/', ' ', $s), 1);
        } else {
            // no-op
            $db->createFunction('strip_html', fn($s) => $s, 1);
        }

        $sql = 'SELECT sqlite_version() as version;';
        $stmt = $db->prepare($sql);
        $stmt->execute();
        if ($post = $stmt->fetchObject()) {
            if ($post->version >= '3.38') {
                return;
            }
        }

        $db->createFunction('unixepoch', function ($s) {
            if (!empty($s) && $s === 'subsec') {
                return microtime(true);
            }
            return time();
        }, 1);
    }

    /**
     * Attach an sqlite database to existing db connection
     */
    public function attachDatabase(string $dbFileName, string $attachDatabase): void
    {
        $db = $this->getDb();
        try {
            $sql = "ATTACH DATABASE '{$dbFileName}' AS {$attachDatabase};";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            $error = sprintf('Cannot attach %s database [%s]: %s', $attachDatabase, $dbFileName, $e->getMessage());
            throw new Exception($error);
        }
    }

    /**
     * Query and return a single value
     */
    public function querySingle(string $query): mixed
    {
        return $this->getDb()->query($query)->fetchColumn();
    }

    /**
     * Summary of query
     * @param array<mixed> $params
     */
    public function query(string $query, array $params = []): \PDOStatement
    {
        $db = $this->getDb();
        if (count($params) > 0) {
            $result = $db->prepare($query);
            $result->execute($params);
        } else {
            $result = $db->query($query);
        }
        return $result;
    }

    /**
     * Summary of queryTotal
     * @param array<mixed> $params
     * @return array{0: integer, 1: \PDOStatement}
     */
    public function queryTotal(string $query, string $columns, string $filter, array $params, int $n, ?int $numberPerPage = null): array
    {
        $totalResult = -1;

        // Normalizer::useNormAndUp() and default max_item_per_page in calling method

        if ($numberPerPage != -1 && $n != -1) {
            // First check total number of results
            $totalResult = $this->countFilter($query, 'count(*)', $filter, $params);

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }
        $db = $this->getDb();
        $result = $db->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        return [$totalResult, $result];
    }

    /**
     * Summary of queryFilter
     * @param array<mixed> $params
     */
    public function queryFilter(string $query, string $columns, string $filter, array $params, int $n, ?int $numberPerPage = null): \PDOStatement
    {
        // Normalizer::useNormAndUp() and default max_item_per_page in calling method

        if ($numberPerPage != -1 && $n != -1) {
            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }

        $db = $this->getDb();
        $result = $db->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        return $result;
    }

    /**
     * Summary of countFilter
     * @param array<mixed> $params
     */
    public function countFilter(string $query, string $columns = 'count(*)', string $filter = '', array $params = []): int
    {
        // assuming order by ... is at the end of the query here
        $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|).*$/i', '', $query);
        $db = $this->getDb();
        $result = $db->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        $totalResult = (int) $result->fetchColumn();
        return $totalResult;
    }

    /**
     * Summary of getDbSchema
     * @param ?string $type get table or view entries
     * @return array<mixed>
     */
    public function getDbSchema(?string $type = null): array
    {
        $query = 'SELECT type, name, tbl_name, rootpage, sql FROM sqlite_schema';
        $params = [];
        if (!empty($type)) {
            $query .= ' WHERE type = ?';
            $params[] = $type;
        }
        $entries = [];
        $result = $this->query($query, $params);
        while ($post = $result->fetchObject()) {
            $entry = (array) $post;
            array_push($entries, $entry);
        }
        return $entries;
    }

    /**
     * Summary of getTableInfo
     * @param string $name table or view name
     * @return array<mixed>
     */
    public function getTableInfo(string $name = 'books'): array
    {
        $query = "PRAGMA table_info({$name})";
        $params = [];
        $result = $this->query($query, $params);
        $entries = [];
        while ($post = $result->fetchObject()) {
            $entry = (array) $post;
            array_push($entries, $entry);
        }
        return $entries;
    }

    /**
     * Summary of getUserVersion
     */
    public function getUserVersion(): int
    {
        $query = "PRAGMA user_version";
        $result = (int) $this->querySingle($query);
        return $result;
    }

    /**
     * Get list of databases (open or attach) from SQLite
     * @return array<string, mixed>
     */
    public function getDatabaseList(): array
    {
        $sql = 'select * from pragma_database_list;';
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute();
        $databases = [];
        while ($post = $stmt->fetchObject()) {
            $databases[$post->name] = (array) $post;
        }
        return $databases;
    }

    /**
     * Summary of hasNotes
     */
    protected function hasNotes(): bool
    {
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        if (file_exists($this->getNotesFileName())) {
            return true;
        }
        return false;
    }

    /**
     * Summary of getNotesFileName
     */
    protected function getNotesFileName(): string
    {
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        return dirname($this->getDbFileName()) . '/' . Database::NOTES_DIR_NAME . '/' . Database::NOTES_DB_FILE;
    }

    /**
     * Summary of getNotesDb
     */
    public function getNotesDb(): ?Sqlite
    {
        if (!is_null($this->notesDb)) {
            return $this->notesDb ? $this->getDb() : null;
        }
        $this->notesDb = false;
        if (!$this->hasNotes()) {
            return null;
        }
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        $databases = $this->getDatabaseList();
        if (!empty($databases[Database::NOTES_DB_NAME])) {
            $this->notesDb = true;
            return $this->getDb();
        }
        $notesFileName = $this->getNotesFileName();
        $this->attachDatabase($notesFileName, Database::NOTES_DB_NAME);
        $databases = $this->getDatabaseList();
        if (!empty($databases[Database::NOTES_DB_NAME])) {
            $this->notesDb = true;
            return $this->getDb();
        }
        return null;
    }
}
