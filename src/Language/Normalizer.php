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

class Normalizer
{
    /**
     * Summary of useNormAndUp
     * @return bool
     */
    public static function useNormAndUp()
    {
        if (Config::get('normalized_search') == '1') {
            if (!extension_loaded('intl')) {
                // this will call exit()
                $response = Response::sendError(null, 'Please enable the "intl" extension to use normalized search');
                $response->send();
                exit;
            }
            return true;
        }
        return false;
    }

    /**
     * Summary of normAndUp
     * @param string $s
     * @return string
     */
    public static function normAndUp($s)
    {
        return (string) (new UnicodeString($s))->ascii()->upper();
    }

    /**
     * Summary of normalize
     * @param string $s
     * @return string
     */
    public static function normalize($s)
    {
        return (string) (new UnicodeString($s))->ascii();
    }

    /**
     * Summary of isAscii
     * @param string $s
     * @return bool
     */
    public static function isAscii($s)
    {
        return preg_match('/^[\x20-\x7e]*$/', $s) ? true : false;
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
