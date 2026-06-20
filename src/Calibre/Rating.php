<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Michael Pfitzner
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Input\RequestConfig;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Pages\PageId;

class Rating extends Base
{
    public const PAGE_ID = PageId::ALL_RATING_ID;
    public const PAGE_ALL = PageId::ALL_RATINGS;
    public const PAGE_DETAIL = PageId::RATING_DETAIL;
    public const ROUTE_ALL = "page-ratings";
    public const ROUTE_DETAIL = "page-rating";
    public const SQL_TABLE = "ratings";
    public const SQL_LINK_TABLE = "books_ratings_link";
    public const SQL_LINK_COLUMN = "rating";
    public const SQL_SORT = "rating";
    public const SQL_COLUMNS = "ratings.id as id, ratings.rating as name";
    public const SQL_ALL_ROWS = "select {0} from ratings, books_ratings_link where books_ratings_link.rating = ratings.id {1} group by ratings.id order by ratings.rating";
    public const SQL_BOOKLIST = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_ratings_link.book = books.id and ratings.id = ? {1} order by books.sort';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where ((books.id not in (select book from books_ratings_link)) or (ratings.rating = 0)) {1} order by books.sort';
    public const URL_PARAM = "r";

    /**
     * Summary of getTitle
     * @return string
     */
    public function getTitle()
    {
        $rating = intval($this->name) / 2;
        return str_format($this->localize("ratingword", $rating), (string) $rating);
    }

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return "ratings.title";
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    /**
     * Summary of getCount
     * @param DatabaseContext $dbContext
     * @param class-string<BaseHandler> $handler
     * @param ?string $locale
     * @return ?Entry
     */
    public static function getCount($dbContext, $handler, $locale = null)
    {
        $count = $dbContext->querySingle('select count(*) from ' . self::SQL_TABLE);
        // str_format (localize("ratings", count(array))
        return self::getCountEntry($count, $dbContext->getDatabase(), "ratings", $handler, [], $locale);
    }

    /**
     * Summary of getDefaultName
     * @return int
     */
    public static function getDefaultName()
    {
        return 0;
    }
}
