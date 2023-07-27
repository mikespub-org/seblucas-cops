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

    /** Use inherited class methods to get entries from <Whatever> by ratingId (linked via books) */

    public function getBooks($n = -1)
    {
        return Book::getEntriesByRatingId($this->id, $n, $this->databaseId);
    }

    public function getAuthors($n = -1)
    {
        return Author::getEntriesByRatingId($this->id, $n, $this->databaseId);
    }

    public function getLanguages($n = -1)
    {
        return Language::getEntriesByRatingId($this->id, $n, $this->databaseId);
    }

    public function getPublishers($n = -1)
    {
        return Publisher::getEntriesByRatingId($this->id, $n, $this->databaseId);
    }

    public function getRatings($n = -1)
    {
        //return Rating::getEntriesByRatingId($this->id, $n, $this->databaseId);
    }

    public function getSeries($n = -1)
    {
        return Serie::getEntriesByRatingId($this->id, $n, $this->databaseId);
    }

    public function getTags($n = -1)
    {
        return Tag::getEntriesByRatingId($this->id, $n, $this->databaseId);
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    public static function getCount($database = null)
    {
        // str_format (localize("ratings", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database, "ratings");
    }

    public static function getAllRatings($n = -1, $database = null)
    {
        return self::getEntryArray(self::SQL_ALL_ROWS, [], $n, $database);
    }

    public static function getEntryArray($query, $params, $n = -1, $database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber($query, self::SQL_COLUMNS, "", $params, self::class, $n, $database, $numberPerPage);
    }

    public static function getRatingById($ratingId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select ratings.id as id, ratings.rating as name from ratings where ratings.id = ?');
        $result->execute([$ratingId]);
        if ($post = $result->fetchObject()) {
            return new Rating($post, $database);
        }
        return new Rating((object)['id' => null, 'name' => 0], $database);
    }
}
