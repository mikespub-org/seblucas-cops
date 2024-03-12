<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use DOMDocument;
use DOMNode;
use DOMText;
use JsonException;

/**
 * Calibre metadata.opf files are based on EPUB 2.0 <https://idpf.org/epub/20/spec/OPF_2.0_latest.htm#Section2.0>,
 * not EPUB 3.x <https://www.w3.org/TR/epub-33/#sec-package-doc>
 */
class Metadata
{
    public string $uniqueIdentifier;
    public string $version;
    /** @var array<mixed> */
    public array $metadata;
    /** @var array<mixed> */
    public array $guide;

    /**
     * Summary of getElement
     * @param string $element like dc:identifier
     * @return array<mixed>
     */
    public function getElement($element)
    {
        $elements = [];
        foreach ($this->metadata as $item) {
            if (empty($item[$element])) {
                continue;
            }
            $elements[] = $item[$element];
        }
        return $elements;
    }

    /**
     * Summary of getIdentifiers
     * @return array<mixed>
     */
    public function getIdentifiers()
    {
        return $this->getElement('dc:identifier');
    }

    /**
     * Summary of getElementName
     * @param string $element like meta
     * @param string $name like calibre:annotation
     * @return array<mixed>
     */
    public function getElementName($element, $name)
    {
        $elements = [];
        foreach ($this->metadata as $item) {
            if (empty($item[$element])) {
                continue;
            }
            if (empty($item[$element]['name']) || $item[$element]['name'] != $name) {
                continue;
            }
            $elements[] = $item[$element]['content'];
        }
        return $elements;
    }

    /**
     * Summary of getAnnotations
     * @return array<mixed>
     */
    public function getAnnotations()
    {
        return $this->getElementName('meta', 'calibre:annotation');
    }

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
        if (empty($file) || !file_exists($file)) {
            return false;
        }
        $content = file_get_contents($file);
        return static::parseData($content);
    }

    /**
     * @param string $data
     * @return self
     */
    public static function parseData($data)
    {
        $doc = new DOMDocument();
        $doc->loadXML($data);
        $root = static::getNode($doc, 'package');

        $package = new self();
        $package->uniqueIdentifier = static::getAttr($root, 'unique-identifier');
        $package->version = static::getAttr($root, 'version');
        $package->metadata = static::addNode(static::getNode($root, 'metadata'));
        $package->guide = static::addNode(static::getNode($root, 'guide'));
        return $package;
    }

    /**
     * @param DOMNode $node
     * @param string $name
     * @return mixed
     */
    public static function getAttr($node, $name)
    {
        return $node->getAttribute($name);
    }

    /**
     * @param DOMNode $node
     * @param string $name
     * @return DOMNode
     */
    public static function getNode($node, $name)
    {
        return $node->getElementsByTagName($name)->item(0);
    }

    /**
     * @param DOMNode $node
     * @return string|array<mixed>
     */
    public static function addNode($node)
    {
        if (!$node->hasAttributes() && !$node->hasChildNodes()) {
            return trim($node->nodeValue);
        }
        $children = null;
        if ($node->hasChildNodes()) {
            if ($node->childNodes->length == 1 && $node->firstChild instanceof DOMText) {
                $children = trim($node->firstChild->nodeValue);
            } else {
                $children = [];
                foreach ($node->childNodes as $child) {
                    if ($child instanceof DOMText) {
                        continue;
                    }
                    $children[] = [$child->nodeName => self::addNode($child)];
                }
            }
        }
        if (!$node->hasAttributes()) {
            return $children;
        }
        $info = [];
        foreach ($node->attributes as $attr) {
            if ($node->nodeName == 'meta' && $attr->name == 'content' && !empty($attr->value)) {
                try {
                    $info[$attr->name] = json_decode($attr->value, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    $info[$attr->name] = $attr->value;
                }
            } elseif ($attr->name == 'value') {
                $info['@value'] = $attr->value;
            } else {
                $info[$attr->name] = $attr->value;
            }
        }
        //$value = trim($node->nodeValue);
        //if ($value !== '') {
        //    $info['value'] = $value;
        //}
        if (!empty($children)) {
            if (is_array($children)) {
                $info['children'] = $children;
            } else {
                $info['value'] = $children;
            }
        }
        return $info;
    }
}
