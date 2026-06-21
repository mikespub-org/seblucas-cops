<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Database\HasDatabaseTrait;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Input\HasConfigTrait;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Language\HasLocaleTrait;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\Page;
use InvalidArgumentException;

abstract class Base
{
    use HasRouteTrait;
    use HasLocaleTrait;
    use HasConfigTrait;
    use HasDatabaseTrait;

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
    //public const SQL_BOOKLIST_NULL = '';
    public const COMPATIBILITY_XML_ALDIKO = "aldiko";
    public const URL_PARAM = "b";

    /** @var ?int */
    public $id;
    /** @var ?string */
    public $name;
    /** @var ?string */
    public $link;
    /** @var ?int */
    public $count;
    public bool $limitSelf = true;
    /** @var ?int */
    protected $filterLimit = null;
    /** @var array<string, mixed> */
    protected $filterParams = [];

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?DatabaseContext $dbContext
     */
    public function __construct($post, $dbContext = null)
    {
        $this->dbContext = $dbContext;
        $this->databaseId = $dbContext?->getDatabase() ?? null;
        $this->config = $dbContext?->getConfig() ?? null;
        $this->id = $post->id;
        $this->name = $post->name;
        $this->link = property_exists($post, 'link') ? $post->link : null;
        $this->count = property_exists($post, 'count') ? $post->count : null;
        // Note: handler and locale are undefined at this point
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        if (!isset($this->id)) {
            // try to find corresponding instance by name for books in folders
            $instance = static::getInstanceByName($this->name, $this->getDbContext(), $this->locale);
            if ($instance) {
                $instance->setHandler($this->handler);
                //$instance->setLocale($this->locale);
                return $instance->getUri($params);
            }
            // link to overview page with (dummy) query
            $params['db'] = $this->getDatabaseId();
            $params['query'] = $this->getTitle();
            return $this->getRoute(static::ROUTE_ALL, $params);
        }
        $params['id'] = $this->id;
        // we need databaseId here because we use $handler::link()
        $params['db'] = $this->getDatabaseId();
        $params['title'] = $this->getTitle();
        return $this->getRoute(static::ROUTE_DETAIL, $params);
    }

    /**
     * Summary of getParentUri
     * @param array<mixed> $params
     * @return string
     */
    public function getParentUri($params = [])
    {
        // we need databaseId here because we use $handler::link()
        $params['db'] = $this->getDatabaseId();
        return $this->getRoute(static::ROUTE_ALL, $params);
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
        return str_format($this->localize("bookword", $count), (string) $count);
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
        $href = fn() => $this->getUri($params);
        return [ new LinkFeed($href, "subsection") ];
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
        return "title.title";
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
        // we need databaseId here because we use $handler::link()
        $params['db'] = $this->getDatabaseId();
        $params['title'] = $this->getTitle();
        $request = Request::build($params, $this->handler);
        $request->locale = $this->locale;
        if ($this->getConfig() !== null) {
            $request->setConfig($this->getConfig());
        }
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
        //if (empty($this->id) && !empty(static::SQL_BOOKLIST_NULL)) {
        //    return [ static::SQL_BOOKLIST_NULL, []];
        //}
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
        $booklist = new BookList(null, $this->getDbContext());
        $booklist->orderBy = $sort;
        if (!empty($this->locale)) {
            $booklist->setLocale($this->locale);
        }
        [$entryArray, ] = $booklist->getBooksByInstance($this, $n);
        return $entryArray;
    }

