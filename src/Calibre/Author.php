<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\PageId;

class Author extends Base
{
    public const PAGE_ID = PageId::ALL_AUTHORS_ID;
    public const PAGE_ALL = PageId::ALL_AUTHORS;
    public const PAGE_DETAIL = PageId::AUTHOR_DETAIL;
    public const PAGE_LETTER = PageId::AUTHORS_FIRST_LETTER;
    public const ROUTE_ALL = "page-authors";
    public const ROUTE_DETAIL = "page-author";
    public const ROUTE_LETTER = "page-authors-letter";
    public const SQL_TABLE = "authors";
    public const SQL_LINK_TABLE = "books_authors_link";
    public const SQL_LINK_COLUMN = "author";
    public const SQL_SORT = "sort";
    public const SQL_COLUMNS = "authors.id as id, authors.name as name, authors.sort as sort, authors.link as link";
    public const SQL_ROWS_BY_FIRST_LETTER = "select {0} from authors, books_authors_link where author = authors.id and upper (authors.sort) like ? {1} group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from authors, books_authors_link where author = authors.id and (upper (authors.sort) like ? or upper (authors.name) like ?) {1} group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_ALL_ROWS = "select {0} from authors, books_authors_link where author = authors.id {1} group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_BOOKLIST = 'select {0} from books_authors_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    left outer join books_series_link on books_series_link.book = books.id
    where books_authors_link.book = books.id and author = ? {1} order by series desc, series_index asc, pubdate asc';
    public const URL_PARAM = "a";

    /** @var string */
    public $sort;

    /**
     * Summary of __construct
     * @param \stdClass $post
     * @param ?int $database
     */
    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->name = str_replace("|", ",", (string) $post->name);
        $this->sort = $post->sort;
        $this->link = property_exists($post, 'link') ? $post->link : null;
        $this->count = property_exists($post, 'count') ? $post->count : null;
        $this->databaseId = $database;
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
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return localize("authors.title");
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    /**
     * Summary of getInstancesByBookId
     * @param int $bookId
     * @param ?int $database
     * @return array<Author>
     */
    public static function getInstancesByBookId($bookId, $database = null)
    {
        $query = 'select ' . self::getInstanceColumns($database) . '
from authors, books_authors_link
where author = authors.id
and book = ? order by books_authors_link.id';
        $result = Database::query($query, [$bookId], $database);
        $authorArray = [];
        while ($post = $result->fetchObject()) {
            array_push($authorArray, new Author($post, $database));
        }
        return $authorArray;
    }
}
