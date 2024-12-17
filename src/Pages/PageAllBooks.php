<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;

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
        $this->title = localize("allbooks.title");
    }

    /**
     * Summary of getEntries
     * @return void
     */
    public function getEntries()
    {
        $booklist = new BookList($this->request);
        $idlist = $this->request->get('idlist');
        if (!empty($idlist)) {
            // [$this->entryArray, $this->totalNumber] = $booklist->getAllBooks($this->n);
            if (!is_array($idlist)) {
                $idlist = explode(',', string: $idlist);
            }
            $idlist = array_map('intval', $idlist);
            // sort entryArray by order in idlist here
            [$this->entryArray, $this->totalNumber] = $booklist->getBooksByIdList($idlist);
            $this->sorted = $booklist->orderBy ?? "id";
        } elseif ($this->request->option("titles_split_first_letter") == 1 || $this->request->get('letter')) {
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
