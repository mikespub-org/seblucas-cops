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

class Serie extends Category
{
    public const PAGE_ID = PageId::ALL_SERIES_ID;
    public const PAGE_ALL = PageId::ALL_SERIES;
    public const PAGE_DETAIL = PageId::SERIE_DETAIL;
    public const PAGE_LETTER = PageId::SERIES_FIRST_LETTER;
    public const ROUTE_ALL = "page-series";
    public const ROUTE_DETAIL = "page-serie";
    public const ROUTE_LETTER = "page-series-letter";
    public const SQL_TABLE = "series";
    public const SQL_LINK_TABLE = "books_series_link";
    public const SQL_LINK_COLUMN = "series";
    public const SQL_SORT = "sort";
    public const SQL_COLUMNS = "series.id as id, series.name as name, series.sort as sort";
    public const SQL_ALL_ROWS = "select {0} from series, books_series_link where series.id = series {1} group by series.id, series.name, series.sort order by series.sort";
    public const SQL_ROWS_BY_FIRST_LETTER = "select {0} from series, books_series_link where series.id = series and upper (series.name) like ? {1} group by series.id, series.name, series.sort order by series.sort";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from series, books_series_link where series.id = series and upper (series.name) like ? {1} group by series.id, series.name, series.sort order by series.sort";
    public const SQL_BOOKLIST = 'select {0} from books_series_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_series_link.book = books.id and series = ? {1} order by series_index';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books.id not in (select book from books_series_link) {1} order by books.sort';
    public const SQL_CREATE = 'insert into series (name) values (?)';  // sort will be set by insert trigger here
    public const URL_PARAM = "s";
    public const CATEGORY = "series";

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return localize("series.title");
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    /**
     * Summary of getInstanceByBookId
     * @param int $bookId
     * @param ?int $database
     * @return Serie|false
     */
    public static function getInstanceByBookId($bookId, $database = null)
    {
        $query = 'select ' . self::getInstanceColumns($database) . '
from books_series_link, series
where series.id = series and book = ?';
        $result = Database::query($query, [$bookId], $database);
        if ($post = $result->fetchObject()) {
            return new Serie($post, $database);
        }
        return false;
    }

    /**
     * Summary of getDefaultName
     * @return string
     */
    public static function getDefaultName()
    {
        return localize("seriesword.none");
    }
}
