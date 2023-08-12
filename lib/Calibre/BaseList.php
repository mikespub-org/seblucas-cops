<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;

class BaseList
{
    public Request $request;
    public string $className;
    /**
     * @var mixed
     */
    protected $databaseId = null;
    /**
     * @var mixed
     */
    protected $numberPerPage = null;
    protected array $ignoredCategories = [];
    /**
     * @var mixed
     */
    public $orderBy = null;

    /**
     * @param mixed $database
     * @param mixed $numberPerPage
     */
    public function __construct(string $className, ?Request $request, $database = null, $numberPerPage = null)
    {
        $this->className = $className;
        $this->request = $request ?? new Request();
        $this->databaseId = $database ?? $this->request->get('db', null, '/^\d+$/');
        $this->numberPerPage = $numberPerPage ?? $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->setOrderBy();
    }

    protected function setOrderBy()
    {
        $this->orderBy = $this->request->getSorted($this->getSort());
        //$this->orderBy ??= $this->request->option('sort');
    }

    protected function getOrderBy()
    {
        switch ($this->orderBy) {
            case 'title':
                return 'sort';
            case 'count':
                return 'count desc';
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

    public function getTable()
    {
        return $this->className::SQL_TABLE;
    }

    public function getSort()
    {
        return $this->className::SQL_SORT;
    }

    public function getColumns()
    {
        return $this->className::SQL_COLUMNS;
    }

    public function getLinkTable()
    {
        return $this->className::SQL_LINK_TABLE;
    }

    public function getLinkColumn()
    {
        return $this->className::SQL_LINK_COLUMN;
    }

    /** Generic methods inherited by Author, Language, Publisher, Rating, Series, Tag classes */

    public function getInstanceById($id)
    {
        return $this->className::getInstanceById($id, $this->databaseId);
    }

    public function getWithoutEntry()
    {
        $count = $this->countWithoutEntries();
        $instance = $this->getInstanceById(null);
        return $instance->getEntry($count);
    }

    public function getEntryCount()
    {
        return self::getCountGeneric($this->getTable(), $this->className::PAGE_ID, $this->className::PAGE_ALL, $this->databaseId);
    }

    /**
     * Summary of countRequestEntries
     * @return integer
     */
    public function countRequestEntries()
    {
        if ($this->request->hasFilter()) {
            return $this->countEntriesByFilter();
        }
        return $this->countAllEntries();
    }

    public function countAllEntries()
    {
        return Database::querySingle('select count(*) from ' . $this->getTable(), $this->databaseId);
    }

    public function countEntriesByFirstLetter($letter)
    {
        $filterString = 'upper(' . $this->getTable() . '.' . $this->getSort() . ') like ?';
        $param =  $letter . "%";
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        $filter->addFilter($filterString, $param);
        return $this->countFilteredEntries($filter);
    }

    public function countEntriesByFilter()
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        return $this->countFilteredEntries($filter);
    }

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

    public function countWithoutEntries()
    {
        // @todo see BookList::getBooksWithoutCustom() to support CustomColumn
        if (!in_array($this->className, [Rating::class, Serie::class, Tag::class])) {
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
    public function getRequestEntries($n = -1)
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
    public function getAllEntries($n = -1)
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

    public function getAllEntriesByQuery($find, $n = -1, $repeat = 1)
    {
        $query = $this->className::SQL_ROWS_FOR_SEARCH;
        $columns = $this->getColumns();
        // Author has 2 params, the rest 1
        $params = array_fill(0, $repeat, '%' . $find . '%');
        return $this->getEntryArrayWithBookNumber($query, $columns, "", $params, $n);
    }

    public function getCountByFirstLetter()
    {
        // substr(upper(authors.sort), 1, 1)
        $groupField = 'substr(upper(' . $this->getTable() . '.' . $this->getSort() . '), 1, 1)';
        return $this->getCountByGroup($groupField, $this->className::PAGE_LETTER, 'letter');
    }

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
                [new LinkNavigation('?page='.$page.'&id='. rawurlencode($post->groupid), null, null, $this->databaseId)],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }
        return $entryArray;
    }

    public function getEntriesByFirstLetter($letter, $n = -1)
    {
        $query = $this->className::SQL_ROWS_BY_FIRST_LETTER;
        $columns = $this->getColumns();
        $filter = new Filter($this->request, [$letter . "%"], $this->getLinkTable(), $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return $this->getEntryArrayWithBookNumber($query, $columns, $filterString, $params, $n);
    }

    public function getEntriesByFilter($n = -1)
    {
        $filter = new Filter($this->request, [], $this->getLinkTable(), $this->databaseId);
        return $this->getFilteredEntries($filter, $n);
    }

    public function getEntriesByInstance($instance, $n = -1)
    {
        $filter = new Filter([], [], $this->getLinkTable(), $this->databaseId);
        $filter->addInstanceFilter($instance);
        return $this->getFilteredEntries($filter, $n);
    }

    public function getEntriesByCustomValueId($customType, $valueId, $n = -1)
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
    public function getFilteredEntries($filter, $n = -1)
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
     * @param mixed $params
     * @param mixed $n
     * @return array<Entry>
     */
    public function getEntryArrayWithBookNumber($query, $columns, $filter, $params, $n = -1)
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
}
