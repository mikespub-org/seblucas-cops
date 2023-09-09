<?php
/**
 * ActionHandler class
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 * @author     mikespub
 */

namespace Marsender\EPubLoader;

class ActionHandler
{
    /** @var array<mixed> */
    protected $dbConfig;
    /** @var CalibreDbLoader */
    protected $db;
    /** @var string */
    public $cacheDir;
    /** @var string */
    public $dbFileName;

    /**
     * Summary of __construct
     * @param array<mixed> $dbConfig
     */
    public function __construct($dbConfig)
    {
        $this->dbConfig = $dbConfig;
        $this->cacheDir = dirname(__DIR__) . '/cache';
        // Init database file
        $dbPath = $this->dbConfig['db_path'];
        $this->dbFileName = $dbPath . DIRECTORY_SEPARATOR . 'metadata.db';
        // Open the database
        $this->db = new CalibreDbLoader($this->dbFileName);
    }

    /**
     * Summary of handle
     * @param string $action
     * @return mixed
     */
    public function handle($action)
    {
        $authorId = isset($_GET['authorId']) ? (int)$_GET['authorId'] : null;
        $matchId = $_GET['matchId'] ?? null;
        if (!empty($matchId) && !preg_match('/^Q\d+$/', $matchId)) {
            $matchId = null;
        }
        switch($action) {
            case 'authors':
                $result = $this->authors($authorId, $matchId);
                break;
            case 'books':
                $bookId = isset($_GET['bookId']) ? (int)$_GET['bookId'] : null;
                $result = $this->books($authorId, $bookId, $matchId);
                break;
            case 'series':
                $seriesId = isset($_GET['seriesId']) ? (int)$_GET['seriesId'] : null;
                $result = $this->series($authorId, $seriesId, $matchId);
                break;
            case 'wikidata':
                $result = $this->wikidata($matchId, $authorId);
                break;
            default:
                $result = null;
        }
        return $result;
    }

    /**
     * Summary of authors
     * @param int|null $authorId
     * @param string|null $matchId
     * @return array<mixed>|null
     */
    public function authors($authorId, $matchId)
    {
        global $gErrorArray;

        // Update the author link
        if (!is_null($authorId) && !is_null($matchId)) {
            $link = WikiMatch::link($matchId);
            if (!$this->db->setAuthorLink($authorId, $link)) {
                $gErrorArray[$this->dbFileName] = "Failed updating link {$link} for authorId {$authorId}";
                return null;
            }
            $authorId = null;
        }
        // List the authors
        $authors = $this->db->getAuthors($authorId);
        $author = null;
        $query = null;
        if (!is_null($authorId) && is_null($matchId)) {
            $author = $authors[$authorId];
            $query = $author['name'];
        }
        $matched = null;
        if (!is_null($query)) {
            // Find match on Wikidata
            $wikimatch = new WikiMatch($this->cacheDir);
            $matched = $wikimatch->findAuthors($query);
            // Find works from author for 1st match
            if (count($matched) > 0) {
                $firstId = array_keys($matched)[0];
                $matched[$firstId]['entries'] = $wikimatch->findWorksByAuthor($author);
            }
            // https://www.googleapis.com/books/v1/volumes?q=inauthor:%22Anne+Bishop%22&langRestrict=en&startIndex=0&maxResults=40
        }
        foreach ($authors as $id => $author) {
            if (!empty($author['link'])) {
                $authors[$id]['entityId'] = WikiMatch::entity($author['link']);
            }
        }
        // Return info
        return ['authors' => $authors, 'authorId' => $authorId, 'matched' => $matched];
    }

    /**
     * Summary of books
     * @param int|null $authorId
     * @param int|null $bookId
     * @param string|null $matchId
     * @return array<mixed>|null
     */
    public function books($authorId, $bookId, $matchId)
    {
        global $gErrorArray;

        $authors = $this->db->getAuthors($authorId);
        if (empty($authorId) && empty($bookId)) {
            //$gErrorArray[$this->dbFileName] = "Please specify authorId and/or bookId";
            //return null;
            $authorId = array_keys($authors)[0];
        }

        if (count($authors) < 1) {
            $gErrorArray[$this->dbFileName] = "Please specify a valid authorId";
            return null;
        }
        $author = $authors[$authorId];

        // Find match on Wikidata
        $wikimatch = new WikiMatch($this->cacheDir);
        //$entityId = $wikimatch->findAuthorId($author);

        $matched = null;
        if (!empty($bookId)) {
            $books = $this->db->getBooks($bookId);
            /**
            if (!empty($entityId)) {
                // Find works from author
                $propId = 'P50';
                $results = $wikimatch->searchBy($propId, $entityId);
                $matched = $results->toArray();
            } else {
                $results = $wikimatch->search($books[0]['title']);
                $matched = $results->toArray();
            }
             */
            $query = $books[$bookId]['title'];
            $matched = $wikimatch->findWorksByTitle($query);
        } else {
            $books = $this->db->getBooksByAuthor($authorId);
            $matched = $wikimatch->findWorksByAuthor($author);
            //$matched = array_merge($matched, $wikimatch->findWorksByName($author));
        }
        $authorList = $this->getAuthorList();

        // Return info
        return ['books' => $books, 'authorId' => $authorId, 'author' => $authors[$authorId], 'bookId' => $bookId, 'matched' => $matched, 'authors' => $authorList];
    }

