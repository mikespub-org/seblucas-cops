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

class PageAllRating extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Rating::PAGE_ID;
        $this->title = localize("ratings.title");
    }

    public function getEntries()
    {
        global $config;
        $this->entryArray = Rating::getRequestEntries($this->request, $this->n, $this->getDatabaseId());
        $this->totalNumber = Rating::countRequestEntries($this->request, $this->getDatabaseId());
        $this->sorted = Rating::SQL_SORT;
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("rating", $config['cops_show_not_set_filter'])) {
            $this->addNotSetEntry();
        }
    }

    public function addNotSetEntry()
    {
        $instance = new Rating((object)['id' => 0, 'name' => 0], $this->getDatabaseId());
        $booklist = new BookList($this->request);
        [$result,] = $booklist->getBooksWithoutRating(-1);
        array_push($this->entryArray, $instance->getEntry(count($result)));
    }
}
