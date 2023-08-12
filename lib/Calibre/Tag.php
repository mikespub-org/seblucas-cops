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
    public const SQL_ROWS_FOR_SEARCH = "select {0} from tags, books_tags_link where tags.id = tag and upper (tags.name) like ? {1} group by tags.id, tags.name order by tags.name";
    public const SQL_BOOKLIST = 'select {0} from books_tags_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_tags_link.book = books.id and tag = ? {1} order by books.sort';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books.id not in (select book from books_tags_link) {1} order by books.sort';
    public const URL_PARAM = "t";

    public function getParentTitle()
    {
        return localize("tags.title");
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    public static function getDefaultName()
    {
        return localize("tagword.none");
    }

    public static function getInstancesByBookId($bookId, $database = null)
    {
        $tags = [];
        $query = 'select tags.id as id, name
            from books_tags_link, tags
            where tag = tags.id
            and book = ?
            order by name';
        $result = Database::query($query, [$bookId], $database);
        while ($post = $result->fetchObject()) {
            array_push($tags, new Tag($post, $database));
        }
        return $tags;
    }
}
