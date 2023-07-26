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
use SebLucas\Cops\Pages\Page;
use SebLucas\Cops\Pages\PageQueryResult;

class BookList extends Base
{
    public const SQL_BOOKS_ALL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . ' where 1=1 {1} order by books.sort ';
    public const SQL_BOOKS_BY_PUBLISHER = 'select {0} from books_publishers_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_publishers_link.book = books.id and publisher = ? {1} order by publisher';
    public const SQL_BOOKS_BY_FIRST_LETTER = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where upper (books.sort) like ? {1} order by books.sort';
    public const SQL_BOOKS_BY_AUTHOR = 'select {0} from books_authors_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    left outer join books_series_link on books_series_link.book = books.id
    where books_authors_link.book = books.id and author = ? {1} order by series desc, series_index asc, pubdate asc';
    public const SQL_BOOKS_BY_SERIE = 'select {0} from books_series_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_series_link.book = books.id and series = ? {1} order by series_index';
    public const SQL_BOOKS_BY_TAG = 'select {0} from books_tags_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_tags_link.book = books.id and tag = ? {1} order by sort';
    public const SQL_BOOKS_BY_LANGUAGE = 'select {0} from books_languages_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_languages_link.book = books.id and lang_code = ? {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.{3} = ? {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM_BOOL_TRUE = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.value = 1 {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM_BOOL_FALSE = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.value = 0 {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books.id not in (select book from {2}) {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM_RATING = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    left join {2} on {2}.book = books.id
    left join {3} on {3}.id = {2}.{4}
    where {3}.value = ?  order by sort';
    public const SQL_BOOKS_BY_CUSTOM_RATING_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    left join {2} on {2}.book = books.id
    left join {3} on {3}.id = {2}.{4}
    where ((books.id not in (select {2}.book from {2})) or ({3}.value = 0)) {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM_DATE = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and date({2}.value) = ? {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM_DIRECT = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.value = ? {1} order by sort';
    public const SQL_BOOKS_BY_CUSTOM_DIRECT_ID = 'select {0} from {2}, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where {2}.book = books.id and {2}.id = ? {1} order by sort';
    public const SQL_BOOKS_QUERY = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where (
    exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?) or
    exists (select null from tags, books_tags_link where book = books.id and tag = tags.id and tags.name like ?) or
    exists (select null from series, books_series_link on book = books.id and books_series_link.series = series.id and series.name like ?) or
    exists (select null from publishers, books_publishers_link where book = books.id and books_publishers_link.publisher = publishers.id and publishers.name like ?) or
    title like ?) {1} order by books.sort';
    public const SQL_BOOKS_RECENT = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where 1=1 {1} order by timestamp desc limit ';
    public const SQL_BOOKS_BY_RATING = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_ratings_link.book = books.id and ratings.id = ? {1} order by sort';
    public const SQL_BOOKS_BY_RATING_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where ((books.id not in (select book from books_ratings_link)) or (ratings.rating = 0)) {1} order by sort';

    public const BAD_SEARCH = 'QQQQQ';

    public Request $request;
    protected mixed $numberPerPage = null;
    protected array $ignoredCategories = [];

    public function __construct(Request $request, mixed $database = null, mixed $numberPerPage = null)
    {
        $this->request = $request;
        $this->databaseId = $database ?? $this->request->get('db');
        $this->numberPerPage = $numberPerPage ?? $this->request->option("max_item_per_page");
        $this->ignoredCategories = $this->request->option('ignored_categories');
    }

    public function getBookCount()
    {
        return parent::executeQuerySingle('select count(*) from books', $this->databaseId);
    }

    public function getCount()
    {
        global $config;
        $nBooks = parent::executeQuerySingle('select count(*) from books', $this->databaseId);
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

    public function getBooksByAuthor($authorId, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_AUTHOR, [$authorId], $n);
    }

    public function getBooksByRating($ratingId, $n)
    {
        if (empty($ratingId)) {
            return $this->getBooksWithoutRating($n);
        }
        return $this->getEntryArray(self::SQL_BOOKS_BY_RATING, [$ratingId], $n);
    }

    public function getBooksWithoutRating($n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_RATING_NULL, [], $n);
    }

