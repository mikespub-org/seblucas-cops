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
    public const SQL_COLUMNS = "publishers.id as id, publishers.name as name, count(*) as count";
    public const SQL_ALL_PUBLISHERS = "select {0} from publishers, books_publishers_link where publishers.id = publisher group by publishers.id, publishers.name order by publishers.name";
    public const SQL_PUBLISHERS_FOR_SEARCH = "select {0} from publishers, books_publishers_link where publishers.id = publisher and upper (publishers.name) like ? group by publishers.id, publishers.name order by publishers.name";


    public $id;
    public $name;

    public function __construct($post)
    {
        $this->id = $post->id;
        $this->name = $post->name;
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
            return new Publisher($post);
        }
        return null;
    }

    public static function getPublisherById($publisherId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select id, name
from publishers where id = ?');
        $result->execute([$publisherId]);
        if ($post = $result->fetchObject()) {
            return new Publisher($post);
        }
        return null;
    }

    public static function getAllPublishers($database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_ALL_PUBLISHERS, self::SQL_COLUMNS, [], self::class, $database, $numberPerPage);
    }

    public static function getAllPublishersByQuery($query, $database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_PUBLISHERS_FOR_SEARCH, self::SQL_COLUMNS, ['%' . $query . '%'], self::class, $database, $numberPerPage);
    }
}
