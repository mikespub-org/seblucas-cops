<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Tag;

class PageAllTags extends Page
{
    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Tag::PAGE_ID;
        $this->title = localize("tags.title");
    }

    public function getEntries()
    {
        global $config;
        $this->entryArray = Tag::getAllTags($this->n, $this->getDatabaseId());
        $this->totalNumber = Tag::countAllEntries($this->getDatabaseId());
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("tag", $config['cops_show_not_set_filter'])) {
            $this->addNotSetEntry();
        }
    }

    public function addNotSetEntry()
    {
        $instance = new Tag((object)['id' => null, 'name' => localize("tagword.none")], $this->getDatabaseId());
        $booklist = new BookList($this->request);
        [$result,] = $booklist->getBooksWithoutTag(-1);
        array_push($this->entryArray, $instance->getEntry(count($result)));
    }
}
