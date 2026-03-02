<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

use SebLucas\Cops\Calibre\Comment;
use SebLucas\Cops\Calibre\Folder;
use SebLucas\Cops\Calibre\Metadata;
use SebLucas\Cops\Input\Config;
use InvalidArgumentException;

class PageFolderDetail extends PageWithDetail
{
    protected $className = Folder::class;

    /**
     * Summary of initializeContent
     * @return void
     */
    public function initializeContent()
    {
        // this would be the folder path - override here
        $this->idGet = $this->request->get('path', '');
        if (is_null($this->idGet)) {
            throw new InvalidArgumentException('Invalid Folder Id');
        }
        if (empty($this->idGet)) {
            $this->idGet = '0';
        }
        $root = Config::get('browse_books_directory', '');
        if (empty($root) || !is_dir($root)) {
            throw new InvalidArgumentException('Invalid Root (browse_books_directory)');
        }
        $getFiles = Config::get('browse_books_getfiles', '');
        if (!empty($getFiles) && !is_file($getFiles)) {
            throw new InvalidArgumentException('Invalid Files (browse_books_getfiles)');
        }
        $folder = Folder::getRootFolder($root, $this->getDatabaseId());
        $folder->setHandler($this->handler);
        $bookName = null;
        if (!empty($this->idGet)) {
            $ebook = $this->request->get('ebook');
            if ($ebook) {
                $bookName = basename($this->idGet);
                $this->idGet = dirname($this->idGet);
                if ($this->idGet == '.') {
                    $this->idGet = '0';
                }
            }
            //$folderPath = $folder->getFolderPath($this->idGet);
        }
        if (!empty($getFiles)) {
            $folder->parseGetFiles($getFiles, $this->idGet);
        }
        // force looking for book files here
        $folder->findBookFiles($this->idGet);
        /** @var Folder $instance */
        $instance = $folder->getChildFolderById($this->idGet);
        if (is_null($instance)) {
            throw new InvalidArgumentException('Invalid Folder');
        }
        if (!empty($bookName)) {
            $this->getBookEntry($instance, $bookName);
            return;
        }
        if ($this->request->get('tree')) {
            $this->getEntriesWithChildren($instance);
        } elseif ($this->request->get('extra')) {
            // show extra information without books
            $this->getExtra($instance);
        } else {
            $this->getEntries($instance);
        }
        $this->setInstance($instance);
        if ($instance->hasChildCategories()) {
            $this->hierarchy = $instance->getHierarchy($this->request->get('tree'));
            // disable tree navigation for root
            if (empty($this->idGet)) {
                $this->hierarchy['hastree'] = true;
            }
        }
    }

    /**
     * Summary of getBookEntry
     * @param Folder $instance
     * @param string $bookName
     * @return void
     */
    public function getBookEntry($instance, $bookName)
    {
        $this->book = $instance->getBookByName($bookName);
        if (is_null($this->book)) {
            throw new InvalidArgumentException('Invalid Book');
        }
        if (!$this->book->isExternal()) {
            foreach (Config::get('prefered_format') as $format) {
                if (!in_array($format, ['EPUB', 'CBZ'])) {
                    continue;
                }
                // add metadata from prefered format file if available
                $data = $this->book->getDataFormat($format);
                if (!empty($data) && file_exists($data->getLocalPath())) {
                    $this->book = Metadata::updateBookFromFile($this->book, $data->getLocalPath(), $format);
                    break;
                }
            }
        }
        if (Comment::hasCalibreLinks($this->book->comment)) {
            $this->book->comment = Comment::fixCalibreLinks($this->book->comment, $this->getDatabaseId());
        }
        $this->book->setHandler($this->handler);
        $this->idPage = $this->book->getEntryId();
        $this->title = $this->book->getTitle();
        $this->currentUri = $this->book->getUri();
        // handle folder book as book page
        $this->request->set('page', PageId::BOOK_DETAIL);
    }

    /**
     * Summary of getEntriesWithChildren
     * @param Folder $instance
     * @return void
     */
    public function getEntriesWithChildren($instance)
    {
        [$this->entryArray, $this->totalNumber] = Folder::getBooksByFolderOrChildren($instance, $this->n);
        $this->sorted = $instance->orderBy ?? "title";
        $this->getExtra($instance);
    }

    /**
     * Summary of getEntries
     * @param ?Folder $instance
     * @return void
     */
    public function getEntries($instance = null)
    {
        [$this->entryArray, $this->totalNumber] = Folder::getBooksByFolder($instance, $this->n);
        $this->sorted = $instance->orderBy ?? "title";
        $this->getExtra($instance);
    }

    /**
     * Summary of canFilter
     * @return bool
     */
    public function canFilter()
    {
        return false;
    }

    /**
     * Summary of getExtra
     * @param Folder $instance
     * @return void
     */
    public function getExtra($instance = null)
    {
        return;
    }

    /**
     * Summary of getSortOptions
     * @return array<string, string>
     */
    public function getSortOptions()
    {
        return [];
    }
}
