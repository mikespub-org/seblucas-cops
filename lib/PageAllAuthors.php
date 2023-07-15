<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;

class PageAllAuthors extends Page
{
    public function InitializeContent()
    {
        $this->title = localize("authors.title");
        if (getCurrentOption("author_split_first_letter") == 1) {
            $this->entryArray = Author::getAllAuthorsByFirstLetter();
        } else {
            $this->entryArray = Author::getAllAuthors();
        }
        $this->idPage = Author::PAGE_ID;
    }
}
