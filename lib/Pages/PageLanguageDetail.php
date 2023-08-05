<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Language;

class PageLanguageDetail extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $language = Language::getLanguageById($this->idGet, $this->getDatabaseId());
        $this->idPage = $language->getEntryId();
        $this->title = $language->getTitle();
        $this->parentTitle = localize("languages.title");
        $this->parentUri = $language->getParentUri();
    }

    public function getEntries()
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByLanguage($this->idGet, $this->n);
    }
}
