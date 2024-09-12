<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

class Link
{
    //public const OPDS_THUMBNAIL_TYPE = "http://opds-spec.org/image/thumbnail";
    //public const OPDS_IMAGE_TYPE = "http://opds-spec.org/image";
    //public const OPDS_ACQUISITION_TYPE = "http://opds-spec.org/acquisition";
    //public const OPDS_NAVIGATION_FEED = "application/atom+xml;profile=opds-catalog;kind=navigation";
    //public const OPDS_ACQUISITION_FEED = "application/atom+xml;profile=opds-catalog;kind=acquisition";

    public string $href;
    public string $type;
    /** @var ?string */
    public $rel;
    /** @var ?string */
    public $title;

    /**
     * Summary of __construct
     * @param string $href uri including the endpoint for images, books etc.
     * @param string $type link type in the OPDS catalog
     * @param ?string $rel relation in the OPDS catalog
     * @param ?string $title title in the OPDS catalog and elsewhere
     */
    public function __construct($href, $type, $rel = null, $title = null)
    {
        $this->href = $href;
        $this->type = $type;
        $this->rel = $rel;
        $this->title = $title;
    }

    /**
     * Summary of hrefXhtml
     * @return string
     */
    public function hrefXhtml()
    {
        // Link()->href includes the endpoint here
        return $this->href;
    }
}
