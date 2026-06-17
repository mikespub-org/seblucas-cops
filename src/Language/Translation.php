<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Language;

use SebLucas\Cops\Input\Config;

class Translation
{
    public const BASE_DIR = 'lang';

    /** @var array<string, self> */
    protected static array $instances = [];

    protected string $locale;
    /** @var array<string, string> */
    protected array $translations = [];

    public function __construct(string $locale = 'en')
    {
        $this->locale = $locale;
        $this->translations = self::loadLocaleTranslations($this->locale);
    }

    public static function getInstance(string $locale): self
    {
        /* Static keyword is used to ensure the file is loaded only once */
        if (!array_key_exists($locale, self::$instances)) {
            self::$instances[$locale] = new self($locale);
        }
        return self::$instances[$locale];
    }

    /**
     * Get all accepted languages from the browser and put them in a sorted array
     * languages id are normalized : fr-fr -> fr_FR
     * @param ?string $accept from $_SERVER['HTTP_ACCEPT_LANGUAGE']
     * @return array<mixed> of languages
     */
    public static function getAcceptLanguages($accept)
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
     * @param ?string $acceptLanguage from $_SERVER['HTTP_ACCEPT_LANGUAGE']
     * @return array<mixed> of language and language file
     */
    public static function getLangAndTranslationFile($acceptLanguage)
    {
        $langs = [];
        $lang = 'en';
        if (!empty(Config::get('language'))) {
            $lang = Config::get('language');
        } elseif (!empty($acceptLanguage)) {
            $langs = self::getAcceptLanguages($acceptLanguage);
        }
        $base_dir = dirname(__DIR__, 2) . '/' . self::BASE_DIR;
        $lang_file = null;
        foreach ($langs as $language => $val) {
            $temp_file = $base_dir . '/Localization_' . $language . '.json';
            if (file_exists($temp_file)) {
                $lang = $language;
                $lang_file = $temp_file;
                break;
            }
        }
        if (empty($lang_file)) {
            $lang_file = $base_dir . '/Localization_' . $lang . '.json';
        }
        return [$lang, $lang_file];
    }

    /**
     * Summary of loadLocaleTranslations
     * @param string $locale
     * @return array<string, string>
     */
    public static function loadLocaleTranslations($locale)
    {
        $base_dir = dirname(__DIR__, 2) . '/' . self::BASE_DIR;
        $lang_file = $base_dir . '/Localization_' . $locale . '.json';

        $lang_file_en = null;
        if ($locale != 'en') {
            $base_dir = dirname(__DIR__, 2) . '/' . self::BASE_DIR;
            $lang_file_en = $base_dir . '/Localization_en.json';
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

        return $translations;
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

        if ($reset) {
            $this->translations = self::loadLocaleTranslations($this->locale);
        }
        if (array_key_exists($phrase, $this->translations)) {
            return $this->translations[$phrase];
        }
        return $phrase;
    }
}
