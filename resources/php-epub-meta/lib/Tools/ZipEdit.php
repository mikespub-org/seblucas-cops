<?php
/**
 * ZipEdit class
 *
 * @author mikespub
 */

namespace SebLucas\EPubMeta\Tools;

//use ZipStream\Option\Archive as ArchiveOptions;
//use ZipStream\Option\File as FileOptions;
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

        //if (!$this->mZip->addFromString($inFileName, $inData)) {
        //    return false;
        //}
        //$this->mEntries[$inFileName] = $this->mZip->statName($inFileName);
        $this->mEntries[$inFileName] = [
            'name' => $inFileName,  // 'foobar/baz',
            //'index' => 3,
            //'crc' => 499465816,
            'size' => strlen($inData),
            'mtime' => time(),  // 1123164748,
            //'comp_size' => 24,
            //'comp_method' => 8,
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

        //if (!$this->mZip->addFile($inFilePath, $inFileName)) {
        //    return false;
        //}
        //$this->mEntries[$inFileName] = $this->mZip->statName($inFileName);
        $this->mEntries[$inFileName] = [
            'name' => $inFileName,  // 'foobar/baz',
            //'index' => 3,
            //'crc' => 499465816,
            'size' => filesize($inFilePath),
            'mtime' => filemtime($inFilePath),  // 1123164748,
            //'comp_size' => 24,
            //'comp_method' => 8,
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

        //if (!$this->mZip->deleteName($inFileName)) {
        //    return false;
        //}
        //unset($this->mEntries[$inFileName]);
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

        //if (!$this->mZip->addFromString($inFileName, $inData)) {
        //    return false;
        //}
        //$this->mEntries[$inFileName] = $this->mZip->statName($inFileName);
        $this->mEntries[$inFileName] ??= [
            'name' => $inFileName,
            'size' => 0,
            'mtime' => 0,
        ];
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

        //if (!$this->mZip->unchangeName($inFileName)) {
        //    return $nbr;
        //}
        $nbr += 1;
        $this->mChanges[$inFileName] = ['status' => 'unchanged'];
        return $nbr;
    }

    /**
     * Close the zip file
     *
     * @return void
     */
    public function Close()
    {
        if (!isset($this->mZip)) {
            return;
        }

        $this->mZip->close();
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
    public function Flush($render=self::DOWNLOAD, $outFileName='', $contentType='', $sendHeaders = true)
    {
        // we don't want to close the zip file to save all changes here - probably what you needed :-)
        //$this->Close();

        $outFileName = $outFileName ?: $this->mFileName;
        $contentType = $contentType ?: self::MIME_TYPE;
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
            //outputStream: $outFileStream,
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
                        //return $inZipFile->getFromName($fileName);
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
        //exit;
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
        //$options = new ArchiveOptions();
        //$options->setOutputStream($outFileStream);

        //$outZipStream = new ZipStream(basename($outFileName), $options);
        $outZipStream = new ZipStream(
            outputName: basename($outFileName),
            outputStream: $outFileStream,
            sendHttpHeaders: false,
        );
        foreach ($entries as $fileName => $entry) {
            //$fileOptions = new FileOptions();
            //$fileOptions->setSize($entry['size']);
            $date = new DateTime();
            $date->setTimestamp($entry['mtime']);
            //$fileOptions->setTime($date);
            // does not work in v2 - the zip stream is not seekable, but ZipStream checks for it in Stream.php
            //$outZipStream->addFileFromStream($fileName, $inZipFile->getStreamName($fileName), $fileOptions);
            //$outZipStream->addFile($fileName, $inZipFile->getFromName($fileName), $fileOptions);
            // does work in v3 - implemented using addFileFromCallback, so we might as well use that :-)
            //$outZipStream->addFileFromStream(
            //    fileName: $fileName,
            //    exactSize: $entry['size'],
            //    lastModificationDateTime: $date,
            //    stream: $inZipFile->getStream($fileName),
            //);
            $outZipStream->addFileFromCallback(
                fileName: $fileName,
                exactSize: $entry['size'],
                lastModificationDateTime: $date,
                callback: function () use ($inZipFile, $fileName) {
                    // this expects a stream as result, not the actual data
                    return $inZipFile->getStream($fileName);
                    //return $inZipFile->getFromName($fileName);
                },
            );
        }

        $outZipStream->finish();
        fclose($outFileStream);
    }
}
