<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Handlers\CalibreHandler;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
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
        if (!empty($this->link)) {
            return $this->link;
        }
        $params['id'] = $this->name;
        // we need databaseId here because we use $handler::link()
        $params['db'] = $this->databaseId;
        return $this->getRoute(Book::ROUTE_PAGEID, $params);
        // @todo get corresponding title + author from book instance
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
        // @todo get corresponding title from book instance
        return "Summary for book #{$this->name}";
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

    /**
     * Replace comment entries with book entries
     * @param array<Entry> $entryArray
     * @param Request $request
     * @param DatabaseContext $dbContext
     * @param array<mixed> $params set query + scope in book links - not used here
     * @return array{0: EntryBook[], 1: integer}
     */
    public static function replaceEntryArray($entryArray, $request, $dbContext, $params = [])
    {
        $idlist = array_map(fn($entry) => (int) $entry->instance->name, $entryArray);
        $booklist = new BookList($request, $dbContext);
        $entryArray = $booklist->getBooksByIdList($idlist, $params);
        return $entryArray;
    }

    /**
     * Update entry title and navlink based on book instance
     * @param array<Entry> $entryArray
     * @param Request $request
     * @param DatabaseContext $dbContext
     * @param array<mixed> $params set query + scope in book links
     * @return array<Entry>
     */
    public static function updateEntryArray($entryArray, $request, $dbContext, $params = [])
    {
        $idlist = array_map(fn($entry) => (int) $entry->instance->name, $entryArray);
        $booklist = new BookList($request, $dbContext);
        [$bookArray, ] = $booklist->getBooksByIdList($idlist);
        $books = [];
        foreach ($bookArray as $entryBook) {
            $bookId = $entryBook->book->id;
            $books[$bookId] = $entryBook->book;
        }
        foreach ($entryArray as $idx => $entry) {
            $bookId = $entry->instance->name;
            if (!empty($books[$bookId])) {
                $entryArray[$idx]->title = $books[$bookId]->getTitle();
                // set query + scope in book links
                $entryArray[$idx]->setNavLink($books[$bookId]->getUri($params));
            }
        }
        return $entryArray;
    }
}
