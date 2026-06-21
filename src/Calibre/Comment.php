<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\CalibreHandler;
use SebLucas\Cops\Routing\UriGenerator;

class Comment extends Base
{
    public const CALIBRE_URL_SCHEME = CalibreHandler::URL_SCHEME;
    public const SQL_TABLE = "comments";
    public const SQL_LINK_TABLE = "comments";
    public const SQL_LINK_COLUMN = "id";
    public const SQL_SORT = "id";
    public const SQL_COLUMNS = "id, book as name, text";
    public const SQL_ALL_ROWS = "select {0} from comments where 1=1 {1}";
    public const SQL_ROWS_FOR_SEARCH = "select {0} from comments where upper (strip_html(comments.text)) like ? {1} group by comments.id order by comments.id";

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        $params['id'] = $this->name;
        // we need databaseId here because we use $handler::link()
        $params['db'] = $this->databaseId;
        return $this->getRoute(Book::ROUTE_PAGEID, $params);
        //$params['author'] = $this->getAuthorsName();
        //$params['title'] = $this->getTitle();
        //return $this->getRoute(self::ROUTE_DETAIL, $params);
    }

    /**
     * Summary of getTitle
     * @return string
     */
    public function getTitle()
    {
        return '(' . strval($this->name) . ')';
    }

    /**
     * Summary of hasCalibreLinks
     * @param string $text
     * @return bool
     */
    public static function hasCalibreLinks($text)
    {
        return str_contains($text, self::CALIBRE_URL_SCHEME . '://');
    }

    /**
     * Summary of fixCalibreLinks
     * @param string $text
     * @param ?int $database
     * @return string
     */
    public static function fixCalibreLinks($text, $database = null)
    {
        // @todo add database param if not null and Library_Name is _ (current)
        $baseurl = UriGenerator::absolute(CalibreHandler::PREFIX);
        return str_replace(self::CALIBRE_URL_SCHEME . '://', $baseurl . '/', $text);
    }
}
