<?php
/**
 * ZipFile class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

namespace Marsender\EPubLoader;

use Exception;
use ZipArchive;

/**
 * ZipFile class allows to open files inside a zip file with the standard php zip functions
 */
class ZipFile
{
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
            //$fileName =  $this->mZip->getNameIndex($i);
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

        //$entry = $this->mEntries[$inFileName];
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
     * @param mixed $Name
     * @param mixed $Data
     * @return mixed
     */
    public function FileAdd($Name, $Data)
    {
        //throw new Exception('ZipFile is read-only, use clsTbsZip class instead');
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
     * Replace the content of a file in the zip entries
     *
     * @param string $inFileName File with content to replace
     * @param string|bool $inData Data content to replace, or false to delete
     * @return mixed
     */
    public function FileReplace($inFileName, $inData)
    {
        //throw new Exception('ZipFile is read-only, use clsTbsZip class instead');
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
     * @param mixed $NameOrIdx
     * @param mixed $ReplacedAndDeleted
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
}