    /**
     * Summary of series
     * @param int|null $authorId
     * @param int|null $seriesId
     * @param string|null $matchId
     * @return array<mixed>|null
     */
    public function series($authorId, $seriesId, $matchId)
    {
        global $gErrorArray;

        $authors = $this->db->getAuthors($authorId);
        if (empty($authorId) && empty($seriesId)) {
            //$gErrorArray[$this->dbFileName] = "Please specify authorId and/or seriesId";
            //return null;
            $authorId = array_keys($authors)[0];
        }

        if (count($authors) < 1) {
            $gErrorArray[$this->dbFileName] = "Please specify a valid authorId";
            return null;
        }
        $author = $authors[$authorId];

        // Find match on Wikidata
        $wikimatch = new WikiMatch($this->cacheDir);
        //$entityId = $wikimatch->findAuthorId($author);

        $matched = null;
        if (!empty($seriesId)) {
            $series = $this->db->getSeries($seriesId);
            $query = $series[$seriesId]['title'];
            $matched = $wikimatch->findSeriesByTitle($query);
        } else {
            $series = $this->db->getSeriesByAuthor($authorId);
            if (count($series) > 0) {
                $matched = $wikimatch->findSeriesByAuthor($author);
            }
        }
        $authorList = $this->getAuthorList();

        // Return info
        return ['series' => $series, 'authorId' => $authorId, 'author' => $authors[$authorId], 'seriesId' => $seriesId, 'matched' => $matched, 'authors' => $authorList];
    }

    /**
     * Summary of wikidata
     * @param string|null $entityId
     * @param int|null $authorId
     * @param string|null $query
     * @return array<mixed>
     */
    public function wikidata($entityId = null, $authorId = null, $query = null)
    {
        $entity = [];
        // Get entity on Wikidata
        if (!empty($authorId) && empty($entityId)) {
            $authors = $this->db->getAuthors($authorId);
            $author = $authors[$authorId];
            $wikimatch = new WikiMatch($this->cacheDir);
            $entityId = $wikimatch->findAuthorId($author);
        }
        if (!empty($entityId)) {
            $wikimatch = new WikiMatch($this->cacheDir);
            $entity = $wikimatch->getEntity($entityId);
        }
        $authorList = $this->getAuthorList();

        // Return info
        return ['entity' => $entity, 'entityId' => $entityId, 'authorId' => $authorId, 'authors' => $authorList];
    }

    /**
     * Summary of getAuthorList
     * @return array<mixed>
     */
    protected function getAuthorList()
    {
        $authorList = [];
        $authors = $this->db->getAuthors();
        foreach ($authors as $authorId => $author) {
            $authorList[$authorId] = $author['name'];
        }
        return $authorList;
    }

    /**
     * Summary of hasAction
     * @param string $action
     * @return bool
     */
    public static function hasAction($action)
    {
        if (method_exists(static::class, $action)) {
            return true;
        }
        return false;
    }

    /**
     * Recursive get files
     *
     * @param string $inPath Base directory to search in
     * @param string $inPattern Search pattern
     * @return array<string>
     */
    public static function getFiles($inPath = '', $inPattern = '*.epub')
    {
        $res = [];

        // Check path
        if (!is_dir($inPath)) {
            return $res;
        }

        // Get the list of directories
        if (substr($inPath, -1) != DIRECTORY_SEPARATOR) {
            $inPath .= DIRECTORY_SEPARATOR;
        }

        // Add files from the current directory
        $files = glob($inPath . $inPattern, GLOB_MARK | GLOB_NOSORT);
        foreach ($files as $item) {
            if (substr($item, -1) == DIRECTORY_SEPARATOR) {
                continue;
            }
            $res[] = $item;
        }

        // Scan sub directories
        $paths = glob($inPath . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
        foreach ($paths as $path) {
            $res = array_merge($res, static::getFiles($path, $inPattern));
        }

        sort($res);

        return $res;
    }
}
