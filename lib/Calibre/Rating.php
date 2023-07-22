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
    public const SQL_COLUMNS = "ratings.id as id, ratings.rating as name, count(*) as count";
    public const SQL_ALL_RATINGS ="select {0} from ratings, books_ratings_link where books_ratings_link.rating = ratings.id group by ratings.id order by ratings.rating";
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

    public static function getCount($database = null)
    {
        // str_format (localize("ratings", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database, "ratings");
    }

    public static function getAllRatings($database = null)
    {
        return self::getEntryArray(self::SQL_ALL_RATINGS, [], $database);
    }

    public static function getEntryArray($query, $params, $database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber($query, self::SQL_COLUMNS, $params, self::class, $database, $numberPerPage);
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
