<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Pfitzner
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\Page;

class Rating extends Base
{
    public const PAGE_ID = Page::ALL_RATING_ID;
    public const PAGE_ALL = Page::ALL_RATINGS;
    public const PAGE_DETAIL = Page::RATING_DETAIL;
    public const SQL_TABLE = "ratings";
    public const SQL_LINK_TABLE = "books_ratings_link";
    public const SQL_LINK_COLUMN = "rating";
    public const SQL_SORT = "rating";
    public const SQL_COLUMNS = "ratings.id as id, ratings.rating as name, count(*) as count";
    public const SQL_ALL_ROWS ="select {0} from ratings, books_ratings_link where books_ratings_link.rating = ratings.id {1} group by ratings.id order by ratings.rating";
    public const SQL_BOOKLIST = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_ratings_link.book = books.id and ratings.id = ? {1} order by books.sort';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where ((books.id not in (select book from books_ratings_link)) or (ratings.rating = 0)) {1} order by books.sort';
    public $id;
    public $name;

    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->name = $post->name;
        $this->databaseId = $database;
    }

    public function getUri()
    {
        return "?page=".self::PAGE_DETAIL."&id=$this->id";
    }

    public function getEntryId()
    {
        return self::PAGE_ID.":".$this->id;
    }

    public function getTitle()
    {
        return str_format(localize("ratingword", $this->name/2), $this->name/2);
    }

    public function getParentTitle()
    {
        return localize("ratings.title");
    }

    /** Use inherited class methods to get entries from <Whatever> by ratingId (linked via books) */

    public function getAuthors($n = -1, $sort = null)
    {
        return Author::getEntriesByRatingId($this->id, $n, $sort, $this->databaseId);
    }

    public function getLanguages($n = -1, $sort = null)
    {
        return Language::getEntriesByRatingId($this->id, $n, $sort, $this->databaseId);
    }

    public function getPublishers($n = -1, $sort = null)
    {
        return Publisher::getEntriesByRatingId($this->id, $n, $sort, $this->databaseId);
    }

    public function getRatings($n = -1, $sort = null)
    {
        //return Rating::getEntriesByRatingId($this->id, $n, $sort, $this->databaseId);
    }

    public function getSeries($n = -1, $sort = null)
    {
        return Serie::getEntriesByRatingId($this->id, $n, $sort, $this->databaseId);
    }

    public function getTags($n = -1, $sort = null)
    {
        return Tag::getEntriesByRatingId($this->id, $n, $sort, $this->databaseId);
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    public static function getCount($database = null)
    {
        // str_format (localize("ratings", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database, "ratings");
    }

    /**
     * Summary of getRatingById
     * @param mixed $ratingId
     * @param mixed $database
     * @return Rating
     */
    public static function getRatingById($ratingId, $database = null)
    {
        return self::getInstanceById($ratingId, 0, self::class, $database);
    }
}
