<?php
/**
 * Epub loader config
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Didier Corbière <contact@atoll-digital-library.org>
 * @author     mikespub
 */

$gConfig = [];

/**
 * URL endpoint for your application
 */
$gConfig['endpoint'] = $_SERVER['SCRIPT_NAME'] ?? null;

/**
 * Application name
 */
$gConfig['app_name'] = 'EPub Loader';

/**
 * Application version
 * @deprecated 3.5.1 see Config::VERSION
 */
//$gConfig['version'] = '3.5';

/**
 * Admin email
 */
$gConfig['admin_email'] = '';

/**
 * Create Calibre databases ?
 *
 * If true: databases are removed and recreated before loading ebooks
 * If false: append ebooks into databases
 */
$gConfig['create_db'] = false;

/**
 * Specify a cache directory for any Google or Wikidata lookup
 */
$gConfig['cache_dir'] = dirname(__DIR__) . '/tests/cache';

/**
 * Specify a template directory to override the standard templates
 */
//$gConfig['template_dir'] = dirname(__DIR__) . '/templates/twigged/loader';

/**
 * Databases infos - will be re-loaded from $config['calibre_directory']
 *
 * For each database:
 *   name: The database name to display
 *   db_path: The path where to create the database
 *   epub_path: The relative path from db_path where to look for the epub files
 */
$gConfig['databases'] = [];
$gConfig['databases'][] = ['name' => 'Some Books', 'db_path' => dirname(__DIR__) . '/tests/BaseWithSomeBooks', 'epub_path' => '.'];
$gConfig['databases'][] = ['name' => 'One Book', 'db_path' => dirname(__DIR__) . '/tests/BaseWithOneBook', 'epub_path' => '.'];
$gConfig['databases'][] = ['name' => 'Custom Columns', 'db_path' => dirname(__DIR__) . '/tests/BaseWithCustomColumns', 'epub_path' => '.'];

/**
 * Available actions
 * @deprecated 3.3.0 use action groups instead
 */
$gConfig['actions'] = [];

/**
 * Available action groups
 */
$gConfig['groups'] = [];
// only if you start without an existing calibre database
$gConfig['groups']['Import'] = [
    //'db_load' => 'Create Calibre database with available epub files',
    //'csv_import' => 'Import CSV records into new Calibre database',
    //'json_import' => 'Import JSON files from Lookup into new Calibre database',
];
$gConfig['groups']['Export'] = [
    'csv_export' => 'Export CSV records with available epub files',
    'csv_dump' => 'Dump CSV records from Calibre database',
];
$gConfig['groups']['Lookup'] = [
    'authors' => 'Authors in database',
    'wd_author' => 'WikiData authors',
    'wd_books' => 'WikiData books for author',
    'wd_series' => 'WikiData series for author',
    'gb_books' => 'Google Books for author',
    'ol_author' => 'OpenLibrary authors',
    'ol_books' => 'OpenLibrary books for author',
    'gr_author' => 'GoodReads authors',
    'gr_books' => 'GoodReads books for author',
    'gr_series' => 'GoodReads series',
    'caches' => 'Cache statistics',
];
// internal actions are not shown on the main menu
$gConfig['groups']['Internal'] = [
    'books' => 'Books in database',
    'series' => 'Series in database',
    'test' => 'Test action (not visible)',
    'wd_entity' => 'WikiData entity',
    'gb_volume' => 'Google Books volume',
    'ol_work' => 'OpenLibrary work',
    'resource' => 'Get Calibre Resource',
];
$gConfig['groups']['Extra'] = [
    'notes' => 'Get Calibre Notes',
    // update metadata in epub files
    //'meta' => 'EPub Metadata App',
    'hello_world' => 'Example: Hello, World - see app/example.php',
    // disable any other actions you don't want to use via COPS
    //'goodbye' => 'Example: Goodbye - see app/example.php',
];

return $gConfig;
