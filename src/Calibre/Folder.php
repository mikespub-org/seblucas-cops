<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     SenorSmartyPants <senorsmartypants@gmail.com>
 * @author     mikespub
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Model\Entry;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Pages\PageId;
use Exception;

/**
 * Browse book files in other folders besides Calibre (WIP)
 */
class Folder extends Category
{
    public const PAGE_ID = PageId::FOLDER_ID;
    public const PAGE_DETAIL = PageId::FOLDER;
    public const ROUTE_DETAIL = "page-folder";  // "folder" or "restapi-folders"
    public const SQL_TABLE = "folders";
    public const URL_PARAM = "folder";
    // when using PageFolderDetail
    public const CATEGORY = "folders";
    public const SEPARATOR = "/";

    /** @var ?string */
    public $id;
    /** @var string */
    public $root = '';
    /** @var Book[] */
    public $bookList = [];
    /** @var Folder[] */
    protected $children = [];
    /** @var Folder|false */
    protected $parent = false;
    /** @var ?int */
    protected $numberPerPage = null;
    /** @var ?string */
    public $orderBy = null;

    public function __construct($post, $database = null)
    {
        parent::__construct($post, $database);
        $this->root = $post->root ?? Config::get('browse_books_directory', '');
    }

    /**
     * Summary of getUri
     * @param array<mixed> $params
     * @return string
     */
    public function getUri($params = [])
    {
        // path cannot be empty string here
        $params['path'] = $this->id;
        return $this->getRoute(static::ROUTE_DETAIL, $params);
    }

    /**
     * Summary of getParentTitle
     * @return string
     */
    public function getParentTitle()
    {
        return localize("folders.title");
    }

    /**
     * Summary of getFolderPath
     * @param ?string $folderName
     * @return string
     */
    public function getFolderPath($folderName = null)
    {
        $folderName ??= $this->id;
        $folderPath = $this->root;
        if (!empty($folderName)) {
            $folderPath .= '/' . $folderName;
        }
        if (is_dir($folderPath) && !str_ends_with($folderPath, '/')) {
            $folderPath .= '/';
        }
        return $folderPath;
    }

