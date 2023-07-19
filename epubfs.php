<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Output\EPubReader;

use SebLucas\Cops\Output\EPubReader;
use Exception;

use function SebLucas\Cops\Request\getURLParam;
use function SebLucas\Cops\Request\notFound;

require_once dirname(__FILE__) . '/config.php';

if (php_sapi_name() === 'cli') {
    return;
}

$idData = (int) getURLParam('data', null);
if (empty($idData)) {
    notFound();
    return;
}
$component = getURLParam('comp', null);
if (empty($component)) {
    notFound();
    return;
}

try {
    $data = EPubReader::getContent($idData, $component);

    $expires = 60*60*24*14;
    header('Pragma: public');
    header('Cache-Control: maxage='.$expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');

    echo $data;
} catch (Exception $e) {
    error_log($e);
    notFound();
}
