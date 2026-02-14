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
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Pages\PageId;
use Exception;

/**
 * Browse book files in other folders besides Calibre (WIP)
 * ```
 * - Folder:
 *   id = folder path relative to root
 *   name = folder basename
 *   root = root
 *   link = /folder/folder id (url-encoded path)
 * - Book:
 *   id = dummy
 *   title = file basename without extension
 *   folderId = see folder id
 *   path = full folder path incl. root
 *   link = /ebook/folder id/book title (url-encoded path)
 * - Data:
 *   id = dummy
 *   name = see book title
 *   format = extension
 *   link = /format/folder id/book title.fomat (url-encoded path)
 * - Cover:
 *   coverFileName = full path to cover file incl. root
 *   link = /images/size/folder id/book title.jpg (url-encoded path) with size = full, html or html2
 * ```
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
    /** @var array<string, Book> */
    public $bookList = [];
    /** @var array<string, Folder> */
    protected $children = [];
    /** @var Folder|false */
    protected $parent = false;
    /** @var ?int */
    protected $numberPerPage = null;
    /** @var ?string */
    public $orderBy = null;
    /** @var bool */
    public $scanned = false;

    public function __construct($post, $database = null)
    {
        if (str_contains($post->id, '..') || str_contains($post->id, './')) {
            throw new Exception('Invalid folder id ' . $post->id);
        }
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
        if ($this->scanned) {
            $bookList = array_values($this->bookList);
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
        $folderPath = $this->getFolderPath($folderName) . '/';
        if (!is_dir($folderPath)) {
            $this->scanned = true;
            return $this->bookList;
        }
        $parent = $this;
        /**
        if (empty($this->id) && empty($folderName) && $recursive) {
            [$fileList, $metaList, $coverList] = self::loadFileList($this->root);
            if (!empty($fileList)) {
                return $this->makeBookList($folderPath, $fileList, $metaList, $coverList, $parent);
            }
        }
         */
        if ($recursive) {
            // for PageFolderDetail
            $flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folderPath, $flags));
            if (!empty($folderName)) {
                $parent = $this->buildHierarchy($folderName, $this);
            }
        } else {
            // for FetchHandler
            $iterator = new \FilesystemIterator($folderPath);
        }
        $allowed = array_map('strtolower', Config::get('prefered_format'));
        $fileList = [];
        $metaList = [];
        $coverList = [];
        $bookCount = 0;
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
                } elseif ($file->getBasename() == 'cover.jpg') {
                    $coverList[$dirPath] = $file->getBasename();
                }
                continue;
            }
            $bookCount++;
            // several books per directory allowed (but not required)
            $fileList[$dirPath] ??= [];
            // several formats per book allowed - assume same bookName with different formats here
            $bookName = $file->getBasename('.' . $format);
            $fileList[$dirPath][$bookName] ??= [];
            array_push($fileList[$dirPath][$bookName], $format);
        }
        ksort($fileList);
        /**
        if (empty($this->id) && empty($folderName) && $recursive && $bookCount > 100) {
            self::saveFileList($this->root, $fileList, $metaList, $coverList);
        }
         */
        return $this->makeBookList($folderPath, $fileList, $metaList, $coverList, $parent);
    }

    /**
     * Summary of buildHierarchy
     * @param string $folderName relative to parent id
     * @param ?Folder $parent
     * @return Folder
     */
    public function buildHierarchy($folderName, $parent = null)
    {
        $parent ??= $this;
        $parent->scanned = true;
        $parentFolder = $parent;
        $currentPath = $parent->id;
        $parts = explode('/', str_replace('\\', '/', $folderName));
        foreach ($parts as $part) {
            if ($part === '.' || $part === '') {
                continue;
            }
            $currentPath = $currentPath ? $currentPath . '/' . $part : $part;
            $childFolder = $parentFolder->addChildFolder($part);
            $parentFolder = $childFolder;
        }
        return $parentFolder;
    }

    /**
     * Summary of makeBookList
     * @param string $folderPath
     * @param array<string, mixed> $fileList
     * @param array<string, mixed> $metaList
     * @param array<string, mixed> $coverList
     * @param ?Folder $parent
     * @throws \Exception
     * @return Book[]
     */
    public function makeBookList($folderPath, $fileList, $metaList = [], $coverList = [], $parent = null)
    {
        $parent ??= $this;
        $bookList = [];
        foreach ($fileList as $dirPath => $books) {
            if ($dirPath == '.') {
                $bookPath = rtrim($folderPath, '/');
                $bookFolder = $parent;
            } elseif (empty($parent->id)) {
                // don't add books in lower levels for Root
                $childName = explode('/', $dirPath)[0];
                $bookFolder = $parent->addChildFolder($childName);
                $count = count($books);
                $bookFolder->count += $count;
                while ($bookFolder->parent) {
                    $bookFolder = $bookFolder->parent;
                    $bookFolder->count += $count;
                }
                continue;
            } else {
                $bookPath = $folderPath . $dirPath;
                $folderId = $parent->id ? $parent->id . '/' . $dirPath : $dirPath;
                $bookFolder = $parent->getChildFolderById($folderId);
                if (empty($bookFolder)) {
                    $bookFolder = $parent->buildHierarchy($dirPath);
                }
            }
            $metadata = null;
            $hasCover = false;
            if (count($books) == 1) {
                if (!empty($coverList[$dirPath])) {
                    $hasCover = true;
                }
                if (!empty($metaList[$dirPath])) {
                    $filePath = $bookPath . '/' . $metaList[$dirPath];
                    if (file_exists($filePath)) {
                        $metadata = Metadata::fromFile($filePath);
                    }
                }
            }
            foreach ($books as $bookName => $formats) {
                $book = $bookFolder->addBookName($bookName, $bookPath, '', $metadata, $hasCover);
                foreach ($formats as $format) {
                    $bookFolder->addBookFormat($bookName, $format);
                }
                array_push($bookList, $book);
            }
        }
        $this->getBookCount();
        return $bookList;
    }

    /**
     * Summary of getBookCount
     * @param bool $refresh
     * @return int
     */
    public function getBookCount($refresh = false)
    {
        if (isset($this->count) && !$refresh) {
            return $this->count;
        }
        $this->count = count($this->bookList);
        if (!empty($this->children)) {
            foreach ($this->children as $child) {
                $this->count += $child->getBookCount($refresh);
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
            return array_values($this->children);
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
     * Summary of addBookName
     * @param string $bookName
     * @param string $bookPath
     * @param string $timestamp
     * @param ?Metadata $metadata
     * @param bool $hasCover
     * @return Book
     */
    public function addBookName($bookName, $bookPath, $timestamp = '', $metadata = null, $hasCover = false)
    {
        if (isset($this->bookList[$bookName])) {
            return $this->bookList[$bookName];
        }
        $bookId = count($this->bookList) + 1;
        $line = (object) ['id' => $bookId, 'title' => $bookName, 'path' => $bookPath, 'timestamp' => $timestamp, 'has_cover' => $hasCover];
        $book = new Book($line);
        $book->setHandler($this->handler);
        if (!empty($metadata)) {
            $metadata->updateBook($book);
        }
        $book->folderId = $this->id;
        $book->datas = [];
        $book->formats = [];
        $this->bookList[$bookName] = $book;
        $this->count += 1;
        return $book;
    }

    /**
     * Summary of addBookFormat
     * @param string $bookName
     * @param string $format
     * @return Data
     */
    public function addBookFormat($bookName, $format)
    {
        // not checking for existing format here
        $book = $this->bookList[$bookName];
        $filePath = $book->path . '/' . $bookName . '.' . $format;
        if (empty($book->timestamp) && file_exists($filePath)) {
            $book->timestamp = filemtime($filePath);
        }
        $dataId = $book->id;
        $post = (object) ['id' => $dataId, 'name' => $bookName, 'format' => strtoupper($format)];
        $data = new Data($post, $book);
        $book->datas[] = $data;
        // $book->formats[] = ...;
        return $data;
    }

    /**
     * Summary of addChildFolder
     * @param string $name
     * @return Folder
     */
    public function addChildFolder($name)
    {
        if (isset($this->children[$name])) {
            return $this->children[$name];
        }
        $childId = $this->id ? $this->id . '/' . $name : $name;
        $post = (object) ['id' => $childId, 'name' => $name, 'root' => $this->root];
        $childFolder = new Folder($post, $this->getDatabaseId());
        $childFolder->setHandler($this->handler);
        $childFolder->parent = $this;
        $childFolder->scanned = true;
        $this->children[$name] = $childFolder;
        return $childFolder;
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
     * @param ?int $database not used here
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
        $bookList = array_values($folder->bookList);
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
        $bookList = array_values($folder->bookList);
        foreach ($folder->children as $child) {
            $bookList = array_merge($bookList, $child->findBookFiles());
        }
        return self::getEntryArray($folder, $bookList, $n);
    }

    /**
     * Summary of getEntryArray
     * @param Folder $folder
     * @param Book[] $bookList
     * @param int $n
     * @return array{0: EntryBook[], 1: integer}
     */
    public static function getEntryArray($folder, $bookList, $n)
    {
        $sorted = $folder->orderBy ?? 'title';
        usort($bookList, function ($a, $b) use ($sorted) {
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
     * @param ?int $database not used here
     * @param ?string $root
     * @return self
     */
    public static function getInstanceById($id, $database = null, $root = null)
    {
        if (!empty($id)) {
            $name = static::findCurrentName($id);
            return new Folder((object) ['id' => $id, 'name' => $name, 'root' => $root], $database);
        }
        return self::getRootFolder($root, $database);
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
     * @param ?int $database not used here
     * @return Folder
     */
    public static function getRootFolder($root = null, $database = null)
    {
        $default = self::getDefaultName();
        // use id = 0 to support route urls
        $post = (object) ['id' => 0, 'name' => $default, 'root' => $root];
        return new Folder($post, $database);
    }

    /**
     * Summary of saveFileList
     * @param string $root
     * @param array<string, mixed> $fileList
     * @param array<string, mixed> $metaList
     * @param array<string, mixed> $coverList
     * @return void
     */
    public static function saveFileList($root, $fileList, $metaList, $coverList)
    {
        /**
        if (function_exists('apcu_store')) {
            $key = 'cops_folders.' . md5($root);
            $data = ['fileList' => $fileList, 'metaList' => $metaList];
            \apcu_store($key, $data);
            return;
        }
         */
        if (is_writable($root)) {
            $fileName = $root . '/cops_folders.php';
        } else {
            $fileName = sys_get_temp_dir() . '/cops_folders.' . md5($root) . '.php';
        }
        $content = '<?php' . "\n\n";
        $content .= "// This file has been auto-generated by the COPS Calibre\Folder class.\n\n";
        $content .= '$fileList = ' . Format::export($fileList) . ";\n\n";
        $content .= '$metaList = ' . Format::export($metaList) . ";\n\n";
        $content .= '$coverList = ' . Format::export($coverList) . ";\n\n";
        $content .= "return [\n";
        $content .= "    'fileList' => \$fileList,\n";
        $content .= "    'metaList' => \$metaList,\n";
        $content .= "    'coverList' => \$coverList,\n";
        $content .= "];\n";
        file_put_contents($fileName, $content);
    }

    /**
     * Summary of loadFileList
     * @param string $root
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: array<string, mixed>}
     */
    public static function loadFileList($root)
    {
        /**
        if (function_exists('apcu_fetch')) {
            $key = 'cops_folders.' . md5($root);
            $data = \apcu_fetch($key);
            if (!empty($data)) {
                return [$data['fileList'], $data['metaList']];
            }
        }
         */
        if (is_writable($root)) {
            $fileName = $root . '/cops_folders.php';
        } else {
            $fileName = sys_get_temp_dir() . '/cops_folders.' . md5($root) . '.php';
        }
        if (!file_exists($fileName)) {
            return [[], [], []];
        }
        if (filemtime($fileName) < time() - 24 * 60 * 60) {
            return [[], [], []];
        }
        try {
            $data = require $fileName;  // NOSONAR
        } catch (Exception) {
            $data = false;
        }
        if (empty($data)) {
            return [[], [], []];
        }
        return [$data['fileList'], $data['metaList'], $data['coverList']];
    }

    /**
     * Summary of parseGetFiles
     * ```sh
     * $ rclone lsjson -R --fast-list --no-mimetype --files-only /volume1/calibre/ >getfiles.json
     * ```
     * @param string $fileName
     * @param ?string $folderName
     * @return Book[]
     */
    public function parseGetFiles($fileName, $folderName = null)
    {
        if (!file_exists($fileName)) {
            throw new Exception('Invalid $fileName ' . $fileName);
        }
        $content = file_get_contents($fileName);
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        unset($content);
        $allowed = array_map('strtolower', Config::get('prefered_format'));
        $fileList = [];
        $metaList = [];
        $coverList = [];
        $large = false;
        if (count($data) > 1000) {
            $large = true;
            $this->count = 0;
        }
        $this->scanned = true;
        $folderPath = $this->getFolderPath($folderName);
        // Author Name/Short Title (id)/Short Title - Author Name.format
        foreach ($data as $item) {
            // ignore directories if present
            if (!empty($item['IsDir'])) {
                continue;
            }
            // ignore books if they're not inside current folder
            if (!empty($folderName) && !str_starts_with($item['Path'], $folderName . '/')) {
                continue;
            }
            $info = pathinfo($item['Path']);
            $info['extension'] ??= '';
            // add direct child to root folder and continue
            if (empty($folderName) && $large) {
                if (!in_array($info['extension'], $allowed)) {
                    continue;
                }
                if (!empty($info['dirname']) && $info['dirname'] != '.') {
                    $base = explode('/', $info['dirname'])[0];
                    $child = $this->addChildFolder($base);
                    $child->count += 1;
                } else {
                    $timestamp = $item['ModTime'] ?? '';
                    // add books in root folder
                    $book = $this->addBookName($info['filename'], $folderPath, $timestamp);
                    $data = $this->addBookFormat($info['filename'], $info['extension']);
                }
                $this->count += 1;
                continue;
            }
            if (!empty($folderName)) {
                if ($info['dirname'] == $folderName) {
                    $info['dirname'] = '.';
                } elseif (str_starts_with($info['dirname'], $folderName . '/')) {
                    $info['dirname'] = substr($info['dirname'], strlen($folderName) + 1);
                }
            }
            if (in_array($info['extension'], $allowed)) {
                // several books per directory allowed (but not required)
                $fileList[$info['dirname']] ??= [];
                // several formats per book allowed - assume same bookName with different formats here
                $fileList[$info['dirname']][$info['filename']] ??= [];
                array_push($fileList[$info['dirname']][$info['filename']], $info['extension']);
            } elseif ($info['extension'] == 'opf') {
                // only one .opf file per directory supported - assume one book per directory here
                $metaList[$info['dirname']] = $info['basename'];
            } elseif ($info['filename'] == 'cover' && in_array($info['extension'], ['jpg', 'png'])) {
                // only one cover file per directory supported - assume one book per directory here
                $coverList[$info['dirname']] = $info['basename'];
            }
        }
        if (empty($folderName) && $large) {
            ksort($this->children);
            return [];
        }
        unset($data);
        ksort($fileList);
        $parent = $this;
        if (!empty($folderName)) {
            $parent = $this->buildHierarchy($folderName, $this);
        }
        $folderPath = $this->getFolderPath($folderName) . '/';
        return $this->makeBookList($folderPath, $fileList, $metaList, $coverList, $parent);
    }
}
