<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\PageId;

abstract class Base
{
    public const PAGE_ID = PageId::ALL_BASES_ID;
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
    public const COMPATIBILITY_XML_ALDIKO = "aldiko";

    /** @var mixed */
    public $id;
    /** @var mixed */
    public $name;
    public bool $limitSelf = true;
    /** @var mixed */
    protected $databaseId = null;
    /** @var mixed */
    protected $filterLimit = null;

    /**
     * Summary of __construct
     * @param mixed $post
     * @param mixed $database
     */
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
     * Summary of getUri
     * @return string
     */
    public function getUri()
    {
        return "?page=".static::PAGE_DETAIL."&id=$this->id";
    }

    /**
     * Summary of getParentUri
     * @return string
     */
    public function getParentUri()
    {
        return "?page=".static::PAGE_ALL;
    }

    /**
     * Summary of getEntryId
     * @return string
     */
    public function getEntryId()
    {
        return static::PAGE_ID.":".$this->id;
    }

    /**
     * Summary of getEntryIdByLetter
     * @param mixed $startingLetter
     * @return string
     */
    public static function getEntryIdByLetter($startingLetter)
    {
        return static::PAGE_ID.":letter:".$startingLetter;
    }

    /**
     * Summary of getTitle
     * @return mixed
     */
    public function getTitle()
    {
        return $this->name;
    }

    /**
     * Summary of getContent
     * @param mixed $count
     * @return string
     */
    public function getContent($count = 0)
    {
        return str_format(localize("bookword", $count), $count);
    }

    /**
     * Summary of getContentType
     * @return string
     */
    public function getContentType()
    {
        return "text";
    }

    /**
     * Summary of getLinkArray
     * @return array<LinkFeed>
     */
    public function getLinkArray()
    {
        return [ new LinkFeed($this->getUri(), "subsection", null, $this->getDatabaseId()) ];
    }

    /**
     * Summary of getClassName
     * @param string|null $className
     * @return string
     */
    public function getClassName($className = null)
    {
        $className ??= get_class($this);
        $classParts = explode('\\', $className);
        return end($classParts);
    }

    /**
     * Summary of getEntry
     * @param mixed $count
     * @return Entry
     */
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
     * @return array{0: string, 1: array<mixed>}
     */
    public function getQuery()
    {
        return [ static::SQL_BOOKLIST, [ $this->id ]];
    }

    /**
     * Summary of getLinkTable
     * @return string
     */
    public function getLinkTable()
    {
        return static::SQL_LINK_TABLE;
    }

    /**
     * Summary of getLinkColumn
     * @return string
     */
    public function getLinkColumn()
    {
        return static::SQL_LINK_COLUMN;
    }

    /**
     * Summary of getBooks
     * @param mixed $n
     * @param mixed $sort
     * @return array<EntryBook>
     */
    public function getBooks($n = 1, $sort = null)
    {
        // @todo see if we want to do something special for books, and deal with static:: inheritance
        //return $this->getEntriesByInstance(Book::class, $n, $sort, $this->databaseId);
        $booklist = new BookList(null, $this->databaseId);
        $booklist->orderBy = $sort;
        [$entryArray, ] = $booklist->getBooksByInstance($this, $n);
        return $entryArray;
    }

    /**
     * Summary of getEntriesByInstance
     * @param mixed $className
     * @param mixed $n
     * @param mixed $sort
     * @param mixed $database
     * @param mixed $numberPerPage
     * @return array<Entry>
     */
    public function getEntriesByInstance($className, $n = 1, $sort = null, $database = null, $numberPerPage = null)
    {
        $database ??= $this->databaseId;
        $numberPerPage ??= $this->filterLimit;
        $baselist = new BaseList($className, null, $database, $numberPerPage);
        $baselist->orderBy = $sort;
        return $baselist->getEntriesByInstance($this, $n);
    }

    /**
     * Summary of getAuthors
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getAuthors($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Author::class, $n, $sort);
    }

    /**
     * Summary of getLanguages
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getLanguages($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Language::class, $n, $sort);
    }

    /**
     * Summary of getPublishers
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getPublishers($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Publisher::class, $n, $sort);
    }

    /**
     * Summary of getRatings
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getRatings($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Rating::class, $n, $sort);
    }

    /**
     * Summary of getSeries
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getSeries($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Serie::class, $n, $sort);
    }

    /**
     * Summary of getTags
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getTags($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Tag::class, $n, $sort);
    }

    /**
     * Summary of getIdentifiers
     * @param mixed $n
     * @param mixed $sort
     * @return array<Entry>
     */
    public function getIdentifiers($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Identifier::class, $n, $sort);
    }

    /**
     * Summary of getCustomValues
     * @param CustomColumnType $customType
     * @return array<mixed>
     */
    public function getCustomValues($customType)
    {
        // we'd need to apply getEntriesBy<Whatever>Id from $instance on $customType instance here - too messy
        return [];
    }

    /**
     * Summary of setFilterLimit
     * @param mixed|null $filterLimit
     * @return void
     */
    public function setFilterLimit($filterLimit)
    {
        $this->filterLimit = $filterLimit;
    }

    /**
     * Summary of getFilterLimit
     * @return mixed|null
     */
    public function getFilterLimit()
    {
        if (empty($this->filterLimit) || $this->filterLimit < 1) {
            return 999999;
        }
        return $this->filterLimit;
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    /**
     * Summary of getInstanceById
     * @param mixed $id
     * @param mixed $database
     * @return object
     */
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

    /**
     * Summary of getDefaultName
     * @return mixed
     */
    public static function getDefaultName()
    {
        return null;
    }

    /**
     * Summary of getCount
     * @param mixed $database
     * @return Entry|null
     */
    public static function getCount($database = null)
    {
        return BaseList::getCountGeneric(static::SQL_TABLE, static::PAGE_ID, static::PAGE_ALL, $database);
    }
}
