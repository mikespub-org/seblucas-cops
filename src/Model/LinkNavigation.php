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
 * From https://specs.opds.io/opds-1.2#22-navigation-feeds
 * A Navigation Feed is an OPDS Catalog Feed Document whose Atom Entries serve to create
 * a suggested hierarchy for presentation and browsing. A Navigation Feed MUST NOT contain
 * OPDS Catalog Entries but instead contains Atom Entries that link to other Navigation or
 * Acquisition Feeds or other Resources.
 *
 * Links to Navigation Feeds MUST use the "type" attribute
 *   "application/atom+xml;profile=opds-catalog;kind=navigation"
 */
class LinkNavigation extends LinkFeed
{
    public const LINK_TYPE = parent::OPDS_NAVIGATION_FEED;
}
