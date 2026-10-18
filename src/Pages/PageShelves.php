<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

class PageShelves extends Page
{
    protected string $template = 'shelves.html';

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = PageId::ALL_SHELVES_ID;
        $this->title = $this->localize("shelves.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        // Client-side shelves are stored in localStorage; server returns an empty list
        $this->entryArray = [];
    }
}
