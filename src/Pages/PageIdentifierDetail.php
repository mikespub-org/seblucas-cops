<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Identifier;

/**
 * This shows the books with a particular identifier type, e.g. amazon, isbn, url, ...
 */
class PageIdentifierDetail extends PageWithDetail
{
    /**
     * Summary of className
     * @var string
     */
    protected string $className = Identifier::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        // this would be the identifier - override here
        $this->idGet = $this->request->get('id', null, '/^\w+$/');
        /** @var Identifier $instance */
        $instance = Identifier::getInstanceById($this->idGet, $this->getDatabaseId());
        $instance->setHandler($this->handler);
        if ($this->request->get('filter')) {
            $this->filterParams = [Identifier::URL_PARAM => $this->idGet];
            $this->getFilters($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->setInstance($instance);
    }

    /**
     * Summary of getEntries
     * @param Identifier $instance
     * @return void
     */
    public function getEntries($instance = null)
    {
        $booklist = new BookList($this->request);
        [$this->entryArray, $this->totalNumber] = $booklist->getBooksByInstance($instance, $this->n);
        $this->sorted = $booklist->orderBy ?? "sort";
    }
}
