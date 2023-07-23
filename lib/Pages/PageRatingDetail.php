<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Rating;

class PageRatingDetail extends Page
{
    public function InitializeContent()
    {
        $rating = Rating::getRatingById($this->idGet, $this->getDatabaseId());
        $this->idPage = $rating->getEntryId();
        $this->title = $rating->getTitle();
        $this->parentTitle = localize("ratings.title");
        $this->parentUri = $rating->getParentUri();
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByRating($this->idGet, $this->n);
    }
}
