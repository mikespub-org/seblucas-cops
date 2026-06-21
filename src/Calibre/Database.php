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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * Summary of getContext
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return DatabaseContext
     */
    public static function getContext($database = null, $config = null)
    {
        return new DatabaseContext($database, $config);
    }

    /**
     * Summary of getContextConnection
     * @param DatabaseContext $dbContext
     * @return DatabaseConnection
     */
    public static function getContextConnection($dbContext)
    {
        $dbFileName = $dbContext->getDbFileName();
        $key = self::getConnectionKey($dbFileName);
        if (!isset(self::$connections[$key])) {
            self::$connections[$key] = new DatabaseConnection($dbFileName, $dbContext->getConfig(), $dbContext->getDatabase());
        }
        return self::$connections[$key];
    }

    /**
     * Summary of isMultipleDatabaseEnabled
     * @deprecated 4.4.10 use DatabaseContext() instead
     * @param ?RequestConfig $config
     * @return bool
     */
    public static function isMultipleDatabaseEnabled($config = null)
    {
        return is_array(Config::getFrom($config, 'calibre_directory', null));
    }

    /**
     * Summary of findDatabaseId
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 not used
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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
     * @deprecated 4.4.10 use DatabaseContext() instead
     * @param string $dbFileName Database file name
     * @param string $attachDatabase
     * @param ?int $database
     * @param ?RequestConfig $config
     * @throws Exception if error
     * @return void
     */
    protected static function attachDatabase($dbFileName, $attachDatabase, $database = null, $config = null)
    {
        self::getConnection($database, $config)->attachDatabase($dbFileName, $attachDatabase);
    }

    /**
     * Summary of addSqliteFunctions
     * @deprecated 4.4.10 use DatabaseContext() instead
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
        $dbContext = self::getContext($database, $config);
        if ($dbContext->noDatabaseSelected()) {
            $count = count($dbContext->getDbList());
            for ($i = 0; $i < $count; $i++) {
                $dbContext->setDatabase($i);
                $dbContext->getDb();
                self::clearDb();
            }
        } else {
            $dbContext->getDb();
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
     * @deprecated 4.4.10 use DatabaseContext() instead
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

        return self::getConnection($database, $config)->querySingle($query);
    }

    /**
     * Summary of query
     * @deprecated 4.4.10 use DatabaseContext() instead
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

        return self::getConnection($database, $config)->query($query, $params);
    }

    /**
     * Summary of queryTotal
     * @deprecated 4.4.10 use DatabaseContext() instead
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
        if (Normalizer::useNormAndUp($config)) {
            $query = str_replace("upper", "normAndUp", $query);
            $columns = str_replace("upper", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = Config::getFrom($config, 'max_item_per_page');
        }

        return self::getConnection($database, $config)->queryTotal($query, $columns, $filter, $params, $n, $numberPerPage);
    }

    /**
     * Summary of queryFilter
     * @deprecated 4.4.10 use DatabaseContext() instead
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

        return self::getConnection($database, $config)->queryFilter($query, $columns, $filter, $params, $n, $numberPerPage);
    }

    /**
     * Summary of countFilter
     * @deprecated 4.4.10 use DatabaseContext() instead
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

        return self::getConnection($database, $config)->countFilter($query, $columns, $filter, $params);
    }

    /**
     * Summary of getDbSchema
     * @deprecated 4.4.10 use DatabaseContext() instead
     * @param ?int $database
     * @param ?string $type get table or view entries
     * @param ?RequestConfig $config
     * @return array<mixed>
     */
    public static function getDbSchema($database = null, $type = null, $config = null)
    {
        return self::getConnection($database, $config)->getDbSchema($type);
    }

    /**
     * Summary of getTableInfo
     * @deprecated 4.4.10 use DatabaseContext() instead
     * @param ?int $database
     * @param string $name table or view name
     * @param ?RequestConfig $config
     * @return array<mixed>
     */
    public static function getTableInfo($database = null, $name = 'books', $config = null)
    {
        return self::getConnection($database, $config)->getTableInfo($name);
    }

    /**
     * Summary of getUserVersion
     * @deprecated 4.4.10 use DatabaseContext() instead
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return int
     */
    public static function getUserVersion($database = null, $config = null)
    {
        return self::getConnection($database, $config)->getUserVersion();
    }

    /**
     * Get list of databases (open or attach) from SQLite
     * @deprecated 4.4.10 use DatabaseContext() instead
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return array<mixed>
     */
    public static function getDatabaseList($database = null, $config = null)
    {
        return self::getConnection($database, $config)->getDatabaseList();
    }

    /**
     * Summary of getNotesDb
     * @deprecated 4.4.10 use DatabaseContext() instead
     * @param ?int $database
     * @param ?RequestConfig $config
     * @return \Pdo\Sqlite|null
     */
    public static function getNotesDb($database = null, $config = null)
    {
        return self::getConnection($database, $config)->getNotesDb();
    }
}
