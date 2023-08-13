<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
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
    public const SQL_BOOKLIST = 'select {0} from books_bases_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_bases_link.book = books.id and base = ? {1} order by books.sort';
    public const CATEGORY = "bases";
    public const COMPATIBILITY_XML_ALDIKO = "aldiko";

    public $id;
    public $name;
    public $limitSelf = true;
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
        $classParts = explode('\\', get_class($this));
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

    /**
     * Get the query to find all books with this value
     * the returning array has two values:
     *  - first the query (string)
     *  - second an array of all PreparedStatement parameters
     * @return array{0: string, 1: array}
     */
    public function getQuery()
    {
        return [ static::SQL_BOOKLIST, [ $this->id ]];
    }

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
        $baselist = new BaseList($className, null, $database, $numberPerPage);
        $baselist->orderBy = $sort;
        return $baselist->getEntriesByInstance($this, $n);
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

    /**
     * Get child categories for hierarchical tags or custom columns
     * @return array
     */
    public function getChildCategories()
    {
        // Fiction -> Fiction.% matching Fiction.Historical and Fiction.Romance
        $find = $this->getTitle() . '.%';
        return $this->getRelatedCategories($find);
    }

    /**
     * Get sibling categories for hierarchical tags or custom columns
     * @return array
     */
    public function getSiblingCategories()
    {
        // Fiction.Historical -> Fiction.% matching Fiction.Historical and Fiction.Romance
        $parent = self::findParentName($this->getTitle());
        if (empty($parent)) {
            return [];
        }
        $find = $parent . '.%';
        return $this->getRelatedCategories($find);
    }

    protected static function findParentName($name)
    {
        $parts = explode('.', $name);
        $child = array_pop($parts);
        if (empty($parts)) {
            return '';
        }
        $parent = implode('.', $parts);
        return $parent;
    }

    /**
     * Get parent category for hierarchical tags or custom columns
     * @return Entry|null
     */
    public function getParentCategory()
    {
        // Fiction.Historical -> Fiction
        $parent = self::findParentName($this->getTitle());
        if (empty($parent)) {
            return null;
        }
        $find = $parent;
        $parents = $this->getRelatedCategories($find);
        if (count($parents) == 1) {
            return $parents[0];
        }
        return null;
    }

    /**
     * Find related categories for hierarchical tags or series - @todo needs title_sort function in sqlite for series
     * Format: tag_browser_tags(id,name,count,avg_rating,sort)
     * @param mixed $find
     * @return Entry[]
     */
    public function getRelatedCategories($find)
    {
        if (empty(Config::get('calibre_categories_using_hierarchy')) || !in_array(static::CATEGORY, Config::get('calibre_categories_using_hierarchy'))) {
            return [];
        }
        $className = get_class($this);
        $tableName = 'tag_browser_' . static::CATEGORY;
        if (strpos($find, '%') === false) {
            $queryFormat = "SELECT id, name, count FROM {0} WHERE name = ? ORDER BY sort";
        } else {
            $queryFormat = "SELECT id, name, count FROM {0} WHERE name LIKE ? ORDER BY sort";
        }
        $query = str_format($queryFormat, $tableName);
        $result = Database::query($query, [$find], $this->databaseId);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $instance = new $className($post, $this->databaseId);
            array_push($tags, $instance->getEntry($post->count));
        }
        return $entryArray;
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    public static function getInstanceById($id, $database = null)
    {
        $className = static::class;
        if (isset($id)) {
            $query = 'select ' . $className::SQL_COLUMNS . ' from ' . $className::SQL_TABLE . ' where id = ?';
            $result = Database::query($query, [$id], $database);
            if ($post = $result->fetchObject()) {
                return new $className($post, $database);
            }
        }
        $default = static::getDefaultName();
        return new $className((object)['id' => null, 'name' => $default, 'sort' => $default], $database);
    }

    public static function getDefaultName()
    {
        return null;
    }

    public static function getCount($database = null)
    {
        return BaseList::getCountGeneric(static::SQL_TABLE, static::PAGE_ID, static::PAGE_ALL, $database);
    }
}
