<?php
/**
 * ZipFile class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 * @author     mikespub
 */

namespace SebLucas\EPubMeta\Tools;

use Exception;
use ZipArchive;

/**
 * ZipFile class allows to open files inside a zip file with the standard php zip functions
 *
 * This class also supports adding/replacing/deleting files inside the zip file, but changes
 * will *not* be reflected correctly until you close the zip file, and open it again if needed
 *
 * Note: this is not meant to handle a massive amount of files inside generic archive files.
 * It is specifically meant for EPUB files which typically contain hundreds/thousands of pages,
 * not millions or more. And any changes you make are kept in memory, so don't re-write every
 * page of War and Peace either - better to unzip that locally and then re-zip it afterwards.
 */
class ZipFile
{
    public const DOWNLOAD = 1;   // download (default)
    public const NOHEADER = 4;   // option to use with DOWNLOAD: no header is sent
    public const FILE = 8;       // output to file  , or add from file
    public const STRING = 32;    // output to string, or add from string
    public const MIME_TYPE = 'application/epub+zip';

    /** @var ZipArchive|null */
    protected $mZip;
    /** @var array<string, mixed>|null */
    protected $mEntries;
    /** @var array<string, mixed> */
    protected $mChanges = [];
    /** @var string|null */
    protected $mFileName;

    public function __construct()
    {
        $this->mZip = null;
        $this->mEntries = null;
        $this->mFileName = null;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->Close();
    }

    /**
     * Open a zip file and read it's entries
     *
     * @param string $inFileName
     * @param int|null $inFlags
     * @return boolean True if zip file has been correctly opended, else false
     */
    public function Open($inFileName, $inFlags = 0)  // ZipArchive::RDONLY)
    {
        $this->Close();
        $inFileName = realpath($inFileName);

        $this->mZip = new ZipArchive();
        $result = $this->mZip->open($inFileName, $inFlags);
        if ($result !== true) {
            return false;
        }

        $this->mFileName = $inFileName;

        $this->mEntries = [];
        $this->mChanges = [];

        for ($i = 0; $i <  $this->mZip->numFiles; $i++) {
            $entry =  $this->mZip->statIndex($i);
            $fileName = $entry['name'];
            $this->mEntries[$fileName] = $entry;
            $this->mChanges[$fileName] = ['status' => 'unchanged'];
        }

        return true;
    }

