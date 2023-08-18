<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Output\Format;

class LinkFacet extends Link
{
    /** @var string|null */
    public $facetGroup;
    public bool $activeFacet;
    /** @var int|null */
    public $threadCount;

    /**
     * Summary of __construct
     * @param string $phref ?queryString relative to current endpoint
     * @param string|null $ptitle title in the OPDS catalog
     * @param string|null $pfacetGroup facetGroup this facet belongs to
     * @param bool $pactiveFacet is the facet currently active
     * @param int|null $pthreadCount number of items expected
     * @param mixed $database current database in multiple database setup
     */
    public function __construct($phref, $ptitle = null, $pfacetGroup = null, $pactiveFacet = false, $pthreadCount = null, $database = null)
    {
        parent::__construct($phref, Link::OPDS_PAGING_TYPE, "http://opds-spec.org/facet", $ptitle);
        $this->href = Format::addDatabaseParam($this->href, $database);
        $this->facetGroup = $pfacetGroup;
        $this->activeFacet = $pactiveFacet;
        $this->threadCount = $pthreadCount;
    }

    /**
     * Summary of hrefXhtml
     * @param string $endpoint
     * @return string
     */
    public function hrefXhtml($endpoint = '')
    {
        // LinkFacet()->href is relative to endpoint
        return $endpoint . $this->href;
    }
}
