<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Author;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\Page;
use ZipStream\ZipStream;

/**
 * Zipper to download multiple books
 */
class Zipper
{
    public static string $handler = "zipper";

    /** @var Request */
    protected $request;
    /** @var ?int */
    protected $databaseId = null;
    /** @var string */
    protected $format = 'EPUB';
    /** @var string */
    protected $fileName = 'download.epub.zip';
    /** @var array<string, string> */
    protected $fileList = [];
    /** @var ?string */
    protected $message = null;

    /**
     * Summary of __construct
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->databaseId = $this->request->database();
        $type = $this->request->get('type', 'any');
        $this->format = strtoupper((string) $type);
    }

    /**
     * Summary of isValidForDownload
     * @return bool
     */
    public function isValidForDownload()
    {
        $entries = $this->hasPage();
        if (!$entries) {
            $entries = $this->hasSeries();
            if (!$entries) {
                $entries = $this->hasAuthor();
                if (!$entries) {
                    return false;
                }
            }
        }
        return $this->checkFileList($entries);
    }

    /**
     * Summary of hasPage
     * @return array<mixed>|bool
     */
    public function hasPage()
    {
        if (!in_array($this->format, Config::get('download_page'))) {
            $this->message ??= 'Invalid format for page';
            return false;
        }
        $pageId = $this->request->get('page');
        if (empty($pageId)) {
            $this->message ??= 'Invalid page id';
            return false;
        }
        /** @var Page $instance */
        $instance = PageId::getPage($pageId, $this->request);
        if ($instance::class == Page::class) {
            $this->message = 'Invalid page';
            return false;
        }
        if ($this->format == 'ANY') {
            $this->fileName = $instance->title . '.zip';
        } else {
            $this->fileName = $instance->title . '.' . strtolower($this->format) . '.zip';
        }
        if (!empty($instance->parentTitle)) {
            $this->fileName = $instance->parentTitle . ' - ' . $this->fileName;
        }
        if (!empty($instance->n) && $instance->n > 1) {
            $this->fileName = str_replace('.zip', '.' . strval($instance->n) . '.zip', $this->fileName);
        }
        $entries = $instance->entryArray;
        if (empty($entries)) {
            $this->message = 'No books found for page';
            return false;
        }
        return $entries;
    }

    /**
     * Summary of hasSeries
     * @return array<mixed>|bool
     */
    public function hasSeries()
    {
        if (!in_array($this->format, Config::get('download_series'))) {
            $this->message ??= 'Invalid format for series';
            return false;
        }
        $seriesId = $this->request->getId('series');
        if (empty($seriesId)) {
            $this->message ??= 'Invalid series id';
            return false;
        }
        /** @var Serie $instance */
        $instance = Serie::getInstanceById($seriesId, $this->databaseId);
        if (empty($instance->id)) {
            $this->message = 'Invalid series';
            return false;
        }
        if ($this->format == 'ANY') {
            $this->fileName = $instance->name . '.zip';
        } else {
            $this->fileName = $instance->name . '.' . strtolower($this->format) . '.zip';
        }
        $entries = $instance->getBooks();  // -1
        if (empty($entries)) {
            $this->message = 'No books found for series';
            return false;
        }
        return $entries;
    }

    /**
     * Summary of hasAuthor
     * @return array<mixed>|bool
     */
    public function hasAuthor()
    {
        if (!in_array($this->format, Config::get('download_author'))) {
            $this->message ??= 'Invalid format for author';
            return false;
        }
        $authorId = $this->request->getId('author');
        if (empty($authorId)) {
            $this->message ??= 'Invalid author id';
            return false;
        }
        /** @var Author $instance */
        $instance = Author::getInstanceById($authorId, $this->databaseId);
        if (empty($instance->id)) {
            $this->message = 'Invalid author';
            return false;
        }
        if ($this->format == 'ANY') {
            $this->fileName = $instance->name . '.zip';
        } else {
            $this->fileName = $instance->name . '.' . strtolower($this->format) . '.zip';
        }
        $entries = $instance->getBooks();  // -1
        if (empty($entries)) {
            $this->message = 'No books found for author';
            return false;
        }
        return $entries;
    }

    /**
     * Summary of checkFileList
     * @param array<mixed> $entries
     * @return bool
     */
    public function checkFileList($entries)
    {
        if (count($entries) < 1) {
            $this->message = 'No books found';
            return false;
        }
        $this->fileList = [];
        if ($this->format == 'ANY') {
            $checkFormats = Config::get('prefered_format');
        } else {
            $checkFormats = [ $this->format ];
        }
        // @todo use download_template to format name
        //$template = Config::get('download_template');
        foreach ($entries as $entry) {
            if ($entry::class != EntryBook::class) {
                continue;
            }
            $data = false;
            foreach ($checkFormats as $format) {
                $data = $entry->book->getDataFormat($format);
                if ($data) {
                    break;
                }
            }
            if (!$data) {
                continue;
            }
            $path = $data->getLocalPath();
            if (!file_exists($path)) {
                continue;
            }
            //$name = basename($path);
            // @todo use normalizeUtf8String() on author, series and title or not?
            // Using {author} - {series} #{series_index} - {title} with .{format}
            $author = $entry->book->getAuthorsName();
            $name = explode(', ', $author)[0];
            $serie = $entry->book->getSerie();
            if (!empty($serie)) {
                $name .= ' - ' . $serie->name . ' #' . $entry->book->seriesIndex;
            }
            $name .= ' - ' . $entry->book->title;
            $info = pathinfo($path);
            $name .= '.' . $info['extension'];
            // allow unicode characters here
            $name = preg_replace('/[^\w\s\d\'\.\-\/_,#\[\]\(\)]/u', '', $name);
            $this->fileList[$name] = $path;
        }
        if (count($this->fileList) < 1) {
            $this->message = 'No files found';
            return false;
        }
        return true;
    }

    /**
     * Summary of isValidForExtraFiles
     * @param Book $book
     * @return bool
     */
    public function isValidForExtraFiles($book)
    {
        $extraFiles = $book->getExtraFiles();
        if (empty($extraFiles)) {
            $this->message = 'No files found for book';
            return false;
        }
        $this->fileName = 'data.zip';
        $this->fileList = [];
        foreach ($extraFiles as $name) {
            $path = $book->path . '/' . Book::DATA_DIR_NAME . '/' . $name;
            if (!file_exists($path)) {
                continue;
            }
            $this->fileList[$name] = $path;
        }
        if (count($this->fileList) < 1) {
            $this->message = 'No files found for book';
            return false;
        }
        return true;
    }

    /**
     * Summary of download
     * @param ?string $fileName
     * @param bool $sendHeaders
     * @return static
     */
    public function download($fileName = null, $sendHeaders = true)
    {
        $fileName ??= $this->fileName;

        // keep it simple for now, and use the basic options
        $zip = new ZipStream(
            outputName: $fileName,
            sendHttpHeaders: $sendHeaders,
        );
        foreach ($this->fileList as $name => $path) {
            $zip->addFileFromPath(
                fileName: $name,
                path: $path,
            );
        }
        $zip->finish();
        return $this;
    }

    /**
     * Summary of getMessage
     * @return string
     */
    public function getMessage()
    {
        return $this->message ?? 'Unknown error';
    }
}
