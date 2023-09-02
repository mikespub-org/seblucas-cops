<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;

class BaseList
{
    public Request $request;
    public string $className;
    /** @var mixed */
    protected $databaseId = null;
    /** @var mixed */
    protected $numberPerPage = null;
    /** @var array<string> */
    //protected $ignoredCategories = [];
    /** @var mixed */
    public $orderBy = null;

    /**
     * @param string $className
     * @param Request|null $request
     * @param mixed $database
     * @param mixed $numberPerPage
     */
    public function __construct($className, $request, $database = null, $numberPerPage = null)
    {
        $this->className = $className;
        $this->request = $request ?? new Request();
        $this->databaseId = $database ?? $this->request->get('db', null, '/^\d+$/');
        $this->numberPerPage = $numberPerPage ?? $this->request->option("max_item_per_page");
        //$this->ignoredCategories = $this->request->option('ignored_categories');
        $this->setOrderBy();
    }

    /**
     * Summary of setOrderBy
     * @return void
     */
    protected function setOrderBy()
    {
        $this->orderBy = $this->request->getSorted($this->getSort());
        //$this->orderBy ??= $this->request->option('sort');
    }

    /**
     * Summary of getOrderBy
     * @return string|null
     */
    protected function getOrderBy()
    {
        switch ($this->orderBy) {
            case 'title':
                return 'sort';
            case 'count':
                return 'count desc, name';
            default:
                return $this->orderBy;
        }
    }

    /**
     * Summary of getDatabaseId
     * @return mixed
     */
    public function getDatabaseId()
    {
        return $this->databaseId;
    }

    /** Use inherited class methods to get entries from <Whatever> by instance (linked via books) */

    /**
     * Summary of getTable
     * @return string
     */
    public function getTable()
    {
        return $this->className::SQL_TABLE;
    }

    /**
     * Summary of getSort
     * @return string
     */
    public function getSort()
    {
        return $this->className::SQL_SORT;
    }

    /**
     * Summary of getColumns
     * @return string
     */
    public function getColumns()
    {
        return $this->className::SQL_COLUMNS . ", count(*) as count";
    }

    /**
     * Summary of getLinkTable
     * @return string
     */
    public function getLinkTable()
    {
        return $this->className::SQL_LINK_TABLE;
    }

    /**
     * Summary of getLinkColumn
     * @return string
     */
    public function getLinkColumn()
    {
        return $this->className::SQL_LINK_COLUMN;
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    /**
     * Summary of getInstanceById
     * @param mixed $id
     * @return mixed
     */
    public function getInstanceById($id)
    {
        return $this->className::getInstanceById($id, $this->databaseId);
    }

    /**
     * Summary of getWithoutEntry
     * @return Entry|null
     */
    public function getWithoutEntry()
    {
        $count = $this->countWithoutEntries();
        $instance = $this->getInstanceById(null);
        return $instance->getEntry($count);
    }

    /**
     * Summary of getEntryCount
     * @return Entry|null
     */
    public function getEntryCount()
    {
        return self::getCountGeneric($this->getTable(), $this->className::PAGE_ID, $this->className::PAGE_ALL, $this->databaseId);
    }

    /**
     * Summary of countRequestEntries
     * @return int
     */
    public function countRequestEntries()
    {
        if ($this->request->hasFilter()) {
            return $this->countEntriesByFilter();
        }
        return $this->countAllEntries();
    }

    /**
     * Summary of countAllEntries
     * @return int
     */
    public function countAllEntries()
    {
        return Database::querySingle('select count(*) from ' . $this->getTable(), $this->databaseId);
    }

    /**
     * Summary of countEntriesByFirstLetter
     * @param string $letter
     * @return int
     */
    public function countEntriesByFirstLetter($letter)
    {
        $filterString = 'upper(' . $this->getTable() . '.' . $this->getSort() . ') like ?';
        $param =  $letter . "%";
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        $filter->addFilter($filterString, $param);
        return $this->countFilteredEntries($filter);
    }

    /**
     * Summary of countEntriesByFilter
     * @return int
     */
    public function countEntriesByFilter()
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        return $this->countFilteredEntries($filter);
    }

    /**
     * Summary of countEntriesByInstance
     * @param Base|Category $instance
     * @return int
     */
    public function countEntriesByInstance($instance)
    {
        $filter = new Filter([], [], $this->getLinkTable(), $this->databaseId);
        $filter->addInstanceFilter($instance);
        return $this->countFilteredEntries($filter);
    }

