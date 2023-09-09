<?php
/**
 * Representation of an EPUB document.
 *
 * @author Andreas Gohr <andi@splitbrain.org> © 2012
 * @author Simon Schrape <simon@epubli.com> © 2015
 */

//namespace Epubli\Epub;

namespace SebLucas\EPubMeta;

use SebLucas\EPubMeta\Dom\Element as EpubDomElement;
use SebLucas\EPubMeta\Dom\XPath as EpubDomXPath;
use SebLucas\EPubMeta\Data\Manifest;
use SebLucas\EPubMeta\Contents\Spine;
use SebLucas\EPubMeta\Contents\Toc;
use DOMDocument;
use DOMElement;
use Exception;
use InvalidArgumentException;
use ZipArchive;

/**
 * @todo These are the methods that haven't been integrated with EPub here...
 */
class Other extends EPub
{
    /**
     * A simple setter for simple meta attributes
     *
     * It should only be used for attributes that are expected to be unique
     *
     * @param string $item XML node to set
     * @param string $value New node value
     * @param bool|string $attribute Attribute name
     * @param bool|string $attributeValue Attribute value
     * @param bool $caseSensitive
     */
    protected function setMeta($item, $value, $attribute = false, $attributeValue = false, $caseSensitive = true)
    {
        $xpath = $this->buildMetaXPath($item, $attribute, $attributeValue, $caseSensitive);

        // set value
        $nodes = $this->xpath->query($xpath);
        if ($nodes->length == 1) {
            /** @var EpubDomElement $node */
            $node = $nodes->item(0);
            if ($value === '') {
                // the user wants to empty this value -> delete the node
                $node->delete();
            } else {
                // replace value
                $node->nodeValueUnescaped = $value;
            }
        } else {
            // if there are multiple matching nodes for some reason delete
            // them. we'll replace them all with our own single one
            foreach ($nodes as $node) {
                /** @var EpubDomElement $node */
                $node->delete();
            }
            // re-add them
            if ($value) {
                $parent = $this->xpath->query('//opf:metadata')->item(0);
                $node = new EpubDomElement($item, $value);
                $node = $parent->appendChild($node);
                if ($attribute) {
                    if (is_array($attributeValue)) {
                        // use first given value for new attribute
                        $attributeValue = reset($attributeValue);
                    }
                    $node->setAttrib($attribute, $attributeValue);
                }
            }
        }

        $this->sync();
    }

    /**
     * A simple getter for simple meta attributes
     *
     * It should only be used for attributes that are expected to be unique
     *
     * @param string $item XML node to get
     * @param bool|string $att Attribute name
     * @param bool|string $aval Attribute value
     * @param bool $caseSensitive
     * @return string
     */
    protected function getMeta($item, $att = false, $aval = false, $caseSensitive = true)
    {
        $xpath = $this->buildMetaXPath($item, $att, $aval, $caseSensitive);

        // get value
        $nodes = $this->xpath->query($xpath);
        if ($nodes->length) {
            /** @var EpubDomElement $node */
            $node = $nodes->item(0);

            return $node->nodeValueUnescaped;
        } else {
            return '';
        }
    }

    /**
     * Sync XPath object with updated DOM.
     */
    protected function sync()
    {
        $dom = $this->xpath->document;
        $dom->loadXML($dom->saveXML());
        $this->xpath = new EpubDomXPath($dom);
        // reset structural members
        $this->manifest = null;
        $this->spine = null;
        $this->tocnav = null;
    }
}
