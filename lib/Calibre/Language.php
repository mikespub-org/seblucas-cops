<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\Page;

class Language extends Base
{
    public const PAGE_ID = Page::ALL_LANGUAGES_ID;
    public const PAGE_ALL = Page::ALL_LANGUAGES;
    public const PAGE_DETAIL = Page::LANGUAGE_DETAIL;
    public const SQL_TABLE = "languages";
    public const SQL_LINK_TABLE = "books_languages_link";
    public const SQL_LINK_COLUMN = "lang_code";
    public const SQL_SORT = "lang_code";
    public const SQL_COLUMNS = "languages.id as id, languages.lang_code as name, count(*) as count";
    public const SQL_ALL_ROWS = "select {0} from languages, books_languages_link where languages.id = books_languages_link.lang_code {1} group by languages.id, books_languages_link.lang_code order by languages.lang_code";
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

    public function getTitle()
    {
        return self::getLanguageString($this->name);
    }

    /** Use inherited class methods to get entries from <Whatever> by languageId (linked via books) */

    public function getBooks($n = -1, $sort = null)
    {
        return Book::getEntriesByLanguageId($this->id, $n, $sort, $this->databaseId);
    }

    public function getAuthors($n = -1, $sort = null)
    {
        return Author::getEntriesByLanguageId($this->id, $n, $sort, $this->databaseId);
    }

    public function getLanguages($n = -1, $sort = null)
    {
        //return Language::getEntriesByLanguageId($this->id, $n, $sort, $this->databaseId);
    }

    public function getPublishers($n = -1, $sort = null)
    {
        return Publisher::getEntriesByLanguageId($this->id, $n, $sort, $this->databaseId);
    }

    public function getRatings($n = -1, $sort = null)
    {
        return Rating::getEntriesByLanguageId($this->id, $n, $sort, $this->databaseId);
    }

    public function getSeries($n = -1, $sort = null)
    {
        return Serie::getEntriesByLanguageId($this->id, $n, $sort, $this->databaseId);
    }

    public function getTags($n = -1, $sort = null)
    {
        return Tag::getEntriesByLanguageId($this->id, $n, $sort, $this->databaseId);
    }

    /** Use inherited class methods to query static SQL_TABLE for this class */

    public static function getLanguageString($code)
    {
        $string = localize("languages.".$code);
        if (preg_match("/^languages/", $string)) {
            return $code;
        }
        return $string;
    }

    public static function getCount($database = null)
    {
        // str_format (localize("languages.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database);
    }

    /**
     * Summary of getLanguageById
     * @param mixed $languageId
     * @param mixed $database
     * @return Language
     */
    public static function getLanguageById($languageId, $database = null)
    {
        return self::getInstanceById($languageId, localize("language.title"), self::class, $database);
    }
}
