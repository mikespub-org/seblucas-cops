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
            $flat = false;
            // flat '17,19' / [17,19]: default database
            if (!is_array($idlist)) {
                $idlist = explode(',', (string) $idlist);
                $flat = true;
            } elseif (array_is_list($idlist)) {
                // distinguish between idlist[]=19&idlist[]=17 (flat) and idlist[0]=19&idlist[1]=17 (grouped)
                $query = $this->request->query() ?? '';
                if (str_contains($query, 'idlist[]=')) {
                    $flat = true;
                }
            }
            if ($flat) {
                $ids = array_map('intval', $idlist);
                [$this->entryArray, $this->totalNumber] = $booklist->getBooksByIdList($ids);
                if ($this->totalNumber < 0) {
                    $this->totalNumber = count($this->entryArray);
                }
                $this->sorted = $booklist->orderBy ?? "id";
                return;
            }
            // nested idlist[<db>]=<csv>: each group handled via per-database BookList instance
            $groups = BookList::parseIdListGroups($idlist);
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
