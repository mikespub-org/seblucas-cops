<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;

class HtmlRenderer extends BaseRenderer
{
    public static string $handler = "index";

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
            'opds_url'              => Route::link("feed"),
            'customHeader'          => '',
            'template'              => $request->template(),
            'server_side_rendering' => $request->render(),
            'current_css'           => Route::path($request->style()),
            'favico'                => Route::path(Config::get('icon')),
            'assets'                => Route::path(Config::get('assets')),
            'images'                => Route::path('images'),
            'resources'             => Route::path('resources'),
            'templates'             => Route::path('templates'),
            'basedir'               => Route::path('.'),
            'getjson_url'           => JsonRenderer::getCurrentUrl($request),
        ];
        if (preg_match('/Kindle/', $request->agent())) {
            $data['customHeader'] = '<style media="screen" type="text/css"> html { font-size: 75%; -webkit-text-size-adjust: 75%; -ms-text-size-adjust: 75%; }</style>';
        }
        return $data;
    }

    /**
     * Summary of render
     * @param Request $request
     * @return string
     */
    public function render($request)
    {
        $data = $this->getTemplateData($request);
        if (in_array($request->template(), Config::get('twig_templates'))) {
            $template = new TwigTemplate($request);
            return $template->renderPage($data);
        }
        $template = new DotPHPTemplate($request);
        return $template->renderPage($data);
    }
}
