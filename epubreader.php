<?php
/**
 * COPS (Calibre OPDS PHP Server) epub reader with monocle
 * URL format: epubreader.php?data={idData}&version={version}
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\EPubReader;

require_once __DIR__ . '/config.php';

$request = new Request();
$idData = $request->getId('data');
if (empty($idData)) {
    // this will call exit()
    $request->notFound();
}

try {
    header('Content-Type: text/html;charset=utf-8');
    echo EPubReader::getReader($idData, $request);
} catch (Exception $e) {
    error_log($e);
    $request->notFound();
}
