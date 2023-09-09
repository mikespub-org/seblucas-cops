<?php
/**
 * PHP EPub Meta library
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\EPubMeta\Dom;

use DOMDocument;
use DOMXPath;

/**
 * EPUB-specific subclass of DOMXPath
 *
 * Source: https://github.com/splitbrain/php-epub-meta
 * @author Andreas Gohr <andi@splitbrain.org> © 2012
 * @author Simon Schrape <simon@epubli.com> © 2015–2018
 * @author Sébastien Lucas <sebastien@slucas.fr>
 */
class XPath extends DOMXPath
{
    public function __construct(DOMDocument $doc)
    {
        parent::__construct($doc);

        foreach (Element::$namespaces as $prefix => $namespaceUri) {
            $this->registerNamespace($prefix, $namespaceUri);
        }
    }
}
