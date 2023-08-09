<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\Page;

class Tag extends Base
{
    public const PAGE_ID = Page::ALL_TAGS_ID;
    public const PAGE_ALL = Page::ALL_TAGS;
    public const PAGE_DETAIL = Page::TAG_DETAIL;
    public const SQL_TABLE = "tags";
    public const SQL_LINK_TABLE = "books_tags_link";
    public const SQL_LINK_COLUMN = "tag";
    public const SQL_SORT = "name";
    public const SQL_COLUMNS = "tags.id as id, tags.name as name, count(*) as count";
    public const SQL_ALL_ROWS = "select {0} from tags, books_tags_link where tags.id = tag {1} group by tags.id, tags.name order by tags.name";

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

    /** Use inherited class methods to get entries from <Whatever> by tagId (linked via books) */

    public function getBooks($n = -1)
    {
        return Book::getEntriesByTagId($this->id, $n, $this->databaseId);
    }

    public function getAuthors($n = -1)
    {
        return Author::getEntriesByTagId($this->id, $n, $this->databaseId);
    }

    public function getLanguages($n = -1)
    {
        return Language::getEntriesByTagId($this->id, $n, $this->databaseId);
    }

    public function getPublishers($n = -1)
    {
        return Publisher::getEntriesByTagId($this->id, $n, $this->databaseId);
    }

    public function getRatings($n = -1)
    {
        return Rating::getEntriesByTagId($this->id, $n, $this->databaseId);
    }

    public function getSeries($n = -1)
    {
        return Serie::getEntriesByTagId($this->id, $n, $this->databaseId);
    }

    public function getTags($n = -1)
    {
        //return Tag::getEntriesByTagId($this->id, $n, $this->databaseId);
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    public static function getCount($database = null)
    {
        // str_format (localize("tags.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database);
    }

    public static function getTagById($tagId, $database = null)
    {
        return self::getInstanceById($tagId, localize("tagword.none"), self::class, $database);
    }

    public static function getAllTagsByQuery($query, $n = -1, $database = null, $numberPerPage = null)
    {
        $columns  = "tags.id as id, tags.name as name, (select count(*) from books_tags_link where tags.id = tag) as count";
        $sql = 'select {0} from tags where upper (tags.name) like ? {1} order by tags.name';
        [$totalNumber, $result] = parent::executeQuery($sql, $columns, "", ['%' . $query . '%'], $n, $database, $numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $tag = new Tag($post, $database);
            array_push($entryArray, $tag->getEntry($post->count));
        }
        return [$entryArray, $totalNumber];
    }
}
