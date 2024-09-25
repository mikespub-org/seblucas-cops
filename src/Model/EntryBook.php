<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Calibre\Book;

class EntryBook extends Entry
{
    public Book $book;

    /**
     * EntryBook constructor.
     * @param string $title
     * @param string $id
     * @param string $content
     * @param string $contentType
     * @param array<LinkEntry|LinkFeed> $linkArray
     * @param Book $book
     */
    public function __construct($title, $id, $content, $contentType, $linkArray, $book)
    {
        parent::__construct($title, $id, $content, $contentType, $linkArray, $book->getDatabaseId());
        $this->book = $book;
        $this->localUpdated = $book->timestamp;
    }

    /**
     * Summary of hasAcquisitionLink
     * @return bool
     */
    public function hasAcquisitionLink()
    {
        foreach ($this->linkArray as $link) {
            if ($link->rel == LinkEntry::OPDS_ACQUISITION_TYPE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Summary of isValidForOPDS
     * @return bool
     */
    public function isValidForOPDS()
    {
        // check that we have at least 1 valid acquisition link for this book - see #28
        return $this->hasAcquisitionLink();
    }
}
