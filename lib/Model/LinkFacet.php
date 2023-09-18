<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

/**
 * From https://specs.opds.io/opds-1.2#4-facets
 * An Acquisition Feed MAY offer multiple links to reorder the Publications listed
 * in the feed or limit them to a subset. This specification defines one new relation
 * to identify such links as Facets:
 *   http://opds-spec.org/facet: An Acquisition Feed with a subset or an alternate order
 *   of the Publications listed.
 *
 * Links using this relation MUST only appear in Acquisition Feeds.
 */
class LinkFacet extends LinkFeed
{
    public const LINK_RELATION = "http://opds-spec.org/facet";

    /** @var ?string */
    public $facetGroup;
    public bool $activeFacet;
    /** @var ?int */
    public $threadCount;

    /**
     * Summary of __construct
     * @param string $phref ?queryString relative to current endpoint
     * @param ?string $ptitle title in the OPDS catalog
     * @param ?string $pfacetGroup facetGroup this facet belongs to
     * @param bool $pactiveFacet is the facet currently active
     * @param ?int $pthreadCount number of items expected
     * @param ?int $database current database in multiple database setup
     */
    public function __construct($phref, $ptitle = null, $pfacetGroup = null, $pactiveFacet = false, $pthreadCount = null, $database = null)
    {
        parent::__construct($phref, static::LINK_RELATION, $ptitle, $database);
        $this->facetGroup = $pfacetGroup;
        $this->activeFacet = $pactiveFacet;
        $this->threadCount = $pthreadCount;
    }
}