    /**
     * Summary of countFilteredEntries
     * @param Filter $filter
     * @return int
     */
    public function countFilteredEntries($filter)
    {
        // select {0} from series, books_series_link where series.id = books_series_link.series {1}
        $query = 'select {0} from ' . $this->getTable() . ', ' . $this->getLinkTable() . ' where ' . $this->getTable() . '.id = ' . $this->getLinkTable() . '.' . $this->getLinkColumn() . ' {1}';
        // count(distinct series.id)
        $columns = 'count(distinct ' . $this->getTable() . '.id)';
        // and (exists (select null from books_authors_link, books where books_series_link.book = books.id and books_authors_link.book = books.id and books_authors_link.author = ?))
        $filterString = $filter->getFilterString();
        // [1]
        $params = $filter->getQueryParams();
        return Database::countFilter($query, $columns, $filterString, $params, $this->databaseId);
    }

    /**
     * Summary of countWithoutEntries
     * @return int
     */
    public function countWithoutEntries()
    {
        // @todo see BookList::getBooksWithoutCustom() to support CustomColumn
        if (!in_array($this->className, [Rating::class, Serie::class, Tag::class, Identifier::class])) {
            return 0;
        }
        $query = $this->className::SQL_BOOKLIST_NULL;
        $columns = 'count(distinct books.id)';
        return Database::countFilter($query, $columns, "", [], $this->databaseId);
    }

    /**
     * Summary of getRequestEntries
     * @param mixed $n
     * @return array<Entry>
     */
    public function getRequestEntries($n = 1)
    {
        if ($this->request->hasFilter()) {
            return self::getEntriesByFilter($n);
        }
        return self::getAllEntries($n);
    }