    /**
     * Check if a file exist in the zip entries
     *
     * @param string $inFileName File to search
     *
     * @return boolean True if the file exist, else false
     */
    public function FileExists($inFileName)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!isset($this->mEntries[$inFileName])) {
            return false;
        }

        return true;
    }

    /**
     * Read the content of a file in the zip entries
     *
     * @param string $inFileName File to search
     *
     * @return mixed File content the file exist, else false
     */
    public function FileRead($inFileName)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!isset($this->mEntries[$inFileName])) {
            return false;
        }

        $data = false;

        $changes = $this->mChanges[$inFileName] ?? ['status' => 'unchanged'];
        switch ($changes['status']) {
            case 'unchanged':
                $data = $this->mZip->getFromName($inFileName);
                break;
            case 'added':
            case 'modified':
                if (isset($changes['data'])) {
                    $data = $changes['data'];
                } elseif (isset($changes['path'])) {
                    $data = file_get_contents($changes['path']);
                }
                break;
            case 'deleted':
            default:
                break;
        }
        return $data;
    }

    /**
     * Get a file handler to a file in the zip entries (read-only)
     *
     * @param string $inFileName File to search
     *
     * @return resource|bool File handler if the file exist, else false
     */
    public function FileStream($inFileName)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!isset($this->mEntries[$inFileName])) {
            return false;
        }

        // @todo streaming of added/modified data?
        return $this->mZip->getStream($inFileName);
    }

    /**
     * Summary of FileAdd
     * @param string $inFileName
     * @param mixed $inData
     * @return bool
     */
    public function FileAdd($inFileName, $inData)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!$this->mZip->addFromString($inFileName, $inData)) {
            return false;
        }
        $this->mEntries[$inFileName] = $this->mZip->statName($inFileName);
        $this->mChanges[$inFileName] = ['status' => 'added', 'data' => $inData];
        return true;
    }

    /**
     * Summary of FileAddPath
     * @param string $inFileName
     * @param string $inFilePath
     * @return mixed
     */
    public function FileAddPath($inFileName, $inFilePath)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!$this->mZip->addFile($inFilePath, $inFileName)) {
            return false;
        }
        $this->mEntries[$inFileName] = $this->mZip->statName($inFileName);
        $this->mChanges[$inFileName] = ['status' => 'added', 'path' => $inFilePath];
        return true;
    }

    /**
     * Summary of FileDelete
     * @param string $inFileName
     * @return bool
     */
    public function FileDelete($inFileName)
    {
        if (!$this->FileExists($inFileName)) {
            return false;
        }

        if (!$this->mZip->deleteName($inFileName)) {
            return false;
        }
        unset($this->mEntries[$inFileName]);
        $this->mChanges[$inFileName] = ['status' => 'deleted'];
        return true;
    }

    /**
     * Replace the content of a file in the zip entries
     *
     * @param string $inFileName File with content to replace
     * @param string|bool $inData Data content to replace, or false to delete
     * @return bool
     */
    public function FileReplace($inFileName, $inData)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if ($inData === false) {
            return $this->FileDelete($inFileName);
        }

        if (!$this->mZip->addFromString($inFileName, $inData)) {
            return false;
        }
        $this->mEntries[$inFileName] = $this->mZip->statName($inFileName);
        $this->mChanges[$inFileName] = ['status' => 'modified', 'data' => $inData];
        return true;
    }

    /**
     * Return the state of the file.
     * @param mixed $inFileName
     * @return string|bool 'u'=unchanged, 'm'=modified, 'd'=deleted, 'a'=added, false=unknown
     */
    public function FileGetState($inFileName)
    {
        $changes = $this->mChanges[$inFileName] ?? ['status' => false];
        return $changes['status'];
    }

    /**
     * Summary of FileCancelModif
     * @param string $inFileName
     * @param bool $ReplacedAndDeleted
     * @return int
     */
    public function FileCancelModif($inFileName, $ReplacedAndDeleted = true)
    {
        // cancel added, modified or deleted modifications on a file in the archive
        // return the number of cancels

        $nbr = 0;

        if (!$this->mZip->unchangeName($inFileName)) {
            return $nbr;
        }
        $nbr += 1;
        $this->mChanges[$inFileName] = ['status' => 'unchanged'];
        return $nbr;
    }

    /**
     * Close the zip file
     *
     * @return void
     * @throws Exception
     */
    public function Close()
    {
        if (!isset($this->mZip)) {
            return;
        }

        if (!$this->mZip->close()) {
            $status = $this->mZip->getStatusString();
            $this->mZip = null;
            throw new Exception($status);
        }
        $this->mZip = null;
    }

    /**
     * Summary of Flush
     * @param mixed $render
     * @param mixed $outFileName
     * @param mixed $contentType
     * @param bool $sendHeaders
     * @return void
     */
    public function Flush($render = self::DOWNLOAD, $outFileName = '', $contentType = '', $sendHeaders = true)
    {
        // we need to close the zip file to save all changes here - probably not what you wanted :-()
        $this->Close();

        $outFileName = $outFileName ?: $this->mFileName;
        $contentType = $contentType ?: static::MIME_TYPE;
        if (!$sendHeaders) {
            $render = $render | static::NOHEADER;
        }
        $inFilePath = realpath($this->mFileName);

        if (($render & static::NOHEADER) !== static::NOHEADER) {
            $expires = 60 * 60 * 24 * 14;
            header('Pragma: public');
            header('Cache-Control: max-age=' . $expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . basename($outFileName) . '"');

            // see fetch.php for use of Config::get('x_accel_redirect')
            header('Content-Length: ' . filesize($inFilePath));
            //header(Config::get('x_accel_redirect') . ': ' . $inFilePath);
        }

        readfile($inFilePath);
    }
}
