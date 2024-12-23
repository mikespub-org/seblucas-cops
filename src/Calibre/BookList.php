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
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\PageQueryScope;
use Exception;

class BookList
{
    use HasRouteTrait;

    public const PAGE_LETTER = PageId::ALL_BOOKS_LETTER;
    public const PAGE_YEAR = PageId::ALL_BOOKS_YEAR;
    public const ROUTE_LETTER = "page-books-letter";
    public const ROUTE_YEAR = "page-books-year";
    public const ROUTE_RECENT = "page-recent";
    public const SQL_BOOKS_ALL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . ' where 1=1 {1} order by books.sort ';
    public const SQL_BOOKS_BY_FIRST_LETTER = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where upper (books.sort) like ? {1} order by books.sort';
    public const SQL_BOOKS_BY_PUB_YEAR = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where substr(date(books.pubdate), 1, 4) = ? {1} order by books.sort';
    public const SQL_BOOKS_BY_ID_LIST = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books.id = ? {1} order by books.sort';
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
    public const URL_PARAM_LIST = "idlist";

    public const BAD_SEARCH = 'QQQQQ';
    public const BATCH_QUERY = false;

    public Request $request;
    /** @var ?int */
    protected $databaseId = null;
    /** @var ?int */
    protected $numberPerPage = null;
    /** @var ?string */
    public $orderBy = null;
    /** @var array<int, mixed> */
    public $bookList = [];

