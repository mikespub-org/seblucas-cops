<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\PageId;

class Language extends Base
{
    public const PAGE_ID = PageId::ALL_LANGUAGES_ID;
    public const PAGE_ALL = PageId::ALL_LANGUAGES;
    public const PAGE_DETAIL = PageId::LANGUAGE_DETAIL;
    public const SQL_TABLE = "languages";
    public const SQL_LINK_TABLE = "books_languages_link";
    public const SQL_LINK_COLUMN = "lang_code";
    public const SQL_SORT = "lang_code";
    public const SQL_COLUMNS = "languages.id as id, languages.lang_code as name";
    public const SQL_ALL_ROWS = "select {0} from languages, books_languages_link where languages.id = books_languages_link.lang_code {1} group by languages.id, books_languages_link.lang_code order by languages.lang_code";
    public const SQL_BOOKLIST = 'select {0} from books_languages_link, books ' . Book::SQL_BOOKS_LEFT_JOIN . '
    where books_languages_link.book = books.id and lang_code = ? {1} order by books.sort';
    public const URL_PARAM = "l";

    /**
     * Summary of getTitle
     * @return string
     */
    public function getTitle()
    {
        return static::getLanguageString($this->name);
    }

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return localize("languages.title");
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    /**
     * Summary of getLanguageString
     * @param string $code
     * @return string
     */
    public static function getLanguageString($code)
    {
        $string = localize("languages." . $code);
        if (preg_match("/^languages/", $string)) {
            return $code;
        }
        return $string;
    }

    /**
     * Summary of getDefaultName
     * @return string
     */
    public static function getDefaultName()
    {
        return localize("language.title");
    }

    /**
     * Summary of getLanguagesByBookId
     * @param int $bookId
     * @param ?int $database
     * @return string
     */
    public static function getLanguagesByBookId($bookId, $database = null)
    {
        $lang = [];
        $query = 'select languages.lang_code
            from books_languages_link, languages
            where books_languages_link.lang_code = languages.id
            and book = ?
            order by item_order';
        $result = Database::query($query, [$bookId], $database);
        while ($post = $result->fetchObject()) {
            array_push($lang, static::getLanguageString($post->lang_code));
        }
        return implode(', ', $lang);
    }
}
