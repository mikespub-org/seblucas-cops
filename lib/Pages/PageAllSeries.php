<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Serie;

class PageAllSeries extends Page
{
    public function InitializeContent()
    {
        global $config;
        $this->idPage = Serie::PAGE_ID;
        $this->title = localize("series.title");
        $this->entryArray = Serie::getAllSeries($this->getDatabaseId());
        if (in_array("series", $config['cops_show_not_set_filter'])) {
            $instance = new Serie((object)['id' => null, 'name' => localize("seriesword.none")], $this->getDatabaseId());
            $booklist = new BookList($this->request);
            [$result,] = $booklist->getBooksWithoutSeries(-1);
            array_push($this->entryArray, $instance->getEntry(count($result)));
        }
    }
}
