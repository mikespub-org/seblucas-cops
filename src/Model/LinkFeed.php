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
 * From https://specs.opds.io/opds-1.2#23-acquisition-feeds
 * An Acquisition Feed is an OPDS Catalog Feed Document that collects OPDS Catalog Entries
 * into a single, ordered set. The simplest complete OPDS Catalog would be a single Acquisition
 * Feed listing all of the available OPDS Catalog Entries from that provider. In more complex
 * OPDS Catalogs, Acquisition Feeds are used to present and organize sets of related OPDS
 * Catalog Entries for browsing and discovery by clients and aggregators.
 *
 * Links to Acquisition Feeds MUST use the "type" attribute
 *   "application/atom+xml;profile=opds-catalog;kind=acquisition"
 */
class LinkFeed extends Link
{
    public const OPDS_NAVIGATION_FEED = "application/atom+xml;profile=opds-catalog;kind=navigation";
    public const OPDS_ACQUISITION_FEED = "application/atom+xml;profile=opds-catalog;kind=acquisition";

    public const LINK_TYPE = self::OPDS_ACQUISITION_FEED;

    /**
     * Summary of __construct
     * @param string $href ?queryString relative to current endpoint
     * @param ?string $rel relation in the OPDS catalog
     * @param ?string $title title in the OPDS catalog and elsewhere
     * @param ?int $database current database in multiple database setup
     */
    public function __construct($href, $rel = null, $title = null, $database = null)
    {
        parent::__construct($href, static::LINK_TYPE, $rel, $title);
        // @todo check if/when we still need to add the database here
        //$this->href = Route::query($this->href, ['db' => $database]);
        if (!is_null($database)) {
            if (str_contains($this->href, '?')) {
                $this->href .= '&db=' . $database;
            } else {
                $this->href .= '?db=' . $database;
            }
        }
    }

    /**
     * Summary of hrefXhtml
     * @return string
     */
    public function hrefXhtml()
    {
        // LinkFeed()->href includes the endpoint here
        return $this->href;
    }
}
