<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Handlers\FeedHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class HtmlRenderer extends BaseRenderer
{
    public const ROUTE_FEED = FeedHandler::HANDLER;

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
            'opds_url'              => FeedHandler::route(self::ROUTE_FEED),
            'customHeader'          => '',
            'template'              => $request->template(),
            'server_side_rendering' => $request->render(),
            'current_css'           => $this->getPath($request->style()),
            'favico'                => $this->getPath(Config::get('icon')),
            'assets'                => $this->getPath(Config::get('assets')),
            'images'                => $this->getPath('images'),
            'resources'             => $this->getPath('resources'),
            'templates'             => $this->getPath('templates'),
            'basedir'               => $this->getPath('.'),
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
