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
        if ($this->request->get('filter')) {
            $this->filterUri = '&r=' . $this->idGet;
            $this->getFilters($rating);
        } else {
            $this->getEntries($rating);
        }
        $this->idPage = $rating->getEntryId();
        $this->title = $rating->getTitle();
        $this->currentUri = $rating->getUri();
        $this->parentTitle = $rating->getParentTitle();
        $this->parentUri = $rating->getParentUri();
    }

    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }
}
