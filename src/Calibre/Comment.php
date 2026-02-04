<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\CalibreHandler;
use SebLucas\Cops\Routing\UriGenerator;

class Comment
{
    public const CALIBRE_URL_SCHEME = CalibreHandler::URL_SCHEME;

    /**
     * Summary of hasCalibreLinks
     * @param string $text
     * @return bool
     */
    public static function hasCalibreLinks($text)
    {
        return str_contains($text, self::CALIBRE_URL_SCHEME . '://');
    }

    /**
     * Summary of fixCalibreLinks
     * @param string $text
     * @param ?int $database
     * @return string
     */
    public static function fixCalibreLinks($text, $database = null)
    {
        // @todo add database param if not null and Library_Name is _ (current)
        $baseurl = UriGenerator::absolute(CalibreHandler::PREFIX);
        return str_replace(self::CALIBRE_URL_SCHEME . '://', $baseurl . '/', $text);
    }
}
