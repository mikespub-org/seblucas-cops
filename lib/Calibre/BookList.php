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
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\Page;
use SebLucas\Cops\Pages\PageQueryResult;

//class BookList extends Base
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

    public const BAD_SEARCH = 'QQQQQ';

    public Request $request;
    protected mixed $databaseId = null;
    protected mixed $numberPerPage = null;
    protected array $ignoredCategories = [];
    public mixed $orderBy = null;

    public function __construct(?Request $request, mixed $database = null, mixed $numberPerPage = null)
    {
        $this->request = $request ?? new Request();
        $this->databaseId = $database ?? $this->request->get('db', null, '/^\d+$/');
        $this->numberPerPage = $numberPerPage ?? $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
        $this->setOrderBy();
    }

    protected function setOrderBy()
    {
        $this->orderBy = $this->request->getSorted();
        //$this->orderBy ??= $this->request->option('sort');
    }

    protected function getOrderBy()
    {
        return match ($this->orderBy) {
            'title' => 'books.sort',
            'author' => 'books.author_sort',
            'pubdate' => 'books.pubdate desc',
            'rating' => 'ratings.rating desc',
            'timestamp' => 'books.timestamp desc',
            'count' => 'count desc',
            default => $this->orderBy,
        };
    }

    public function getBookCount()
    {
        return Database::querySingle('select count(*) from books', $this->databaseId);
    }

    public function getCount()
    {
        global $config;
        $nBooks = $this->getBookCount();
        $result = [];
        $entry = new Entry(
            localize('allbooks.title'),
            Book::PAGE_ID,
            str_format(localize('allbooks.alphabetical', $nBooks), $nBooks),
            'text',
            [new LinkNavigation('?page='.Book::PAGE_ALL, null, null, $this->databaseId)],
            $this->databaseId,
            '',
            $nBooks
        );
        array_push($result, $entry);
        if ($config['cops_recentbooks_limit'] > 0) {
            $entry = new Entry(
                localize('recent.title'),
                Page::ALL_RECENT_BOOKS_ID,
                str_format(localize('recent.list'), $config['cops_recentbooks_limit']),
                'text',
                [ new LinkNavigation('?page='.Page::ALL_RECENT_BOOKS, null, null, $this->databaseId)],
                $this->databaseId,
                '',
                $config['cops_recentbooks_limit']
            );
            array_push($result, $entry);
        }
        return $result;
    }

    /**
     * Summary of getBooksByInstance
     * @param Author|Language|Publisher|Rating|Serie|Tag|CustomColumn $instance
     * @param mixed $n
     * @return array
     */
    public function getBooksByInstance($instance, $n)
    {
        if ($instance instanceof CustomColumn) {
            return $this->getBooksByCustom($instance->customColumnType, $instance->id, $n);
        }
        return $this->getEntryArray($instance::SQL_BOOKLIST, [$instance->id], $n);
    }

    public function getBooksByAuthor($authorId, $n)
    {
        return $this->getEntryArray(Author::SQL_BOOKLIST, [$authorId], $n);
    }

    public function getBooksByRating($ratingId, $n)
    {
        if (empty($ratingId)) {
            return $this->getBooksWithoutRating($n);
        }
        return $this->getEntryArray(Rating::SQL_BOOKLIST, [$ratingId], $n);
    }

    public function getBooksWithoutRating($n)
    {
        return $this->getEntryArray(Rating::SQL_BOOKLIST_NULL, [], $n);
    }

    public function getBooksByPublisher($publisherId, $n)
    {
        return $this->getEntryArray(Publisher::SQL_BOOKLIST, [$publisherId], $n);
    }

    public function getBooksBySeries($serieId, $n)
    {
        global $config;
        if (empty($serieId) && in_array("series", $config['cops_show_not_set_filter'])) {
            return $this->getBooksWithoutSeries($n);
        }
        return $this->getEntryArray(Serie::SQL_BOOKLIST, [$serieId], $n);
    }

    public function getBooksWithoutSeries($n)
    {
        return $this->getEntryArray(Serie::SQL_BOOKLIST_NULL, [], $n);
    }

    public function getBooksByTag($tagId, $n)
    {
        global $config;
        if (empty($tagId) && in_array("tag", $config['cops_show_not_set_filter'])) {
            return $this->getBooksWithoutTag($n);
        }
        return $this->getEntryArray(Tag::SQL_BOOKLIST, [$tagId], $n);
    }

    public function getBooksWithoutTag($n)
    {
        return $this->getEntryArray(Tag::SQL_BOOKLIST_NULL, [], $n);
    }

    public function getBooksByLanguage($languageId, $n)
    {
        return $this->getEntryArray(Language::SQL_BOOKLIST, [$languageId], $n);
    }

    /**
     * Summary of getBooksByCustom
     * @param CustomColumnType $columnType
     * @param integer $id
     * @param integer $n
     * @return array
     */
    public function getBooksByCustom($columnType, $id, $n)
    {
        [$query, $params] = $columnType->getQuery($id);

        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksByCustomYear
     * @param CustomColumnTypeDate $columnType
     * @param mixed $year
     * @param mixed $n
     * @return array
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
     * @return array
     */
    public function getBooksByCustomRange($columnType, $range, $n)
    {
        [$query, $params] = $columnType->getQueryByRange($range);

        return $this->getEntryArray($query, $params, $n);
    }

    public function getBooksWithoutCustom($columnType, $n)
    {
        // use null here to reduce conflict with bool and int custom columns
        [$query, $params] = $columnType->getQuery(null);
        return $this->getEntryArray($query, $params, $n);
    }

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

    public function getAllBooks($n)
    {
        [$entryArray, $totalNumber] = $this->getEntryArray(self::SQL_BOOKS_ALL, [], $n);
        return [$entryArray, $totalNumber];
    }

    public function getCountByFirstLetter()
    {
        return $this->getCountByGroup('substr(upper(books.sort), 1, 1)', Book::PAGE_LETTER, 'letter');
    }

    public function getCountByPubYear()
    {
        return $this->getCountByGroup('substr(date(books.pubdate), 1, 4)', Book::PAGE_YEAR, 'year');
    }

    public function getCountByGroup($groupField, $page, $label)
    {
        $filter = new Filter($this->request, [], "books", $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();

        // @todo check orderBy to sort by count
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
                [new LinkNavigation('?page='.$page.'&id='. rawurlencode($post->groupid), null, null, $this->databaseId)],
                $this->databaseId,
                ucfirst($label),
                $post->count
            ));
        }
        return $entryArray;
    }

    public function getBooksByFirstLetter($letter, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_FIRST_LETTER, [$letter . '%'], $n);
    }

    public function getBooksByPubYear($year, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_PUB_YEAR, [$year], $n);
    }

    public function getAllRecentBooks()
    {
        global $config;
        [$entryArray, ] = $this->getEntryArray(self::SQL_BOOKS_RECENT . $config['cops_recentbooks_limit'], [], -1);
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
            if (str_contains($query, 'order by')) {
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
