<?php
/**
 * BookInfos class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

namespace Marsender\EPubLoader;

use SebLucas\EPubMeta\EPub;
use SebLucas\EPubMeta\Dom\Element;

$ePubMetaPath = realpath(dirname(__DIR__)) . '/php-epub-meta';
require_once $ePubMetaPath . '/lib/EPub.php';
require_once $ePubMetaPath . '/lib/Dom/Element.php';
require_once $ePubMetaPath . '/lib/Dom/XPath.php';

class BookEPub extends EPub
{
    /**
     * Get or set the book author(s)
     * @deprecated 1.5.0 use getAuthors() or setAuthors() instead
     * @param array<mixed>|string|false $authors
     * @return mixed
     */
    public function Authors($authors = false)
    {
        // set new data
        if ($authors !== false) {
            $this->setAuthors($authors);
        }

        // read current data
        return $this->getAuthors();
    }

    /**
     * Set the book author(s)
     *
     * Authors should be given with a "file-as" and a real name. The file as
     * is used for sorting in e-readers.
     *
     * Example:
     *
     * array(
     * 'Pratchett, Terry' => 'Terry Pratchett',
     * 'Simpson, Jacqueline' => 'Jacqueline Simpson',
     * )
     *
     * @param array<mixed>|string $authors
     * @return void
     */
    public function setAuthors($authors)
    {
        // Author where given as a comma separated list
        if (is_string($authors)) {
            if ($authors == '') {
                $authors = [];
            } else {
                $authors = explode(',', $authors);
                $authors = array_map('trim', $authors);
            }
        }

        // delete existing nodes
        $nodes = $this->xpath->query('//opf:metadata/dc:creator[@opf:role="aut"]');
        $this->deleteNodes($nodes);

        // add new nodes
        /** @var Element $parent */
        $parent = $this->xpath->query('//opf:metadata')->item(0);
        foreach ($authors as $as => $name) {
            if (is_int($as)) {
                $as = $name;
            } //numeric array given
            /** @var Element $node */
            $node = $parent->newChild('dc:creator', $name);
            $node->setAttrib('opf:role', 'aut');
            $node->setAttrib('opf:file-as', $as);
        }

        $this->reparse();
    }

    /**
     * Get the book author(s)
     *
     * Authors will be given with a "file-as" and a real name. The file as
     * is used for sorting in e-readers.
     *
     * Example:
     *
     * array(
     * 'Pratchett, Terry' => 'Terry Pratchett',
     * 'Simpson, Jacqueline' => 'Jacqueline Simpson',
     * )
     *
     * @return mixed
     */
    public function getAuthors()
    {
        $rolefix = false;
        $authors = [];
        $version = $this->getEpubVersion();
        if ($version == 3) {
            $rolefix = true;
            // <dc:creator id="create1">Marie d'Agoult</dc:creator>
            $nodes = $this->xpath->query('//opf:metadata/dc:creator');
        } else {
            // <dc:creator opf:file-as="Bouvier, Alexis" opf:role="aut">Alexis Bouvier</dc:creator>
            $nodes = $this->xpath->query('//opf:metadata/dc:creator[@opf:role="aut"]');
        }
        foreach ($nodes as $node) {
            /** @var Element $node */
            $as = '';
            $name = $node->nodeValue;
            if ($version == 3) {
                $property = '';
                $id = $node->getAttrib('id');
                if (empty($id)) {
                    $as = $name;
                    $node->setAttrib('opf:file-as', $as);
                    if ($rolefix) {
                        $node->setAttrib('opf:role', 'aut');
                    }
                    $authors[$as] = $name;
                    continue;
                }
                // Check if role is aut
                // <meta refines="#create1" scheme="marc:relators" property="role">aut</meta>
                $metaNodes = $this->xpath->query('//opf:metadata/opf:meta[@refines="#' . $id . '"]');
                foreach ($metaNodes as $metaNode) {
                    /** @var Element $metaNode */
                    $metaProperty = $metaNode->getAttrib('property');
                    switch ($metaProperty) {
                        case 'role':
                            $property = $metaNode->nodeValue;
                            break;
                        case 'file-as':
                            $as = $metaNode->nodeValue;
                            break;
                    }
                }
                if (count($metaNodes) > 0 && $property != 'aut') {
                    continue;
                }
            } else {
                $as = $node->getAttrib('opf:file-as');
            }
            if (!$as) {
                $as = $name;
                $node->setAttrib('opf:file-as', $as);
            }
            if ($rolefix) {
                $node->setAttrib('opf:role', 'aut');
            }
            $authors[$as] = $name;
        }

        if (count($authors) > 1) {
            ksort($authors);
        }

        return $authors;
    }

    /**
     * Set or get the book's creation date - with fallback to dcterms:created
     *
     * @param string|false $date Date eg: 2012-05-19T12:54:25Z
     */
    public function CreationDate($date = false)
    {
        // <dc:date opf:event="creation">2014-08-03T16:01:40Z</dc:date>
        $res = $this->getset('dc:date', $date, 'opf:event', 'creation');

        // <meta property="dcterms:created">2014-06-08T14:22:53Z</meta>
        if (empty($res)) {
            $version = $this->getEpubVersion();
            if ($version == 3) {
                $res = $this->getset('opf:meta', $date, 'property', 'dcterms:created');
            }
        }

        // <meta content="2014-08-03T18:01:35" name="amanuensis:xhtml-creation-date" />
        if (empty($res)) {
            $res = $this->getset('opf:meta', $date, 'name', 'amanuensis:xhtml-creation-date', 'content');
        }

        // <dc:date>2014-08-03T16:01:40Z</dc:date>
        if (empty($res)) {
            $res = $this->getset('dc:date', $date);
        }

        return $res;
    }

    /**
     * Set or get the book's modification date - with fallback to dcterms:modified
     *
     * @param string|false $date Date eg: 2012-05-19T12:54:25Z
     */
    public function ModificationDate($date = false)
    {
        // <dc:date opf:event="modification">2014-08-03T16:01:40Z</dc:date>
        $res = $this->getset('dc:date', $date, 'opf:event', 'modification');

        // <meta property="dcterms:modified">2018-12-20T13:59:10Z</meta>
        if (empty($res)) {
            $version = $this->getEpubVersion();
            if ($version == 3) {
                $res = $this->getset('opf:meta', $date, 'property', 'dcterms:modified');
            }
        }

        return $res;
    }

    // public function Cover($path = false, $mime = false)
    // $zip->close();
}
