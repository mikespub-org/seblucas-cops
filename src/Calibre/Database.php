<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\CheckHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\Response;
use Exception;
use PDO;

class Database
{
    public const KEEP_STATS = false;
    public const CALIBRE_DB_FILE = 'metadata.db';
    public const NOTES_DIR_NAME = '.calnotes';
    public const NOTES_DB_FILE = 'notes.db';
    public const NOTES_DB_NAME = 'notes_db';
    public const ROUTE_CHECK = "check";

    /** @var ?PDO */
    protected static $db = null;
    protected static ?string $dbFileName = null;
    protected static int $count = 0;
    /** @var array<string> */
    protected static $queries = [];
    /** @var bool */
    protected static $functions = false;

    /**
     * Summary of getDbStatistics
     * @return array<mixed>
     */
    public static function getDbStatistics()
    {
        return ['count' => self::$count, 'queries' => self::$queries];
    }

    /**
     * Summary of isMultipleDatabaseEnabled
     * @return bool
     */
    public static function isMultipleDatabaseEnabled()
    {
        return is_array(Config::get('calibre_directory'));
    }

    /**
     * Summary of useAbsolutePath
     * @param ?int $database
     * @return bool
     */
    public static function useAbsolutePath($database)
    {
        $path = self::getDbDirectory($database);
        return preg_match('/^\//', $path) || // Linux /
               preg_match('/^\w\:/', $path); // Windows X:
    }

    /**
     * Summary of noDatabaseSelected
     * @param ?int $database
     * @return bool
     */
    public static function noDatabaseSelected($database)
    {
        return self::isMultipleDatabaseEnabled() && is_null($database);
    }

    /**
     * Summary of getDbList
     * @return array<string, string>
     */
    public static function getDbList()
    {
        if (self::isMultipleDatabaseEnabled()) {
            return Config::get('calibre_directory');
        } else {
            return ["" => Config::get('calibre_directory')];
        }
    }

    /**
     * Summary of getDbNameList
     * @return array<string>
     */
    public static function getDbNameList()
    {
        if (self::isMultipleDatabaseEnabled()) {
            return array_keys(Config::get('calibre_directory'));
        } else {
            return [""];
        }
    }

