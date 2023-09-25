<?php
/**
 * COPS (Calibre OPDS PHP Server) download all books of a series or author
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 *
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Downloader;

require_once __DIR__ . '/config.php';

if (empty(Config::get('download_page')) && empty(Config::get('download_series')) && empty(Config::get('download_author'))) {
    echo 'Downloads by page, series or author are disabled in config';
    exit();
}

$request = new Request();

if (Config::get('fetch_protect') == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        // this will call exit()
        $request->notFound();
    }
}

$downloader = new Downloader($request);

if ($downloader->isValid()) {
    // disable nginx buffering by default
    header('X-Accel-Buffering: no');
    $downloader->download();
} else {
    echo "Invalid download: " . $downloader->getMessage();
}
exit();
