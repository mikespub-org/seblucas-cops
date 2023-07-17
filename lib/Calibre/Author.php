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

use function SebLucas\Cops\Language\localize;
use function SebLucas\Cops\Language\str_format;

class Author extends Base
{
    public const PAGE_ID = Page::ALL_AUTHORS_ID;
    public const PAGE_ALL = Page::ALL_AUTHORS;
    public const PAGE_LETTER = Page::AUTHORS_FIRST_LETTER;
    public const PAGE_DETAIL = Page::AUTHOR_DETAIL;
    public const SQL_TABLE = "authors";
    public const SQL_COLUMNS = "authors.id as id, authors.name as name, authors.sort as sort, count(*) as count";
    public const SQL_AUTHORS_BY_FIRST_LETTER = "select {0} from authors, books_authors_link where author = authors.id and upper (authors.sort) like ? group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_AUTHORS_FOR_SEARCH = "select {0} from authors, books_authors_link where author = authors.id and (upper (authors.sort) like ? or upper (authors.name) like ?) group by authors.id, authors.name, authors.sort order by sort";
    public const SQL_ALL_AUTHORS = "select {0} from authors, books_authors_link where author = authors.id group by authors.id, authors.name, authors.sort order by sort";

    public $id;
    public $name;
    public $sort;

    public function __construct($post)
    {
        $this->id = $post->id;
        $this->name = str_replace("|", ",", $post->name);
        $this->sort = $post->sort;
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

    public static function getCount()
    {
        // str_format (localize("authors.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL);
    }

    public static function getAllAuthorsByFirstLetter()
    {
        [, $result] = parent::executeQuery("select {0}
from authors
group by substr (upper (sort), 1, 1)
order by substr (upper (sort), 1, 1)", "substr (upper (sort), 1, 1) as title, count(*) as count", "", [], -1);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            array_push($entryArray, new Entry(
                $post->title,
                Author::getEntryIdByLetter($post->title),
                str_format(localize("authorword", $post->count), $post->count),
                "text",
                [ new LinkNavigation("?page=".self::PAGE_LETTER."&id=". rawurlencode($post->title))],
                "",
                $post->count
            ));
        }
        return $entryArray;
    }

    public static function getAuthorsByStartingLetter($letter)
    {
        return self::getEntryArray(self::SQL_AUTHORS_BY_FIRST_LETTER, [$letter . "%"]);
    }

    public static function getAuthorsForSearch($query)
    {
        return self::getEntryArray(self::SQL_AUTHORS_FOR_SEARCH, [$query . "%", $query . "%"]);
    }

    public static function getAllAuthors()
    {
        return self::getEntryArray(self::SQL_ALL_AUTHORS, []);
    }

    public static function getEntryArray($query, $params)
    {
        return Base::getEntryArrayWithBookNumber($query, self::SQL_COLUMNS, $params, self::class);
    }

    public static function getAuthorById($authorId)
    {
        $result = parent::getDb()->prepare('select ' . self::SQL_COLUMNS . ' from authors where id = ?');
        $result->execute([$authorId]);
        $post = $result->fetchObject();
        return new Author($post);
    }

    public static function getAuthorByBookId($bookId)
    {
        $result = parent::getDb()->prepare('select authors.id as id, authors.name as name, authors.sort as sort from authors, books_authors_link
where author = authors.id
and book = ? order by books_authors_link.id');
        $result->execute([$bookId]);
        $authorArray = [];
        while ($post = $result->fetchObject()) {
            array_push($authorArray, new Author($post));
        }
        return $authorArray;
    }
}
