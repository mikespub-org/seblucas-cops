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
        $this->entryArray = Language::getAllLanguages($this->n, $this->getDatabaseId());
        $this->totalNumber = Language::countAllEntries($this->getDatabaseId());
        $this->sorted = Language::SQL_SORT;
    }
}
