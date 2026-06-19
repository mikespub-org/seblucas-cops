<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\CheckHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\RequestConfig;
use SebLucas\Cops\Language\Normalizer;
use SebLucas\Cops\Output\Response;
use Exception;

/**
 * Keep Database static methods as legacy + provide mapping
 * from $database + $config to actual database connection
 */
class Database
{
    public const KEEP_STATS = false;
    public const CALIBRE_DB_FILE = 'metadata.db';
    public const NOTES_DIR_NAME = '.calnotes';
    public const NOTES_DB_FILE = 'notes.db';
    public const NOTES_DB_NAME = 'notes_db';
    public const ROUTE_CHECK = "check";

    /** @var array<string, DatabaseConnection> */
    protected static array $connections = [];
    protected static int $count = 0;
    /** @var array<string> */
    protected static $queries = [];

    /**
     * Summary of getDbStatistics
     * @return array<mixed>
     */
    public static function getDbStatistics()
    {
        return ['count' => self::$count, 'queries' => self::$queries];
    }

    /**
     * Summary of getConnectionKey
     * @param string $dbFileName
     * @return string
     */
    protected static function getConnectionKey(string $dbFileName): string
    {
        return $dbFileName;
    }

    /**
     * Summary of getConnection
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return DatabaseConnection
     */
    public static function getConnection($database = null, $config = null)
    {
        $dbFileName = self::getDbFileName($database, $config);
        $key = self::getConnectionKey($dbFileName);
        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = new DatabaseConnection($dbFileName, $config, $database);
        }
        return self::$connections[$key];
    }

    /**
     * Summary of isMultipleDatabaseEnabled
     * @param ?RequestConfig $config
     * @return bool
     */
    public static function isMultipleDatabaseEnabled($config = null)
    {
        return is_array(Config::getFrom($config, 'calibre_directory', null));
    }

    /**
     * Summary of findDatabaseId
     * @param string $dbName
     * @param ?RequestConfig $config
     * @return int|null
     */
    public static function findDatabaseId($dbName, $config = null)
    {
        if (!self::isMultipleDatabaseEnabled($config)) {
            return null;
        }
        $array = array_keys(Config::getFrom($config, 'calibre_directory', []));
        $database = array_search($dbName, $array);
        if ($database === false || !is_numeric($database)) {
            return null;
        }
        return (int) $database;
    }

    /**
     * Summary of useAbsolutePath
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return bool
     */
    public static function useAbsolutePath($database, $config = null)
    {
        $path = self::getDbDirectory($database, $config);
        return preg_match('/^\//', $path) // Linux /
               || preg_match('/^\w\:/', $path); // Windows X:
    }

    /**
     * Summary of noDatabaseSelected
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return bool
     */
    public static function noDatabaseSelected($database, $config = null)
    {
        return self::isMultipleDatabaseEnabled($config) && is_null($database);
    }

    /**
     * Summary of getDbList
     * @param ?RequestConfig $config
     * @return array<string, string>
     */
    public static function getDbList($config = null)
    {
        if (self::isMultipleDatabaseEnabled($config)) {
            return Config::getFrom($config, 'calibre_directory', []);
        } else {
            return ["" => Config::getFrom($config, 'calibre_directory', '')];
        }
    }

    /**
     * Summary of getDbNameList
     * @param ?RequestConfig $config
     * @return array<string>
     */
    public static function getDbNameList($config = null)
    {
        if (self::isMultipleDatabaseEnabled($config)) {
            return array_keys(Config::getFrom($config, 'calibre_directory', []));
        } else {
            return [""];
        }
    }

    /**
     * Summary of getDbName
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return string
     */
    public static function getDbName($database, $config = null)
    {
        if (self::isMultipleDatabaseEnabled($config)) {
            if (is_null($database)) {
                $database = 0;
            }
            $array = array_keys(Config::getFrom($config, 'calibre_directory', []));
            return  $array[$database];
        }
        return "";
    }

    /**
     * Summary of getDbDirectory
     * @param ?int $database
     * @throws Exception if error
     * @param ?RequestConfig $config
     * @return string
     */
    public static function getDbDirectory($database, $config = null)
    {
        if (self::isMultipleDatabaseEnabled($config)) {
            if (is_null($database)) {
                $database = 0;
            }
            $array = array_values(Config::getFrom($config, 'calibre_directory', []));
            if ($database > count($array) - 1) {
                throw new Exception("Database <{$database}> not found.");
            }
            return  $array[$database];
        }
        return Config::getFrom($config, 'calibre_directory', '');
    }

    // -DC- Add image directory
    /**
     * Summary of getImgDirectory
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return string
     */
    public static function getImgDirectory($database, $config = null)
    {
        if (self::isMultipleDatabaseEnabled($config)) {
            if (is_null($database)) {
                $database = 0;
            }
            $array = array_values(Config::getFrom($config, 'image_directory', []));
            return  $array[$database];
        }
        return Config::getFrom($config, 'image_directory', '');
    }

    /**
     * Summary of getDbFileName
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return string
     */
    public static function getDbFileName($database, $config = null)
    {
        return self::getDbDirectory($database, $config) . self::CALIBRE_DB_FILE;
    }

    /**
     * Summary of getLastModified
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return string
     */
    public static function getLastModified($database, $config = null)
    {
        $fileName = self::getDbFileName($database, $config);
        return date(DATE_ATOM, filemtime($fileName));
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
            $response = Response::redirect(CheckHandler::route(self::ROUTE_CHECK, ['err' => 1]));
            $response->send();
            exit;
        }
        throw new Exception("Database <{$database}> not found");
    }

    /**
     * Summary of getDb
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return \Pdo\Sqlite
     */
    public static function getDb($database = null, $config = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            self::$count += 1;
        }
        return self::getConnection($database, $config)->getDb();
    }

    /**
     * Summary of createSqliteFunctions
     * @param ?RequestConfig $config
     * @return void
     */
    public static function createSqliteFunctions($config = null)
    {
        // Keep compatibility for callers that still invoke the static helper.
        $connection = self::getConnection(null, $config);
        $connection->getDb();
    }

    /**
     * Attach an sqlite database to existing db connection
     * @param string $dbFileName Database file name
     * @param string $attachDatabase
     * @param ?int $database
     * @param ?RequestConfig $config
     * @throws Exception if error
     * @return void
     */
    protected static function attachDatabase($dbFileName, $attachDatabase, $database = null, $config = null)
    {
        // Attach the database file
        try {
            $sql = "ATTACH DATABASE '{$dbFileName}' AS {$attachDatabase};";
            $stmt = self::getConnection($database, $config)->getDb()->prepare($sql);
            $stmt->execute();
        } catch (Exception $e) {
            $error = sprintf('Cannot attach %s database [%s]: %s', $attachDatabase, $dbFileName, $e->getMessage());
            throw new Exception($error);
        }
    }

    /**
     * Summary of addSqliteFunctions
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return void
     */
    public static function addSqliteFunctions($database, $config = null)
    {
        self::getConnection($database, $config)->addSqliteFunctions();
    }

    /**
     * Summary of checkDatabaseAvailability
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return bool
     */
    public static function checkDatabaseAvailability($database, $config = null)
    {
        if (self::noDatabaseSelected($database, $config)) {
            for ($i = 0; $i < count(self::getDbList($config)); $i++) {
                self::getDb($i, $config);
                self::clearDb();
            }
        } else {
            self::getDb($database, $config);
        }
        return true;
    }

    /**
     * Summary of clearDb
     * @return void
     */
    public static function clearDb()
    {
        foreach (self::$connections as $connection) {
            $connection->clear();
        }
        self::$connections = [];
    }

    /**
     * Summary of querySingle
     * @param string $query
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return mixed
     */
    public static function querySingle($query, $database = null, $config = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, $query);
        }
        return self::getConnection($database, $config)->getDb()->query($query)->fetchColumn();
    }


    /**
     * Summary of query
     * @param string $query
     * @param array<mixed> $params
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return \PDOStatement
     */
    public static function query($query, $params = [], $database = null, $config = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, $query);
        }
        $db = self::getConnection($database, $config)->getDb();
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
     * @param string $query
     * @param string $columns
     * @param string $filter
     * @param array<mixed> $params
     * @param int $n
     * @param ?int $database
     * @param ?int $numberPerPage
     * @param ?RequestConfig $config
     * @return array{0: integer, 1: \PDOStatement}
     */
    public static function queryTotal($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null, $config = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, [$query, $columns, $filter]);
        }
        $totalResult = -1;

        if (Normalizer::useNormAndUp($config)) {
            $query = str_replace("upper", "normAndUp", $query);
            $columns = str_replace("upper", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = Config::getFrom($config, 'max_item_per_page');
        }

        if ($numberPerPage != -1 && $n != -1) {
            // First check total number of results
            $totalResult = self::countFilter($query, 'count(*)', $filter, $params, $database, $config);

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }
        $db = self::getConnection($database, $config)->getDb();
        $result = $db->prepare(str_format($query, $columns, $filter));
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
     * @param ?RequestConfig $config
     * @return \PDOStatement
     */
    public static function queryFilter($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null, $config = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, [$query, $columns, $filter]);
        }
        if (Normalizer::useNormAndUp($config)) {
            $query = str_replace("upper", "normAndUp", $query);
            $columns = str_replace("upper", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = Config::getFrom($config, 'max_item_per_page');
        }

        if ($numberPerPage != -1 && $n != -1) {
            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }

        $db = self::getConnection($database, $config)->getDb();
        $result = $db->prepare(str_format($query, $columns, $filter));
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
     * @param ?RequestConfig $config
     * @return integer
     */
    public static function countFilter($query, $columns = 'count(*)', $filter = '', $params = [], $database = null, $config = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, [$query, $columns, $filter]);
        }
        // assuming order by ... is at the end of the query here
        $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|).*$/i', '', $query);
        $db = self::getConnection($database, $config)->getDb();
        $result = $db->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        $totalResult = $result->fetchColumn();
        return $totalResult;
    }

    /**
     * Summary of getDbSchema
     * @param ?int $database
     * @param ?string $type get table or view entries
     * @param ?RequestConfig $config
     * @return array<mixed>
     */
    public static function getDbSchema($database = null, $type = null, $config = null)
    {
        $query = 'SELECT type, name, tbl_name, rootpage, sql FROM sqlite_schema';
        $params = [];
        if (!empty($type)) {
            $query .= ' WHERE type = ?';
            $params[] = $type;
        }
        $entries = [];
        $result = self::query($query, $params, $database, $config);
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
     * @param ?RequestConfig $config
     * @return array<mixed>
     */
    public static function getTableInfo($database = null, $name = 'books', $config = null)
    {
        $query = "PRAGMA table_info({$name})";
        $params = [];
        $result = self::query($query, $params, $database, $config);
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
     * @param ?RequestConfig $config
     * @return int
     */
    public static function getUserVersion($database = null, $config = null)
    {
        $query = "PRAGMA user_version";
        $result = self::querySingle($query, $database, $config);
        return $result;
    }

    /**
     * Get list of databases (open or attach) from SQLite
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return array<mixed>
     */
    public static function getDatabaseList($database = null, $config = null)
    {
        // PRAGMA database_list;
        $sql = 'select * from pragma_database_list;';
        $stmt = self::getConnection($database, $config)->getDb()->prepare($sql);
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
     * @param ?RequestConfig $config
     * @return bool
     */
    public static function hasNotes($database = null, $config = null)
    {
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        if (file_exists(dirname(self::getDbFileName($database, $config)) . '/' . self::NOTES_DIR_NAME . '/' . self::NOTES_DB_FILE)) {
            return true;
        }
        return false;
    }

    /**
     * Summary of getNotesDb
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return \Pdo\Sqlite|null
     */
    public static function getNotesDb($database = null, $config = null)
    {
        if (!self::hasNotes($database, $config)) {
            return null;
        }
        // calibre_dir/.calnotes/notes.db file -> notes_db database in sqlite
        $databases = self::getDatabaseList($database, $config);
        if (!empty($databases[self::NOTES_DB_NAME])) {
            return self::getDb($database, $config);
        }
        $notesFileName = dirname(self::getDbFileName($database, $config)) . '/' . self::NOTES_DIR_NAME . '/' . self::NOTES_DB_FILE;
        self::attachDatabase($notesFileName, self::NOTES_DB_NAME, $database, $config);
        $databases = self::getDatabaseList($database, $config);
        if (!empty($databases[self::NOTES_DB_NAME])) {
            return self::getConnection($database, $config)->getDb();
        }
        return null;
    }
}