    /**
     * @param ?Request $request
     * @param ?int $database
     * @param ?int $numberPerPage
     */
    public function __construct($request, $database = null, $numberPerPage = null)
    {
        $this->request = $request ?? new Request();
        $this->databaseId = $database ?? $this->request->database();
        $this->numberPerPage = $numberPerPage ?? $this->request->option("max_item_per_page");
        $this->setOrderBy();
        // get handler based on $this->request
        $this->setHandler($this->request->getHandler());
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
     * @return ?string
     */
    protected function getOrderBy()
    {
        return match ($this->orderBy) {
            'title asc', 'title' => 'books.sort asc',
            'title desc' => 'books.sort desc',
            'author asc', 'author' => 'books.author_sort asc',
            'author desc' => 'books.author_sort desc',
            'pubdate desc', 'pubdate' => 'books.pubdate desc',
            'pubdate asc' => 'books.pubdate asc',
            'rating desc', 'rating' => 'ratings.rating desc',
            'rating asc' => 'ratings.rating asc',
            'timestamp desc', 'timestamp' => 'books.timestamp desc',
            'timestamp asc' => 'books.timestamp asc',
            'count desc', 'count' => 'count desc',
            'count asc' => 'count asc',
            default => $this->orderBy,
        };
    }

    /**
     * Summary of getBookCount
     * @return int
     */
    public function getBookCount()
    {
        if ($this->request->hasFilter()) {
            return $this->getFilterBookCount();
        }
        return Database::querySingle('select count(*) from books', $this->databaseId);
    }

    /**
     * Summary of getFilterBookCount
     * @return int
     */
    public function getFilterBookCount()
    {
        $filter = new Filter($this->request, [], "books", $this->databaseId);
        $filterString = $filter->getFilterString();
        $params = $filter->getQueryParams();
        return Database::countFilter(self::SQL_BOOKS_ALL, 'count(*)', $filterString, $params, $this->databaseId);
    }

    /**
     * Summary of getCount
     * @return array<Entry>
     */
    public function getCount()
    {
        $nBooks = $this->getBookCount();
        $result = [];
        $params = $this->request->getFilterParams();
        $params["db"] ??= $this->databaseId;
        $href = fn() => $this->getRoute(Book::ROUTE_ALL, $params);
        // issue #26 for koreader: section is not supported
        if (!empty(Config::get('titles_split_first_letter'))) {
            $linkArray = [ new LinkNavigation($href, "subsection") ];
        } elseif (!empty(Config::get('titles_split_publication_year'))) {
            $linkArray = [ new LinkNavigation($href, "subsection") ];
        } else {
            $linkArray = [ new LinkFeed($href, null) ];
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
            $href = fn() => $this->getRoute(self::ROUTE_RECENT, $params);
            $count = ($nBooks > Config::get('recentbooks_limit')) ? Config::get('recentbooks_limit') : $nBooks;
            $entry = new Entry(
                localize('recent.title'),
                PageId::ALL_RECENT_BOOKS_ID,
                str_format(localize('recent.list'), $count),
                'text',
                [ new LinkFeed($href, 'http://opds-spec.org/sort/new')],
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
     * @param Base|Author|Format|Identifier|Language|Publisher|Rating|Serie|Tag|CustomColumn $instance
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByInstance($instance, $n)
    {
        if (empty($instance->id) && in_array($instance::class, [Rating::class, Serie::class, Tag::class, Identifier::class, Format::class])) {
            return $this->getBooksWithoutInstance($instance, $n);
        }
        [$query, $params] = $instance->getQuery();
        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksByInstanceOrChildren
     * @param Category $instance
     * @param int $n
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
            $query = str_replace(' = ? ', ' IN (' . str_repeat('?,', count($params) - 1) . '?) ', $query);
            // use distinct here in case books belong to several child categories
            $query = str_ireplace('select ', 'select distinct ', $query);
        }
        return $this->getEntryArray($query, $params, $n);
    }

    /**
     * Summary of getBooksWithoutInstance
     * @param mixed $instance
     * @param int $n
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
     * @param string|int $year
     * @param int $n
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
     * @param string $range
     * @param int $n
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
     * @param int $n
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
     * @param int $n
     * @param array<string> $ignoredCategories
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByQueryScope($queryScope, $n, $ignoredCategories = [])
    {
        $i = 0;
        $critArray = [];
        foreach ([PageQueryScope::AUTHOR,
            PageQueryScope::TAG,
            PageQueryScope::SERIES,
            PageQueryScope::PUBLISHER,
            PageQueryScope::BOOK] as $scope) {
            $key = $scope->value;
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
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getAllBooks($n = 1)
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
        return $this->getCountByGroup('substr(upper(books.sort), 1, 1)', self::ROUTE_LETTER, 'letter');
    }

    /**
     * Summary of getCountByPubYear
     * @return array<Entry>
     */
    public function getCountByPubYear()
    {
        return $this->getCountByGroup('substr(date(books.pubdate), 1, 4)', self::ROUTE_YEAR, 'year');
    }

    /**
     * Summary of getCountByGroup
     * @param string $groupField
     * @param string $routeName
     * @param string $param
     * @return array<Entry>
     */
    public function getCountByGroup($groupField, $routeName, $param)
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
            $params = [$param => $post->groupid, 'db' => $this->databaseId];
            $href = fn() => $this->getRoute($routeName, $params);
            array_push($entryArray, new Entry(
                $post->groupid,
                Book::PAGE_ID . ':' . $param . ':' . $post->groupid,
                str_format(localize('bookword', $post->count), $post->count),
                'text',
                [ new LinkFeed($href, "subsection") ],
                $this->databaseId,
                ucfirst($param),
                $post->count
            ));
        }
        return $entryArray;
    }

    /**
     * Summary of getBooksByFirstLetter
     * @param string $letter
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByFirstLetter($letter, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_FIRST_LETTER, [$letter . '%'], $n);
    }

    /**
     * Summary of getBooksByPubYear
     * @param string|int $year
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByPubYear($year, $n)
    {
        return $this->getEntryArray(self::SQL_BOOKS_BY_PUB_YEAR, [$year], $n);
    }

    /**
     * Summary of getBooksByIdList
     * @param array<int> $idlist
     * @return array{0: EntryBook[], 1: integer}
     */
    public function getBooksByIdList($idlist)
    {
        if (count($idlist) < 1) {
            $this->orderBy ??= 'id';
            return $this->getAllBooks();
        }
        // @todo this is already covered in Filter::addBookIdListFilter()
        $query = self::SQL_BOOKS_BY_ID_LIST;
        $query = str_replace('books.id = ? ', 'books.id IN (' . str_repeat('?,', count($idlist) - 1) . '?) ', $query);
        // no page limit here
        [$entryArray, $totalNumber] = $this->getEntryArray($query, $idlist, -1);
        $sorted = $this->orderBy ?? "id";
        if ($sorted == "id") {
            // sort entryArray by order in idlist
            $order = array_flip($idlist);
            uasort($entryArray, function ($a, $b) use ($order) {
                return $order[$a->book->id] <=> $order[$b->book->id];
            });
        }
        return [$entryArray, $totalNumber];
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
     * @param string $query
     * @param array<mixed> $params
     * @param int $n
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

        /** @phpstan-ignore-next-line */
        if (self::BATCH_QUERY) {
            return $this->batchQuery($totalNumber, $result);
        }
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $this->databaseId);
            $book->setHandler($this->handler);
            array_push($entryArray, $book->getEntry());
        }
        return [$entryArray, $totalNumber];
    }

    /**
     * Summary of batchQuery
     * @param int $totalNumber
     * @param \PDOStatement $result
     * @throws \Exception
     * @return array{0: EntryBook[], 1: integer}
     */
    public function batchQuery($totalNumber, $result)
    {
        $this->bookList = [];
        while ($post = $result->fetchObject()) {
            $book = new Book($post, $this->databaseId);
            $book->setHandler($this->handler);
            $this->bookList[$book->id] = $book;
        }
        $entryArray = [];
        if (count($this->bookList) < 1) {
            return [$entryArray, $totalNumber];
        }
        $this->setAuthors();
        $this->setSerie();
        $this->setPublisher();
        $this->setTags();
        $this->setLanguages();
        $this->setDatas();
        foreach ($this->bookList as $bookId => $book) {
            array_push($entryArray, $book->getEntry());
        }
        $this->bookList = [];
        return [$entryArray, $totalNumber];
    }

    /**
     * Summary of setAuthors
     * @throws \Exception
     * @return void
     */
    public function setAuthors()
    {
        $bookIds = array_keys($this->bookList);
        $baselist = new BaseList(Author::class, $this->request, $this->databaseId);
        $authorIds = $baselist->getInstanceIdsByBookIds($bookIds);
        $authors = $baselist->getInstancesByIds($authorIds);
        foreach ($bookIds as $bookId) {
            $this->bookList[$bookId]->authors = [];
            $authorIds[$bookId] ??= [];
            foreach ($authorIds[$bookId] as $authorId) {
                if (!array_key_exists($authorId, $authors)) {
                    throw new Exception('Unknown author ' . $authorId . ' in ' . var_export($authors, true));
                }
                array_push($this->bookList[$bookId]->authors, $authors[$authorId]);
            }
        }
    }

    /**
     * Summary of setSerie
     * @throws \Exception
     * @return void
     */
    public function setSerie()
    {
        $bookIds = array_keys($this->bookList);
        $baselist = new BaseList(Serie::class, $this->request, $this->databaseId);
        $seriesIds = $baselist->getInstanceIdsByBookIds($bookIds);
        $series = $baselist->getInstancesByIds($seriesIds);
        foreach ($bookIds as $bookId) {
            $this->bookList[$bookId]->serie = false;
            $seriesIds[$bookId] ??= [];
            foreach ($seriesIds[$bookId] as $seriesId) {
                if (!array_key_exists($seriesId, $series)) {
                    throw new Exception('Unknown series ' . $seriesId . ' in ' . var_export($series, true));
                }
                $this->bookList[$bookId]->serie = $series[$seriesId];
                break;
            }
        }
    }

    /**
     * Summary of setPublisher
     * @throws \Exception
     * @return void
     */
    public function setPublisher()
    {
        $bookIds = array_keys($this->bookList);
        $baselist = new BaseList(Publisher::class, $this->request, $this->databaseId);
        $publisherIds = $baselist->getInstanceIdsByBookIds($bookIds);
        $publishers = $baselist->getInstancesByIds($publisherIds);
        foreach ($bookIds as $bookId) {
            $this->bookList[$bookId]->publisher = false;
            $publisherIds[$bookId] ??= [];
            foreach ($publisherIds[$bookId] as $publisherId) {
                if (!array_key_exists($publisherId, $publishers)) {
                    throw new Exception('Unknown publisher ' . $publisherId . ' in ' . var_export($publishers, true));
                }
                $this->bookList[$bookId]->publisher = $publishers[$publisherId];
                break;
            }
        }
    }

    /**
     * Summary of setTags
     * @throws \Exception
     * @return void
     */
    public function setTags()
    {
        $bookIds = array_keys($this->bookList);
        $baselist = new BaseList(Tag::class, $this->request, $this->databaseId);
        $tagIds = $baselist->getInstanceIdsByBookIds($bookIds);
        $tags = $baselist->getInstancesByIds($tagIds);
        foreach ($bookIds as $bookId) {
            $this->bookList[$bookId]->tags = [];
            $tagIds[$bookId] ??= [];
            foreach ($tagIds[$bookId] as $tagId) {
                if (!array_key_exists($tagId, $tags)) {
                    throw new Exception('Unknown tag ' . $tagId . ' in ' . var_export($tags, true));
                }
                array_push($this->bookList[$bookId]->tags, $tags[$tagId]);
            }
        }
    }

    /**
     * Summary of setLanguages
     * @throws \Exception
     * @return void
     */
    public function setLanguages()
    {
        $bookIds = array_keys($this->bookList);
        $baselist = new BaseList(Language::class, $this->request, $this->databaseId);
        $languageIds = $baselist->getInstanceIdsByBookIds($bookIds);
        $languages = $baselist->getInstancesByIds($languageIds);
        foreach ($bookIds as $bookId) {
            $langCodes = [];
            $languageIds[$bookId] ??= [];
            foreach ($languageIds[$bookId] as $languageId) {
                if (!array_key_exists($languageId, $languages)) {
                    throw new Exception('Unknown language ' . $languageId . ' in ' . var_export($languages, true));
                }
                array_push($langCodes, $languages[$languageId]->getTitle());
            }
            $this->bookList[$bookId]->languages = implode(', ', $langCodes);
        }
    }

    /**
     * Summary of setDatas
     * @throws \Exception
     * @return void
     */
    public function setDatas()
    {
        $bookIds = array_keys($this->bookList);
        $baselist = new BaseList(Data::class, $this->request, $this->databaseId);
        $dataIds = $baselist->getInstanceIdsByBookIds($bookIds);
        $datas = $baselist->getInstancesByIds($dataIds);
        $ignored_formats = Config::get('ignored_formats');
        foreach ($bookIds as $bookId) {
            $this->bookList[$bookId]->datas = [];
            $dataIds[$bookId] ??= [];
            foreach ($dataIds[$bookId] as $dataId) {
                if (!array_key_exists($dataId, $datas)) {
                    throw new Exception('Unknown data ' . $dataId . ' in ' . var_export($datas, true));
                }
                if (!empty($ignored_formats) && in_array($datas[$dataId]->format, $ignored_formats)) {
                    continue;
                }
                // we need to set the book here, since we didn't do it above
                $datas[$dataId]->setBook($this->bookList[$bookId]);
                array_push($this->bookList[$bookId]->datas, $datas[$dataId]);
            }
        }
    }
}
