<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Translation;
use Exception;
use PDO;

class Database
{
    public const KEEP_STATS = false;
    /** @var PDO|null */
    private static $db = null;
    private static int $count = 0;
    /** @var array<string> */
    private static $queries = [];

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
     * @param mixed $database
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
     * @param mixed $database
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
     * @param mixed $database
     * @return string
     */
    public static function getDbName($database)
    {
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            if (!preg_match('/^\d+$/', $database)) {
                self::error($database);
            }
            $array = array_keys(Config::get('calibre_directory'));
            return  $array[$database];
        }
        return "";
    }

    /**
     * Summary of getDbDirectory
     * @param mixed $database
     * @return string
     */
    public static function getDbDirectory($database)
    {
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            if (!preg_match('/^\d+$/', $database)) {
                self::error($database);
            }
            $array = array_values(Config::get('calibre_directory'));
            return  $array[$database];
        }
        return Config::get('calibre_directory');
    }

    // -DC- Add image directory
    /**
     * Summary of getImgDirectory
     * @param mixed $database
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
     * @param mixed $database
     * @return string
     */
    public static function getDbFileName($database)
    {
        return self::getDbDirectory($database) .'metadata.db';
    }

    /**
     * Summary of error
     * @param mixed $database
     * @throws \Exception
     * @return never
     */
    private static function error($database)
    {
        if (php_sapi_name() != "cli") {
            header("location: " . Config::ENDPOINT["check"] . "?err=1");
        }
        throw new Exception("Database <{$database}> not found.");
    }

    /**
     * Summary of getDb
     * @param mixed $database
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
                    self::$db = new PDO('sqlite:'. self::getDbFileName($database));
                    if (Translation::useNormAndUp()) {
                        self::$db->sqliteCreateFunction('normAndUp', function ($s) {
                            return Translation::normAndUp($s);
                        }, 1);
                    }
                } else {
                    self::error($database);
                }
            } catch (Exception $e) {
                self::error($database);
            }
        }
        return self::$db;
    }

    /**
     * Summary of checkDatabaseAvailability
     * @param mixed $database
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
     * @param mixed $query
     * @param mixed $database
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
     * @param mixed $query
     * @param mixed $params
     * @param mixed $database
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
     * @param mixed $query
     * @param mixed $columns
     * @param mixed $filter
     * @param mixed $params
     * @param mixed $n
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array{0: integer, 1: \PDOStatement}
     */
    public static function queryTotal($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, $query);
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
     * @param mixed $query
     * @param mixed $columns
     * @param mixed $filter
     * @param mixed $params
     * @param mixed $n
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return \PDOStatement
     */
    public static function queryFilter($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, $query);
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
     * @param mixed $query
     * @param mixed $columns
     * @param mixed $filter
     * @param mixed $params
     * @param mixed $database
     * @return integer
     */
    public static function countFilter($query, $columns = 'count(*)', $filter = '', $params = [], $database = null)
    {
        /** @phpstan-ignore-next-line */
        if (self::KEEP_STATS) {
            array_push(self::$queries, $query);
        }
        // assuming order by ... is at the end of the query here
        $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|).*$/i', '', $query);
        $result = self::getDb($database)->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        $totalResult = $result->fetchColumn();
        return $totalResult;
    }
}
