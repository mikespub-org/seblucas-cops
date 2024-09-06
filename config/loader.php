<?php
/**
 * Epub loader config
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
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
$gConfig['app_name'] = 'Epub Loader';

/**
 * Application version
 */
$gConfig['version'] = '3.0';

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
 * Databases infos
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
 */
$gConfig['actions'] = [];
$gConfig['actions']['csv_export'] = 'Csv export';
// only if you start without an existing calibre database
//$gConfig['actions']['db_load'] = 'Create database';
$gConfig['actions']['authors'] = 'List authors in database';
$gConfig['actions']['wd_author'] = 'Check authors in database';
$gConfig['actions']['wd_books'] = 'Check books for author';
$gConfig['actions']['wd_series'] = 'Check series for author';
$gConfig['actions']['wd_entity'] = 'Check Wikidata entity';
$gConfig['actions']['gb_books'] = 'Search Google Books';
$gConfig['actions']['gb_volume'] = 'Search Google Books Volume';
$gConfig['actions']['ol_author'] = 'Find OpenLibrary author';
$gConfig['actions']['ol_books'] = 'Find OpenLibrary books';
$gConfig['actions']['ol_work'] = 'Find OpenLibrary work';
$gConfig['actions']['notes'] = 'Get Calibre Notes';
$gConfig['actions']['resource'] = 'Get Calibre Resource';
$gConfig['actions']['hello_world'] = 'Example: Hello, World - see app/example.php';
//$gConfig['actions']['goodbye'] = 'Example: Goodbye - see app/example.php';

return $gConfig;
