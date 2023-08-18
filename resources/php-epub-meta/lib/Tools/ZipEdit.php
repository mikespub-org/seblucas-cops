<?php
/**
 * ZipEdit class
 *
 * @author mikespub
 */

namespace SebLucas\EPubMeta\Tools;

use ZipStream\ZipStream;
use DateTime;
use Exception;
use ZipArchive;

/**
 * ZipEdit class allows to edit zip files on the fly and stream them afterwards
 */
class ZipEdit
{
    public const DOWNLOAD = 1;   // download (default)
    public const NOHEADER = 4;   // option to use with DOWNLOAD: no header is sent
    public const FILE = 8;       // output to file  , or add from file
    public const STRING = 32;    // output to string, or add from string
    public const MIME_TYPE = 'application/epub+zip';

    /** @var ZipArchive|null */
    private $mZip;
    /** @var array<string, mixed>|null */
    private $mEntries;
    /** @var array<string, mixed> */
    private $mChanges = [];
    /** @var string|null */
    private $mFileName;
    private bool $mSaveMe = false;

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

        $this->mZip = new ZipArchive();
        $result = $this->mZip->open($inFileName, ZipArchive::RDONLY);
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

        $this->mEntries[$inFileName] = [
            'name' => $inFileName,  // 'foobar/baz',
            'size' => strlen($inData),
            'mtime' => time(),  // 1123164748,
        ];
        $this->mChanges[$inFileName] = ['status' => 'added', 'data' => $inData];
        return true;
    }

    /**
     * Summary of FileAddPath
     * @param string $inFileName
     * @param string $inFilePath
     * @return bool
     */
    public function FileAddPath($inFileName, $inFilePath)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        $this->mEntries[$inFileName] = [
            'name' => $inFileName,  // 'foobar/baz',
            'size' => filesize($inFilePath),
            'mtime' => filemtime($inFilePath),  // 1123164748,
        ];
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

        $this->mEntries[$inFileName]['size'] = 0;
        $this->mEntries[$inFileName]['mtime'] = time();
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

        $this->mEntries[$inFileName] ??= [];
        $this->mEntries[$inFileName]['name'] = $inFileName;
        $this->mEntries[$inFileName]['size'] = strlen($inData);
        $this->mEntries[$inFileName]['mtime'] = time();
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
    public function FileCancelModif($inFileName, $ReplacedAndDeleted=true)
    {
        // cancel added, modified or deleted modifications on a file in the archive
        // return the number of cancels

        $nbr = 0;

        $this->mChanges[$inFileName] = ['status' => 'unchanged'];
        $nbr += 1;
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

        $outFileName = $this->mFileName . '.copy';
        if ($this->mSaveMe) {
            $outFileStream = fopen($outFileName, 'wb+');
            if ($outFileStream === false) {
                throw new Exception('Unable to open zip copy ' . $outFileName);
            }
            $this->Flush(self::DOWNLOAD, $this->mFileName, self::MIME_TYPE, false, $outFileStream);
            $result = fclose($outFileStream);
            if ($result === false) {
                throw new Exception('Unable to close zip copy ' . $outFileName);
            }
        }

        if (!$this->mZip->close()) {
            $status = $this->mZip->getStatusString();
            $this->mZip = null;
            throw new Exception($status);
        }
        if ($this->mSaveMe) {
            $result = rename($outFileName, $this->mFileName);
            if ($result === false) {
                throw new Exception('Unable to rename zip copy ' . $outFileName);
            }
            $this->mSaveMe = false;
        }
        $this->mZip = null;
    }

    /**
     * Summary of SaveBeforeClose
     * @return void
     */
    public function SaveBeforeClose()
    {
        // Coming from EPub()->download() without fileName, called in EPub()->save()
        // This comes right before EPub()->zip->close(), at which point we're lost
        $this->mSaveMe = true;
    }

    /**
     * Summary of Flush
     * @param mixed $render
     * @param mixed $outFileName
     * @param mixed $contentType
     * @param bool $sendHeaders
     * @param resource|null $outFileStream
     * @return void
     */
    public function Flush($render=self::DOWNLOAD, $outFileName='', $contentType='', $sendHeaders = true, $outFileStream = null)
    {
        // we don't want to close the zip file to save all changes here - probably what you needed :-)
        //$this->Close();

        $outFileName = $outFileName ?: $this->mFileName;
        $contentType = $contentType ?: self::MIME_TYPE;
        if ($outFileStream) {
            $sendHeaders = false;
        }
        if (!$sendHeaders) {
            $render = $render | self::NOHEADER;
        }
        if (($render & self::NOHEADER) !== self::NOHEADER) {
            $sendHeaders = true;
        } else {
            $sendHeaders = false;
        }

        $outZipStream = new ZipStream(
            outputName: basename($outFileName),
            outputStream: $outFileStream,
            sendHttpHeaders: $sendHeaders,
            contentType: $contentType,
        );
        foreach ($this->mEntries as $fileName => $entry) {
            $changes = $this->mChanges[$fileName];
            switch ($changes['status']) {
                case 'unchanged':
                    // Automatic binding of $this
                    $callback = function () use ($fileName) {
                        // this expects a stream as result, not the actual data
                        return $this->mZip->getStream($fileName);
                    };
                    $date = new DateTime();
                    $date->setTimestamp($entry['mtime']);
                    $outZipStream->addFileFromCallback(
                        fileName: $fileName,
                        exactSize: $entry['size'],
                        lastModificationDateTime: $date,
                        callback: $callback,
                    );
                    break;
                case 'added':
                case 'modified':
                    if (isset($changes['data'])) {
                        $outZipStream->addFile(
                            fileName: $fileName,
                            data: $changes['data'],
                        );
                    } elseif (isset($changes['path'])) {
                        $outZipStream->addFileFromPath(
                            fileName: $fileName,
                            path: $changes['path'],
                        );
                    }
                    break;
                case 'deleted':
                default:
                    break;
            }
        }

        $outZipStream->finish();
    }

    /**
     * Summary of copyTest
     * @param string $inFileName
     * @param string $outFileName
     * @return void
     */
    public static function copyTest($inFileName, $outFileName)
    {
        $inZipFile = new ZipArchive();
        $result = $inZipFile->open($inFileName, ZipArchive::RDONLY);
        if ($result !== true) {
            throw new Exception('Unable to open zip file ' . $inFileName);
        }

        $entries = [];
        for ($i = 0; $i <  $inZipFile->numFiles; $i++) {
            $entry =  $inZipFile->statIndex($i);
            $fileName = $entry['name'];
            $entries[$fileName] = $entry;
        }

        // see ZipStreamTest.php
        $outFileStream = fopen($outFileName, 'wb+');

        $outZipStream = new ZipStream(
            outputName: basename($outFileName),
            outputStream: $outFileStream,
            sendHttpHeaders: false,
        );
        foreach ($entries as $fileName => $entry) {
            $date = new DateTime();
            $date->setTimestamp($entry['mtime']);
            // does not work in v2 - the zip stream is not seekable, but ZipStream checks for it in Stream.php
            // does work in v3 - implemented using addFileFromCallback, so we might as well use that :-)
            $outZipStream->addFileFromCallback(
                fileName: $fileName,
                exactSize: $entry['size'],
                lastModificationDateTime: $date,
                callback: function () use ($inZipFile, $fileName) {
                    // this expects a stream as result, not the actual data
                    return $inZipFile->getStream($fileName);
                },
            );
        }

        $outZipStream->finish();
        fclose($outFileStream);
    }
}
