<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

use function SebLucas\Cops\Request\addURLParameter;
use function SebLucas\Cops\Request\getURLParam;

class LinkNavigation extends Link
{
    public function __construct($phref, $prel = null, $ptitle = null)
    {
        parent::__construct($phref, Link::OPDS_NAVIGATION_TYPE, $prel, $ptitle);
        if (!is_null(getURLParam('db'))) {
            $this->href = addURLParameter($this->href, 'db', getURLParam('db'));
        }
        if (!preg_match("#^\?(.*)#", $this->href) && !empty($this->href)) {
            $this->href = "?" . $this->href;
        }
        if (preg_match("/(bookdetail|getJSON).php/", parent::getScriptName())) {
            $this->href = parent::$endpoint . $this->href;
        } else {
            $this->href = parent::getScriptName() . $this->href;
        }
    }
}
