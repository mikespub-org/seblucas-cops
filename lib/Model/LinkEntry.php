<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
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
    public $length;
    /** @var ?string */
    public $mtime;

    /**
     * Summary of hrefXhtml
     * @param string $endpoint
     * @return string
     */
    public function hrefXhtml($endpoint = '')
    {
        // LinkEntry()->href includes the endpoint here
        return $this->href;
    }

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
        $this->length = (string) filesize($filepath);
        $this->mtime = date(DATE_ATOM, filemtime($filepath));
    }
}
