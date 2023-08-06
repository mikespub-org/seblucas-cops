<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;

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
    protected $databaseId = null;

    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->name = $post->name;
        $this->databaseId = $database;
    }

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

    public function getClassName()
    {
        $classParts = explode('\\', $this::class);
        return end($classParts);
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
            $this->getClassName(),
            $count
        );
    }

    /**
     * Summary of getDb
     * @param mixed $database
     * @return \PDO
     */
    public static function getDb($database = null)
    {
        return Database::getDb($database);
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    public static function getInstanceById($id, $default = null, $category = self::class, $database = null)
    {
        $query = 'select ' . static::SQL_COLUMNS . ' from ' . static::SQL_TABLE . ' where id = ?';
        $result = self::getDb($database)->prepare($query);
        $result->execute([$id]);
        if ($post = $result->fetchObject()) {
            return new $category($post, $database);
        }
        return new $category((object)['id' => null, 'name' => $default, 'sort' => $default], $database);
    }

    public static function getEntryCount($database = null)
    {
        return self::getCountGeneric(static::SQL_TABLE, static::PAGE_ID, static::PAGE_ALL, $database);
    }

    public static function countAllEntries($database = null)
    {
        $query = 'select {0} from ' . static::SQL_TABLE;
        return self::countQuery($query, "", [], $database);
    }

    public static function countEntriesByFirstLetter($letter, $database = null)
    {
        $query = 'select {0} from ' . static::SQL_TABLE . ' where {1}';
        $filter = 'upper(' . static::SQL_SORT . ') like ?';
        return self::countQuery($query, $filter, [$letter . "%"], $database);
    }

    /**
     * Summary of getAllEntries = same as getAll<Whatever>() in <Whatever> child class
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array
     */
    public static function getAllEntries($n = -1, $database = null, $numberPerPage = null)
    {
        return self::getEntryArrayWithBookNumber(static::SQL_ALL_ROWS, static::SQL_COLUMNS, "", [], static::class, $n, $database, $numberPerPage);
    }

    public static function getAllEntriesByQuery($query, $n = -1, $database = null, $numberPerPage = null)
    {
        return self::getEntryArrayWithBookNumber(static::SQL_ROWS_FOR_SEARCH, static::SQL_COLUMNS, "", ['%' . $query . '%'], static::class, $n, $database, $numberPerPage);
    }

    public static function getEntriesByFirstLetter($letter, $n = -1, $database = null, $numberPerPage = null)
    {
        return self::getEntryArrayWithBookNumber(static::SQL_ROWS_BY_FIRST_LETTER, static::SQL_COLUMNS, "", [$letter . "%"], static::class, $n, $database, $numberPerPage);
    }

    public static function getEntriesByFilter($request, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter($request, [], static::SQL_LINK_TABLE, $database);
        return self::getFilteredEntries($filter, $n, $database, $numberPerPage);
    }

    public static function getEntriesByAuthorId($authorId, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addAuthorIdFilter($authorId);
        return self::getFilteredEntries($filter, $n, $database, $numberPerPage);
    }

    public static function getEntriesByLanguageId($languageId, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addLanguageIdFilter($languageId);
        return self::getFilteredEntries($filter, $n, $database, $numberPerPage);
    }

    public static function getEntriesByPublisherId($publisherId, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addPublisherIdFilter($publisherId);
        return self::getFilteredEntries($filter, $n, $database, $numberPerPage);
    }

    public static function getEntriesByRatingId($ratingId, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addRatingIdFilter($ratingId);
        return self::getFilteredEntries($filter, $n, $database, $numberPerPage);
    }

    public static function getEntriesBySeriesId($seriesId, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addSeriesIdFilter($seriesId);
        return self::getFilteredEntries($filter, $n, $database, $numberPerPage);
    }

    public static function getEntriesByTagId($tagId, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addTagIdFilter($tagId);
        return self::getFilteredEntries($filter, $n, $database, $numberPerPage);
    }

    public static function getFilteredEntries($filter, $n = -1, $database = null, $numberPerPage = null)
    {
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return self::getEntryArrayWithBookNumber(static::SQL_ALL_ROWS, static::SQL_COLUMNS, $filterString, $params, static::class, $n, $database, $numberPerPage);
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
     * @param mixed $n
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return Entry[]
     */
    public static function getEntryArrayWithBookNumber($query, $columns, $filter, $params, $category, $n = -1, $database = null, $numberPerPage = null)
    {
        /** @var \PDOStatement $result */

        [, $result] = self::executeQuery($query, $columns, $filter, $params, $n, $database, $numberPerPage);
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
     * @return array{0: integer, 1: \PDOStatement}
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
            $totalResult = self::countQuery($query, $filter, $params, $database);

            // Next modify the query and params
            $query .= " limit ?, ?";
            array_push($params, ($n - 1) * $numberPerPage, $numberPerPage);
        }

        $result = self::getDb($database)->prepare(str_format($query, $columns, $filter));
        $result->execute($params);
        return [$totalResult, $result];
    }

    /**
     * Summary of countQuery
     * @param mixed $query
     * @param mixed $filter
     * @param mixed $params
     * @param mixed $database
     * @return integer
     */
    public static function countQuery($query, $filter = "", $params = [], $database = null)
    {
        $result = self::getDb($database)->prepare(str_format($query, "count(*)", $filter));
        $result->execute($params);
        $totalResult = $result->fetchColumn();
        return $totalResult;
    }
}
