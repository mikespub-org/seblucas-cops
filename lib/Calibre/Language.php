<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Output\Entry;
use SebLucas\Cops\Output\LinkNavigation;
use SebLucas\Cops\Pages\Page;

use function SebLucas\Cops\Language\localize;
use function SebLucas\Cops\Language\str_format;

class Language extends Base
{
    public const PAGE_ID = Page::ALL_LANGUAGES_ID;
    public const PAGE_ALL = Page::ALL_LANGUAGES;
    public const PAGE_DETAIL = Page::LANGUAGE_DETAIL;
    public const SQL_TABLE = "languages";
    public const SQL_COLUMNS = "languages.id as id, languages.lang_code as lang_code, count(*) as count";
    public $id;
    public $lang_code;

    public function __construct($pid, $plang_code)
    {
        $this->id = $pid;
        $this->lang_code = $plang_code;
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

    public static function getCount()
    {
        // str_format (localize("languages.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL);
    }

    public static function getLanguageById($languageId)
    {
        $result = parent::getDb()->prepare('select id, lang_code  from languages where id = ?');
        $result->execute([$languageId]);
        if ($post = $result->fetchObject()) {
            return new Language($post->id, Language::getLanguageString($post->lang_code));
        }
        return null;
    }



    public static function getAllLanguages()
    {
        $result = parent::getDb()->query('select ' . self::SQL_COLUMNS . '
from languages, books_languages_link
where languages.id = books_languages_link.lang_code
group by languages.id, books_languages_link.lang_code
order by languages.lang_code');
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $language = new Language($post->id, $post->lang_code);
            array_push($entryArray, new Entry(
                Language::getLanguageString($language->lang_code),
                $language->getEntryId(),
                str_format(localize("bookword", $post->count), $post->count),
                "text",
                [ new LinkNavigation($language->getUri())],
                "",
                $post->count
            ));
        }
        return $entryArray;
    }
}