    /**
     * Summary of getEntriesByInstance
     * @param class-string<Base> $className
     * @param int $n
     * @param ?string $sort
     * @param ?int $numberPerPage
     * @return array<Entry>
     */
    public function getEntriesByInstance($className, $n = 1, $sort = null, $numberPerPage = null)
    {
        $numberPerPage ??= $this->filterLimit;
        // @todo get rid of extraParams in JsonRenderer and OpdsRenderer as filters should be included in navlink now
        $params = $this->getExtraParams();
        $request = Request::build($params, $this->handler);
        $request->locale = $this->locale;
        if ($this->getConfig() !== null) {
            $request->setConfig($this->getConfig());
        }
        $baselist = new BaseList($className, $request, $this->getDbContext(), $numberPerPage);
        $baselist->orderBy = $sort;
        $baselist->pagination = true;
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
     * Summary of getFormats
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getFormats($n = 1, $sort = null)
    {
        return $this->getEntriesByInstance(Format::class, $n, $sort);
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
     * @see \SebLucas\Cops\Pages\PageWithDetail::getFilters()
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
        return Note::getInstanceByTypeItem($tableName, $this->id, $this->getDbContext());
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    /**
     * Summary of getInstanceById
     * @param string|int|null $id
     * @param ?DatabaseContext $dbContext - allow null here for tests
     * @param ?string $locale
     * @throws \InvalidArgumentException
     * @return static
     */
    public static function getInstanceById($id, $dbContext = null, $locale = null)
    {
        $className = static::class;
        $dbContext ??= new DatabaseContext();
        if (isset($id)) {
            $query = 'select ' . static::getInstanceColumns($dbContext) . ' from ' . $className::SQL_TABLE . ' where id = ?';
            $result = $dbContext->query($query, [$id]);
            if ($post = $result->fetchObject()) {
                return new $className($post, $dbContext);
            }
            if (!empty($id)) {
                $classParts = explode('\\', $className);
                throw new InvalidArgumentException('Invalid ' . end($classParts));
            }
        }
        $default = static::getDefaultName();
        if (!empty($default)) {
            $default = localize($default, -1, $locale);
        }
        // use id = 0 to support route urls
        return new $className((object) ['id' => 0, 'name' => $default, 'sort' => $default], $dbContext);
    }

    /**
     * Summary of getInstanceByName
     * @param string $name
     * @param DatabaseContext $dbContext
     * @param ?string $locale
     * @return static|null
     */
    public static function getInstanceByName($name, $dbContext, $locale = null)
    {
        $className = static::class;
        $query = 'select ' . static::getInstanceColumns($dbContext) . ' from ' . $className::SQL_TABLE . ' where name = ?';
        $result = $dbContext->query($query, [$name]);
        if ($post = $result->fetchObject()) {
            return new $className($post, $dbContext);
        }
        return null;
    }

    /**
     * Summary of getInstanceColumns
     * @param DatabaseContext $dbContext
     * @return string
     */
    public static function getInstanceColumns($dbContext)
    {
        $className = static::class;
        // add link field for database user_version 26 = Calibre version 6.15.0 and later (Apr 7, 2023)
        if (in_array($className::SQL_TABLE, ['languages', 'publishers', 'ratings', 'series', 'tags']) && $dbContext->getUserVersion() > 25) {
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
     * @param DatabaseContext $dbContext
     * @param class-string<BaseHandler> $handler
     * @param ?string $locale
     * @return ?Entry
     */
    public static function getCount($dbContext, $handler, $locale = null)
    {
        $count = $dbContext->querySingle('select count(*) from ' . static::SQL_TABLE);
        return static::getCountEntry($count, $dbContext->getDatabase(), null, $handler, [], $locale);
    }

    /**
     * Summary of getCountEntry
     * @param int $count
     * @param ?int $database
     * @param ?string $numberOfString
     * @param class-string<BaseHandler> $handler
     * @param array<mixed> $params
     * @param ?string $locale
     * @return ?Entry
     */
    public static function getCountEntry($count, $database, $numberOfString, $handler, $params = [], $locale = null)
    {
        if ($count == 0) {
            return null;
        }
        if (!$numberOfString) {
            $numberOfString = static::SQL_TABLE . ".alphabetical";
        }
        $params["db"] ??= $database;
        // @todo replace static calls with handler instance and method calls someday
        $href = fn() => self::getHandlerRoute($handler, static::ROUTE_ALL, $params);
        $entry = new Entry(
            localize(static::SQL_TABLE . ".title", -1, $locale),
            static::PAGE_ID,
            str_format(localize($numberOfString, $count, $locale), (string) $count),
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
