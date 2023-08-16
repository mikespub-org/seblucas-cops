<?php

namespace SebLucas\EPubMeta\Data;

use SebLucas\EPubMeta\Tools\HtmlTools;
use DOMDocument;
use DOMElement;
use DOMText;
use DOMXPath;
use Exception;

/**
 * An item of the EPUB manifest.
 *
 * @author Simon Schrape <simon@epubli.com>
 */
class Item
{
    public const XHTML = 'application/xhtml+xml';
    /** @var string */
    private $id;
    /** @var string The path to the corresponding file. */
    private $href;
    /** @var string */
    private $mediaType;
    /** @var resource|null A handle to the referenced file. */
    private $dataHandle;
    /** @var string The data read from the referenced file. */
    private $data;
    /** @var int The size of the referenced file. */
    private $size;

    /**
     * @param string $id This Item’s identifier.
     * @param string $href The path to the corresponding file.
     * @param resource $dataHandle A handle to the referenced file.
     * @param int $size The size of the referenced file.
     * @param string|null $mediaType The media type of the corresponding file. If omitted XHTML is assumed.
     */
    public function __construct($id, $href, $dataHandle, $size, $mediaType = null)
    {
        $this->id = $id;
        $this->href = $href;
        $this->dataHandle = $dataHandle;
        $this->size = $size;
        $this->mediaType = $mediaType ?: static::XHTML;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @return string
     */
    public function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * Extract (a part of) the contents from the referenced XML file.
     *
     * @param string|null $fragmentBegin ID of the element where to start reading the contents.
     * @param string|null $fragmentEnd ID of the element where to stop reading the contents.
     * @param bool $keepMarkup Whether to keep the XHTML markup rather than extracted plain text.
     * @return string The contents of that fragment.
     * @throws Exception
     */
    public function getContents($fragmentBegin = null, $fragmentEnd = null, $keepMarkup = false)
    {
        $dom = new DOMDocument();
        $dom->loadXML(HtmlTools::convertEntitiesNamedToNumeric($this->getData()));

        // get the starting point
        if ($fragmentBegin) {
            $xp = new DOMXPath($dom);
            $node = $xp->query("//*[@id='$fragmentBegin']")->item(0);
            if (!$node) {
                throw new Exception("Begin of fragment not found: No element with ID $fragmentBegin!");
            }
        } else {
            $node = $dom->getElementsByTagName('body')->item(0) ?: $dom->documentElement;
        }

        $allowableTags = [
            'br',
            'p',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'span',
            'div',
            'i',
            'strong',
            'b',
            'table',
            'td',
            'th',
            'tr',
        ];
        $contents = '';
        $endTags = [];
        /** @var DOMElement|DOMText $node */
        // traverse DOM structure till end point is reached, accumulating the contents
        while ($node && (!$fragmentEnd || !$node->hasAttributes() || $node->getAttribute('id') != $fragmentEnd)) {
            if ($node instanceof DOMText) {
                // when encountering a text node append its value to the contents
                $contents .= $keepMarkup ? htmlspecialchars($node->nodeValue) : $node->nodeValue;
            } elseif ($node instanceof DOMElement) {
                $tag = $node->localName;
                if ($keepMarkup && in_array($tag, $allowableTags)) {
                    $contents .= "<$tag>";
                    $endTags[] = "</$tag>";
                } elseif (HtmlTools::isBlockLevelElement($tag)) {
                    // add whitespace between contents of adjacent blocks
                    $endTags[] = PHP_EOL;
                } else {
                    $endTags[] = '';
                }

                if ($node->hasChildNodes()) {
                    // step into
                    $node = $node->firstChild;
                    continue;
                }
            }

            // leave node
            while ($node) {
                if ($node instanceof DOMElement) {
                    $contents .= array_pop($endTags);
                }

                if ($node->nextSibling) {
                    // step right
                    $node = $node->nextSibling;
                    break;
                } elseif ($node = $node->parentNode) {
                    // step out
                    continue;
                } elseif ($fragmentEnd) {
                    // reached end of DOM without finding fragment end
                    throw new Exception("End of fragment not found: No element with ID $fragmentEnd!");
                }
            }
        }
        while ($endTags) {
            $contents .= array_pop($endTags);
        }

        return $contents;
    }

    /**
     * Get the file data. - @todo no data handle here
     *
     * @return string The binary data of the corresponding file.
     */
    public function getData()
    {
        if ($this->dataHandle) {
            while (($line = fgets($this->dataHandle)) !== false) {
                $this->data .= $line;
            }
            fclose($this->dataHandle);
        }

        return $this->data;
    }

    /**
     * Get the size of the corresponding file.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size ?: strlen($this->getData());
    }
}
