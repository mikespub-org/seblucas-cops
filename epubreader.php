<?php
/**
 * COPS (Calibre OPDS PHP Server) epub reader
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Config;
use SebLucas\EPubMeta\EPub;
use SebLucas\Template\doT;

use function SebLucas\Cops\Request\getURLParam;

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/base.php';
/** @var array $config */

$idData = (int)getURLParam('data', null);
$add = 'data=' . $idData . '&';
if (!is_null(getURLParam('db'))) {
    $add .= 'db=' . getURLParam('db') . '&';
}
$myBook = Book::getBookByDataId($idData);

$book = new EPub($myBook->getFilePath('EPUB', $idData));
$book->initSpineComponent();

$components = implode(', ', array_map(function ($comp) {
    return "'" . $comp . "'";
}, $book->components()));
$contents = implode(', ', array_map(function ($content) {
    return "{title: '" . addslashes($content['title']) . "', src: '". $content['src'] . "'}";
}, $book->contents()));

$endpoint = Config::ENDPOINT["epubfs"];

$data = [
    'title'      => $myBook->title,
    'version'    => Config::VERSION,
    'components' => $components,
    'contents'   => $contents,
    'link'       => $endpoint . "?" . $add .  "comp=",
];

header('Content-Type: text/html;charset=utf-8');

$filecontent = file_get_contents('templates/epubreader.html');
$template = new doT();
$dot = $template->template($filecontent, null);
echo($dot($data));
