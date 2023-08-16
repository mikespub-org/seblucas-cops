<?php

namespace Epubli\Epub\Dom;

use Epubli\Common\Basic\Enum;
use Epubli\Common\Tools\StringTools;

/**
 * XML Namespaces used in EPUB
 * See https://en.wikipedia.org/wiki/EPUB
 *
 * @author Simon Schrape <simon@epubli.com>
 *
 * @method static XmlNamespace OCF()
 * @method static XmlNamespace OPF()
 * @method static XmlNamespace DC()
 */
class XmlNamespace extends Enum
{
    /** @var string Open Container Format XML namespace */
    public const OCF = 'urn:oasis:names:tc:opendocument:xmlns:container';

    /** @var string Open Packaging Format XML namespace */
    public const OPF = 'http://www.idpf.org/2007/opf';

    /** @var string Dublin Core Metadata Element Set XML namespace */
    public const DC = 'http://purl.org/dc/elements/1.1/';

    /** @var string “Navigation Control file for XML” XML namespace */
    public const NCX = 'http://www.daisy.org/z3986/2005/ncx/';

    /**
     * Directly get the URI for a given namespace prefix.
     *
     * @param $prefix
     * @return string
     */
    public static function getUri($prefix)
    {
        $key = StringTools::toUpperSnakeCase($prefix);
        if (!static::isValidKey($key)) {
            throw new \UnexpectedValueException("Unknown XML namespace $prefix!");
        }

        return static::toArray()[$key];
    }

    /**
     * Get an array with namespace URIs mapped to namespace prefixes.
     *
     * @return array (prefix => uri)
     */
    public static function toPrefixArray()
    {
        $array = static::toArray();
        $prefixArray = [];
        foreach ($array as $key => $uri) {
            $prefixArray[StringTools::toLowerCamelCase($key)] = $uri;
        }

        return $prefixArray;
    }
}
