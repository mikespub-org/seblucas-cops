<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Database\DatabaseException;

class PageAllBooks extends Page
{
    protected $className = Book::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->getEntries();
        $this->idPage = Book::PAGE_ID;
        $this->title = $this->localize("allbooks.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $booklist = new BookList($this->request, $this->getDbContext());
        $idlist = $this->request->get('idlist');
        if (!empty($idlist)) {
            $this->parentTitle = $this->request->get('source', '', '/^[\w\s-]+$/');
            // [$this->entryArray, $this->totalNumber] = $booklist->getAllBooks($this->n);
            // flat '17,19' / [17,19]: default database
            // nested idlist[<db>]=<csv>: single-db group handled by Filter; other
            // groups handled here via per-database BookList instances
            $groups = BookList::parseIdListGroups($idlist);
            if (count($groups) < 2) {
                $ids = reset($groups);
                $ids = array_map('intval', $ids);
                [$this->entryArray, $this->totalNumber] = $booklist->getBooksByIdList($ids);
                if ($this->totalNumber < 0) {
                    $this->totalNumber = count($this->entryArray);
                }
                $this->sorted = $booklist->orderBy ?? "id";
                return;
            }
            $entryArray = [];
            $totalNumber = 0;
            foreach ($groups as $dbId => $ids) {
                $list = new BookList($this->request, new DatabaseContext((int) $dbId, $this->getConfig()));
                try {
                    [$entries, $total] = $list->getBooksByIdList(array_map('intval', $ids));
                    $entryArray = array_merge($entryArray, $entries);
                    $totalNumber += ($total > -1) ? $total : count($entries);
                } catch (DatabaseException) {
                    continue;
                }
            }
            [$this->entryArray, $this->totalNumber] = [$entryArray, $totalNumber];
            $this->sorted = $booklist->orderBy ?? "id";
            return;
        }
        if ($this->request->option("titles_split_first_letter") == 1 || $this->request->get('letter')) {
            $this->entryArray = $booklist->getCountByFirstLetter();
            $this->sorted = $booklist->orderBy ?? "letter";
        } elseif (!empty($this->request->option("titles_split_publication_year")) || $this->request->get('year')) {
            $this->entryArray = $booklist->getCountByPubYear();
            $this->sorted = $booklist->orderBy ?? "year";
        } else {
            [$this->entryArray, $this->totalNumber] = $booklist->getAllBooks($this->n);
            $this->sorted = $booklist->orderBy ?? Book::SQL_SORT;
        }
    }
}
