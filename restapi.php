<?php
/**
 * COPS (Calibre OPDS PHP Server) REST API endpoint
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 *
 */

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\RestApi;

require_once __DIR__ . '/config.php';

// override splitting authors and books by first letter here?
Config::set('author_split_first_letter', '0');
Config::set('titles_split_first_letter', '0');
//Config::set('titles_split_publication_year', '0');

$request = new Request();
$apiHandler = new RestApi($request);

header('Content-Type:application/json;charset=utf-8');

try {
    echo $apiHandler->getOutput();
} catch (Exception $e) {
    echo json_encode(["Exception" => $e->getMessage()]);
}
