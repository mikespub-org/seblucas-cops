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
    private static $db = null;

    /**
     * Summary of isMultipleDatabaseEnabled
     * @return bool
     */
    public static function isMultipleDatabaseEnabled()
    {
        global $config;
        return is_array($config['calibre_directory']);
    }

    /**
     * Summary of useAbsolutePath
     * @param mixed $database
     * @return bool
     */
    public static function useAbsolutePath($database)
    {
        global $config;
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
     * @return array
     */
    public static function getDbList()
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            return $config['calibre_directory'];
        } else {
            return ["" => $config['calibre_directory']];
        }
    }

    /**
     * Summary of getDbNameList
     * @return array
     */
    public static function getDbNameList()
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            return array_keys($config['calibre_directory']);
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
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            if (!preg_match('/^\d+$/', $database)) {
                self::error($database);
            }
            $array = array_keys($config['calibre_directory']);
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
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            if (!preg_match('/^\d+$/', $database)) {
                self::error($database);
            }
            $array = array_values($config['calibre_directory']);
            return  $array[$database];
        }
        return $config['calibre_directory'];
    }

    // -DC- Add image directory
    /**
     * Summary of getImgDirectory
     * @param mixed $database
     * @return string
     */
    public static function getImgDirectory($database)
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = 0;
            }
            $array = array_values($config['image_directory']);
            return  $array[$database];
        }
        return $config['image_directory'];
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
}
