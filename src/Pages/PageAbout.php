<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Input\Config;

class PageAbout extends Page
{
    public const PAGE_ID = PageId::ABOUT_ID;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->idPage = static::PAGE_ID;
        $this->title = localize("about.title");
    }

    /**
     * Summary of getContent
     * @return string
     */
    public function getContent()
    {
        return preg_replace("/\<h1\>About COPS\<\/h1\>/", "<h1>About COPS " . Config::VERSION . "</h1>", file_get_contents('templates/about.html'));
    }
}
