<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

class PageAbout extends Page
{
    public const PAGE_ID = PageId::ABOUT_ID;

    /**
     * Summary of InitializeContent
     * @return void
     */
    public function InitializeContent()
    {
        $this->idPage = static::PAGE_ID;
        $this->title = localize("about.title");
    }
}
