<?php

/**
 * COPS (Calibre OPDS PHP Server) functions file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Format;

if (!function_exists('str_format')) {
    /**
     * Summary of str_format
     * @param string $format
     * @param array<mixed> $args
     * @return string
     */
    function str_format($format, ...$args)
    {
        return Format::str_format($format, ...$args);
    }
}

if (!function_exists('localize')) {
    $translator = new Translation($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null);
    Config::set('_translator_', $translator);

    /**
     * Summary of localize
     * @param string $phrase
     * @param int $count
     * @param bool $reset
     * @return string
     */
    function localize($phrase, $count = -1, $reset = false)
    {
        return Config::get('_translator_')->localize($phrase, $count, $reset);
    }
}
