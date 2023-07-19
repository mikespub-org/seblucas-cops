<?php
/**
 * COPS (Calibre OPDS PHP Server) epub reader
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

use SebLucas\Cops\Output\EPubReader;

use function SebLucas\Cops\Request\getURLParam;
use function SebLucas\Cops\Request\notFound;

require_once dirname(__FILE__) . '/config.php';
/** @var array $config */

$idData = (int) getURLParam('data', null);
if (empty($idData)) {
    notFound();
    exit;
}

header('Content-Type: text/html;charset=utf-8');

echo EPubReader::getReader($idData);
