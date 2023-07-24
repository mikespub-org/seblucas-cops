<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\Page;

class Serie extends Base
{
    public const PAGE_ID = Page::ALL_SERIES_ID;
    public const PAGE_ALL = Page::ALL_SERIES;
    public const PAGE_DETAIL = Page::SERIE_DETAIL;
    public const SQL_TABLE = "series";
    public const SQL_LINK_TABLE = "books_series_link";
    public const SQL_LINK_COLUMN = "series";
    public const SQL_SORT = "sort";
    public const SQL_COLUMNS = "series.id as id, series.name as name, series.sort as sort, count(*) as count";
    public const SQL_ALL_ROWS = "select {0} from series, books_series_link where series.id = series {1} group by series.id, series.name, series.sort order by series.sort";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from series, books_series_link where series.id = series and upper (series.name) like ? {1} group by series.id, series.name, series.sort order by series.sort";

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

    public static function getCount($database = null)
    {
        // str_format (localize("series.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database);
    }

    public static function getSerieByBookId($bookId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select  series.id as id, name
from books_series_link, series
where series.id = series and book = ?');
        $result->execute([$bookId]);
        if ($post = $result->fetchObject()) {
            return new Serie($post, $database);
        }
        return null;
    }

    public static function getSerieById($serieId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select series.id as id, series.name as name from series where series.id = ?');
        $result->execute([$serieId]);
        if ($post = $result->fetchObject()) {
            return new Serie($post, $database);
        }
        return new Serie((object)['id' => null, 'name' => localize("seriesword.none")], $database);
    }

    public static function getAllSeries($database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_ALL_ROWS, self::SQL_COLUMNS, "", [], self::class, $database, $numberPerPage);
    }

    public static function getAllSeriesByQuery($query, $database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_ROWS_FOR_SEARCH, self::SQL_COLUMNS, "", ['%' . $query . '%'], self::class, $database, $numberPerPage);
    }
}
