<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Language;

class PageLanguageDetail extends Page
{
    public function InitializeContent()
    {
        $language = Language::getLanguageById($this->idGet, $this->getDatabaseId());
        $this->idPage = $language->getEntryId();
        $this->title = $language->getTitle();
        [$this->entryArray, $this->totalNumber] = Book::getBooksByLanguage($this->idGet, $this->n, $this->getDatabaseId());
    }
}
