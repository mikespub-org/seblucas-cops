<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Output\Entry;
use SebLucas\Cops\Output\LinkNavigation;
use Exception;
use PDO;

abstract class Base
{
    public const COMPATIBILITY_XML_ALDIKO = "aldiko";

    private static $db = null;

    public static function isMultipleDatabaseEnabled()
    {
        global $config;
        return is_array($config['calibre_directory']);
    }

    public static function useAbsolutePath()
    {
        global $config;
        $path = self::getDbDirectory();
        return preg_match('/^\//', $path) || // Linux /
               preg_match('/^\w\:/', $path); // Windows X:
    }

    public static function noDatabaseSelected()
    {
        return self::isMultipleDatabaseEnabled() && is_null(getURLParam(COPS_DB_PARAM));
    }

    public static function getDbList()
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            return $config['calibre_directory'];
        } else {
            return ["" => $config['calibre_directory']];
        }
    }

    public static function getDbNameList()
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            return array_keys($config['calibre_directory']);
        } else {
            return [""];
        }
    }

    public static function getDbName($database = null)
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = getURLParam(COPS_DB_PARAM, 0);
            }
            if (!is_null($database) && !preg_match('/^\d+$/', $database)) {
                self::error($database);
            }
            $array = array_keys($config['calibre_directory']);
            return  $array[$database];
        }
        return "";
    }

    public static function getDbDirectory($database = null)
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = getURLParam(COPS_DB_PARAM, 0);
            }
            if (!is_null($database) && !preg_match('/^\d+$/', $database)) {
                self::error($database);
            }
            $array = array_values($config['calibre_directory']);
            return  $array[$database];
        }
        return $config['calibre_directory'];
    }

    // -DC- Add image directory
    public static function getImgDirectory($database = null)
    {
        global $config;
        if (self::isMultipleDatabaseEnabled()) {
            if (is_null($database)) {
                $database = getURLParam(COPS_DB_PARAM, 0);
            }
            $array = array_values($config['image_directory']);
            return  $array[$database];
        }
        return $config['image_directory'];
    }

    public static function getDbFileName($database = null)
    {
        return self::getDbDirectory($database) .'metadata.db';
    }

    private static function error($database)
    {
        if (php_sapi_name() != "cli") {
            header("location: checkconfig.php?err=1");
        }
        throw new Exception("Database <{$database}> not found.");
    }

    public static function getDb($database = null)
    {
        if (is_null(self::$db)) {
            try {
                if (is_readable(self::getDbFileName($database))) {
                    self::$db = new PDO('sqlite:'. self::getDbFileName($database));
                    if (useNormAndUp()) {
                        self::$db->sqliteCreateFunction('normAndUp', 'normAndUp', 1);
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

    public static function checkDatabaseAvailability()
    {
        if (self::noDatabaseSelected()) {
            for ($i = 0; $i < count(self::getDbList()); $i++) {
                self::getDb($i);
                self::clearDb();
            }
        } else {
            self::getDb();
        }
        return true;
    }

    public static function clearDb()
    {
        self::$db = null;
    }

    public static function executeQuerySingle($query, $database = null)
    {
        return self::getDb($database)->query($query)->fetchColumn();
    }

    public static function getCountGeneric($table, $id, $pageId, $numberOfString = null)
    {
        if (!$numberOfString) {
            $numberOfString = $table . ".alphabetical";
        }
        $count = self::executeQuerySingle('select count(*) from ' . $table);
        if ($count == 0) {
            return null;
        }
        $entry = new Entry(
            localize($table . ".title"),
            $id,
            str_format(localize($numberOfString, $count), $count),
            "text",
            [ new LinkNavigation("?page=".$pageId)],
            "",
            $count
        );
        return $entry;
    }

    public static function getEntryArrayWithBookNumber($query, $columns, $params, $category)
    {
        /* @var $result PDOStatement */

        [, $result] = self::executeQuery($query, $columns, "", $params, -1);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            /* @var $instance Author|Tag|Serie|Publisher */

            $instance = new $category($post);
            if (property_exists($post, "sort")) {
                $title = $post->sort;
            } else {
                $title = $post->name;
            }
            array_push($entryArray, new Entry(
                $title,
                $instance->getEntryId(),
                str_format(localize("bookword", $post->count), $post->count),
                "text",
                [ new LinkNavigation($instance->getUri())],
                "",
                $post->count
            ));
        }
        return $entryArray;
    }

    public static function executeQuery($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null)
    {
        $totalResult = -1;

        if (useNormAndUp()) {
            $query = preg_replace("/upper/", "normAndUp", $query);
            $columns = preg_replace("/upper/", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            $numberPerPage = getCurrentOption("max_item_per_page");
        }

        if ($numberPerPage != -1 && $n != -1) {
            // First check total number of results
            $result = self::getDb($database)->prepare(str_format($query, "count(*)", $filter));
            $result->execute($params);
            $totalResult = $result->fetchColumn();

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }

        $result = self::getDb($database)->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        return [$totalResult, $result];
    }
}
