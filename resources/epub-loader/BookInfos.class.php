<?php
/**
 * BookInfos class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

namespace Marsender\EPubLoader;

use SebLucas\EPubMeta\Tools\ZipFile;
use Exception;

require_once(realpath(__DIR__) . '/BookEPub.class.php');
$ePubMetaPath = realpath(dirname(__DIR__)) . '/php-epub-meta';
require_once $ePubMetaPath . '/lib/Tools/ZipFile.php';

/**
 * BookInfos class contains informations about a book,
 * and methods to load this informations from multiple sources (eg epub file)
 */
class BookInfos
{
    public string $mBasePath = '';

    public string $mPath = '';

    public string $mName = '';

    public string $mFormat = '';

    public string $mUuid = '';

    public string $mUri = '';

    public string $mTitle = '';

    /** @var array<mixed>|null */
    public $mAuthors = null;

    public string $mLanguage = '';

    public string $mDescription = '';

    /** @var array<string>|null */
    public $mSubjects = null;

    public string $mCover = '';

    public string $mIsbn = '';

    public string $mRights = '';

    public string $mPublisher = '';

    public string $mSerie = '';

    public string $mSerieIndex = '';

    public string $mCreationDate = '';

    public string $mModificationDate = '';

    public string $mTimeStamp = '0';

    /**
     * Loads book infos from an epub file
     *
     * @param string $inBasePath Epub base directory
     * @param string $inFileName Epub file name (from base directory)
     * @throws Exception if error
     *
     * @return void
     */
    public function LoadFromEpub($inBasePath, $inFileName)
    {
        $fullFileName = sprintf('%s%s%s', $inBasePath, DIRECTORY_SEPARATOR, $inFileName);
        // Check file access
        if (!is_readable($fullFileName)) {
            throw new Exception('Cannot read file');
        }

        // Load the epub file
        $ePub = new BookEPub($fullFileName, ZipFile::class);

        // Check epub version
        $version = $ePub->getEpubVersion();
        switch ($version) {
            case 2:
            case 3:
                break;
            default:
                $error = sprintf('Incorrect ebook epub version=%d', $version);
                throw new Exception($error);
        }

        // Get the epub infos
        $this->mFormat = 'epub';
        $this->mBasePath = $inBasePath;
        $this->mPath = pathinfo($inFileName, PATHINFO_DIRNAME);
        $this->mName = pathinfo($inFileName, PATHINFO_FILENAME);
        $this->mUuid = $ePub->getUuid();
        $this->mUri = $ePub->getUri();
        $this->mTitle = $ePub->getTitle();
        $this->mAuthors = $ePub->getAuthors();
        $this->mLanguage = $ePub->getLanguage();
        $this->mDescription = $ePub->getDescription();
        $this->mSubjects = $ePub->getSubjects();
        $cover = $ePub->getCoverInfo();
        $cover = $cover['found'];
        if (($cover !== false)) {
            // Remove meta base path
            $meta = $ePub->meta();
            $len = strlen($meta) - strlen(pathinfo($meta, PATHINFO_BASENAME));
            $this->mCover = substr($cover, $len);
        }
        $this->mIsbn = $ePub->getIsbn();
        $this->mRights = $ePub->getCopyright();
        $this->mPublisher = $ePub->getPublisher();
        // Tag sample in opf file:
        //   <meta content="Histoire de la Monarchie de Juillet" name="calibre:series"/>
        $this->mSerie = $ePub->getSeries();
        // Tag sample in opf file:
        //   <meta content="7" name="calibre:series_index"/>
        $this->mSerieIndex = $ePub->getSeriesIndex();
        $this->mCreationDate = $this->GetSqlDate($ePub->CreationDate()) ?? '';
        $this->mModificationDate = $this->GetSqlDate($ePub->ModificationDate()) ?? '';
        // Timestamp is used to get latest ebooks
        $this->mTimeStamp = $this->mCreationDate;
    }

    /**
     * Format an date from a date
     *
     * @param string $inDate
     *
     * @return ?string Sql formated date
     */
    public static function GetSqlDate($inDate)
    {
        if (empty($inDate)) {
            return null;
        }

        $date = new \DateTime($inDate);
        $res = $date->format('Y-m-d H:i:s');

        return $res;
    }

    /**
     * Format a timestamp from a date
     *
     * @param string $inDate
     *
     * @return ?int Timestamp
     */
    public static function GetTimeStamp($inDate)
    {
        if (empty($inDate)) {
            return null;
        }

        $date = new \DateTime($inDate);
        $res = $date->getTimestamp();

        return $res;
    }

    /**
     * Format a string for sort
     *
     * @param string $inStr Any string
     *
     * @return string Same string without any accents
     */
    public static function GetSortString($inStr)
    {
        $search = [
            '@(*UTF8)[éèêëÉÈÊË]@i',
            '@(*UTF8)[áàâäÁÀÂÄ]@i',
            '@(*UTF8)[íìîïÍÌÎÏ]@i',
            '@(*UTF8)[úùûüÚÙÛÜ]@i',
            '@(*UTF8)[óòôöÓÒÔÖ]@i',
            '@(*UTF8)[œŒ]@i',
            '@(*UTF8)[æÆ]@i',
            '@(*UTF8)[çÇ]@i',
            //'@[ ]@i',
            '@[^a-zA-Z0-9_\-\.\ ]@',
        ];
        $replace = [
            'e',
            'a',
            'i',
            'u',
            'o',
            'oe',
            'ae',
            'c',
            //'-',
            '',
        ];
        $res = preg_replace($search, $replace, $inStr);

        // Remove double white spaces
        while (strpos($res, '  ') !== false) {
            $res = str_replace('  ', ' ', $res);
        }

        $res = trim($res, ' -.');

        return $res;
    }

    /**
     * Create a new unique id (same as shell uuidgen)
     *
     * @return void
     */
    public function CreateUuid()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        $this->mUuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
