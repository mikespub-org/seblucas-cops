<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Language;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\Response;
use Symfony\Component\String\UnicodeString;

/**
 * Provide fallback if intl extension is not installed - see #118
 * @deprecated 3.5.1 use UnicodeString()->ascii()
 */
if (!function_exists('transliterator_create')) {
    /**
     * Not available, install the intl extension
     * @param string $id
     * @return bool
     */
    function transliterator_create($id)
    {
        return false;
    }

    /**
     * If you want anything better, install the intl extension
     * @param bool $transliterator
     * @param string $s
     * @return string
     */
    function transliterator_transliterate($transliterator, $s)
    {
        $s = preg_replace('/[\x80-\xff]/', '_', $s);
        return preg_replace('/__+/', '_', $s);
    }
}

class Normalizer
{
    /** @var \Transliterator|bool|null */
    protected static $transliterator;

    /**
     * Summary of useNormAndUp
     * @return bool
     */
    public static function useNormAndUp()
    {
        if (Config::get('normalized_search') == '1') {
            if (!extension_loaded('intl')) {
                // this will call exit()
                Response::sendError(null, 'Please enable the "intl" extension to use normalized search');
            }
            return true;
        }
        return false;
    }

    /**
     * Summary of normalizeUtf8String
     * @param string $s
     * @deprecated 3.5.1 use UnicodeString()->ascii()
     * @return string
     */
    public static function normalizeUtf8String($s)
    {
        // ASCII is always valid NFC! If we're only ever given plain ASCII, we can
        // avoid the overhead of initializing the transliterator by skipping
        // out early.
        if (!preg_match('/[\x80-\xff]/', $s)) {
            return $s;
        }

        // see https://www.drupal.org/project/rename_admin_paths/issues/3275140 for different order
        if (!isset(self::$transliterator)) {
            //self::$transliterator = transliterator_create("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC;");
            self::$transliterator = transliterator_create("Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;");
        }
        return transliterator_transliterate(self::$transliterator, $s);
    }

    /**
     * Summary of normAndUp
     * @param string $s
     * @return string
     */
    public static function normAndUp($s)
    {
        return (string) (new UnicodeString($s))->ascii()->upper();
        // @deprecated 3.5.1 use UnicodeString()->ascii()
        //return mb_strtoupper(self::normalizeUtf8String($s), 'UTF-8');
    }

    /**
     * Summary of getTitleSort
     * @param string $str
     * @return string
     */
    public static function getTitleSort($str)
    {
        $str = trim($str, ' -.');
        // @todo add articles to ignore in other languages
        if (!preg_match('/^(The|A|An) /u', $str)) {
            return $str;
        }
        return preg_replace('/^(The|A|An) (.+)$/u', '$2, $1', $str);
    }
}