    /**
     * Summary of findBookFiles
     * @param ?string $folderName
     * @param bool $recursive
     * @throws \Exception
     * @return Book[]
     */
    public function findBookFiles($folderName = null, $recursive = true)
    {
        if (empty($folderName) && (!empty($this->bookList) || !empty($this->children))) {
            $bookList = $this->bookList;
            if ($recursive) {
                foreach ($this->children as $child) {
                    $bookList = array_merge($bookList, $child->findBookFiles());
                }
            }
            return $bookList;
        }
        $this->bookList = [];
        $this->children = [];
        $this->parent = false;
        $folderPath = $this->getFolderPath($folderName);
        if (!is_dir($folderPath)) {
            return $this->bookList;
        }
        $allowed = array_map('strtolower', Config::get('prefered_format'));
        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath));
        } else {
            $iterator = new \FilesystemIterator($folderPath);
        }
        $fileList = [];
        $metaList = [];
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (!str_starts_with((string) $file->getPathname(), $folderPath)) {
                continue;
            }
            $filePath = substr((string) $file->getPathname(), strlen($folderPath));
            // this returns '.' for current directory
            $dirPath = pathinfo($filePath, PATHINFO_DIRNAME);
            $format = $file->getExtension();
            if (!in_array($format, $allowed)) {
                if ($format == 'opf') {
                    // only one .opf file per directory supported - assume one book per directory here
                    $metaList[$dirPath] = $file->getBasename();
                }
                continue;
            }
            // several books per directory allowed (but not required)
            $fileList[$dirPath] ??= [];
            // several formats per book allowed - assume same bookName with different formats here
            $bookName = $file->getBasename('.' . $format);
            $fileList[$dirPath][$bookName] ??= [];
            array_push($fileList[$dirPath][$bookName], $format);
        }
        return $this->makeBookList($folderPath, $fileList, $metaList);
    }

    /**
     * Summary of makeBookList
     * @param string $folderPath
     * @param array<string, mixed> $fileList
     * @param array<string, mixed> $metaList
     * @throws \Exception
     * @return Book[]
     */
    public function makeBookList($folderPath, $fileList, $metaList = [])
    {
        $bookList = [];
        $bookId = 0;
        $dataId = 0;
        $folders = [];
        $folders['.'] = $this;
        foreach ($fileList as $dirPath => $books) {
            $metadata = null;
            $hasCover = false;
            if (count($books) == 1) {
                if (file_exists($folderPath . $dirPath . '/cover.jpg')) {
                    $hasCover = true;
                }
                if (!empty($metaList[$dirPath])) {
                    $filePath = $folderPath . $dirPath . '/' . $metaList[$dirPath];
                    if (!file_exists($filePath)) {
                        throw new Exception('Invalid metadata path ' . $filePath);
                    }
                    $metadata = Metadata::fromFile($filePath);
                }
            }
            if (empty($folders[$dirPath])) {
                $parts = explode('/', str_replace('\\', '/', $dirPath));
                $currentPath = '';
                $parentFolder = $this;
                foreach ($parts as $part) {
                    if ($part === '.' || $part === '') {
                        continue;
                    }
                    $currentPath = $currentPath ? $currentPath . '/' . $part : $part;
                    if (!isset($folders[$currentPath])) {
                        $childId = $this->id ? $this->id . '/' . $currentPath : $currentPath;
                        $post = (object) ['id' => $childId, 'name' => $part, 'root' => $this->root];
                        $folder = new Folder($post, $this->getDatabaseId());
                        $folder->setHandler($this->handler);
                        $folder->children = [];
                        $folder->parent = $parentFolder;
                        $parentFolder->children[] = $folder;
                        $folders[$currentPath] = $folder;
                    }
                    $parentFolder = $folders[$currentPath];
                }
            }
            $folderUri = $folders[$dirPath]->getUri();
            if (str_ends_with($folderUri, '/0')) {
                $folderUri = substr($folderUri, 0, -2);
            }
            foreach ($books as $bookName => $formats) {
                $bookId++;
                $line = (object) ['id' => $bookId, 'title' => $bookName, 'path' => $folderPath . $dirPath, 'timestamp' => '', 'has_cover' => $hasCover];
                $book = new Book($line);
                $book->setHandler($this->handler);
                if (!empty($metadata)) {
                    $metadata->updateBook($book);
                }
                $book->folderId = $folders[$dirPath]->id;
                $book->datas = [];
                $book->formats = [];
                foreach ($formats as $format) {
                    $filePath = $folderPath . $dirPath . '/' . $bookName . '.' . $format;
                    if (!file_exists($filePath)) {
                        throw new Exception('Invalid file path ' . $filePath);
                    }
                    if (empty($book->timestamp)) {
                        $book->timestamp = filemtime($filePath);
                    }
                    $dataId++;
                    $post = (object) ['id' => $dataId, 'name' => $bookName, 'format' => strtoupper($format)];
                    $data = new Data($post, $book);
                    $book->datas[] = $data;
                    // $book->formats[] = ...;
                }
                if (isset($folders[$dirPath])) {
                    $folders[$dirPath]->bookList[] = $book;
                }
                array_push($bookList, $book);
            }
        }
        $this->getBookCount();
        //foreach ($bookList as $bookId => $book) {
        //    array_push($entryArray, $book->getEntry());
        //}
        //return [$entryArray, $totalNumber];
        return $bookList;
    }

    /**
     * Summary of getBookCount
     * @return int
     */
    public function getBookCount()
    {
        $this->count = count($this->bookList);
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $this->count += $child->getBookCount();
            }
        }
        return $this->count;
    }

    /**
     * Summary of getBookByName
     * @param string $bookName
     * @return Book|null
     */
    public function getBookByName($bookName)
    {
        foreach ($this->bookList as $book) {
            if ($book->title == $bookName) {
                return $book;
            }
        }
        return null;
    }

    /**
     * Summary of hasChildCategories
     * @return bool
     */
    public function hasChildCategories()
    {
        return true;
    }

    /**
     * Summary of getChildFolders
     * @param bool $recursive
     * @return Folder[]
     */
    public function getChildFolders($recursive = false)
    {
        if (!$recursive) {
            return $this->children;
        }
        $children = [];
        foreach ($this->children as $child) {
            $children[] = $child;
            $children = array_merge($children, $child->getChildFolders($recursive));
        }
        return $children;
    }

    /**
     * Get child entries for hierarchical tags or custom columns
     * @param int|bool|null $expand include all child categories at all levels or only direct children
     * @return Entry[]
     */
    public function getChildEntries($expand = false)
    {
        $entryArray = [];
        foreach ($this->getChildFolders($expand) as $child) {
            array_push($entryArray, $child->getEntry($child->count));
        }
        return $entryArray;
    }

    /**
     * Summary of getChildFolderById
     * @param string $id
     * @param bool $recursive (default true)
     * @return Folder|null
     */
    public function getChildFolderById($id, $recursive = true)
    {
        if ($this->id == $id) {
            return $this;
        }
        foreach ($this->getChildFolders($recursive) as $child) {
            if ($child->id == $id) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Summary of getChildFolderByName
     * @param string $name
     * @param bool $recursive (default false)
     * @return Folder|null
     */
    public function getChildFolderByName($name, $recursive = false)
    {
        if ($this->name == $name) {
            return $this;
        }
        foreach ($this->getChildFolders($recursive) as $child) {
            if ($child->name == $name) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Summary of getParentTrail
     * @return Entry[]
     */
    public function getParentTrail()
    {
        $trail = [];
        $folder = $this;
        while ($folder->parent) {
            $folder = $folder->parent;
            $entry = $folder->getEntry($folder->count);
            $entry->title = static::findCurrentName($entry->title);
            $trail[] = $entry;
        }
        return array_reverse($trail);
    }

    /**
     * Summary of getCount
     * @param ?int $database
     * @param class-string<BaseHandler> $handler
     * @return ?Entry
     */
    public static function getCount($database, $handler)
    {
        $count = 1;
        return static::getCountEntry($count, $database, "folders", $handler);
    }

    /**
     * Summary of getBooksByFolder
     * @param Folder $folder
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public static function getBooksByFolder($folder, $n = 1)
    {
        $bookList = $folder->bookList;
        return self::getEntryArray($folder, $bookList, $n);
    }

    /**
     * Summary of getBooksByFolderOrChildren
     * @param Folder $folder
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public static function getBooksByFolderOrChildren($folder, $n = 1)
    {
        $bookList = $folder->bookList;
        foreach ($folder->children as $child) {
            $bookList = array_merge($bookList, $child->findBookFiles());
        }
        return self::getEntryArray($folder, $bookList, $n);
    }

    /**
     * Summary of getEntryArray
     * @param Folder $folder
     * @param array<int, mixed> $bookList
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public static function getEntryArray($folder, $bookList, $n)
    {
        $sorted = $folder->orderBy ?? 'title';
        usort($bookList, function($a, $b) use ($sorted) {
            return strcmp($a->{$sorted}, $b->{$sorted});
        });
        $totalNumber = count($bookList);
        $numberPerPage = Config::get('max_item_per_page');
        if ($numberPerPage != -1 && $n != -1) {
            $bookList = array_slice($bookList, ($n - 1) * $numberPerPage, $numberPerPage);
        }
        $entryArray = [];
        foreach ($bookList as $book) {
            array_push($entryArray, $book->getEntry());
        }
        return [$entryArray, $totalNumber];
    }

    /**
     * Summary of getInstanceById
     * @param string|int|null $id used for the folder here
     * @param ?int $database
     * @param ?string $root
     * @return self
     */
    public static function getInstanceById($id, $database = null, $root = null)
    {
        if (!empty($id)) {
            return new Folder((object) ['id' => $id, 'name' => $id, 'root' => $root], $database);
        }
        return self::getRootFolder($root, $database);
    }

    /**
     * Summary of getInstanceByPath
     * @param string|int|null $path used for the folder here
     * @param ?int $database
     * @return self
     */
    public static function getInstanceByPath($path, $database = null)
    {
        return self::getInstanceById($path, $database);
    }

    /**
     * Summary of getDefaultName
     * @return string
     */
    public static function getDefaultName()
    {
        return localize("folders.root");
    }

    /**
     * Summary of getRootFolder
     * @param ?string $root
     * @param ?int $database
     * @return Folder
     */
    public static function getRootFolder($root = null, $database = null)
    {
        $default = self::getDefaultName();
        // use id = 0 to support route urls
        $post = (object) ['id' => 0, 'name' => $default, 'root' => $root];
        return new Folder($post, $database);
    }
}
