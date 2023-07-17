<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Model;

use SebLucas\Cops\Calibre\Book;

class EntryBook extends Entry
{
    public $book;

    /**
     * EntryBook constructor.
     * @param string $ptitle
     * @param integer $pid
     * @param string $pcontent
     * @param string $pcontentType
     * @param array $plinkArray
     * @param Book $pbook
     */
    public function __construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray, $pbook)
    {
        parent::__construct($ptitle, $pid, $pcontent, $pcontentType, $plinkArray);
        $this->book = $pbook;
        $this->localUpdated = $pbook->timestamp;
    }

    /**
     * @deprecated 1.4.0 use getThumbnail() instead
     */
    public function getCoverThumbnail()
    {
        return $this->getThumbnail();
    }

    /**
     * @deprecated 1.4.0 use getImage() instead
     */
    public function getCover()
    {
        return $this->getImage();
    }
}
