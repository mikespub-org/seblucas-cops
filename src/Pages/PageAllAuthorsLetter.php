<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Input\Route;

class PageAllAuthorsLetter extends Page
{
    protected $className = Author::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        // this would be the first letter - override here
        $this->idGet = $this->request->get('id', null, '/^[\p{L}\p{N}]$/u');
        $this->getEntries();
        $this->idPage = Author::getEntryIdByLetter($this->idGet);
        $count = $this->totalNumber;
        if ($count == -1) {
            $count = count($this->entryArray);
        }
        $this->title = str_format(localize("splitByLetter.letter"), str_format(localize("authorword", $count), $count), $this->idGet);
        $this->parentTitle = "";  // localize("authors.title");
        $filterParams = $this->request->getFilterParams();
        $this->parentUri = $this->handler::route(Author::ROUTE_ALL, $filterParams);
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $baselist = new BaseList($this->className, $this->request);
        $this->entryArray = $baselist->getEntriesByFirstLetter($this->idGet, $this->n);
        $this->totalNumber = $baselist->countEntriesByFirstLetter($this->idGet);
        $this->sorted = $baselist->orderBy;
    }
}
