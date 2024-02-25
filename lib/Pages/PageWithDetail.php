<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Base;

class PageWithDetail extends Page
{
    /**
     * Summary of getExtra
     * @param Base $instance
     * @return void
     */
    public function getExtra($instance = null)
    {
        if (!is_null($instance) && !empty($instance->link)) {
            $this->extra = [
                "title" => localize("extra.title"),
                "link" => $instance->link,
                "content" => null,
            ];
        }
    }
}
