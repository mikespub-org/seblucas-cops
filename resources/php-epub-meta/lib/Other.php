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
    /** Identifier for cover image inserted by this lib. */
    public const COVER_ID = 'epubli-epub-cover';
    /** Identifier for title page inserted by this lib. */
    public const TITLE_PAGE_ID = 'epubli-epub-titlepage';

    /** @var Manifest|null The manifest (catalog of files) of this EPUB */
    private $manifest;
    /** @var Spine|null The spine structure of this EPUB */
    private $spine;
    /** @var Toc|null The TOC structure of this EPUB */
    private $tocnav;

    /**
     * Remove the cover image
     *
     * If the actual image file was added by this library it will be removed. Otherwise only the
     * reference to it is removed from the metadata, since the same image might be referenced
     * by other parts of the EPUB file.
     */
    public function clearCover()
    {
        if (!$this->hasCover()) {
            return;
        }

        // remove any cover image file added by us
        $this->zip->deleteName($this->packageDir . static::COVER_ID . '.img');

        // remove metadata cover pointer
        $nodes = $this->xpath->query('//opf:metadata/opf:meta[@name="cover"]');
        foreach ($nodes as $node) {
            /** @var EpubDomElement $node */
            $node->delete();
        }

        // remove previous manifest entries if they where made by us
        $nodes = $this->xpath->query('//opf:manifest/opf:item[@id="' . static::COVER_ID . '"]');
        foreach ($nodes as $node) {
            /** @var EpubDomElement $node */
            $node->delete();
        }

        $this->sync();
    }

    /**
     * Set the cover image
     *
     * @param string $path local filesystem path to a new cover image
     * @param string $mime mime type of the given file
     */
    public function setCover($path, $mime)
    {
        if (!$path) {
            throw new InvalidArgumentException('Parameter $path must not be empty!');
        }

        if (!is_readable($path)) {
            throw new InvalidArgumentException("Cannot add $path as new cover image since that file is not readable!");
        }

        $this->clearCover();

        // add metadata cover pointer
        /** @var EpubDomElement $parent */
        $parent = $this->xpath->query('//opf:metadata')->item(0);
        $node = $parent->newChild('opf:meta');
        $node->setAttrib('opf:name', 'cover');
        $node->setAttrib('opf:content', static::COVER_ID);

        // add manifest item
        $parent = $this->xpath->query('//opf:manifest')->item(0);
        $node = $parent->newChild('opf:item');
        $node->setAttrib('id', static::COVER_ID);
        $node->setAttrib('opf:href', static::COVER_ID . '.img');
        $node->setAttrib('opf:media-type', $mime);

        // add the cover image
        $this->zip->addFile($path, $this->packageDir . static::COVER_ID . '.img');

        $this->sync();
    }

    /**
     * Add a title page with the cover image to the EPUB.
     *
     * @param string $templatePath The path to the template file. Defaults to an XHTML file contained in this library.
     */
    public function addCoverImageTitlePage($templatePath = __DIR__ . '/../templates/titlepage.xhtml')
    {
        $xhtmlFilename = static::TITLE_PAGE_ID . '.xhtml';

        // add title page file to zip
        $template = file_get_contents($templatePath);
        $xhtml = strtr($template, ['{{ title }}' => $this->getTitle(), '{{ coverPath }}' => $this->getCoverPath()]);
        $this->zip->addFromString($this->packageDir . $xhtmlFilename, $xhtml);

        // prepend title page file to manifest
        $parent = $this->xpath->query('//opf:manifest')->item(0);
        $node = new EpubDomElement('opf:item');
        $parent->insertBefore($node, $parent->firstChild);
        $node->setAttrib('id', static::TITLE_PAGE_ID);
        $node->setAttrib('opf:href', $xhtmlFilename);
        $node->setAttrib('opf:media-type', 'application/xhtml+xml');

        // prepend title page spine item
        $parent = $this->xpath->query('//opf:spine')->item(0);
        $node = new EpubDomElement('opf:itemref');
        $parent->insertBefore($node, $parent->firstChild);
        $node->setAttrib('idref', static::TITLE_PAGE_ID);

        // prepend title page guide reference
        $parent = $this->xpath->query('//opf:guide')->item(0);
        $node = new EpubDomElement('opf:reference');
        $parent->insertBefore($node, $parent->firstChild);
        $node->setAttrib('opf:href', $xhtmlFilename);
        $node->setAttrib('opf:type', 'cover');
        $node->setAttrib('opf:title', 'Title Page');
    }

    /**
     * Remove the title page added by this library (determined by a certain manifest item ID).
     */
    public function removeTitlePage()
    {
        $xhtmlFilename = static::TITLE_PAGE_ID . '.xhtml';

        // remove title page file from zip
        $this->zip->deleteName($this->packageDir . $xhtmlFilename);

        // remove title page file from manifest
        $nodes = $this->xpath->query('//opf:manifest/opf:item[@id="' . static::TITLE_PAGE_ID . '"]');
        foreach ($nodes as $node) {
            /** @var EpubDomElement $node */
            $node->delete();
        }

        // remove title page spine item
        $nodes = $this->xpath->query('//opf:spine/opf:itemref[@idref="' . static::TITLE_PAGE_ID . '"]');
        foreach ($nodes as $node) {
            /** @var EpubDomElement $node */
            $node->delete();
        }

        // remove title page guide reference
        $nodes = $this->xpath->query('//opf:guide/opf:reference[@href="' . $xhtmlFilename . '"]');
        foreach ($nodes as $node) {
            /** @var EpubDomElement $node */
            $node->delete();
        }
    }

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
    private function setMeta($item, $value, $attribute = false, $attributeValue = false, $caseSensitive = true)
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
    private function getMeta($item, $att = false, $aval = false, $caseSensitive = true)
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
     * Build an XPath expression to select certain nodes in the metadata section.
     *
     * @param string $element The node name of the elements to select.
     * @param string $attribute If set, the attribute required in the element.
     * @param string|array $value If set, the value of the above named attribute. If an array is given
     * all of its values will be allowed in the selector.
     * @param bool $caseSensitive If false, attribute values are matched case insensitively.
     * (This is not completely true, as only full upper or lower case strings are matched, not mixed case.
     * A lower-case function is missing in XPath 1.0.)
     * @return string
     */
    private function buildMetaXPath($element, $attribute, $value, $caseSensitive = true)
    {
        $xpath = '//opf:metadata/'.$element;
        if ($attribute) {
            $xpath .= "[@$attribute";
            if ($value) {
                $values = is_array($value) ? $value : [$value];
                if (!$caseSensitive) {
                    $temp = [];
                    foreach ($values as $item) {
                        $temp[] = strtolower($item);
                        $temp[] = strtoupper($item);
                    }
                    $values = $temp;
                }

                $xpath .= '="';
                $xpath .= implode("\" or @$attribute=\"", $values);
                $xpath .= '"';
            }
            $xpath .= ']';
        }

        return $xpath;
    }

    /**
     * Load an XML file from the EPUB/ZIP archive into a new XPath object.
     *
     * @param $path string The XML file to load from the ZIP archive.
     * @return EpubDomXPath The XPath representation of the XML file.
     * @throws Exception If the given path could not be read.
     */
    private function loadXPathFromItem($path)
    {
        $data = $this->zip->getFromName($path);
        if (!$data) {
            throw new Exception("Failed to read from EPUB container: $path.");
        }
        $xml = new DOMDocument();
        $xml->registerNodeClass(DOMElement::class, EpubDomElement::class);
        $xml->loadXML($data);

        return new EpubDomXPath($xml);
    }

    /**
     * Sync XPath object with updated DOM.
     */
    private function sync()
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
