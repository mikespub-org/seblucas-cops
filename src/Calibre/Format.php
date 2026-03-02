<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     SenorSmartyPants <senorsmartypants@gmail.com>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Pages\PageId;

class Format extends Base
{
    public const PAGE_ID = PageId::ALL_FORMATS_ID;
    public const PAGE_ALL = PageId::ALL_FORMATS;
    public const PAGE_DETAIL = PageId::FORMAT_DETAIL;
    public const ROUTE_ALL = "page-formats";
    public const ROUTE_DETAIL = "page-format";
    public const SQL_TABLE = "data";
    //public const SQL_COLUMNS = "id, name, format";
    public const SQL_LINK_TABLE = "data";
    public const SQL_LINK_COLUMN = "format";
    public const SQL_SORT = "format";
    public const SQL_COLUMNS = "data.format as id, data.format as name";
    public const SQL_ALL_ROWS = "select {0} from data where 1=1 {1} group by data.format order by data.format";
    public const SQL_ROWS_FOR_SEARCH = "";  // "select {0} from tags, books_tags_link where tags.id = tag and upper (tags.name) like ? {1} group by tags.id, tags.name order by tags.name";
    public const SQL_BOOKLIST = 'select {0} from data, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where data.book = books.id and data.format = ? {1} order by books.sort';
    public const SQL_BOOKLIST_NULL = 'select {0} from books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books.id not in (select book from data) {1} order by books.sort';
    public const URL_PARAM = "format";

    /** @var ?string */
    public $id;

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $params['id'] = $this->id;
        // we need databaseId here because we use $handler::link()
        $params['db'] = $this->getDatabaseId();
        //$params['title'] = $this->getTitle();
        return $this->getRoute(static::ROUTE_DETAIL, $params);
    }

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return localize("formats.title");
    }

    /**
     * Summary of getCount
     * @param ?int $database
     * @param class-string<BaseHandler> $handler
     * @return ?Entry
     */
    public static function getCount($database, $handler)
    {
        $count = Database::querySingle('select count(distinct format) from ' . static::SQL_TABLE, $database);
        return static::getCountEntry($count, $database, null, $handler);
    }

    /**
     * Summary of getInstanceById
     * @param string|int|null $id used for the format here
     * @param ?int $database
     * @return self
     */
    public static function getInstanceById($id, $database = null)
    {
        if (!empty($id)) {
            return new Format((object) ['id' => $id, 'name' => $id], $database);
        }
        $default = self::getDefaultName();
        // use id = 0 to support route urls
        return new Format((object) ['id' => 0, 'name' => $default], $database);
    }

    /**
     * Summary of getInstanceByName
     * @param string|int|null $name used for the format here
     * @param ?int $database
     * @return self
     */
    public static function getInstanceByName($name, $database = null)
    {
        return self::getInstanceById($name, $database);
    }

    /**
     * Summary of getDefaultName
     * @return string
     */
    public static function getDefaultName()
    {
        return localize("formatword.none");
    }

    /**
     * Summary of getInstancesByBookId
     * @param int $bookId
     * @param ?int $database
     * @return array<Format>
     */
    public static function getInstancesByBookId($bookId, $database = null)
    {
        $formats = [];

        // get formats here, not actual data
        $query = 'select ' . self::getInstanceColumns($database) . '
            from data
            where data.book = ?
            order by data.format';
        $result = Database::query($query, [$bookId], $database);
        while ($post = $result->fetchObject()) {
            array_push($formats, new Format($post, $database));
        }
        return $formats;
    }
}
