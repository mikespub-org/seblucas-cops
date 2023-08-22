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
$idData = (int) $request->get('data', null);
if (empty($idData)) {
    $request->notFound();
    exit;
}

header('Content-Type: text/html;charset=utf-8');

echo EPubReader::getReader($idData, $request);
