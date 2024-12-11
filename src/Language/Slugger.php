<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Language;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Slugger extends AsciiSlugger
{
    /**
     * Summary of slugify
     * @param string $string
     * @deprecated 3.5.1 use Slugger()->slug()
     * @return string
     */
    public static function slugify($string)
    {
        $replace = [
            ' ' => '_',
            '&' => '-',
            '#' => '-',
            '"' => '',
            "'" => '_',
            ':' => '',
            ';' => '',
            '<' => '',
            '>' => '',
            '{' => '',
            '}' => '',
            '?' => '',
            ',' => '',
            '/' => '.',
            '\\' => '.',
            '.' => '',
        ];
        // normalize first
        $string = Normalizer::normalizeUtf8String($string);

        // then clean the new string - e.g. 'sun wu' to 'sun_wu'
        return str_replace(array_keys($replace), array_values($replace), trim($string));
    }
}
