<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\Page;

abstract class Base
{
    public const PAGE_ID = PageId::ALL_BASES_ID;
    public const PAGE_ALL = 0;
    public const PAGE_DETAIL = 0;
    public const PAGE_LETTER = 0;
    public const ROUTE_ALL = "";
    public const ROUTE_DETAIL = "";
    public const ROUTE_LETTER = "";
    public const SQL_TABLE = "bases";
    public const SQL_LINK_TABLE = "books_bases_link";
    public const SQL_LINK_COLUMN = "base";
    public const SQL_SORT = "sort";
    public const SQL_COLUMNS = "bases.id as id, bases.name as name, bases.sort as sort, bases.link as link";
    public const SQL_ALL_ROWS = "select {0} from bases, books_bases_link where base = bases.id {1} group by bases.id, bases.name, bases.sort order by sort";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from bases, books_bases_link where base = bases.id and (upper (bases.sort) like ? or upper (bases.name) like ?) {1} group by bases.id, bases.name, bases.sort order by sort";
    public const SQL_ROWS_BY_FIRST_LETTER = "select {0} from bases, books_bases_link where base = bases.id and upper (bases.sort) like ? {1} group by bases.id, bases.name, bases.sort order by sort";
    public const SQL_BOOKLIST = 'select {0} from books_bases_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_bases_link.book = books.id and base = ? {1} order by books.sort';
    public const COMPATIBILITY_XML_ALDIKO = "aldiko";
    public const URL_PARAM = "b";

