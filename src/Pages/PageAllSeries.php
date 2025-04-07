<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Input\Config;

class PageAllSeries extends Page
{
    protected $className = Serie::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = Serie::PAGE_ID;
        $this->title = localize("series.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $baselist = new BaseList($this->className, $this->request);
        if ($this->request->option("series_split_first_letter") == 1 || $this->request->get('letter')) {
            $this->entryArray = $baselist->getCountByFirstLetter();
            $this->sorted = $baselist->orderBy;
            if (in_array("series", Config::get('show_not_set_filter'))) {
                array_push($this->entryArray, $baselist->getWithoutEntry());
            }
            return;
        }
        if ($baselist->hasChildCategories()) {
            // use tag_browser_series view here, to get the full hierarchy?
            $this->entryArray = $baselist->browseAllEntries($this->n, $this->request->get('tree'));
        } else {
            $this->entryArray = $baselist->getRequestEntries($this->n);
        }
        $this->totalNumber = $baselist->countRequestEntries();
        $this->sorted = $baselist->orderBy;
        if ((!$this->isPaginated() || $this->n == $this->getMaxPage()) && in_array("series", Config::get('show_not_set_filter'))) {
            array_push($this->entryArray, $baselist->getWithoutEntry());
        }
    }
}
