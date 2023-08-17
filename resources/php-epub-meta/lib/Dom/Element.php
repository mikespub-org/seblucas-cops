<?php
/**
 * PHP EPub Meta library
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\EPubMeta\Dom;

use DOMElement;

/**
 * EPUB-specific subclass of DOMElement
 *
 * Source: https://github.com/splitbrain/php-epub-meta
 * @author Andreas Gohr <andi@splitbrain.org> © 2012
 * @author Simon Schrape <simon@epubli.com> © 2015–2018
 * @author Sébastien Lucas <sebastien@slucas.fr>
 *
 * @property string $nodeValueUnescaped
 */
class Element extends DOMElement
{
    /** @var array<string, string> */
    public static $namespaces = [
        'n'   => 'urn:oasis:names:tc:opendocument:xmlns:container',
        'opf' => 'http://www.idpf.org/2007/opf',
        'dc'  => 'http://purl.org/dc/elements/1.1/',
        'ncx' => 'http://www.daisy.org/z3986/2005/ncx/',
    ];

    /**
     * Summary of __construct
     * @param string $name
     * @param string $value
     * @param string $namespaceUri
     */
    public function __construct($name, $value='', $namespaceUri='')
    {
        [$prefix, $name] = $this->splitQualifiedName($name);
        $value = htmlspecialchars($value);
        if (!$namespaceUri && $prefix) {
            //$namespaceUri = XmlNamespace::getUri($prefix);
            $namespaceUri = static::$namespaces[$prefix];
        }
        parent::__construct($name, $value, $namespaceUri);
    }

    /**
     * Summary of __get
     * @param string $name
     * @return string|null
     */
    public function __get($name)
    {
        switch ($name) {
            case 'nodeValueUnescaped':
                return htmlspecialchars_decode($this->nodeValue);
        }

        return null;
    }

    /**
     * Summary of __set
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'nodeValueUnescaped':
                $this->nodeValue = htmlspecialchars($value);
        }
    }

    /**
     * Create and append a new child
     *
     * Works with our epub namespaces and omits default namespaces
     * @param string $name
     * @param string $value
     * @return Element|bool
     */
    public function newChild($name, $value='')
    {
        [$localName, $namespaceUri] = $this->getNameContext($name);

        // this doesn't call the constructor: $node = $this->ownerDocument->createElement($name,$value);
        $node = new Element($namespaceUri ? $name : $localName, $value, $namespaceUri);

        /** @var Element $node */
        $node = $this->appendChild($node);
        return $node;
    }

    /**
     * Split given name in namespace prefix and local part
     *
     * @deprecated 1.5.0 use splitQualifiedName() instead
     * @param  string $name
     * @return array<string>  (namespace, name)
     */
    public function splitns($name)
    {
        $list = explode(':', $name, 2);
        if (count($list) < 2) {
            array_unshift($list, '');
        }
        return $list;
    }

    /**
     * Simple EPub namespace aware attribute accessor
     * @deprecated 1.5.0 use getAttrib(), setAttrib() or removeAttrib() instead
     * @param string $attr
     * @param string|false|null $value
     * @return string|void
     */
    public function attr($attr, $value=null)
    {
        if (!is_null($value)) {
            if ($value === false) {
                // delete if false was given
                $this->removeAttrib($attr);
            } else {
                // modify if value was given
                $this->setAttrib($attr, $value);
            }
        } else {
            // return value if none was given
            return $this->getAttrib($attr);
        }
    }

    /**
     * Simple EPUB namespace aware attribute getter
     * @param string $name
     * @return string
     */
    public function getAttrib($name)
    {
        [$localName, $namespaceUri] = $this->getNameContext($name);

        // return value if none was given
        if ($namespaceUri) {
            return $this->getAttributeNS($namespaceUri, $localName);
        } else {
            return $this->getAttribute($localName);
        }
    }

    /**
     * Simple EPUB namespace aware attribute setter
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setAttrib($name, $value)
    {
        [$localName, $namespaceUri] = $this->getNameContext($name);

        if ($namespaceUri) {
            $this->setAttributeNS($namespaceUri, $localName, $value);
        } else {
            $this->setAttribute($localName, $value);
        }
    }

    /**
     * Simple EPUB namespace aware attribute remover
     * @param string $name
     * @return void
     */
    public function removeAttrib($name)
    {
        [$localName, $namespaceUri] = $this->getNameContext($name);

        if ($namespaceUri) {
            $this->removeAttributeNS($namespaceUri, $localName);
        } else {
            $this->removeAttribute($localName);
        }
    }

    /**
     * Remove this node from the DOM
     * @return void
     */
    public function delete()
    {
        $this->parentNode->removeChild($this);
    }

    /**
     * Split given name in namespace prefix and local part
     *
     * @param  string $name
     * @return array<string>  (prefix, name)
     */
    protected function splitQualifiedName($name)
    {
        $list = explode(':', $name, 2);
        if (count($list) < 2) {
            array_unshift($list, '');
        }

        return $list;
    }

    /**
     * @param  string $name
     * @return array<string>
     */
    protected function getNameContext($name)
    {
        [$prefix, $localName] = $this->splitQualifiedName($name);

        $namespaceUri = '';
        if ($prefix) {
            //$namespaceUri = XmlNamespace::getUri($prefix);
            $namespaceUri = static::$namespaces[$prefix];
            if (
                !$this->namespaceURI && $this->isDefaultNamespace($namespaceUri)
                || $this->namespaceURI == $namespaceUri
            ) {
                $namespaceUri = '';
            }
        }

        return [$localName, $namespaceUri];
    }
}
