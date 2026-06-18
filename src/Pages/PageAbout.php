<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Handlers\FeedHandler;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\Format;

class PageAbout extends Page
{
    protected string $template = 'about.html';

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->idPage = PageId::ABOUT_ID;
        $this->title = $this->localize("about.title");
    }

    /**
     * Summary of getContent
     * @return string
     */
    public function getContent()
    {
        $data = [
            'version'    => Config::VERSION,
            'site_url'   => HtmlHandler::index(),
            'opds_url'   => FeedHandler::route(FeedHandler::HANDLER),
        ];
        $template = $this->config('templates_directory') . $this->template;
        return Format::template($data, $template);
    }
}
