<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\HasRouteTrait;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\PageId;
use Exception;

/**
 * A single calibre custom column
 */
abstract class CustomColumnType
{
    use HasRouteTrait;

    public const PAGE_ID = PageId::ALL_CUSTOMS_ID;
    public const PAGE_ALL = PageId::ALL_CUSTOMS;
    public const PAGE_DETAIL = PageId::CUSTOM_DETAIL;
    public const ROUTE_ALL = "page-customtype";
    public const ROUTE_DETAIL = "page-custom";
    public const ROUTE_TYPES = "restapi-customtypes";
    public const SQL_TABLE = "custom_columns";
    public const SQL_BOOKLIST_LINK = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.{3} = ? {1} order by books.sort';
    public const SQL_BOOKLIST_ID = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.id = ? {1} order by books.sort';
    public const SQL_BOOKLIST_VALUE = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.value = ? {1} order by books.sort';
    public const SQL_BOOKLIST_RANGE = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.value >= ? and {2}.value <= ? {1} order by {2}.value';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books.id not in (select book from {2}) {1} order by books.sort';
    public const ALL_WILDCARD         = ["*"];

    public const TYPE_TEXT      = "text";        // type 1 + 2 (calibre)
    public const TYPE_CSV       = "csv";         // type 2 (internal)
    public const TYPE_COMMENT   = "comments";    // type 3
    public const TYPE_SERIES    = "series";      // type 4
    public const TYPE_ENUM      = "enumeration"; // type 5
    public const TYPE_DATE      = "datetime";    // type 6
    public const TYPE_FLOAT     = "float";       // type 7
    public const TYPE_INT       = "int";         // type 8
    public const TYPE_RATING    = "rating";      // type 9
    public const TYPE_BOOL      = "bool";        // type 10
    public const TYPE_COMPOSITE = "composite";   // type 11 + 12

    /** @var array<int, CustomColumnType>  */
    protected static $customColumnCacheID = [];

    /** @var array<string, CustomColumnType>  */
    protected static $customColumnCacheLookup = [];

    /** @var integer the id of this column */
    public $customId;
    /** @var string name/title of this column */
    public $columnTitle;
    /** @var string the datatype of this column (one of the TYPE_* constant values) */
    public $datatype;
    /** @var null|Entry[] */
    protected $customValues = null;
    /** @var ?int */
    protected $databaseId = null;
    /** @var ?int */
    protected $numberPerPage = -1;
    /** @var array<string, mixed> */
    protected $displaySettings = [];

