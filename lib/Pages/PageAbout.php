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
    public function InitializeContent()
    {
        $this->idPage = self::ABOUT_ID;
        $this->title = localize("about.title");
    }
}
