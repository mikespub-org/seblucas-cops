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
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\PageQueryResult;

class BookList
{
    public const SQL_BOOKS_ALL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . ' where 1=1 {1} order by books.sort ';
    public const SQL_BOOKS_BY_FIRST_LETTER = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where upper (books.sort) like ? {1} order by books.sort';
    public const SQL_BOOKS_BY_PUB_YEAR = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where substr(date(books.pubdate), 1, 4) = ? {1} order by books.sort';
    public const SQL_BOOKS_QUERY = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where (
    exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?) or
    exists (select null from tags, books_tags_link where book = books.id and tag = tags.id and tags.name like ?) or
    exists (select null from series, books_series_link on book = books.id and books_series_link.series = series.id and series.name like ?) or
    exists (select null from publishers, books_publishers_link where book = books.id and books_publishers_link.publisher = publishers.id and publishers.name like ?) or
    title like ?) {1} order by books.sort';
    public const SQL_BOOKS_RECENT = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where 1=1 {1} order by books.timestamp desc limit ';
    public const URL_PARAM_FIRST = "f";
    public const URL_PARAM_YEAR = "y";

    public const BAD_SEARCH = 'QQQQQ';

    public Request $request;
    /** @var mixed */
    protected $databaseId = null;
    /** @var mixed */
    protected $numberPerPage = null;
    /** @var array<string> */
    protected $ignoredCategories = [];
    /** @var string|null */
    public $orderBy = null;

    /**
     * @param Request|null $request
     * @param mixed $database
     * @param mixed $numberPerPage
     */
    public function __construct($request, $database = null, $numberPerPage = null)
    {
        $this->request = $request ?? new Request();
        $this->databaseId = $database ?? $this->request->get('db', null, '/^\d+$/');
        $this->numberPerPage = $numberPerPage ?? $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->setOrderBy();
    }

    /**
     * Summary of setOrderBy
     * @return void
     */
    protected function setOrderBy()
    {
        $this->orderBy = $this->request->getSorted();
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
                return 'books.sort';
            case 'author':
                return 'books.author_sort';
            case 'pubdate':
                return 'books.pubdate desc';
            case 'rating':
                return 'ratings.rating desc';
            case 'timestamp':
                return 'books.timestamp desc';
            case 'count':
                return 'count desc';
            default:
                return $this->orderBy;
        }
    }

    /**
     * Summary of getBookCount
     * @return mixed
     */
    public function getBookCount()
    {
        return Database::querySingle('select count(*) from books', $this->databaseId);
    }

    /**
     * Summary of getCount
     * @return array<Entry>
     */
    public function getCount()
    {
        $nBooks = $this->getBookCount();
        $result = [];
        if (!empty(Config::get('titles_split_first_letter'))) {
            $linkArray = [new LinkNavigation('?page='.Book::PAGE_ALL, "section", null, $this->databaseId)];
        } elseif (!empty(Config::get('titles_split_publication_year'))) {
            $linkArray = [new LinkNavigation('?page='.Book::PAGE_ALL, "section", null, $this->databaseId)];
        } else {
            $linkArray = [new LinkFeed('?page='.Book::PAGE_ALL, null, null, $this->databaseId)];
        }
        $entry = new Entry(
            localize('allbooks.title'),
            Book::PAGE_ID,
            str_format(localize('allbooks.alphabetical', $nBooks), $nBooks),
            'text',
            $linkArray,
            $this->databaseId,
            '',
            $nBooks
        );
        array_push($result, $entry);
        if (Config::get('recentbooks_limit') > 0) {
            $count = ($nBooks > Config::get('recentbooks_limit')) ? Config::get('recentbooks_limit') : $nBooks;
            $entry = new Entry(
                localize('recent.title'),
                PageId::ALL_RECENT_BOOKS_ID,
                str_format(localize('recent.list'), $count),
                'text',
                [ new LinkFeed('?page='.PageId::ALL_RECENT_BOOKS, 'http://opds-spec.org/sort/new', null, $this->databaseId)],
                $this->databaseId,
                '',
                $count
            );
            array_push($result, $entry);
        }
        return $result;
    }

    /**
     * Summary of getBooksByInstance
     * @param Base|Author|Language|Publisher|Rating|Serie|Tag|CustomColumn $instance
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByInstance($instance, $n)
    {
        if (empty($instance->id) && in_array(get_class($instance), [Rating::class, Serie::class, Tag::class, Identifier::class])) {
            return $this->getBooksWithoutInstance($instance, $n);
        }
        [$query, $params] = $instance->getQuery();
        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksByInstanceOrChildren
     * @param Category $instance
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByInstanceOrChildren($instance, $n)
    {
        [$query, $params] = $instance->getQuery();
        $children = $instance->getChildCategories();
        if (!empty($children)) {
            $childIds = [];
            foreach ($children as $child) {
                array_push($childIds, $child->id);
            }
            $params = array_merge($params, $childIds);
            $query = str_replace(' = ? ', ' IN (' . str_repeat('?,', count($params) - 1) . '?)', $query);
            // use distinct here in case books belong to several child categories
            $query = str_ireplace('select ', 'select distinct ', $query);
        }
        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksWithoutInstance
     * @param mixed $instance
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksWithoutInstance($instance, $n)
    {
        // in_array("series", Config::get('show_not_set_filter'))
        if ($instance instanceof CustomColumn) {
            return $this->getBooksWithoutCustom($instance->customColumnType, $n);
        }
        return $this->getEntryArray($instance::SQL_BOOKLIST_NULL, [], $n);
    }

    /**
     * Summary of getBooksByCustomYear
     * @param CustomColumnTypeDate $columnType
     * @param mixed $year
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByCustomYear($columnType, $year, $n)
    {
        [$query, $params] = $columnType->getQueryByYear($year);

        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksByCustomRange
     * @param CustomColumnTypeInteger $columnType
     * @param mixed $range
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByCustomRange($columnType, $range, $n)
    {
        [$query, $params] = $columnType->getQueryByRange($range);

        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksWithoutCustom
     * @param CustomColumnType $columnType
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksWithoutCustom($columnType, $n)
    {
        // use null here to reduce conflict with bool and int custom columns
        [$query, $params] = $columnType->getQuery(null);
        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksByQueryScope
     * @param array<string, string> $queryScope
     * @param mixed $n
     * @param array<string> $ignoredCategories
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByQueryScope($queryScope, $n, $ignoredCategories = [])
    {
        $i = 0;
        $critArray = [];
        foreach ([PageQueryResult::SCOPE_AUTHOR,
                       PageQueryResult::SCOPE_TAG,
                       PageQueryResult::SCOPE_SERIES,
                       PageQueryResult::SCOPE_PUBLISHER,
                       PageQueryResult::SCOPE_BOOK] as $key) {
            if (in_array($key, $ignoredCategories) ||
                (!array_key_exists($key, $queryScope) && !array_key_exists('all', $queryScope))) {
                $critArray[$i] = self::BAD_SEARCH;
            } else {
                if (array_key_exists($key, $queryScope)) {
                    $critArray[$i] = $queryScope[$key];
                } else {
                    $critArray[$i] = $queryScope["all"];
                }
            }
            $i++;
        }
        return $this->getEntryArray(self::SQL_BOOKS_QUERY, $critArray, $n);
    }

    /**
     * Summary of getAllBooks
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getAllBooks($n)
    {
        [$entryArray, $totalNumber] = $this->getEntryArray(self::SQL_BOOKS_ALL, [], $n);
        return [$entryArray, $totalNumber];
    }

    /**
     * Summary of getCountByFirstLetter
     * @return array<Entry>
     */
    public function getCountByFirstLetter()
    {
        return $this->getCountByGroup('substr(upper(books.sort), 1, 1)', Book::PAGE_LETTER, 'letter');
    }

    /**
     * Summary of getCountByPubYear
     * @return array<Entry>
     */
    public function getCountByPubYear()
    {
        return $this->getCountByGroup('substr(date(books.pubdate), 1, 4)', Book::PAGE_YEAR, 'year');
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
        $filter = new Filter($this->request, [], "books", $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();

        // check orderBy to sort by count
        if (!in_array($this->orderBy, ['groupid', 'count'])) {
            $this->orderBy = 'groupid';
        }
        $sortBy = $this->getOrderBy();
        $result = Database::queryFilter('select {0}
from books
where 1=1 {1}
group by groupid
order by ' . $sortBy, $groupField . ' as groupid, count(*) as count', $filterString, $params, -1, $this->databaseId);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->groupid,
                Book::PAGE_ID.':'.$label.':'.$post->groupid,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [new LinkFeed('?page='.$page.'&id='. rawurlencode($post->groupid), "subsection", null, $this->databaseId)],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }
        return $entryArray;
    }

    /**
     * Summary of getBooksByFirstLetter
     * @param mixed $letter
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByFirstLetter($letter, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_FIRST_LETTER, [$letter . '%'], $n);
    }

    /**
     * Summary of getBooksByPubYear
     * @param mixed $year
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByPubYear($year, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_PUB_YEAR, [$year], $n);
    }

    /**
     * Summary of getAllRecentBooks
     * @return array<EntryBook>
     */
    public function getAllRecentBooks()
    {
        [$entryArray, ] = $this->getEntryArray(self::SQL_BOOKS_RECENT . Config::get('recentbooks_limit'), [], -1);
        return $entryArray;
    }

    /**
     * Summary of getEntryArray
     * @param mixed $query
     * @param mixed $params
     * @param mixed $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getEntryArray($query, $params, $n)
    {
        $filter = new Filter($this->request, $params, "books", $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();

        if (isset($this->orderBy) && $this->orderBy !== Book::SQL_SORT) {
            if (strpos($query, 'order by') !== false) {
                $query = preg_replace('/\s+order\s+by\s+[\w.]+(\s+(asc|desc)|)\s*/i', ' order by ' . $this->getOrderBy() . ' ', $query);
            } else {
                $query .= ' order by ' . $this->getOrderBy() . ' ';
            }
        }

        /** @var integer $totalNumber */
        /** @var \PDOStatement $result */
        [$totalNumber, $result] = Database::queryTotal($query, Book::getBookColumns(), $filterString, $params, $n, $this->databaseId, $this->numberPerPage);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $this->databaseId);
            array_push($entryArray, $book->getEntry());
        }
        return [$entryArray, $totalNumber];
    }
}