    /**
     * Summary of __construct
     * @param int $customId
     * @param string $datatype
     * @param ?int $database
     * @param array<string, mixed> $displaySettings
     */
    protected function __construct($customId, $datatype, $database = null, $displaySettings = [])
    {
        $this->columnTitle = static::getTitleByCustomID($customId, $database);
        $this->customId = $customId;
        $this->datatype = $datatype;
        $this->customValues = null;
        $this->databaseId = $database;
        $this->numberPerPage = Config::get('max_item_per_page');
        $this->displaySettings = $displaySettings;
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
     * Get the name of the sqlite table for this column
     *
     * @return string
     */
    protected function getTableName()
    {
        return "custom_column_{$this->customId}";
    }

    /**
     * The URI to show all the values of this column
     *
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $params['custom'] = $this->customId;
        // we need databaseId here because we use Route::link with $handler
        $params['db'] = $this->getDatabaseId();
        return $this->getRoute(static::ROUTE_ALL, $params);
    }

    /**
     * The EntryID to show all the values of this column
     *
     * @return string
     */
    public function getEntryId()
    {
        return static::PAGE_ID . ":" . $this->customId;
    }

    /**
     * The title of this column
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->columnTitle;
    }

    /**
     * Summary of getContentType
     * @return mixed|string
     */
    public function getContentType()
    {
        // @checkme convert "csv" back to "text" here?
        return $this->datatype;
    }

    /**
     * Summary of getLinkArray
     * @param array<mixed> $params
     * @return array<LinkNavigation>
     */
    public function getLinkArray($params = [])
    {
        $href = fn() => $this->getUri($params);
        // issue #26 for koreader: section is not supported
        return [ new LinkNavigation($href, "subsection") ];
    }

    /**
     * The description used in the index page
     *
     * @param int $count
     * @return string
     */
    public function getContent($count = 0)
    {
        $desc = $this->getDatabaseDescription();
        if ($desc === null || empty($desc)) {
            $desc = str_format(localize("customcolumn.description"), $this->getTitle());
        }
        return $desc;
    }

    /**
     * The description of this column as it is definied in the database
     *
     * @return ?string
     */
    public function getDatabaseDescription()
    {
        $query = 'SELECT display FROM custom_columns WHERE id = ?';
        $result = Database::query($query, [$this->customId], $this->databaseId);
        if ($post = $result->fetchObject()) {
            $json = json_decode($post->display);
            return (isset($json->description) && !empty($json->description)) ? $json->description : null;
        }
        return null;
    }

    /**
     * Get the Entry for this column
     * This is used in the initializeContent method to display e.g. the index page
     *
     * @param array<mixed> $params
     * @return Entry
     */
    public function getCount($params = [])
    {
        // @todo do we want to filter by virtual library etc. here?
        $pcount = $this->getDistinctValueCount();
        $ptitle = $this->getTitle();
        $pid = $this->getEntryId();
        $pcontent = $this->getContent($pcount);
        // @checkme convert "csv" back to "text" here?
        $pcontentType = $this->getContentType();
        $database = $this->getDatabaseId();
        $plinkArray = $this->getLinkArray($params);
        $pclass = "";

        return new Entry($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $database, $pclass, $pcount);
    }

    /**
     * Return an entry array for all possible (in the DB used) values of this column
     * These are the values used in the PageAllCustoms() page
     *
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function getAllCustomValues($n = -1, $sort = null)
    {
        // lazy loading
        if ($this->customValues == null) {
            $this->customValues = $this->getAllCustomValuesFromDatabase($n, $sort);
        }

        return $this->customValues;
    }

    /**
     * Summary of getPaginatedResult
     * @param string $query
     * @param array<mixed> $params
     * @param int $n
     * @return \PDOStatement
     */
    public function getPaginatedResult($query, $params = [], $n = 1)
    {
        if ($this->numberPerPage != -1 && $n != -1) {
            $query .= " LIMIT ?, ?";
            array_push($params, ($n - 1) * $this->numberPerPage, $this->numberPerPage);
        }
        $result = Database::query($query, $params, $this->databaseId);

        return $result;
    }

    /**
     * Get the amount of distinct values for this column
     *
     * @return int
     */
    public function getDistinctValueCount()
    {
        $queryFormat = "SELECT COUNT(DISTINCT value) AS count FROM {0}";
        $query = str_format($queryFormat, $this->getTableName());
        // @todo do we want to filter by virtual library etc. here?
        return Database::querySingle($query, $this->databaseId);
    }

    /**
     * Use the Calibre tag browser view to retrieve all custom values with count
     * Format: tag_browser_custom_column_2(id,value,count,avg_rating,sort)
     * @param int $n
     * @param ?string $sort
     * @return array<Entry>
     */
    public function browseAllCustomValues($n = -1, $sort = null)
    {
        if (!$this->hasChildCategories()) {
            return [];
        }
        $tableName = 'tag_browser_' . $this->getTableName();
        $queryFormat = "SELECT id, value, count FROM {0} ORDER BY {1}";
        if (!in_array($sort, ['id', 'value', 'count', 'sort'])) {
            $sort = "sort";
        }
        if ($sort == 'count') {
            $sort .= ' desc, value';
        }
        $query = str_format($queryFormat, $tableName, $sort);

        $result = $this->getPaginatedResult($query, [], $n);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $customcolumn = new CustomColumn($post->id, $post->value, $this);
            array_push($entryArray, $customcolumn->getEntry($post->count));
        }
        return $entryArray;
    }

    /**
     * Summary of hasChildCategories
     * @return bool
     */
    public function hasChildCategories()
    {
        // @todo this only works with column titles/names, not the lookup names used elsewhere
        if (empty(Config::get('calibre_categories_using_hierarchy')) || !in_array($this->columnTitle, Config::get('calibre_categories_using_hierarchy'))) {
            return false;
        }
        return true;
    }

