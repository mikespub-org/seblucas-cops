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
use SebLucas\Cops\Input\Config;

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

    /**
     * Summary of canFilter
     * @return bool
     */
    public function canFilter()
    {
        if ($this->request->isFeed()) {
            $filterLinks = Config::get('opds_filter_links');
        } else {
            $filterLinks = Config::get('html_filter_links');
        }
        if (!empty($filterLinks)) {
            return true;
        }
        return false;
    }
}
