<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Base;
use SebLucas\Cops\Calibre\Resource;

class PageWithDetail extends Page
{
    /**
     * Summary of getExtra
     * @param Base $instance
     * @return void
     */
    public function getExtra($instance = null)
    {
        if (!is_null($instance) && !empty($instance->id)) {
            $content = null;
            $note = $instance->getNote();
            if (!empty($note) && !empty($note->doc)) {
                $content = Resource::fixResourceLinks($note->doc, $instance->getDatabaseId());
            }
            if (!empty($instance->link) || !empty($content)) {
                $this->extra = [
                    "title" => localize("extra.title"),
                    "link" => $instance->link,
                    "content" => $content,
                ];
            }
        }
    }
}