    /**
     * Find related categories for hierarchical custom columns
     * Format: tag_browser_custom_column_2(id,value,count,avg_rating,sort)
     * @param string|array<mixed> $find pattern match or exact match for name, or array of child ids
     * @return array<CustomColumn>
     */
    public function getRelatedCategories($find)
    {
        if (!$this->hasChildCategories()) {
            return [];
        }
        $tableName = 'tag_browser_' . $this->getTableName();
        if (is_array($find)) {
            $queryFormat = "SELECT id, value, count FROM {0} WHERE id IN (" . str_repeat("?,", count($find) - 1) . "?) ORDER BY sort";
            $params = $find;
        } elseif (!str_contains($find, '%')) {
            $queryFormat = "SELECT id, value, count FROM {0} WHERE value = ? ORDER BY sort";
            $params = [$find];
        } else {
            $queryFormat = "SELECT id, value, count FROM {0} WHERE value LIKE ? ORDER BY sort";
            $params = [$find];
        }
        $query = str_format($queryFormat, $tableName);
        $result = Database::query($query, $params, $this->databaseId);

        $instances = [];
        while ($post = $result->fetchObject()) {
            $customcolumn = new CustomColumn($post->id, $post->value, $this);
            $customcolumn->count = $post->count;
            array_push($instances, $customcolumn);
        }
        return $instances;
    }

    /**
     * Encode a value of this column ready to be displayed in an HTML document
     *
     * @param integer|string $value
     * @return string
     */
    public function encodeHTMLValue($value)
    {
        return htmlspecialchars($value);
    }

    /**
     * Return this object as an array
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            'customId'        => $this->customId,
            'columnTitle'     => $this->columnTitle,
            'datatype'        => $this->datatype,
            'displaySettings' => $this->displaySettings,
            //'customValues'  => $this->customValues,
        ];
    }

    /**
     * Get the datatype & display-settings of a CustomColumn by its customID
     *
     * @param int $customId
     * @param ?int $database
     * @return array<mixed>
     */
    protected static function getDatatypeAndDisplaySettingsByCustomID($customId, $database = null)
    {
        $query = 'SELECT datatype, is_multiple, display FROM custom_columns WHERE id = ?';
        $result = Database::query($query, [$customId], $database);
        if ($post = $result->fetchObject()) {

            $settings = $post->display ? json_decode($post->display, true) : [];

            // handle case where we have several values, e.g. array of text for type 2 (csv)
            if ($post->datatype === "text" && $post->is_multiple === 1) {
                return ["csv", $settings];
            }
            return [$post->datatype, $settings];
        }
        return [null, []];
    }

    /**
     * Create a CustomColumnType by CustomID
     *
     * @param int $customId the id of the custom column
     * @param ?int $database
     * @param bool $cached
     * @return ?CustomColumnType
     * @throws Exception If the $customId is not found or the datatype is unknown
     */
    public static function createByCustomID($customId, $database = null, $cached = true)
    {
        if (!$cached) {
            static::$customColumnCacheID = [];
        }
        // Reuse already created CustomColumns for performance
        if (array_key_exists($customId, static::$customColumnCacheID)) {
            return static::$customColumnCacheID[$customId];
        }

        [$datatype, $displaySettings] = static::getDatatypeAndDisplaySettingsByCustomID($customId, $database);

        static::$customColumnCacheID[$customId] = match ($datatype) {
            static::TYPE_TEXT => new CustomColumnTypeText($customId, static::TYPE_TEXT, $database, $displaySettings),
            static::TYPE_CSV => new CustomColumnTypeText($customId, static::TYPE_CSV, $database, $displaySettings),
            static::TYPE_SERIES => new CustomColumnTypeSeries($customId, $database, $displaySettings),
            static::TYPE_ENUM => new CustomColumnTypeEnumeration($customId, $database, $displaySettings),
            static::TYPE_COMMENT => new CustomColumnTypeComment($customId, $database, $displaySettings),
            static::TYPE_DATE => new CustomColumnTypeDate($customId, $database, $displaySettings),
            static::TYPE_FLOAT => new CustomColumnTypeFloat($customId, $database, $displaySettings),
            static::TYPE_INT => new CustomColumnTypeInteger($customId, static::TYPE_INT, $database, $displaySettings),
            static::TYPE_RATING => new CustomColumnTypeRating($customId, $database, $displaySettings),
            static::TYPE_BOOL => new CustomColumnTypeBool($customId, $database, $displaySettings),
            static::TYPE_COMPOSITE => null,
            default => throw new Exception("Unkown column type: " . $datatype),
        };
        return static::$customColumnCacheID[$customId];
    }

