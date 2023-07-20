<?php
/**
 * COPS (Calibre OPDS PHP Server) main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\OPDSRenderer;
use SebLucas\Cops\Pages\Page;

require_once dirname(__FILE__) . '/config.php';
/** @var array $config */

$request = new Request();
$page = $request->get('page', Page::INDEX);
$query = $request->get('query');
$n = $request->get('n', '1');
if ($query) {
    $page = Page::OPENSEARCH_QUERY;
}
$qid = $request->get('id');

if ($config ['cops_fetch_protect'] == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        $_SESSION['connected'] = 0;
    }
}

header('Content-Type:application/xml');

$OPDSRender = new OPDSRenderer();

switch ($page) {
    case Page::OPENSEARCH :
        echo $OPDSRender->getOpenSearch($request);
        return;
    default:
        $currentPage = Page::getPage($page, $qid, $query, $n, $request);
        $currentPage->InitializeContent();
        echo $OPDSRender->render($currentPage, $request);
        return;
}
