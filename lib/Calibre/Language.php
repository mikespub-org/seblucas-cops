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

class Language extends Base
{
    public const PAGE_ID = Page::ALL_LANGUAGES_ID;
    public const PAGE_ALL = Page::ALL_LANGUAGES;
    public const PAGE_DETAIL = Page::LANGUAGE_DETAIL;
    public const SQL_TABLE = "languages";
    public const SQL_COLUMNS = "languages.id as id, languages.lang_code as lang_code, count(*) as count";
    public $id;
    public $lang_code;

    public function __construct($pid, $plang_code, $database = null)
    {
        $this->id = $pid;
        $this->lang_code = $plang_code;
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
        $result = parent::getDb($database)->prepare('select id, lang_code  from languages where id = ?');
        $result->execute([$languageId]);
        if ($post = $result->fetchObject()) {
            return new Language($post->id, Language::getLanguageString($post->lang_code), $database);
        }
        return null;
    }



    public static function getAllLanguages($database = null)
    {
        $result = parent::getDb($database)->query('select ' . self::SQL_COLUMNS . '
from languages, books_languages_link
where languages.id = books_languages_link.lang_code
group by languages.id, books_languages_link.lang_code
order by languages.lang_code');
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $language = new Language($post->id, $post->lang_code, $database);
            array_push($entryArray, new Entry(
                Language::getLanguageString($language->lang_code),
                $language->getEntryId(),
                str_format(localize("bookword", $post->count), $post->count),
                "text",
                [ new LinkNavigation($language->getUri(), null, null, $database)],
                $database,
                "",
                $post->count
            ));
        }
        return $entryArray;
    }
}