    /**
     * Create a CustomColumnType by its lookup name
     *
     * @param string $lookup the lookup-name of the custom column
     * @param ?int $database
     * @param bool $cached
     * @return ?CustomColumnType
     */
    public static function createByLookup($lookup, $database = null, $cached = true)
    {
        if (!$cached) {
            static::$customColumnCacheLookup = [];
        }
        // Reuse already created CustomColumns for performance
        if (array_key_exists($lookup, static::$customColumnCacheLookup)) {
            return static::$customColumnCacheLookup[$lookup];
        }

        $query = 'SELECT id FROM custom_columns WHERE label = ?';
        $result = Database::query($query, [$lookup], $database);
        if ($post = $result->fetchObject()) {
            return static::$customColumnCacheLookup[$lookup] = static::createByCustomID($post->id, $database);
        }
        return static::$customColumnCacheLookup[$lookup] = null;
    }

    /**
     * Get the title of a CustomColumn by its customID
     *
     * @param int $customId
     * @param ?int $database
     * @return string
     */
    protected static function getTitleByCustomID($customId, $database = null)
    {
        $query = 'SELECT name FROM custom_columns WHERE id = ?';
        $result = Database::query($query, [$customId], $database);
        if ($post = $result->fetchObject()) {
            return $post->name;
        }
        return "";
    }

    /**
     * Check the list of custom columns requested (and expand the wildcard if needed)
     *
     * @param array<string> $columnList
     * @param ?int $database
     * @return array<string>
     */
    public static function checkCustomColumnList($columnList, $database = null)
    {
        if ($columnList === static::ALL_WILDCARD) {
            $columnList = array_keys(static::getAllCustomColumns($database));
        }
        return $columnList;
    }

    /**
     * Get all defined custom columns from the database
     *
     * @param ?int $database
     * @return array<string, array<mixed>>
     */
    public static function getAllCustomColumns($database = null)
    {
        $query = 'SELECT id, label, name, datatype, display, is_multiple, normalized FROM custom_columns';
        $result = Database::query($query, [], $database);
        $columns = [];
        while ($post = $result->fetchObject()) {
            $columns[$post->label] = (array) $post;
        }
        return $columns;
    }

    /**
     * Get the query to find all books with a specific value of this column
     * the returning array has two values:
     *  - first the query (string)
     *  - second an array of all PreparedStatement parameters
     *
     * @param string|integer|null $id the id of the searched value
     * @return ?array{0: string, 1: array<mixed>}
     */
    abstract public function getQuery($id);

    /**
     * Summary of getFilter
     * @param string|int|null $id
     * @param ?string $parentTable
     * @return ?array{0: string, 1: array<mixed>}
     */
    abstract public function getFilter($id, $parentTable = null);

    /**
     * Summary of getNotSetFilter
     * @param ?string $parentTable
     * @return ?array{0: string, 1: array<mixed>}
     */
    public function getNotSetFilter($parentTable = null)
    {
        if (method_exists($this, 'getTableLinkName')) {
            $linkTable = $this->getTableLinkName();
        } else {
            $linkTable = $this->getTableName();
        }
        // @todo doesn't make sense in this case
        if (empty($parentTable) || $parentTable == $linkTable) {
            $filter = "false";
        } elseif ($parentTable == "books") {
            $filter = "books.id not in (select book from {$linkTable})";
        } else {
            $filter = "exists (select null from books where {$parentTable}.book = books.id and books.id not in (select book from {$linkTable}))";
        }
        return [$filter, []];
    }

    /**
     * Get a CustomColumn for a specified (by ID) value
     *
     * @param string|int|null $id the id of the searched value
     * @return ?CustomColumn
     */
    abstract public function getCustom($id);

    /**
     * Return an entry array for all possible (in the DB used) values of this column by querying the database
     *
     * @param int $n
     * @param ?string $sort
     * @return ?array<Entry>
     */
    abstract protected function getAllCustomValuesFromDatabase($n = -1, $sort = null);

    /**
     * Find the value of this column for a specific book
     *
     * @param Book $book
     * @return CustomColumn
     */
    abstract public function getCustomByBook($book);

    /**
     * Is this column searchable by value
     * only searchable columns can be displayed on the index page
     *
     * @return bool
     */
    abstract public function isSearchable();
}
