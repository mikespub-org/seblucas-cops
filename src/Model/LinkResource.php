<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Routing\UriGenerator;

/**
 * From https://specs.opds.io/opds-1.2#52-catalog-entry-relations
 * OPDS Catalog Entry Documents SHOULD include links to related Resources. This specification
 * defines new relations for linking from OPDS Catalog Entry Documents. They are defined in the
 * Sections Acquisition Relations and Artwork Relations.
 */
class LinkResource extends Link
{
    /** @var ?string */
    public $filepath = null;
    /** @var ?string */
    public $length = null;
    /** @var ?string */
    public $mtime = null;

    /**
     * Summary of __construct
     * @param string|\Closure $href uri or closure including the endpoint
     * @param string $type link type in the OPDS catalog
     * @param ?string $rel relation in the OPDS catalog
     * @param ?string $title title in the OPDS catalog and elsewhere
     * @param ?string $filepath filepath of this resource
     */
    public function __construct($href, $type, $rel = null, $title = null, $filepath = null)
    {
        parent::__construct($href, $type, $rel, $title);
        $this->addFileInfo($filepath);
    }

    /**
     * Summary of addFileInfo
     * @param string $filepath
     * @return void
     */
    public function addFileInfo($filepath)
    {
        if (empty($filepath) || !file_exists($filepath)) {
            return;
        }
        $this->filepath = $filepath;
    }

    /**
     * Summary of hasFileInfo
     * @return bool
     */
    public function hasFileInfo()
    {
        return isset($this->filepath);
    }

    /**
     * Summary of getSize
     * @return string|null
     */
    public function getSize()
    {
        if (!isset($this->filepath)) {
            return $this->length;
        }
        $this->length ??= (string) filesize($this->filepath);
        return $this->length;
    }

    /**
     * Summary of getLastModified
     * @return string|null
     */
    public function getLastModified()
    {
        if (!isset($this->filepath)) {
            return $this->mtime;
        }
        $this->mtime ??= date(DATE_ATOM, filemtime($this->filepath));
        return $this->mtime;
    }

    /**
     * Summary of getUri
     * @return string
     */
    public function getUri()
    {
        return UriGenerator::cached(parent::getUri());
    }
}
