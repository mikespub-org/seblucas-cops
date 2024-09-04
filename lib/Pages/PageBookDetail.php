<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Book;

class PageBookDetail extends Page
{
    protected string $className = Book::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->book = Book::getBookById($this->idGet, $this->getDatabaseId());
        if (is_null($this->book)) {
            $this->idPage = PageId::ERROR_ID;
            $this->title = 'Not Found';
            return;
        }
        $this->book->setHandler($this->handler);
        $this->idPage = $this->book->getEntryId();
        $this->title = $this->book->getTitle();
        $this->currentUri = $this->book->getUri();
    }
}
