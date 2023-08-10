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
        $this->getEntries();
        $this->idPage = Publisher::PAGE_ID;
        $this->title = localize("publishers.title");
    }

    public function getEntries()
    {
        $this->entryArray = Publisher::getRequestEntries($this->request, $this->n, $this->getDatabaseId());
        $this->totalNumber = Publisher::countRequestEntries($this->request, $this->getDatabaseId());
        $this->sorted = $this->request->getSorted(Publisher::SQL_SORT);
    }
}
