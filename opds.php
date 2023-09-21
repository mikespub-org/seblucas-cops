<?php
/**
 * COPS (Calibre OPDS PHP Server) main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 *
 */
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
//use SebLucas\Cops\Output\OPDSRenderer;
use SebLucas\Cops\Output\KiwilanOPDS as OPDSRenderer;
use SebLucas\Cops\Pages\PageId;
use Kiwilan\Opds\OpdsResponse;

require_once __DIR__ . '/config.php';

if (!class_exists('Kiwilan\Opds\OpdsResponse')) {
    echo 'This endpoint is an example for development only';
    return;
}

// try out route urls
Config::set('use_route_urls', true);

$request = new Request();
$page = $request->get('page', PageId::INDEX);
$query = $request->get('query');  // 'q' by default for php-opds
if ($query) {
    $page = PageId::OPENSEARCH_QUERY;
}

if (Config::get('fetch_protect') == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        $_SESSION['connected'] = 0;
    }
}

$OPDSRender = new OPDSRenderer();

switch ($page) {
    case PageId::OPENSEARCH :
        $response = $OPDSRender->getOpenSearch($request);
        break;
    default:
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();
        $response = $OPDSRender->render($currentPage, $request);
}

foreach ($response->getHeaders() as $type => $value) {
    header($type.': '.$value);
}
http_response_code($response->getStatus());

echo $response->getContents();
