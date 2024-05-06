<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Template\doT;

class HtmlRenderer
{
    public static string $endpoint = Config::ENDPOINT["index"];

    /**
     * Summary of getTemplateData
     * @param Request $request
     * @return array<string, mixed>
     */
    public function getTemplateData($request)
    {
        $data = [
            'title'                 => Config::get('title_default'),
            'version'               => Config::VERSION,
            'opds_url'              => Route::url(Config::ENDPOINT["feed"]),
            'customHeader'          => '',
            'template'              => $request->template(),
            'server_side_rendering' => $request->render(),
            'current_css'           => Route::url($request->style()),
            'favico'                => Route::url(Config::get('icon')),
            'assets'                => Route::url(Config::get('assets')),
            'images'                => Route::url('images'),
            'resources'             => Route::url('resources'),
            'templates'             => Route::url('templates'),
            'basedir'               => Route::url('.'),
            'getjson_url'           => JsonRenderer::getCurrentUrl($request),
        ];
        if (preg_match('/Kindle/', $request->agent())) {
            $data['customHeader'] = '<style media="screen" type="text/css"> html { font-size: 75%; -webkit-text-size-adjust: 75%; -ms-text-size-adjust: 75%; }</style>';
        }
        return $data;
    }

    /**
     * Summary of getTwigEnvironment
     * @param string|string[] $templateDir
     * @return \Twig\Environment
     */
    public function getTwigEnvironment($templateDir = 'templates/twigged')
    {
        $loader = new \Twig\Loader\FilesystemLoader($templateDir);
        $twig = new \Twig\Environment($loader);
        $function = new \Twig\TwigFunction('str_format', function ($format, ...$args) {
            //return str_format($format, ...$args);
            return Format::str_format($format, ...$args);
        });
        $twig->addFunction($function);
        $assets = Route::url(Config::get('assets'));
        $function = new \Twig\TwigFunction('asset', function ($file) use ($assets) {
            return $assets . '/' . $file . '?v=' . Config::VERSION;
        });
        $twig->addFunction($function);

        return $twig;
    }

    /**
     * Summary of renderTwigTemplate
     * @param Request $request
     * @return string
     */
    public function renderTwigTemplate($request)
    {
        // @todo support other Twig template directories too
        $twig = $this->getTwigEnvironment('templates/twigged');
        $data = $this->getTemplateData($request);
        if ($request->render()) {
            // Get the page data
            $data['page_it'] = JsonRenderer::getJson($request, true);
            if ($data['title'] != $data['page_it']['title']) {
                $data['title'] .= ' - ' . $data['page_it']['title'];
            }
        }
        return $twig->render('index.html', ['it' => $data]);
    }

    /**
     * Summary of getDotTemplate
     * @param string $templateFile
     * @return \Closure
     */
    public function getDotTemplate($templateFile)
    {
        // production mode was required here for issue seblucas/cops#392
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        $headcontent = file_get_contents($templateFile);
        $template = new doT();
        $dot = $template->template($headcontent, null);

        return $dot;
    }

    /**
     * Summary of renderDotTemplate
     * @param Request $request
     * @return string
     */
    public function renderDotTemplate($request)
    {
        $dot = $this->getDotTemplate('templates/' . $request->template() . '/file.html');
        $data = $this->getTemplateData($request);
        if ($request->render()) {
            // Get the page data
            $page_it = JsonRenderer::getJson($request, true);
            if ($data['title'] != $page_it['title']) {
                $data['title'] .= ' - ' . $page_it['title'];
            }
            $output = $dot($data);
            $output .= "<body>\n";
            $output .= Format::serverSideRender($page_it, $request->template());
            $output .= "</body>\n</html>\n";
            return $output;
        }
        $output = $dot($data);
        $output .= "<body>\n</body>\n</html>\n";
        return $output;
    }

    /**
     * Summary of render
     * @param Request $request
     * @return string
     */
    public function render($request)
    {
        if ($request->template() == 'twigged') {
            return $this->renderTwigTemplate($request);
        }
        return $this->renderDotTemplate($request);
    }
}
