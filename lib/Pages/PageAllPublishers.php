<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Publisher;

class PageAllPublishers extends Page
{
    public function InitializeContent()
    {
        $this->title = localize("publishers.title");
        $this->entryArray = Publisher::getAllPublishers($this->getDatabaseId());
        $this->idPage = Publisher::PAGE_ID;
    }
}
