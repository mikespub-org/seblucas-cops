<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\LinkNavigation;
use SebLucas\Cops\Pages\Page;

class Author extends Base
{
    public const PAGE_ID = Page::ALL_AUTHORS_ID;
    public const PAGE_ALL = Page::ALL_AUTHORS;
    public const PAGE_LETTER = Page::AUTHORS_FIRST_LETTER;
    public const PAGE_DETAIL = Page::AUTHOR_DETAIL;
    public const SQL_TABLE = "authors";
    public const SQL_LINK_TABLE = "books_authors_link";
    public const SQL_LINK_COLUMN = "author";
    public const SQL_SORT = "sort";
    public const SQL_COLUMNS = "authors.id as id, authors.name as name, authors.sort as sort, count(*) as count";
    public const SQL_ROWS_BY_FIRST_LETTER = "select {0} from authors, books_authors_link where author = authors.id and upper (authors.sort) like ? {1} group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from authors, books_authors_link where author = authors.id and (upper (authors.sort) like ? or upper (authors.name) like ?) {1} group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_ALL_ROWS = "select {0} from authors, books_authors_link where author = authors.id {1} group by authors.id, authors.name, authors.sort order by sort";

    public $id;
    public $name;
    public $sort;

    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->name = str_replace("|", ",", $post->name);
        $this->sort = $post->sort;
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

    public static function getEntryIdByLetter($startingLetter)
    {
        return self::PAGE_ID.":letter:".$startingLetter;
    }

    public function getTitle()
    {
        return $this->sort;
    }

    public function getContent($count = 0)
    {
        return str_format(localize("authorword", $count), $count);
    }

    /** Use inherited class methods to get entries from <Whatever> by authorId (linked via books) */

    public function getBooks($n = -1)
    {
        return Book::getEntriesByAuthorId($this->id, $n, $this->databaseId);
    }

    public function getAuthors($n = -1)
    {
        //return Author::getEntriesByAuthorId($this->id, $n, $this->databaseId);
    }

    public function getLanguages($n = -1)
    {
        return Language::getEntriesByAuthorId($this->id, $n, $this->databaseId);
    }

    public function getPublishers($n = -1)
    {
        return Publisher::getEntriesByAuthorId($this->id, $n, $this->databaseId);
    }

    public function getRatings($n = -1)
    {
        return Rating::getEntriesByAuthorId($this->id, $n, $this->databaseId);
    }

    public function getSeries($n = -1)
    {
        return Serie::getEntriesByAuthorId($this->id, $n, $this->databaseId);
    }

    public function getTags($n = -1)
    {
        return Tag::getEntriesByAuthorId($this->id, $n, $this->databaseId);
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    public static function getCount($database = null)
    {
        // str_format (localize("authors.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database);
    }

    public static function getAllAuthorsByFirstLetter($database = null, $numberPerPage = null)
    {
        [, $result] = parent::executeQuery("select {0}
from authors
group by substr (upper (sort), 1, 1)
order by substr (upper (sort), 1, 1)", "substr (upper (sort), 1, 1) as title, count(*) as count", "", [], -1, $database, $numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->title,
                Author::getEntryIdByLetter($post->title),
                str_format(localize("authorword", $post->count), $post->count),
                "text",
                [ new LinkNavigation("?page=".self::PAGE_LETTER."&id=". rawurlencode($post->title), null, null, $database)],
                $database,
                "",
                $post->count
            ));
        }
        return $entryArray;
    }

    public static function getAuthorsByStartingLetter($letter, $n = -1, $database = null)
    {
        return self::getEntryArray(self::SQL_ROWS_BY_FIRST_LETTER, [$letter . "%"], $n, $database);
    }

    public static function getAuthorsForSearch($query, $n = -1, $database = null)
    {
        return self::getEntryArray(self::SQL_ROWS_FOR_SEARCH, [$query . "%", $query . "%"], $n, $database);
    }

    public static function getAllAuthors($n = -1, $database = null)
    {
        return self::getEntryArray(self::SQL_ALL_ROWS, [], $n, $database);
    }

    public static function getEntryArray($query, $params, $n = -1, $database = null, $numberPerPage = null)
    {
        return Base::getEntryArrayWithBookNumber($query, self::SQL_COLUMNS, "", $params, self::class, $n, $database, $numberPerPage);
    }

    public static function getAuthorById($authorId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select ' . self::SQL_COLUMNS . ' from authors where id = ?');
        $result->execute([$authorId]);
        if ($post = $result->fetchObject()) {
            return new Author($post, $database);
        }
        return null;
    }

    public static function getAuthorByBookId($bookId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select authors.id as id, authors.name as name, authors.sort as sort from authors, books_authors_link
where author = authors.id
and book = ? order by books_authors_link.id');
        $result->execute([$bookId]);
        $authorArray = [];
        while ($post = $result->fetchObject()) {
            array_push($authorArray, new Author($post, $database));
        }
        return $authorArray;
    }
}
