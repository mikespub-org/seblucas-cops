<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Request;
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

    /** Use inherited class methods to get entries from <Whatever> by instance (linked via books) */

    public function getLinkTable()
    {
        return static::SQL_LINK_TABLE;
    }

    public function getLinkColumn()
    {
        return static::SQL_LINK_COLUMN;
    }

    public function getBooks($n = -1, $sort = null)
    {
        // @todo see if we want to do something special for books, and deal with static:: inheritance
        //return $this->getEntriesByInstance(Book::class, $n, $sort, $this->databaseId);
        $booklist = new BookList(null, $this->databaseId);
        $booklist->orderBy = $sort;
        [$entryArray, ] = $booklist->getBooksByInstance($this, $n);
        return $entryArray;
    }

    public function getEntriesByInstance($className, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        //$baselist = new BaseList(null, $className, $database, $numberPerPage);
        //$baselist->orderBy = $sort;
        //[$entryArray, ] = $baselist->getEntriesByInstance($this, $n);
        $filter = new Filter([], [], $className::SQL_LINK_TABLE, $database);
        $filter->addInstanceFilter($this);
        return $className::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public function getAuthors($n = -1, $sort = null)
    {
        return $this->getEntriesByInstance(Author::class, $n, $sort, $this->databaseId);
    }

    public function getLanguages($n = -1, $sort = null)
    {
        return $this->getEntriesByInstance(Language::class, $n, $sort, $this->databaseId);
    }

    public function getPublishers($n = -1, $sort = null)
    {
        return $this->getEntriesByInstance(Publisher::class, $n, $sort, $this->databaseId);
    }

    public function getRatings($n = -1, $sort = null)
    {
        return $this->getEntriesByInstance(Rating::class, $n, $sort, $this->databaseId);
    }

    public function getSeries($n = -1, $sort = null)
    {
        return $this->getEntriesByInstance(Serie::class, $n, $sort, $this->databaseId);
    }

    public function getTags($n = -1, $sort = null)
    {
        return $this->getEntriesByInstance(Tag::class, $n, $sort, $this->databaseId);
    }

    public function getCustomValues($customType)
    {
        // we'd need to apply getEntriesBy<Whatever>Id from $instance on $customType instance here - too messy
        return [];
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    public static function getInstanceById($id, $default = null, $className = self::class, $database = null)
    {
        $query = 'select ' . $className::SQL_COLUMNS . ' from ' . $className::SQL_TABLE . ' where id = ?';
        $result = Database::query($query, [$id], $database);
        if ($post = $result->fetchObject()) {
            return new $className($post, $database);
        }
        return new $className((object)['id' => null, 'name' => $default, 'sort' => $default], $database);
    }

    public static function getEntryCount($database = null)
    {
        return self::getCountGeneric(static::SQL_TABLE, static::PAGE_ID, static::PAGE_ALL, $database);
    }

    /**
     * Summary of countRequestEntries
     * @param Request $request
     * @param mixed $database
     * @return integer
     */
    public static function countRequestEntries($request, $database = null)
    {
        if ($request->hasFilter()) {
            return self::countEntriesByFilter($request, $database);
        }
        return self::countAllEntries($database);
    }

    public static function countAllEntries($database = null)
    {
        return Database::querySingle('select count(*) from ' . static::SQL_TABLE, $database);
    }

    public static function countEntriesByFirstLetter($request, $letter, $database = null)
    {
        $filter = new Filter($request, [], static::SQL_LINK_TABLE, $database);
        $filter->addFilter('upper(' . static::SQL_TABLE . '.' . static::SQL_SORT . ') like ?', $letter . "%");
        return self::countFilteredEntries($filter, $database);
        //$query = 'select {0} from ' . static::SQL_TABLE . ' where {1}';
        //$columns = 'count(*)';
        //$filter = 'upper(' . static::SQL_SORT . ') like ?';
        //return self::countQuery($query, $columns, $filter, [$letter . "%"], $database);
    }

    public static function countEntriesByFilter($request, $database = null)
    {
        $filter = new Filter($request, [], static::SQL_LINK_TABLE, $database);
        return self::countFilteredEntries($filter, $database);
    }

    public static function countFilteredEntries($filter, $database = null)
    {
        // select {0} from series, books_series_link where series.id = books_series_link.series {1}
        $query = 'select {0} from ' . static::SQL_TABLE . ', ' . static::SQL_LINK_TABLE . ' where ' . static::SQL_TABLE . '.id = ' . static::SQL_LINK_TABLE . '.' . static::SQL_LINK_COLUMN . ' {1}';
        // count(distinct series.id)
        $columns = 'count(distinct ' . static::SQL_TABLE . '.id)';
        // and (exists (select null from books_authors_link, books where books_series_link.book = books.id and books_authors_link.book = books.id and books_authors_link.author = ?))
        $filterString = $filter->getFilterString();
        // [1]
        $params = $filter->getQueryParams();
        return Database::countFilter($query, $columns, $filterString, $params, $database);
    }

    /**
     * Summary of getRequestEntries
     * @param Request $request
     * @param mixed $n
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array<Entry>
     */
    public static function getRequestEntries($request, $n = -1, $database = null, $numberPerPage = null)
    {
        $sort = $request->getSorted();
        if ($request->hasFilter()) {
            return self::getEntriesByFilter($request, $n, $sort, $database, $numberPerPage);
        }
        return self::getAllEntries($n, $sort, $database, $numberPerPage);
    }

    /**
     * Summary of getAllEntries = same as getAll<Whatever>() in <Whatever> child class
     * @param mixed $n
     * @param mixed $sort
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array<Entry>
     */
    public static function getAllEntries($n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $query = static::SQL_ALL_ROWS;
        if (!empty($sort) && $sort != static::SQL_SORT && str_contains(static::SQL_COLUMNS, ' as ' . $sort)) {
            if (str_contains($query, 'order by')) {
                $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|)\s*/i', ' order by ' . static::getSortBy($sort) . ' ', $query);
            } else {
                $query .= ' order by ' . static::getSortBy($sort) . ' ';
            }
        }
        return self::getEntryArrayWithBookNumber($query, static::SQL_COLUMNS, "", [], static::class, $n, $database, $numberPerPage);
    }

    public static function getAllEntriesByQuery($query, $n = -1, $database = null, $numberPerPage = null)
    {
        return self::getEntryArrayWithBookNumber(static::SQL_ROWS_FOR_SEARCH, static::SQL_COLUMNS, "", ['%' . $query . '%'], static::class, $n, $database, $numberPerPage);
    }

    public static function getEntriesByFirstLetter($request, $letter, $n = -1, $database = null, $numberPerPage = null)
    {
        $filter = new Filter($request, [$letter . "%"], static::SQL_LINK_TABLE, $database);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return self::getEntryArrayWithBookNumber(static::SQL_ROWS_BY_FIRST_LETTER, static::SQL_COLUMNS, $filterString, $params, static::class, $n, $database, $numberPerPage);
    }

    public static function getEntriesByFilter($request, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter($request, [], static::SQL_LINK_TABLE, $database);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public static function getEntriesByAuthorId($authorId, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addAuthorIdFilter($authorId);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public static function getEntriesByLanguageId($languageId, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addLanguageIdFilter($languageId);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public static function getEntriesByPublisherId($publisherId, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addPublisherIdFilter($publisherId);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public static function getEntriesByRatingId($ratingId, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addRatingIdFilter($ratingId);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public static function getEntriesBySeriesId($seriesId, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addSeriesIdFilter($seriesId);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public static function getEntriesByTagId($tagId, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addTagIdFilter($tagId);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    public static function getEntriesByCustomValueId($customType, $valueId, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filter = new Filter([], [], static::SQL_LINK_TABLE, $database);
        $filter->addCustomIdFilter($customType, $valueId);
        return self::getFilteredEntries($filter, $n, $sort, $database, $numberPerPage);
    }

    /**
     * Summary of getFilteredEntries
     * @param mixed $filter
     * @param mixed $n
     * @param mixed $sort
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array<Entry>
     */
    public static function getFilteredEntries($filter, $n = -1, $sort = null, $database = null, $numberPerPage = null)
    {
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        $query = static::SQL_ALL_ROWS;
        if (!empty($sort) && $sort != static::SQL_SORT && str_contains(static::SQL_COLUMNS, ' as ' . $sort)) {
            if (str_contains($query, 'order by')) {
                $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|)\s*/i', ' order by ' . $sort . ' ', $query);
            } else {
                $query .= ' order by ' . $sort . ' ';
            }
        }
        return self::getEntryArrayWithBookNumber($query, static::SQL_COLUMNS, $filterString, $params, static::class, $n, $database, $numberPerPage);
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
        $count = Database::querySingle('select count(*) from ' . $table, $database);
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
     * @param mixed $className
     * @param mixed $n
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array<Entry>
     */
    public static function getEntryArrayWithBookNumber($query, $columns, $filter, $params, $className, $n = -1, $database = null, $numberPerPage = null)
    {
        $result = Database::queryFilter($query, $columns, $filter, $params, $n, $database, $numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            /** @var Author|Tag|Serie|Publisher|Language|Rating|Book $instance */
            if ($className == Book::class) {
                $post->count = 1;
            }

            $instance = new $className($post, $database);
            array_push($entryArray, $instance->getEntry($post->count));
        }
        return $entryArray;
    }

    protected static function getSortBy($sort)
    {
        return match ($sort) {
            'title' => 'sort',  // or name
            'count' => 'count desc',
            default => $sort,
        };
    }
}
