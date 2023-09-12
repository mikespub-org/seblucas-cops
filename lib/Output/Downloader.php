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
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use ZipStream\ZipStream;

/**
 * Downloader for multiple books
 */
class Downloader
{
    public static string $endpoint = Config::ENDPOINT["download"];

    /** @var Request */
    protected $request;
    /** @var mixed */
    protected $databaseId = null;
    /** @var string */
    protected $format = 'EPUB';
    /** @var string */
    protected $fileName = 'download.epub.zip';
    /** @var array<string> */
    protected $fileList = [];

    /**
     * Summary of __construct
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->databaseId = $this->request->get('db');
        $type = $this->request->get('type', 'epub');
        $this->format = strtoupper($type);
    }

    /**
     * Summary of isValid
     * @return bool
     */
    public function isValid()
    {
        // @todo support other grouped downloads?
        $instance = $this->hasSeries();
        if (!$instance) {
            $instance = $this->hasAuthor();
            if (!$instance) {
                return false;
            }
        }
        return $this->checkFileList($instance);
    }

    /**
     * Summary of hasSeries
     * @return Serie|bool
     */
    public function hasSeries()
    {
        if (!in_array($this->format, Config::get('download_series'))) {
            return false;
        }
        $seriesId = $this->request->get('series', null, '/^\d+$/');
        if (empty($seriesId)) {
            return false;
        }
        /** @var Serie $instance */
        $instance = Serie::getInstanceById($seriesId, $this->databaseId);
        if (empty($instance->id)) {
            return false;
        }
        return $instance;
    }

    /**
     * Summary of hasAuthor
     * @return Author|bool
     */
    public function hasAuthor()
    {
        if (!in_array($this->format, Config::get('download_author'))) {
            return false;
        }
        $authorId = $this->request->get('author', null, '/^\d+$/');
        if (empty($authorId)) {
            return false;
        }
        /** @var Author $instance */
        $instance = Author::getInstanceById($authorId, $this->databaseId);
        if (empty($instance->id)) {
            return false;
        }
        return $instance;
    }

    /**
     * Summary of checkFileList
     * @param Serie|Author $instance
     * @return bool
     */
    public function checkFileList($instance)
    {
        $entries = $instance->getBooks();  // -1
        if (count($entries) < 1) {
            return false;
        }
        $this->fileList = [];
        foreach ($entries as $entry) {
            $data = $entry->book->getDataFormat($this->format);
            if (!$data) {
                continue;
            }
            $path = $data->getLocalPath();
            if (!file_exists($path)) {
                continue;
            }
            $this->fileList[] = $path;
        }
        if (count($this->fileList) < 1) {
            return false;
        }
        $this->fileName = $instance->name . '.' . strtolower($this->format) . '.zip';
        return true;
    }

    /**
     * Summary of download
     * @return void
     */
    public function download()
    {
        // keep it simple for now, and use the basic options
        $zip = new ZipStream(
            outputName: $this->fileName,
            sendHttpHeaders: true,
        );
        foreach ($this->fileList as $path) {
            $zip->addFileFromPath(
                fileName: basename($path),
                path: $path,
            );
        }
        $zip->finish();
    }
}
