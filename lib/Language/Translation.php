<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Language;

use SebLucas\Cops\Input\Config;

class Translation
{
    public const BASE_DIR = './lang';
    /** @var ?\Transliterator */
    protected static $transliterator;
    /** @var ?string */
    protected $acceptLanguageHeader;

    /**
     * Summary of __construct
     * @param ?string $acceptLanguageHeader from $_SERVER['HTTP_ACCEPT_LANGUAGE']
     */
    public function __construct($acceptLanguageHeader = null)
    {
        $this->acceptLanguageHeader = $acceptLanguageHeader;
    }

    /**
     * Get all accepted languages from the browser and put them in a sorted array
     * languages id are normalized : fr-fr -> fr_FR
     * @param ?string $accept from $_SERVER['HTTP_ACCEPT_LANGUAGE']
     * @return array<mixed> of languages
     */
    public function getAcceptLanguages($accept)
    {
        $langs = [];

        if (empty($accept)) {
            return $langs;
        }

        // break up string into pieces (languages and q factors)
        if (preg_match('/^(\w{2})-\w{2}$/', $accept, $matches)) {
            // Special fix for IE11 which send fr-FR and nothing else
            $accept = $accept . ',' . $matches[1] . ';q=0.8';
        }
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $accept, $lang_parse);

        if (count($lang_parse[1])) {
            $langs = [];
            foreach ($lang_parse[1] as $lang) {
                // Format the language code (not standard among browsers)
                if (strlen($lang) == 5) {
                    $lang = str_replace('-', '_', $lang);
                    $splitted = preg_split('/_/', $lang);
                    $lang = $splitted[0] . '_' . strtoupper($splitted[1]);
                }
                array_push($langs, $lang);
            }
            // create a list like "en" => 0.8
            $langs = array_combine($langs, $lang_parse[4]);

            // set default to 1 for any without q factor
            foreach ($langs as $lang => $val) {
                if ($val === '') {
                    $langs[$lang] = 1;
                }
            }

            // sort list based on value
            arsort($langs, SORT_NUMERIC);
        }

        return $langs;
    }

    /**
     * Find the best translation file possible based on the accepted languages
     * @return array<mixed> of language and language file
     */
    public function getLangAndTranslationFile()
    {
        $langs = [];
        $lang = 'en';
        if (!empty(Config::get('language'))) {
            $lang = Config::get('language');
        } elseif (!empty($this->acceptLanguageHeader)) {
            $langs = $this->getAcceptLanguages($this->acceptLanguageHeader);
        }
        $lang_file = null;
        foreach ($langs as $language => $val) {
            $temp_file = static::BASE_DIR . '/Localization_' . $language . '.json';
            if (file_exists($temp_file)) {
                $lang = $language;
                $lang_file = $temp_file;
                break;
            }
        }
        if (empty($lang_file)) {
            $lang_file = static::BASE_DIR . '/Localization_' . $lang . '.json';
        }
        return [$lang, $lang_file];
    }

    /**
     * This method is based on this page
     * http://www.mind-it.info/2010/02/22/a-simple-approach-to-localization-in-php/
     * @param string $phrase
     * @param int $count
     * @param bool $reset
     * @return string
     */
    public function localize($phrase, $count = -1, $reset = false)
    {
        if ($count == 0) {
            $phrase .= '.none';
        }
        if ($count == 1) {
            $phrase .= '.one';
        }
        if ($count > 1) {
            $phrase .= '.many';
        }

        /* Static keyword is used to ensure the file is loaded only once */
        static $translations = null;
        if ($reset) {
            $translations = null;
        }
        /* If no instance of $translations has occured load the language file */
        if (is_null($translations)) {
            $lang_file_en = null;
            [$lang, $lang_file] = $this->getLangAndTranslationFile();
            if ($lang != 'en') {
                $lang_file_en = static::BASE_DIR . '/Localization_en.json';
            }

            $lang_file_content = file_get_contents($lang_file);
            /* Load the language file as a JSON object and transform it into an associative array */
            $translations = json_decode($lang_file_content, true);

            /* Clean the array of all unfinished translations */
            foreach (array_keys($translations) as $key) {
                if (preg_match('/^##TODO##/', $key)) {
                    unset($translations [$key]);
                }
            }
            if (!is_null($lang_file_en)) {
                $lang_file_content = file_get_contents($lang_file_en);
                $translations_en = json_decode($lang_file_content, true);
                $translations = array_merge($translations_en, $translations);
            }
        }
        if (array_key_exists($phrase, $translations)) {
            return $translations[$phrase];
        }
        return $phrase;
    }

    /**
     * Summary of useNormAndUp
     * @return bool
     */
    public static function useNormAndUp()
    {
        return Config::get('normalized_search') == '1';
    }

    /**
     * Summary of normalizeUtf8String
     * @param string $s
     * @return string
     */
    public static function normalizeUtf8String($s)
    {
        //return Transliteration::process($s);

        // ASCII is always valid NFC! If we're only ever given plain ASCII, we can
        // avoid the overhead of initializing the transliterator by skipping
        // out early.
        if (!preg_match('/[\x80-\xff]/', $s)) {
            return $s;
        }

        // see https://www.drupal.org/project/rename_admin_paths/issues/3275140 for different order
        if (!isset(static::$transliterator)) {
            //static::$transliterator = transliterator_create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");
            static::$transliterator = transliterator_create("Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;");
        }
        return transliterator_transliterate(static::$transliterator, $s);
    }

    /**
     * Summary of normAndUp
     * @param string $s
     * @return string
     */
    public static function normAndUp($s)
    {
        return mb_strtoupper(static::normalizeUtf8String($s), 'UTF-8');
    }
}
