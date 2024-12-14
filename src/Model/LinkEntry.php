<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Model;

/**
 * From https://specs.opds.io/opds-1.2#52-catalog-entry-relations
 * OPDS Catalog Entry Documents SHOULD include links to related Resources. This specification
 * defines new relations for linking from OPDS Catalog Entry Documents. They are defined in the
 * Sections Acquisition Relations and Artwork Relations.
 */
class LinkEntry extends Link
{
    public const OPDS_THUMBNAIL_TYPE = "http://opds-spec.org/image/thumbnail";
    public const OPDS_IMAGE_TYPE = "http://opds-spec.org/image";
    public const OPDS_ACQUISITION_TYPE = "http://opds-spec.org/acquisition";
    /** @var ?string */
    public $length = null;
    /** @var ?string */
    public $mtime = null;
    /** @var ?string */
    public $filepath = null;

    /**
     * Summary of addFileInfo
     * @param string $filepath
     * @return void
     */
    public function addFileInfo($filepath)
    {
        if (!file_exists($filepath)) {
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
}
