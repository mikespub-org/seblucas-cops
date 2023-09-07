<?php
/**
 * Epub loader application action: load ebooks into calibre databases
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 */

use Marsender\EPubLoader\CalibreDbLoader;
use Wikidata\Wikidata;

/** @var array<mixed> $dbConfig */
/** @var array<mixed> $gErrorArray */

defined('DEF_AppName') or die('Restricted access');

global $dbConfig;
global $gErrorArray;

$authorId = isset($_GET['authorId']) ? (int)$_GET['authorId'] : null;

// Init database file
$dbPath = $dbConfig['db_path'];
$calibreFileName = $dbPath . DIRECTORY_SEPARATOR . 'metadata.db';
try {
    // Open the database
    $db = new CalibreDbLoader($calibreFileName);
    // List the authors
    $authors = $db->getAuthors();
    $query = null;
    if (!is_null($authorId)) {
        foreach ($authors as $author) {
            if ($author['id'] == $authorId) {
                $query = $author['name'];
            }
        }
    }
    $matched = null;
    if (!is_null($query)) {
        // Find match on Wikidata
        $wikidata = new Wikidata();
        $results = $wikidata->search($query);
        $matched = $results->toArray();
    }
    // Return info
    return ['authors' => $authors, 'authorId' => $authorId, 'matched' => $matched];
} catch (Exception $e) {
    $gErrorArray[$calibreFileName] = $e->getMessage();
}
