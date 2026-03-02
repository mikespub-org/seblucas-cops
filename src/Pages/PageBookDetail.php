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
use InvalidArgumentException;
use SebLucas\Cops\Calibre\Comment;

class PageBookDetail extends Page
{
    protected $className = Book::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        $this->book = Book::getBookById($this->idGet, $this->getDatabaseId());
        if (is_null($this->book)) {
            throw new InvalidArgumentException('Invalid Book');
        }
        if (Comment::hasCalibreLinks($this->book->comment)) {
            $this->book->comment = Comment::fixCalibreLinks($this->book->comment, $this->getDatabaseId());
        }
        $this->book->setHandler($this->handler);
        $this->idPage = $this->book->getEntryId();
        $this->title = $this->book->getTitle();
        $this->currentUri = $this->book->getUri();
    }
}
