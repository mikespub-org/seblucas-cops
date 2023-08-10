<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Language;

class PageAllLanguages extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Language::PAGE_ID;
        $this->title = localize("languages.title");
    }

    public function getEntries()
    {
        $this->entryArray = Language::getRequestEntries($this->request, $this->n, $this->getDatabaseId());
        $this->totalNumber = Language::countRequestEntries($this->request, $this->getDatabaseId());
        $this->sorted = $this->request->getSorted(Language::SQL_SORT);
    }
}