    /**
     * Summary of getAllEntries = same as getAll<Whatever>() in <Whatever> child class
     * @param mixed $n
     * @return array<Entry>
     */
    public function getAllEntries($n = 1)
    {
        $query = $this->className::SQL_ALL_ROWS;
        if (!empty($this->orderBy) && $this->orderBy != $this->getSort() && strpos($this->getColumns(), ' as ' . $this->orderBy) !== false) {
            if (strpos($query, 'order by') !== false) {
                $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|)\s*/i', ' order by ' . $this->getOrderBy() . ' ', $query);
            } else {
                $query .= ' order by ' . $this->getOrderBy() . ' ';
            }
        }
        $columns = $this->getColumns();
        return $this->getEntryArrayWithBookNumber($query, $columns, "", [], $n);
    }

    /**
     * Summary of getAllEntriesByQuery
     * @param string $find
     * @param mixed $n
     * @param mixed $repeat
     * @return array<Entry>
     */
    public function getAllEntriesByQuery($find, $n = 1, $repeat = 1)
    {
        $query = $this->className::SQL_ROWS_FOR_SEARCH;
        $columns = $this->getColumns();
        // Author has 2 params, the rest 1
        $params = array_fill(0, $repeat, '%' . $find . '%');
        return $this->getEntryArrayWithBookNumber($query, $columns, "", $params, $n);
    }

    /**
     * Summary of getCountByFirstLetter
     * @return array<Entry>
     */
    public function getCountByFirstLetter()
    {
        // substr(upper(authors.sort), 1, 1)
        $groupField = 'substr(upper(' . $this->getTable() . '.' . $this->getSort() . '), 1, 1)';
        return $this->getCountByGroup($groupField, $this->className::PAGE_LETTER, 'letter');
    }

    /**
     * Summary of getCountByGroup
     * @param string $groupField
     * @param string $page
     * @param string $label
     * @return array<Entry>
     */
    public function getCountByGroup($groupField, $page, $label)
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();

        if (!in_array($this->orderBy, ['groupid', 'count'])) {
            $this->orderBy = 'groupid';
        }
        $sortBy = $this->getOrderBy();
        // select {0} from authors, books_authors_link where authors.id = books_authors_link.author {1}
        $query = 'select {0} from ' . $this->getTable() . ', ' . $this->getLinkTable() . ' where ' . $this->getTable() . '.id = ' . $this->getLinkTable() . '.' . $this->getLinkColumn() . ' {1}';
        // group by groupid
        $query .= ' group by groupid';
        // order by $sortBy
        $query .= ' order by ' . $sortBy;
        // $groupField as groupid, count(distinct authors.id) as count
        $columns = $groupField . ' as groupid, count(distinct ' . $this->getTable() . '.id) as count';
        $result = Database::queryFilter($query, $columns, $filterString, $params, -1, $this->databaseId);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->groupid,
                $this->className::PAGE_ID.':'.$label.':'.$post->groupid,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [new LinkNavigation('?page='.$page.'&id='. rawurlencode($post->groupid), "subsection", null, $this->databaseId)],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }
        return $entryArray;
    }

    /**
     * Summary of getEntriesByFirstLetter
     * @param mixed $letter
     * @param mixed $n
     * @return array<Entry>
     */
    public function getEntriesByFirstLetter($letter, $n = 1)
    {
        $query = $this->className::SQL_ROWS_BY_FIRST_LETTER;
        $columns = $this->getColumns();
        $filter = new Filter($this->request, [$letter . "%"], $this->getLinkTable(), $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return $this->getEntryArrayWithBookNumber($query, $columns, $filterString, $params, $n);
    }

    /**
     * Summary of getEntriesByFilter
     * @param mixed $n
     * @return array<Entry>
     */
    public function getEntriesByFilter($n = 1)
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        return $this->getFilteredEntries($filter, $n);
    }

    /**
     * Summary of getEntriesByInstance
     * @param Base|Category $instance
     * @param mixed $n
     * @return array<Entry>
     */
    public function getEntriesByInstance($instance, $n = 1)
    {
        $filter = new Filter([], [], $this->getLinkTable(), $this->databaseId);
        $filter->addInstanceFilter($instance);
        $entries = $this->getFilteredEntries($filter, $n);
        $limit = $instance->getFilterLimit();
        // are we at the filter limit for this instance?
        if ($n == 1 && count($entries) < $limit) {
            return $entries;
        }
        // if so, let's see how many entries we're missing
        $total = $this->countEntriesByInstance($instance);
        $count = $total - count($entries);
        if ($count < 1) {
            return $entries;
        }
        // @todo let the caller know there are more entries available
        // @todo we can't use facetGroups here, or OPDS reader thinks we're drilling down :-()
        $className = $instance->getClassName($this->className);
        $title = strtolower($className);
        $title = localize($title . 's.title');
        if ($n > 1) {
            $paging = '&filter=1';
            if ($n > 2) {
                $paging .= '&g[' . $this->className::URL_PARAM . ']=' . strval($n - 1);
            }
            $entry = new Entry(
                localize("paging.previous.alternate") . " " . $title,
                $instance->getEntryId() . ':filter:',
                $instance->getContent($count),
                "text",
                [ new LinkFeed($instance->getUri() . $paging) ],
                $this->databaseId,
                $className,
                $count
            );
            array_push($entries, $entry);
        }
        if ($n < ceil($total / $limit)) {
            $paging = '&filter=1';
            $paging .= '&g[' . $this->className::URL_PARAM . ']=' . strval($n + 1);
            $entry = new Entry(
                localize("paging.next.alternate") . " " . $title,
                $instance->getEntryId() . ':filter:',
                $instance->getContent($count),
                "text",
                [ new LinkFeed($instance->getUri() . $paging) ],
                $this->databaseId,
                $className,
                $count
            );
            array_push($entries, $entry);
        }
        return $entries;
    }

    /**
     * Summary of getEntriesByCustomValueId
     * @param CustomColumnType $customType
     * @param mixed $valueId
     * @param mixed $n
     * @return array<Entry>
     */
    public function getEntriesByCustomValueId($customType, $valueId, $n = 1)
    {
        $filter = new Filter([], [], $this->getLinkTable(), $this->databaseId);
        $filter->addCustomIdFilter($customType, $valueId);
        return $this->getFilteredEntries($filter, $n);
    }

    /**
     * Summary of getFilteredEntries
     * @param mixed $filter
     * @param mixed $n
     * @return array<Entry>
     */
    public function getFilteredEntries($filter, $n = 1)
    {
        $query = $this->className::SQL_ALL_ROWS;
        if (!empty($this->orderBy) && $this->orderBy != $this->getSort() && strpos($this->getColumns(), ' as ' . $this->orderBy) !== false) {
            if (strpos($query, 'order by') !== false) {
                $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|)\s*/i', ' order by ' . $this->getOrderBy() . ' ', $query);
            } else {
                $query .= ' order by ' . $this->getOrderBy() . ' ';
            }
        }
        $columns = $this->getColumns();
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return $this->getEntryArrayWithBookNumber($query, $columns, $filterString, $params, $n);
    }

    /**
     * Summary of getEntryArrayWithBookNumber
     * @param mixed $query
     * @param mixed $columns
     * @param mixed $filter
     * @param mixed $params
     * @param mixed $n
     * @return array<Entry>
     */
    public function getEntryArrayWithBookNumber($query, $columns, $filter, $params, $n)
    {
        $result = Database::queryFilter($query, $columns, $filter, $params, $n, $this->databaseId, $this->numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            /** @var Author|Tag|Serie|Publisher|Language|Rating|Book $instance */
            if ($this->className == Book::class) {
                $post->count = 1;
            }

            $instance = new $this->className($post, $this->databaseId);
            array_push($entryArray, $instance->getEntry($post->count));
        }
        return $entryArray;
    }

    /**
     * Summary of hasChildCategories
     * @return bool
     */
    public function hasChildCategories()
    {
        if (empty(Config::get('calibre_categories_using_hierarchy')) || !in_array($this->className::CATEGORY, Config::get('calibre_categories_using_hierarchy'))) {
            return false;
        }
        return true;
    }

    /**
     * Use the Calibre tag browser view to retrieve all tags or series with count
     * Format: tag_browser_tags(id,name,count,avg_rating,sort)
     * @param mixed $n
     * @return array<Entry>
     */
    public function browseAllEntries($n = 1)
    {
        if (!$this->hasChildCategories()) {
            return [];
        }
        $tableName = 'tag_browser_' . $this->className::CATEGORY;
        $queryFormat = "SELECT id, name, count FROM {0} ORDER BY {1}";
        if (!in_array($this->orderBy, ['id', 'name', 'count', 'sort'])) {
            $this->orderBy = "sort";
        }
        $query = str_format($queryFormat, $tableName, $this->getOrderBy());

        $result = Database::queryFilter($query, "", "", [], $n, $this->databaseId, $this->numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $instance = new $this->className($post, $this->databaseId);
            array_push($entryArray, $instance->getEntry($post->count));
        }
        return $entryArray;
    }

    /**
     * Summary of getInstanceIdsByBookIds
     * @param array<int> $bookIds
     * @return array<int, array<int>>
     */
    public function getInstanceIdsByBookIds($bookIds)
    {
        if (count($bookIds) < 1) {
            return [];
        }
        $queryFormat = 'SELECT book, {1} as instanceId FROM {0} WHERE book IN (' . str_repeat('?,', count($bookIds) - 1) . '?)';
        $query = str_format($queryFormat, $this->getLinkTable(), $this->getLinkColumn());
        $result = Database::query($query, $bookIds, $this->databaseId);

        $instanceIds = [];
        while ($post = $result->fetchObject()) {
            $instanceIds[$post->book] ??= [];
            array_push($instanceIds[$post->book], $post->instanceId);
        }
        return $instanceIds;
    }

    /**
     * Summary of getInstancesByIds
     * @param array<int, array<int>> $instanceIds
     * @return array<int, mixed>
     */
    public function getInstancesByIds($instanceIds)
    {
        $uniqueIds = self::getUniqueInstanceIds($instanceIds);
        if (count($uniqueIds) < 1) {
            return [];
        }
        $query = 'select ' . $this->className::SQL_COLUMNS . ' from ' . $this->className::SQL_TABLE . ' where id IN (' . str_repeat('?,', count($uniqueIds) - 1) . '?)';
        $result = Database::query($query, $uniqueIds, $this->databaseId);
        $instances = [];
        while ($post = $result->fetchObject()) {
            if ($this->className == Data::class) {
                // we don't have the book available here, set later
                $instances[$post->id] = new $this->className($post);
            } else {
                $instances[$post->id] = new $this->className($post, $this->databaseId);
            }
        }
        return $instances;
    }

    /**
     * Summary of getUniqueInstanceIds
     * @param array<int, array<int>> $instanceIds
     * @return array<int>
     */
    public static function getUniqueInstanceIds($instanceIds)
    {
        $uniqueIds = [];
        foreach ($instanceIds as $bookId => $instanceIdList) {
            $uniqueIds = array_values(array_unique(array_merge($uniqueIds, $instanceIdList)));
        }
        return $uniqueIds;
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
            // issue #26 for koreader: section is not supported
            [ new LinkNavigation("?page=".$pageId, "subsection", null, $database)],
            $database,
            "",
            $count
        );
        return $entry;
    }
}
