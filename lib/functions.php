<?php
/**
 * COPS (Calibre OPDS PHP Server) functions file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Format;

if (!function_exists('str_format')) {
    function str_format($format, ...$args)
    {
        return Format::str_format($format, ...$args);
    }
}

if (!function_exists('localize')) {
    $translator = new Translation($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null);
    Config::set('_translator_', $translator);

    function localize($phrase, $count=-1, $reset=false)
    {
        return Config::get('_translator_')->localize($phrase, $count, $reset);
    }
}
