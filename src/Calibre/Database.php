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
use SebLucas\Cops\Input\Route;
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
        return ['count' => static::$count, 'queries' => static::$queries];
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
        $path = static::getDbDirectory($database);
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
        return static::isMultipleDatabaseEnabled() && is_null($database);
    }

    /**
     * Summary of getDbList
     * @return array<string, string>
     */
    public static function getDbList()
    {
        if (static::isMultipleDatabaseEnabled()) {
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
        if (static::isMultipleDatabaseEnabled()) {
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
        if (static::isMultipleDatabaseEnabled()) {
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
        if (static::isMultipleDatabaseEnabled()) {
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
        if (static::isMultipleDatabaseEnabled()) {
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
        return static::getDbDirectory($database) . static::CALIBRE_DB_FILE;
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
            Response::redirect(CheckHandler::getLink(['err' => 1]));
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
        if (static::KEEP_STATS) {
            static::$count += 1;
        }
        if (is_null(static::$db)) {
            try {
                if (is_readable(static::getDbFileName($database))) {
                    static::$db = new PDO('sqlite:' . static::getDbFileName($database));
                    static::createSqliteFunctions();
                    static::$dbFileName = static::getDbFileName($database);
                    static::$functions = false;
                } else {
                    // this will call exit()
                    static::error($database);
                }
            } catch (Exception) {
                // this will call exit()
                static::error($database);
            }
        }
        return static::$db;
    }

    /**
     * Summary of createSqliteFunctions
     * @return void
     */
    public static function createSqliteFunctions()
    {
        // Use normalized search function
        if (Translation::useNormAndUp()) {
            static::$db->sqliteCreateFunction('normAndUp', function ($s) {
                return Translation::normAndUp($s);
            }, 1);
        }
        // Check if we need to add unixepoch() for notes_db.notes
        $sql = 'SELECT sqlite_version() as version;';
        $stmt = static::$db->prepare($sql);
        $stmt->execute();
        if ($post = $stmt->fetchObject()) {
            if ($post->version >= '3.38') {
                return;
            }
        }
        // @todo no support for actual datetime conversion here
        // mtime REAL DEFAULT (unixepoch('subsec')),
        static::$db->sqliteCreateFunction('unixepoch', function ($s) {
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
            $stmt = static::$db->prepare($sql);
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
        if (static::$functions) {
            return;
        }
        static::getDb($database);
        static::$functions = true;
        // add dummy functions for selecting in meta and tag_browser_* views
        static::$db->sqliteCreateFunction('title_sort', function ($s) {
            return Format::getTitleSort($s);
        }, 1);
        static::$db->sqliteCreateFunction('books_list_filter', function ($s) {
            return 1;
        }, 1);
        static::$db->sqliteCreateAggregate('concat', function ($context, $row, $string) {
            $context ??= [];
            $context[] = $string;
            return $context;
        }, function ($context, $count) {
            $context ??= [];
            return implode(',', $context);
        }, 1);
        static::$db->sqliteCreateAggregate('sortconcat', function ($context, $row, $id, $string) {
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
        if (static::noDatabaseSelected($database)) {
            for ($i = 0; $i < count(static::getDbList()); $i++) {
                static::getDb($i);
                static::clearDb();
            }
        } else {
            static::getDb($database);
        }
        return true;
    }

    /**
     * Summary of clearDb
     * @return void
     */
    public static function clearDb()
    {
        static::$db = null;
    }

    /**
     * Summary of querySingle
     * @param string $query
     * @param ?int $database
     * @return mixed
     */
    public static function querySingle($query, $database = null)
    {
        if (static::KEEP_STATS) {
            array_push(static::$queries, $query);
        }
        return static::getDb($database)->query($query)->fetchColumn();
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
        if (static::KEEP_STATS) {
            array_push(static::$queries, $query);
        }
        if (count($params) > 0) {
            $result = static::getDb($database)->prepare($query);
            $result->execute($params);
        } else {
            $result = static::getDb($database)->query($query);
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
        if (static::KEEP_STATS) {
            array_push(static::$queries, [$query, $columns, $filter]);
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
            $totalResult = static::countFilter($query, 'count(*)', $filter, $params, $database);

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }
        $result = static::getDb($database)->prepare(str_format($query, $columns, $filter));
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
        if (static::KEEP_STATS) {
            array_push(static::$queries, [$query, $columns, $filter]);
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

        $result = static::getDb($database)->prepare(str_format($query, $columns, $filter));
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
        if (static::KEEP_STATS) {
            array_push(static::$queries, [$query, $columns, $filter]);
        }
        // assuming order by ... is at the end of the query here
        $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|).*$/i', '', $query);
        $result = static::getDb($database)->prepare(str_format($query, $columns, $filter));
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
        $result = static::query($query, $params, $database);
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
        $result = static::query($query, $params, $database);
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
        $result = static::querySingle($query, $database);
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
        $stmt = static::getDb($database)->prepare($sql);
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
        if (file_exists(dirname(static::getDbFileName($database)) . '/' . static::NOTES_DIR_NAME . '/' . static::NOTES_DB_FILE)) {
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
        if (!static::hasNotes($database)) {
            return null;
        }
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        $databases = static::getDatabaseList($database);
        if (!empty($databases[static::NOTES_DB_NAME])) {
            return static::getDb($database);
        }
        $notesFileName = dirname(static::getDbFileName($database)) . '/' . static::NOTES_DIR_NAME . '/' . static::NOTES_DB_FILE;
        static::attachDatabase($notesFileName, static::NOTES_DB_NAME);
        $databases = static::getDatabaseList($database);
        if (!empty($databases[static::NOTES_DB_NAME])) {
            return static::getDb($database);
        }
        return null;
    }
}
