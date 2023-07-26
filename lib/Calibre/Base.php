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
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;
use Exception;
use PDO;

abstract class Base
{
    public const PAGE_ID = "cops:base";
    public const PAGE_ALL = 0;
    public const PAGE_DETAIL = 0;
    public const PAGE_LETTER = 0;
    public const SQL_TABLE = "bases";
    public const SQL_LINK_TABLE = "books_bases_link";
    public const SQL_LINK_COLUMN = "base";
    public const SQL_SORT = "sort";
    public const SQL_COLUMNS = "bases.id as id, bases.name as name, bases.sort as sort, count(*) as count";
    public const SQL_ALL_ROWS = "select {0} from bases, books_bases_link where base = bases.id {1} group by bases.id, bases.name, bases.sort order by sort";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from bases, books_bases_link where base = bases.id and (upper (bases.sort) like ? or upper (bases.name) like ?) {1} group by bases.id, bases.name, bases.sort order by sort";
    public const SQL_ROWS_BY_FIRST_LETTER = "select {0} from bases, books_bases_link where base = bases.id and upper (bases.sort) like ? {1} group by bases.id, bases.name, bases.sort order by sort";
    public const COMPATIBILITY_XML_ALDIKO = "aldiko";

    public $id;
    public $name;
    private static $db = null;
    protected $databaseId = null;

    /**
     * Summary of getDatabaseId
     * @return mixed
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * Summary of setDatabaseId
     * @param mixed $database
     * @return void
     */
    public function setDatabaseId($database = null)
    {
        $this->databaseId = $database;
    }

    public function getUri()
    {
        return "?page=".static::PAGE_DETAIL."&id=$this->id";
    }

    public function getParentUri()
    {
        return "?page=".static::PAGE_ALL;
    }

    public function getEntryId()
    {
        return static::PAGE_ID.":".$this->id;
    }

