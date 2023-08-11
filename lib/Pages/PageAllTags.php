<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Tag;

class PageAllTags extends Page
{
    protected $className = Tag::class;

    public function InitializeContent()
    {
        $this->getEntries();
        $this->idPage = Tag::PAGE_ID;
        $this->title = localize("tags.title");
    }

    public function getEntries()
    {
        global $config;
        $baselist = new BaseList($this->request, $this->className);
        $this->entryArray = $baselist->getRequestEntries($this->n);
        $this->totalNumber = $baselist->countRequestEntries();
        $this->sorted = $baselist->orderBy;
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("tag", $config['cops_show_not_set_filter'])) {
            $this->addNotSetEntry($baselist);
        }
    }

    /**
     * Summary of addNotSetEntry
     * @param BaseList $baselist
     * @return void
     */
    public function addNotSetEntry($baselist)
    {
        $count = $baselist->countWithoutEntries();
        $instance = new $this->className((object)['id' => null, 'name' => localize("tagword.none")], $this->getDatabaseId());
        array_push($this->entryArray, $instance->getEntry($count));
    }
}
