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

    /** @var ZipArchive|null */
    private $mZip;
    /** @var array<string, mixed>|null */
    private $mEntries;
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
        $result = $this->mZip->open($inFileName, $inFlags);
        if ($result !== true) {
            return false;
        }

        $this->mFileName = $inFileName;

        $this->mEntries = [];

        for ($i = 0; $i <  $this->mZip->numFiles; $i++) {
            $entry =  $this->mZip->statIndex($i);
            $fileName = $entry['name'];
            $this->mEntries[$fileName] = $entry;
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

        $data = $this->mZip->getFromName($inFileName);

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

        return $this->mZip->getStream($inFileName);
    }

    /**
     * Summary of FileAdd
     * @param string $Name
     * @param mixed $Data
     * @return bool
     */
    public function FileAdd($Name, $Data)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!$this->mZip->addFromString($Name, $Data)) {
            return false;
        }
        $this->mEntries[$Name] = $this->mZip->statName($Name);
        return true;
    }

    /**
     * Summary of FileAddPath
     * @param string $Name
     * @param string $Path
     * @return bool
     */
    public function FileAddPath($Name, $Path)
    {
        if (!isset($this->mZip)) {
            return false;
        }

        if (!$this->mZip->addFile($Path, $Name)) {
            return false;
        }
        $this->mEntries[$Name] = $this->mZip->statName($Name);
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
            if ($this->FileExists($inFileName)) {
                if (!$this->mZip->deleteName($inFileName)) {
                    return false;
                }
                unset($this->mEntries[$inFileName]);
            }
            return true;
        }

        if (!$this->mZip->addFromString($inFileName, $inData)) {
            return false;
        }
        $this->mEntries[$inFileName] = $this->mZip->statName($inFileName);
        return true;
    }

    /**
     * Summary of FileCancelModif
     * @param string $NameOrIdx
     * @param bool $ReplacedAndDeleted
     * @return int
     */
    public function FileCancelModif($NameOrIdx, $ReplacedAndDeleted=true)
    {
        // cancel added, modified or deleted modifications on a file in the archive
        // return the number of cancels

        $nbr = 0;

        if (!$this->mZip->unchangeName($NameOrIdx)) {
            return $nbr;
        }
        $nbr += 1;

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
     * @param mixed $Render
     * @param mixed $File
     * @param mixed $ContentType
     * @return bool
     */
    public function Flush($Render=self::DOWNLOAD, $File='', $ContentType='')
    {
        $File = $File ?: $this->mFileName;
        return false;
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