    public static function getEntryIdByLetter($startingLetter)
    {
        return static::PAGE_ID.":letter:".$startingLetter;
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function getContent($count = 0)
    {
        return str_format(localize("bookword", $count), $count);
    }

    public function getContentType()
    {
        return "text";
    }

    public function getLinkArray()
    {
        return [ new LinkNavigation($this->getUri(), null, null, $this->getDatabaseId()) ];
    }

    public function getEntry($count = 0)
    {
        return new Entry(
            $this->getTitle(),
            $this->getEntryId(),
            $this->getContent($count),
            $this->getContentType(),
            $this->getLinkArray(),
            $this->getDatabaseId(),
            $this::class,
            $count
        );
    }

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

    public static function getEntryById($database = null)
    {
        //return new static((object)['id' => null, 'name' => localize("seriesword.none")], $database);
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    public static function getEntryCount($database = null)
    {
        return self::getCountGeneric(static::SQL_TABLE, static::PAGE_ID, static::PAGE_ALL, $database);
    }

    /**
     * Summary of getAllEntries = same as getAll<Whatever>() in <Whatever> child class
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array
     */
    public static function getAllEntries($database = null, $numberPerPage = null)
    {
        return self::getEntryArrayWithBookNumber(static::SQL_ALL_ROWS, static::SQL_COLUMNS, "", [], static::class, $database, $numberPerPage);
    }

    public static function getAllEntriesByQuery($query, $n, $database = null, $numberPerPage = null)
    {
        return self::getEntryArrayWithBookNumber(static::SQL_ROWS_FOR_SEARCH, static::SQL_COLUMNS, "", ['%' . $query . '%'], static::class, $database, $numberPerPage);
    }

    public static function getEntriesByStartingLetter($letter, $database = null, $numberPerPage = null)
    {
        return self::getEntryArrayWithBookNumber(static::SQL_ROWS_BY_FIRST_LETTER, static::SQL_COLUMNS, "", [$letter . "%"], static::class, $database, $numberPerPage);
    }

    public static function getEntriesByFilter($request, $database = null, $numberPerPage = null)
    {
        $filter = new Filter($request, [], static::SQL_LINK_TABLE, $database);
        return self::getFilteredEntries($filter, $database, $numberPerPage);
    }

    public static function getEntriesByAuthorId($authorId, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addAuthorIdFilter($authorId);
        return self::getFilteredEntries($filter, $database, $numberPerPage);
    }

    public static function getEntriesByLanguageId($languageId, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addLanguageIdFilter($languageId);
        return self::getFilteredEntries($filter, $database, $numberPerPage);
    }

    public static function getEntriesByPublisherId($publisherId, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addPublisherIdFilter($publisherId);
        return self::getFilteredEntries($filter, $database, $numberPerPage);
    }

    public static function getEntriesByRatingId($ratingId, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addRatingIdFilter($ratingId);
        return self::getFilteredEntries($filter, $database, $numberPerPage);
    }

    public static function getEntriesBySeriesId($seriesId, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addSeriesIdFilter($seriesId);
        return self::getFilteredEntries($filter, $database, $numberPerPage);
    }

    public static function getEntriesByTagId($tagId, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addTagIdFilter($tagId);
        return self::getFilteredEntries($filter, $database, $numberPerPage);
    }

    public static function getFilteredEntries($filter, $database = null, $numberPerPage = null)
    {
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return self::getEntryArrayWithBookNumber(static::SQL_ALL_ROWS, static::SQL_COLUMNS, $filterString, $params, static::class, $database, $numberPerPage);
    }

    /**
     * Summary of executeQuerySingle
     * @param mixed $query
     * @param mixed $database
     * @return mixed
     */
    public static function executeQuerySingle($query, $database = null)
    {
        return self::getDb($database)->query($query)->fetchColumn();
    }

    /**
     * Summary of getCountGeneric
     * @param mixed $table
     * @param mixed $id
     * @param mixed $pageId
     * @param mixed $database
     * @param mixed $numberOfString
     * @return Entry|null
     */
    public static function getCountGeneric($table, $id, $pageId, $database = null, $numberOfString = null)
    {
        if (!$numberOfString) {
            $numberOfString = $table . ".alphabetical";
        }
        $count = self::executeQuerySingle('select count(*) from ' . $table, $database);
        if ($count == 0) {
            return null;
        }
        $entry = new Entry(
            localize($table . ".title"),
            $id,
            str_format(localize($numberOfString, $count), $count),
            "text",
            [ new LinkNavigation("?page=".$pageId, null, null, $database)],
            $database,
            "",
            $count
        );
        return $entry;
    }

    /**
     * Summary of getEntryArrayWithBookNumber
     * @param mixed $query
     * @param mixed $columns
     * @param mixed $params
     * @param mixed $category
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array
     */
    public static function getEntryArrayWithBookNumber($query, $columns, $filter, $params, $category, $database = null, $numberPerPage = null)
    {
        /** @var \PDOStatement $result */

        [, $result] = self::executeQuery($query, $columns, $filter, $params, -1, $database, $numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            /** @var Author|Tag|Serie|Publisher|Language|Rating|Book $instance */
            if ($category == Book::class) {
                $post->count = 1;
            }

            $instance = new $category($post, $database);
            array_push($entryArray, $instance->getEntry($post->count));
        }
        return $entryArray;
    }

    /**
     * Summary of executeQuery
     * @param mixed $query
     * @param mixed $columns
     * @param mixed $filter
     * @param mixed $params
     * @param mixed $n
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array
     */
    public static function executeQuery($query, $columns, $filter, $params, $n, $database = null, $numberPerPage = null)
    {
        $totalResult = -1;

        if (Translation::useNormAndUp()) {
            $query = preg_replace("/upper/", "normAndUp", $query);
            $columns = preg_replace("/upper/", "normAndUp", $columns);
        }

        if (is_null($numberPerPage)) {
            global $config;
            $numberPerPage = $config['cops_max_item_per_page'];
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
