<?php
/**
 * COPS (Calibre OPDS PHP Server) epub reader
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
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

header('Content-Type: text/html;charset=utf-8');

echo EPubReader::getReader($idData, $request);