    public function getBooksByPublisher($publisherId, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_PUBLISHER, [$publisherId], $n);
    }

    public function getBooksBySeries($serieId, $n)
    {
        global $config;
        if (empty($serieId) && in_array("series", $config['cops_show_not_set_filter'])) {
            return $this->getBooksWithoutSeries($n);
        }
        return $this->getEntryArray(self::SQL_BOOKS_BY_SERIE, [$serieId], $n);
    }

    public function getBooksWithoutSeries($n)
    {
        $query = str_format(self::SQL_BOOKS_BY_CUSTOM_NULL, "{0}", "{1}", "books_series_link");
        return $this->getEntryArray($query, [], $n);
    }

    public function getBooksByTag($tagId, $n)
    {
        global $config;
        if (empty($tagId) && in_array("tag", $config['cops_show_not_set_filter'])) {
            return $this->getBooksWithoutTag($n);
        }
        return $this->getEntryArray(self::SQL_BOOKS_BY_TAG, [$tagId], $n);
    }

    public function getBooksWithoutTag($n)
    {
        $query = str_format(self::SQL_BOOKS_BY_CUSTOM_NULL, "{0}", "{1}", "books_tags_link");
        return $this->getEntryArray($query, [], $n);
    }

    public function getBooksByLanguage($languageId, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_LANGUAGE, [$languageId], $n);
    }

    /**
     * @param $customColumn CustomColumn
     * @param $id integer
     * @param $n integer
     * @return array
     */
    public function getBooksByCustom($customColumn, $id, $n)
    {
        [$query, $params] = $customColumn->getQuery($id);

        return $this->getEntryArray($query, $params, $n);
    }

    public function getBooksWithoutCustom($customColumn, $n)
    {
        // use null here to reduce conflict with bool and int custom columns
        [$query, $params] = $customColumn->getQuery(null);
        return $this->getEntryArray($query, $params, $n);
    }

    public function getBooksByQuery($query, $n, $ignoredCategories = [])
    {
        $i = 0;
        $critArray = [];
        foreach ([PageQueryResult::SCOPE_AUTHOR,
                       PageQueryResult::SCOPE_TAG,
                       PageQueryResult::SCOPE_SERIES,
                       PageQueryResult::SCOPE_PUBLISHER,
                       PageQueryResult::SCOPE_BOOK] as $key) {
            if (in_array($key, $ignoredCategories) ||
                (!array_key_exists($key, $query) && !array_key_exists('all', $query))) {
                $critArray[$i] = self::BAD_SEARCH;
            } else {
                if (array_key_exists($key, $query)) {
                    $critArray[$i] = $query[$key];
                } else {
                    $critArray[$i] = $query["all"];
                }
            }
            $i++;
        }
        return $this->getEntryArray(self::SQL_BOOKS_QUERY, $critArray, $n);
    }

    public function getBooks($n)
    {
        [$entryArray, $totalNumber] = $this->getEntryArray(self::SQL_BOOKS_ALL, [], $n);
        return [$entryArray, $totalNumber];
    }

    public function getAllBooks()
    {
        $filter = new Filter($this->request, [], "books", $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();

        /** @var \PDOStatement $result */
        [, $result] = parent::executeQuery('select {0}
from books
where 1=1 {1}
group by substr (upper (sort), 1, 1)
order by substr (upper (sort), 1, 1)', 'substr (upper (sort), 1, 1) as title, count(*) as count', $filterString, $params, -1, $this->databaseId);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->title,
                Book::getEntryIdByLetter($post->title),
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [new LinkNavigation('?page='.Book::PAGE_LETTER.'&id='. rawurlencode($post->title), null, null, $this->databaseId)],
                $this->databaseId,
                '',
                $post->count
            ));
        }
        return $entryArray;
    }

    public function getBooksByStartingLetter($letter, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_FIRST_LETTER, [$letter . '%'], $n);
    }

    public function getAllRecentBooks()
    {
        global $config;
        [$entryArray, ] = $this->getEntryArray(self::SQL_BOOKS_RECENT . $config['cops_recentbooks_limit'], [], -1);
        return $entryArray;
    }

    public function getEntryArray($query, $params, $n)
    {
        $filter = new Filter($this->request, $params, "books", $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();

        /** @var integer $totalNumber */
        /** @var \PDOStatement $result */
        [$totalNumber, $result] = parent::executeQuery($query, Book::getBookColumns(), $filterString, $params, $n, $this->databaseId, $this->numberPerPage);

        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $this->databaseId);
            array_push($entryArray, $book->getEntry());
        }
        return [$entryArray, $totalNumber];
    }
}
