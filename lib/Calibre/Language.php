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
    public const SQL_ALL_LANGUAGES = "select {0} from languages, books_languages_link where languages.id = books_languages_link.lang_code {1} group by languages.id, books_languages_link.lang_code order by languages.lang_code";
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

    public static function getLanguageById($languageId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select languages.id as id, languages.lang_code as name from languages where languages.id = ?');
        $result->execute([$languageId]);
        if ($post = $result->fetchObject()) {
            return new Language($post, $database);
        }
        return new Language((object)['id' => null, 'name' => localize("language.title")], $database);
    }

    public static function getAllLanguages($database = null)
    {
        return Base::getEntryArrayWithBookNumber(self::SQL_ALL_LANGUAGES, self::SQL_COLUMNS, "", [], self::class, $database);
    }
}
