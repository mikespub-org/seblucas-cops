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
    public function __construct($phref, $ptitle = null, $pfacetGroup = null, $pactiveFacet = false)
    {
        parent::__construct($phref, Link::OPDS_PAGING_TYPE, "http://opds-spec.org/facet", $ptitle, $pfacetGroup, $pactiveFacet);
        $this->href = Format::addDatabaseParam($this->href);
        $this->href = parent::getScriptName() . $this->href;
    }
}
