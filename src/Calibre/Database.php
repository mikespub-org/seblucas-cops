<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Response;
use Exception;
use PDO;

class Database
{
    public const KEEP_STATS = false;
    public const CALIBRE_DB_NAME = 'metadata.db';
    public const NOTES_DIR_NAME = '.calnotes';
    public const NOTES_DB_NAME = 'notes.db';
    /** @var ?PDO */
    protected static $db = null;
    protected static ?string $dbFileName = null;
    /** @var ?PDO */
    protected static $notesDb = null;
    protected static int $count = 0;
    /** @var array<string> */
    protected static $queries = [];

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
        return static::getDbDirectory($database) . 'metadata.db';
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
            Response::redirect(Route::link("check") . "?err=1");
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
                    if (Translation::useNormAndUp()) {
                        static::$db->sqliteCreateFunction('normAndUp', function ($s) {
                            return Translation::normAndUp($s);
                        }, 1);
                    }
                    static::$dbFileName = static::getDbFileName($database);
                } else {
                    static::error($database);
                }
            } catch (Exception) {
                static::error($database);
            }
        }
        return static::$db;
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
        static::$notesDb = null;
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
            array_push(static::$queries, $query);
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
            array_push(static::$queries, $query);
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
            array_push(static::$queries, $query);
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
     * Summary of getNotesDb
     * @param ?int $database
     * @return PDO|null
     */
    public static function getNotesDb($database = null)
    {
        if (!is_null(static::$notesDb)) {
            return static::$notesDb;
        }
        static::getDb($database);
        // calibre_dir/.calnotes/notes.db
        $dbFileName = dirname((string) static::$dbFileName) . '/' . static::NOTES_DIR_NAME . '/' . static::NOTES_DB_NAME;
        if (!file_exists($dbFileName) || !is_readable($dbFileName)) {
            return null;
        }
        static::$notesDb = new PDO('sqlite:' . $dbFileName);
        return static::$notesDb;
    }
}