    /** @var ?int */
    public $id;
    /** @var ?string */
    public $name;
    /** @var ?string */
    public $link;
    public bool $limitSelf = true;
    /** @var ?int */
    protected $databaseId = null;
    /** @var ?int */
    protected $filterLimit = null;
    /** @var array<string, mixed> */
    protected $filterParams = [];
    /** @var class-string */
    protected $handler;

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->name = $post->name;
        $this->link = property_exists($post, 'link') ? $post->link : null;
        $this->databaseId = $database;
    }

    /**
     * Summary of getDatabaseId
     * @return ?int
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $params['id'] = $this->id;
        // we need databaseId here because we use Route::link with $handler
        $params['db'] = $this->getDatabaseId();
        $params['title'] = $this->getTitle();
        return $this->handler::route(static::ROUTE_DETAIL, $params);
    }

    /**
     * Summary of getParentUri
     * @param array<mixed> $params
     * @return string
     */
    public function getParentUri($params = [])
    {
        // we need databaseId here because we use Route::link with $handler
        $params['db'] = $this->getDatabaseId();
        return $this->handler::route(static::ROUTE_ALL, $params);
    }

    /**
     * Summary of getEntryId
     * @return string
     */
    public function getEntryId()
    {
        return static::PAGE_ID . ":" . $this->id;
    }

    /**
     * Summary of getEntryIdByLetter
     * @param string $startingLetter
     * @return string
     */
    public static function getEntryIdByLetter($startingLetter)
    {
        return static::PAGE_ID . ":letter:" . $startingLetter;
    }

    /**
     * Summary of getTitle
     * @return string
     */
    public function getTitle()
    {
        return $this->name;
    }

    /**
     * Summary of getContent
     * @param int $count
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
     * @param array<mixed> $params
     * @return array<LinkFeed>
     */
    public function getLinkArray($params = [])
    {
        // remove for Filter::getEntryArray() - see filterTest
        unset($params[static::URL_PARAM]);
        return [ new LinkFeed($this->getUri($params), "subsection") ];
    }

    /**
     * Summary of setHandler
     * @param class-string $handler
     * @return void
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Summary of getHandler
     * @return class-string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Summary of getClassName
     * @param ?class-string $className
     * @return string
     */
    public function getClassName($className = null)
    {
        $className ??= static::class;
        $classParts = explode('\\', $className);
        return end($classParts);
    }

    /**
     * Summary of getEntry
     * @param int $count
     * @param array<mixed> $params
     * @return Entry
     */
    public function getEntry($count = 0, $params = [])
    {
        $entry = new Entry(
            $this->getTitle(),
            $this->getEntryId(),
            $this->getContent($count),
            $this->getContentType(),
            $this->getLinkArray($params),
            $this->getDatabaseId(),
            $this->getClassName(),
            $count
        );
        $entry->instance = $this;
        return $entry;
    }

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return localize("title.title");
    }

    /**
     * Summary of getPage
     * @param int $count
     * @param array<mixed> $params
     * @todo investigate potential use as alternative to getEntry()
     * @return Page
     */
    public function getPage($count = 0, $params = [])
    {
        $params['id'] = $this->id;
        // we need databaseId here because we use Route::link with $handler
        $params['db'] = $this->getDatabaseId();
        $params['title'] = $this->getTitle();
        $request = Request::build($params, $this->handler);
        $page = PageId::getPage(static::PAGE_DETAIL, $request, $this);
        if (!empty($count)) {
            $page->totalNumber = $count;
        }
        return $page;
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
     * @param int $n
     * @param ?string $sort
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
     * @param class-string $className
     * @param int $n
     * @param ?string $sort
     * @param ?int $database
     * @param ?int $numberPerPage
     * @return array<Entry>
     */
    public function getEntriesByInstance($className, $n = 1, $sort = null, $database = null, $numberPerPage = null)
    {
        $database ??= $this->databaseId;
        $numberPerPage ??= $this->filterLimit;
        // @todo get rid of extraParams in JsonRenderer and OpdsRenderer as filters should be included in navlink now
        $params = $this->getExtraParams();
        $request = Request::build($params, $this->handler);
        $baselist = new BaseList($className, $request, $database, $numberPerPage);
        $baselist->orderBy = $sort;
        return $baselist->getEntriesByInstance($this, $n, $this->filterParams);
    }

    /**
     * Summary of getAuthors
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getAuthors($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Author::class, $n, $sort);
    }

    /**
     * Summary of getLanguages
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getLanguages($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Language::class, $n, $sort);
    }

    /**
     * Summary of getPublishers
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getPublishers($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Publisher::class, $n, $sort);
    }

    /**
     * Summary of getRatings
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getRatings($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Rating::class, $n, $sort);
    }

    /**
     * Summary of getSeries
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getSeries($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Serie::class, $n, $sort);
    }

    /**
     * Summary of getTags
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getTags($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Tag::class, $n, $sort);
    }

    /**
     * Summary of getIdentifiers
     * @param int $n
     * @param ?string $sort
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
     * @param ?int $filterLimit
     * @return void
     */
    public function setFilterLimit($filterLimit)
    {
        $this->filterLimit = $filterLimit;
    }

    /**
     * Summary of getFilterLimit
     * @return ?int
     */
    public function getFilterLimit()
    {
        if (empty($this->filterLimit) || $this->filterLimit < 1) {
            return 999999;
        }
        return $this->filterLimit;
    }

    /**
     * Summary of setFilterParams if we want to filter by virtual library etc.
     * @see Page::getFilters()
     * @param array<string, mixed> $filterParams
     * @return void
     */
    public function setFilterParams($filterParams)
    {
        $this->filterParams = $filterParams;
    }

    /**
     * Summary of getFilterParams
     * @return array<string, mixed>
     */
    public function getFilterParams()
    {
        return $this->filterParams;
    }

    /**
     * Summary of getExtraParams if we want to add extra params to entry links etc.
     * @return array<string, mixed>
     */
    public function getExtraParams()
    {
        return array_merge([static::URL_PARAM => $this->id], $this->filterParams);
    }

    /**
     * Summary of getNote
     * @return Note|null
     */
    public function getNote()
    {
        $className = static::class;
        $tableName = $className::SQL_TABLE;
        return Note::getInstanceByTypeId($tableName, $this->id, $this->databaseId);
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    /**
     * Summary of getInstanceById
     * @param string|int|null $id
     * @param ?int $database
     * @return static
     */
    public static function getInstanceById($id, $database = null)
    {
        $className = static::class;
        if (isset($id)) {
            $query = 'select ' . static::getInstanceColumns($database) . ' from ' . $className::SQL_TABLE . ' where id = ?';
            $result = Database::query($query, [$id], $database);
            if ($post = $result->fetchObject()) {
                return new $className($post, $database);
            }
        }
        $default = static::getDefaultName();
        // use id = 0 to support route urls
        return new $className((object) ['id' => 0, 'name' => $default, 'sort' => $default], $database);
    }

    /**
     * Summary of getInstanceByName
     * @param string $name
     * @param ?int $database
     * @return static|null
     */
    public static function getInstanceByName($name, $database = null)
    {
        $className = static::class;
        $query = 'select ' . static::getInstanceColumns($database) . ' from ' . $className::SQL_TABLE . ' where name = ?';
        $result = Database::query($query, [$name], $database);
        if ($post = $result->fetchObject()) {
            return new $className($post, $database);
        }
        return null;
    }

    /**
     * Summary of getInstanceColumns
     * @param ?int $database
     * @return string
     */
    public static function getInstanceColumns($database = null)
    {
        $className = static::class;
        // add link field for database user_version 26 = Calibre version 6.15.0 and later (Apr 7, 2023)
        if (in_array($className::SQL_TABLE, ['languages', 'publishers', 'ratings', 'series', 'tags']) && Database::getUserVersion($database) > 25) {
            return $className::SQL_COLUMNS . ', ' . $className::SQL_TABLE . '.link as link';
        }
        return $className::SQL_COLUMNS;
    }

    /**
     * Summary of getDefaultName
     * @return ?string
     */
    public static function getDefaultName()
    {
        return null;
    }

    /**
     * Summary of getCount
     * @param ?int $database
     * @param class-string $handler
     * @return ?Entry
     */
    public static function getCount($database, $handler)
    {
        $count = Database::querySingle('select count(*) from ' . static::SQL_TABLE, $database);
        return static::getCountEntry($count, $database, null, $handler);
    }

    /**
     * Summary of getCountEntry
     * @param int $count
     * @param ?int $database
     * @param ?string $numberOfString
     * @param class-string $handler
     * @param array<mixed> $params
     * @return ?Entry
     */
    public static function getCountEntry($count, $database, $numberOfString, $handler, $params = [])
    {
        if ($count == 0) {
            return null;
        }
        if (!$numberOfString) {
            $numberOfString = static::SQL_TABLE . ".alphabetical";
        }
        $params["db"] ??= $database;
        $href = $handler::route(static::ROUTE_ALL, $params);
        $entry = new Entry(
            localize(static::SQL_TABLE . ".title"),
            static::PAGE_ID,
            str_format(localize($numberOfString, $count), $count),
            "text",
            // issue #26 for koreader: section is not supported
            [ new LinkNavigation($href, "subsection") ],
            $database,
            "",
            $count
        );
        return $entry;
    }
}
