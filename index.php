<?php
/**
 * COPS (Calibre OPDS PHP Server) HTML main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\JSONRenderer;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Template\doT;

require_once __DIR__ . '/config.php';

// If we detect that an OPDS reader try to connect try to redirect to feed.php
if (preg_match('/(Librera|MantanoReader|FBReader|Stanza|Marvin|Aldiko|Moon\+ Reader|Chunky|AlReader|EBookDroid|BookReader|CoolReader|PageTurner|books\.ebook\.pdf\.reader|com\.hiwapps\.ebookreader|OpenBook)/', $_SERVER['HTTP_USER_AGENT'])) {
    header('location: ' . Config::ENDPOINT["feed"]);
    exit();
}

$request = new Request();
$page     = $request->get('page');
$query    = $request->get('query');
$qid      = $request->get('id');
$n        = $request->get('n', 1);
$database = $request->get('db');

// Use the configured home page if needed
if (!isset($page)) {
    $page = PageId::INDEX;
    if (!empty(Config::get('home_page')) && defined('SebLucas\Cops\Pages\PageId::' . Config::get('home_page'))) {
        $page = constant('SebLucas\Cops\Pages\PageId::' . Config::get('home_page'));
    }
    $request->set('page', $page);
}

// Access the database ASAP to be sure it's readable, redirect if that's not the case.
// It has to be done before any header is sent.
Database::checkDatabaseAvailability($database);

if (Config::get('fetch_protect') == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        $_SESSION['connected'] = 0;
    }
}

header('Content-Type:text/html;charset=utf-8');

$data = ['title'                 => Config::get('title_default'),
              'version'               => Config::VERSION,
              'opds_url'              => Config::get('full_url') . Config::ENDPOINT["feed"],
              'customHeader'          => '',
              'template'              => $request->template(),
              'server_side_rendering' => $request->render(),
              'current_css'           => $request->style(),
              'favico'                => Config::get('icon'),
              'assets'                => Config::get('assets'),
              'getjson_url'           => JSONRenderer::getCurrentUrl($request->query())];
if (preg_match('/Kindle/', $request->agent())) {
    $data['customHeader'] = '<style media="screen" type="text/css"> html { font-size: 75%; -webkit-text-size-adjust: 75%; -ms-text-size-adjust: 75%; }</style>';
}
if ($request->template() == 'twigged') {
    $loader = new \Twig\Loader\FilesystemLoader('templates/twigged');
    $twig = new \Twig\Environment($loader);
    $function = new \Twig\TwigFunction('str_format', function ($format, ...$args) {
        //return str_format($format, ...$args);
        return Format::str_format($format, ...$args);
    });
    $twig->addFunction($function);
    $function = new \Twig\TwigFunction('asset', function ($file) {
        return Config::get('assets') . '/' . $file . '?v=' . Config::VERSION;
    });
    $twig->addFunction($function);
    if ($request->render()) {
        // Get the page data
        $data['page_it'] = JSONRenderer::getJson($request, true);
        if ($data['title'] != $data['page_it']['title']) {
            $data['title'] .= ' - ' . $data['page_it']['title'];
        }
    }
    echo $twig->render('index.html', ['it' => $data]);
    return;
}
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
$headcontent = file_get_contents('templates/' . $request->template() . '/file.html');
$template = new doT();
$dot = $template->template($headcontent, null);
if ($request->render()) {
    // Get the page data
    $page_it = JSONRenderer::getJson($request, true);
    if ($data['title'] != $page_it['title']) {
        $data['title'] .= ' - ' . $page_it['title'];
    }
    echo($dot($data));
    echo "<body>\n";

    echo Format::serverSideRender($page_it, $request->template());
    echo "</body>\n</html>\n";
    return;
}
echo($dot($data));
echo "<body>\n";
echo "</body>\n</html>\n";
