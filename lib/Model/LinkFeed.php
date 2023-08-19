<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Output\Format;

class LinkFeed extends Link
{
    public const OPDS_NAVIGATION_FEED = "application/atom+xml;profile=opds-catalog;kind=navigation";
    public const OPDS_ACQUISITION_FEED = "application/atom+xml;profile=opds-catalog;kind=acquisition";

    public const LINK_TYPE = '';

    /**
     * Summary of __construct
     * @param string $phref ?queryString relative to current endpoint
     * @param ?string $prel relation in the OPDS catalog
     * @param ?string $ptitle title in the OPDS catalog and elsewhere
     * @param mixed $database current database in multiple database setup
     */
    public function __construct($phref, $prel = null, $ptitle = null, $database = null)
    {
        parent::__construct($phref, static::LINK_TYPE, $prel, $ptitle);
        $this->href = Format::addDatabaseParam($this->href, $database);
        //if (!preg_match("#^\?(.*)#", $this->href) && !empty($this->href)) {
        //    $this->href = "?" . $this->href;
        //}
    }

    /**
     * Summary of hrefXhtml
     * @param mixed $endpoint
     * @return string
     */
    public function hrefXhtml($endpoint = '')
    {
        // LinkFeed()->href is relative to endpoint
        return $endpoint . $this->href;
    }
}
