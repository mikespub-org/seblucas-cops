<?php

/**
 * COPS (Calibre OPDS PHP Server) functions file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Format;

if (!function_exists('str_format')) {
    /**
     * Summary of str_format
     * @param string $format
     * @param string ...$args
     * @return string
     */
    function str_format($format, ...$args)
    {
        return Format::str_format($format, ...$args);
    }
}

if (!function_exists('localize')) {
    // set locale for Translation per request
    /**
     * Summary of localize
     * @param string $phrase
     * @param int $count
     * @param string $locale
     * @param bool $reset
     * @return string
     */
    function localize($phrase, $count = -1, $locale = 'en', $reset = false)
    {
        return Translation::getInstance($locale)->localize($phrase, $count, $reset);
    }
}
