<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Tag;

class PageTagDetail extends Page
{
    public function InitializeContent()
    {
        $tag = Tag::getTagById($this->idGet, $this->getDatabaseId());
        $this->idPage = $tag->getEntryId();
        $this->title = $tag->getTitle();
        [$this->entryArray, $this->totalNumber] = Book::getBooksByTag($this->idGet, $this->n, $this->getDatabaseId());
    }
}
