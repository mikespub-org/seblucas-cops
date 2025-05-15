<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\EPubMeta\Metadata as EPubMetadata;

/**
 * Calibre metadata.opf files are based on EPUB 2.0 <https://idpf.org/epub/20/spec/OPF_2.0_latest.htm#Section2.0>,
 * not EPUB 3.x <https://www.w3.org/TR/epub-33/#sec-package-doc>
 */
class Metadata extends EPubMetadata
{
    public const ROUTE_DETAIL = "restapi-metadata";
    public const ROUTE_ELEMENT = "restapi-metadata-element";
    public const ROUTE_ELEMENT_NAME = "restapi-metadata-element-name";

    /**
     * Summary of getInstanceByBookId
     * @param int $bookId
     * @param ?int $database
     * @return Metadata|false
     */
    public static function getInstanceByBookId($bookId, $database = null)
    {
        $book = Book::getBookById($bookId, $database);
        if (empty($book)) {
            return false;
        }
        $file = realpath($book->path . '/metadata.opf');
        return self::fromFile($file);
    }
}
