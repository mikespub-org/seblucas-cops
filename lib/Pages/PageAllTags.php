<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\Tag;

class PageAllTags extends Page
{
    public function InitializeContent()
    {
        global $config;
        $this->idPage = Tag::PAGE_ID;
        $this->title = localize("tags.title");
        $this->entryArray = Tag::getAllTags($this->n, $this->getDatabaseId());
        if (in_array("tag", $config['cops_show_not_set_filter'])) {
            $instance = new Tag((object)['id' => null, 'name' => localize("tagword.none")], $this->getDatabaseId());
            $booklist = new BookList($this->request);
            [$result,] = $booklist->getBooksWithoutTag(-1);
            array_push($this->entryArray, $instance->getEntry(count($result)));
        }
    }
}
