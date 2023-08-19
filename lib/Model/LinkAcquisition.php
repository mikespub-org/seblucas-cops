<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
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
class LinkAcquisition extends LinkFeed
{
    public const LINK_TYPE = parent::OPDS_ACQUISITION_FEED;
}
