<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for OPDS 1.2 feed
 * URL format: feed.php?page={page}&query={query}&...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\OpdsRenderer;
use SebLucas\Cops\Pages\PageId;

require_once __DIR__ . '/config.php';

$request = new Request();
$page = $request->get('page', PageId::INDEX);
$query = $request->get('query');
if ($query) {
    $page = PageId::OPENSEARCH_QUERY;
}
// @todo handle special case of OPDS not expecting filter while HTML does better
$request->set('filter', null);

if (Config::get('fetch_protect') == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        $_SESSION['connected'] = 0;
    }
}

header('Content-Type:application/xml');

$OPDSRender = new OpdsRenderer();

switch ($page) {
    case PageId::OPENSEARCH :
        echo $OPDSRender->getOpenSearch($request);
        return;
    default:
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();
        echo $OPDSRender->render($currentPage, $request);
        return;
}
