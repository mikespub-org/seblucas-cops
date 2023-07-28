<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     At Libitum <eljarec@yahoo.com>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\Page;

class Publisher extends Base
{
    public const PAGE_ID = Page::ALL_PUBLISHERS_ID;
    public const PAGE_ALL = Page::ALL_PUBLISHERS;
    public const PAGE_DETAIL = Page::PUBLISHER_DETAIL;
    public const SQL_TABLE = "publishers";
    public const SQL_LINK_TABLE = "books_publishers_link";
    public const SQL_LINK_COLUMN = "publisher";
    public const SQL_SORT = "name";
    public const SQL_COLUMNS = "publishers.id as id, publishers.name as name, count(*) as count";
    public const SQL_ALL_ROWS = "select {0} from publishers, books_publishers_link where publishers.id = publisher {1} group by publishers.id, publishers.name order by publishers.name";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from publishers, books_publishers_link where publishers.id = publisher and upper (publishers.name) like ? {1} group by publishers.id, publishers.name order by publishers.name";


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

    /** Use inherited class methods to get entries from <Whatever> by publisherId (linked via books) */

    public function getBooks($n = -1)
    {
        return Book::getEntriesByPublisherId($this->id, $n, $this->databaseId);
    }

    public function getAuthors($n = -1)
    {
        return Author::getEntriesByPublisherId($this->id, $n, $this->databaseId);
    }

    public function getLanguages($n = -1)
    {
        return Language::getEntriesByPublisherId($this->id, $n, $this->databaseId);
    }

    public function getPublishers($n = -1)
    {
        //return Publisher::getEntriesByPublisherId($this->id, $n, $this->databaseId);
    }

    public function getRatings($n = -1)
    {
        return Rating::getEntriesByPublisherId($this->id, $n, $this->databaseId);
    }

    public function getSeries($n = -1)
    {
        return Serie::getEntriesByPublisherId($this->id, $n, $this->databaseId);
    }

    public function getTags($n = -1)
    {
        return Tag::getEntriesByPublisherId($this->id, $n, $this->databaseId);
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    public static function getCount($database = null)
    {
        // str_format (localize("publishers.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database);
    }

    public static function getPublisherByBookId($bookId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select publishers.id as id, name
from books_publishers_link, publishers
where publishers.id = publisher and book = ?');
        $result->execute([$bookId]);
        if ($post = $result->fetchObject()) {
            return new Publisher($post, $database);
        }
        return null;
    }

    public static function getPublisherById($publisherId, $database = null)
    {
        return self::getInstanceById($publisherId, localize("publisherword.none"), self::class, $database);
    }

    public static function getAllPublishers($n = -1, $database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_ALL_ROWS, self::SQL_COLUMNS, "", [], self::class, $n, $database, $numberPerPage);
    }

    public static function getAllPublishersByQuery($query, $n = -1, $database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_ROWS_FOR_SEARCH, self::SQL_COLUMNS, "", ['%' . $query . '%'], self::class, $n, $database, $numberPerPage);
    }
}