    /**
     * Summary of getDbName
     * @param ?int $database
     * @return string
     */
    public static function getDbName($database)
    {
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            $array = array_keys(Config::get('calibre_directory'));
            return  $array[$database];
        }
        return "";
    }

    /**
     * Summary of getDbDirectory
     * @param ?int $database
     * @return string
     */
    public static function getDbDirectory($database)
    {
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            $array = array_values(Config::get('calibre_directory'));
            return  $array[$database];
        }
        return Config::get('calibre_directory');
    }

    // -DC- Add image directory
    /**
     * Summary of getImgDirectory
     * @param ?int $database
     * @return string
     */
    public static function getImgDirectory($database)
    {
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            $array = array_values(Config::get('image_directory'));
            return  $array[$database];
        }
        return Config::get('image_directory');
    }

    /**
     * Summary of getDbFileName
     * @param ?int $database
     * @return string
     */
    public static function getDbFileName($database)
    {
        return self::getDbDirectory($database) . self::CALIBRE_DB_FILE;
    }

    /**
     * Summary of error
     * @param ?int $database
     * @throws \Exception
     * @return never
     */
    protected static function error($database)
    {
        if (php_sapi_name() != "cli") {
            Response::redirect(CheckHandler::route(self::ROUTE_CHECK, ['err' => 1]));
            exit;
        }
        throw new Exception("Database <{$database}> not found.");
    }

    /**
     * Summary of getDb
     * @param ?int $database
     * @return \PDO
     */
    public static function getDb($database = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            self::$count += 1;
        }
        if (is_null(self::$db)) {
            try {
                if (is_readable(self::getDbFileName($database))) {
                    self::$db = new PDO('sqlite:' . self::getDbFileName($database));
                    self::createSqliteFunctions();
                    self::$dbFileName = self::getDbFileName($database);
                    self::$functions = false;
                } else {
                    // this will call exit()
                    self::error($database);
                }
            } catch (Exception) {
                // this will call exit()
                self::error($database);
            }
        }
        return self::$db;
    }

    /**
     * Summary of createSqliteFunctions
     * @return void
     */
    public static function createSqliteFunctions()
    {
        // Use normalized search function
        if (Translation::useNormAndUp()) {
            self::$db->sqliteCreateFunction('normAndUp', function ($s) {
                return Translation::normAndUp($s);
            }, 1);
        }
        // Check if we need to add unixepoch() for notes_db.notes
        $sql = 'SELECT sqlite_version() as version;';
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        if ($post = $stmt->fetchObject()) {
            if ($post->version >= '3.38') {
                return;
            }
        }
        // @todo no support for actual datetime conversion here
        // mtime REAL DEFAULT (unixepoch('subsec')),
        self::$db->sqliteCreateFunction('unixepoch', function ($s) {
            if (!empty($s) && $s == 'subsec') {
                return microtime(true);
            }
            return time();
        }, 1);
    }

    /**
     * Attach an sqlite database to existing db connection
     * @param string $dbFileName Database file name
     * @param string $attachDatabase
     * @throws Exception if error
     * @return void
     */
    protected static function attachDatabase($dbFileName, $attachDatabase)
    {
        // Attach the database file
        try {
            $sql = "ATTACH DATABASE '{$dbFileName}' AS {$attachDatabase};";
            $stmt = self::$db->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            $error = sprintf('Cannot attach %s database [%s]: %s', $attachDatabase, $dbFileName, $e->getMessage());
            throw new Exception($error);
        }
    }

    /**
     * Summary of addSqliteFunctions
     * @param ?int $database
     * @return void
     */
    public static function addSqliteFunctions($database)
    {
        if (self::$functions) {
            return;
        }
        self::getDb($database);
        self::$functions = true;
        // add dummy functions for selecting in meta and tag_browser_* views
        self::$db->sqliteCreateFunction('title_sort', function ($s) {
            return Format::getTitleSort($s);
        }, 1);
        self::$db->sqliteCreateFunction('books_list_filter', function ($s) {
            return 1;
        }, 1);
        self::$db->sqliteCreateAggregate('concat', function ($context, $row, $string) {
            $context ??= [];
            $context[] = $string;
            return $context;
        }, function ($context, $count) {
            $context ??= [];
            return implode(',', $context);
        }, 1);
        self::$db->sqliteCreateAggregate('sortconcat', function ($context, $row, $id, $string) {
            $context ??= [];
            $context[$id] = $string;
            return $context;
        }, function ($context, $count) {
            $context ??= [];
            sort($context);
            return implode(',', $context);
        }, 2);
    }

    /**
     * Summary of checkDatabaseAvailability
     * @param ?int $database
     * @return bool
     */
    public static function checkDatabaseAvailability($database)
    {
        if (self::noDatabaseSelected($database)) {
            for ($i = 0; $i < count(self::getDbList()); $i++) {
                self::getDb($i);
                self::clearDb();
            }
        } else {
            self::getDb($database);
        }
        return true;
    }

    /**
     * Summary of clearDb
     * @return void
     */
    public static function clearDb()
    {
        self::$db = null;
    }

    /**
     * Summary of querySingle
     * @param string $query
     * @param ?int $database
     * @return mixed
     */
    public static function querySingle($query, $database = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, $query);
        }
        return self::getDb($database)->query($query)->fetchColumn();
    }


    /**
     * Summary of query
     * @param string $query
     * @param array<mixed> $params
     * @param ?int $database
     * @return \PDOStatement
     */
    public static function query($query, $params = [], $database = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, $query);
        }
        if (count($params) > 0) {
            $result = self::getDb($database)->prepare($query);
            $result->execute($params);
        } else {
            $result = self::getDb($database)->query($query);
        }
        return $result;
    }

    /**
     * Summary of queryTotal
     * @param string $query
     * @param string $columns
     * @param string $filter
     * @param array<mixed> $params
     * @param int $n
     * @param ?int $database
     * @param ?int $numberPerPage
     * @return array{0: integer, 1: \PDOStatement}
     */
    public static function queryTotal($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, [$query, $columns, $filter]);
        }
        $totalResult = -1;

        if (Translation::useNormAndUp()) {
            $query = preg_replace("/upper/", "normAndUp", $query);
            $columns = preg_replace("/upper/", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = Config::get('max_item_per_page');
        }

        if ($numberPerPage != -1 && $n != -1) {
            // First check total number of results
            $totalResult = self::countFilter($query, 'count(*)', $filter, $params, $database);

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }
        $result = self::getDb($database)->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        return [$totalResult, $result];
    }

    /**
     * Summary of queryFilter
     * @param string $query
     * @param string $columns
     * @param string $filter
     * @param array<mixed> $params
     * @param int $n
     * @param ?int $database
     * @param ?int $numberPerPage
     * @return \PDOStatement
     */
    public static function queryFilter($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, [$query, $columns, $filter]);
        }
        if (Translation::useNormAndUp()) {
            $query = preg_replace("/upper/", "normAndUp", $query);
            $columns = preg_replace("/upper/", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = Config::get('max_item_per_page');
        }

        if ($numberPerPage != -1 && $n != -1) {
            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }

        $result = self::getDb($database)->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        return $result;
    }

    /**
     * Summary of countFilter
     * @param string $query
     * @param string $columns
     * @param string $filter
     * @param array<mixed> $params
     * @param ?int $database
     * @return integer
     */
    public static function countFilter($query, $columns = 'count(*)', $filter = '', $params = [], $database = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, [$query, $columns, $filter]);
        }
        // assuming order by ... is at the end of the query here
        $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|).*$/i', '', $query);
        $result = self::getDb($database)->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        $totalResult = $result->fetchColumn();
        return $totalResult;
    }

    /**
     * Summary of getDbSchema
     * @param ?int $database
     * @param ?string $type get table or view entries
     * @return array<mixed>
     */
    public static function getDbSchema($database = null, $type = null)
    {
        $query = 'SELECT type, name, tbl_name, rootpage, sql FROM sqlite_schema';
        $params = [];
        if (!empty($type)) {
            $query .= ' WHERE type = ?';
            $params[] = $type;
        }
        $entries = [];
        $result = self::query($query, $params, $database);
        while ($post = $result->fetchObject()) {
            $entry = (array) $post;
            array_push($entries, $entry);
        }
        return $entries;
    }

    /**
     * Summary of getTableInfo
     * @param ?int $database
     * @param string $name table or view name
     * @return array<mixed>
     */
    public static function getTableInfo($database = null, $name = 'books')
    {
        $query = "PRAGMA table_info({$name})";
        $params = [];
        $result = self::query($query, $params, $database);
        $entries = [];
        while ($post = $result->fetchObject()) {
            $entry = (array) $post;
            array_push($entries, $entry);
        }
        return $entries;
    }

    /**
     * Summary of getUserVersion
     * @param ?int $database
     * @return int
     */
    public static function getUserVersion($database = null)
    {
        $query = "PRAGMA user_version";
        $result = self::querySingle($query, $database);
        return $result;
    }

    /**
     * Get list of databases (open or attach) from SQLite
     * @param ?int $database
     * @return array<mixed>
     */
    public static function getDatabaseList($database = null)
    {
        // PRAGMA database_list;
        $sql = 'select * from pragma_database_list;';
        $stmt = self::getDb($database)->prepare($sql);
        $stmt->execute();
        $databases = [];
        while ($post = $stmt->fetchObject()) {
            $databases[$post->name] = (array) $post;
        }
        return $databases;
    }

    /**
     * Summary of hasNotes
     * @param ?int $database
     * @return bool
     */
    public static function hasNotes($database = null)
    {
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        if (file_exists(dirname(self::getDbFileName($database)) . '/' . self::NOTES_DIR_NAME . '/' . self::NOTES_DB_FILE)) {
            return true;
        }
        return false;
    }

    /**
     * Summary of getNotesDb
     * @param ?int $database
     * @return PDO|null
     */
    public static function getNotesDb($database = null)
    {
        if (!self::hasNotes($database)) {
            return null;
        }
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        $databases = self::getDatabaseList($database);
        if (!empty($databases[self::NOTES_DB_NAME])) {
            return self::getDb($database);
        }
        $notesFileName = dirname(self::getDbFileName($database)) . '/' . self::NOTES_DIR_NAME . '/' . self::NOTES_DB_FILE;
        self::attachDatabase($notesFileName, self::NOTES_DB_NAME);
        $databases = self::getDatabaseList($database);
        if (!empty($databases[self::NOTES_DB_NAME])) {
            return self::getDb($database);
        }
        return null;
    }
}
