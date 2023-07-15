<?php
/**
 * COPS (Calibre OPDS PHP Server) main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
use SebLucas\Cops\Output\OPDSRenderer;
use SebLucas\Cops\Pages\Page;

use function SebLucas\Cops\Request\getURLParam;
use function SebLucas\Cops\Request\initURLParam;

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/base.php';
/** @var array $config */

initURLParam();

header('Content-Type:application/xml');
$page = getURLParam('page', Page::INDEX);
$query = getURLParam('query');
$n = getURLParam('n', '1');
if ($query) {
    $page = Page::OPENSEARCH_QUERY;
}
$qid = getURLParam('id');

if ($config ['cops_fetch_protect'] == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        $_SESSION['connected'] = 0;
    }
}

$OPDSRender = new OPDSRenderer();

switch ($page) {
    case Page::OPENSEARCH :
        echo $OPDSRender->getOpenSearch();
        return;
    default:
        $currentPage = Page::getPage($page, $qid, $query, $n);
        $currentPage->InitializeContent();
        echo $OPDSRender->render($currentPage);
        return;
}
