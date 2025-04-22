<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\Response;

/**
 * Summary of AdminHandler - @todo
 */
class AdminHandler extends BaseHandler
{
    public const HANDLER = "admin";
    public const PREFIX = "/admin";
    public const PARAMLIST = ["action"];

    protected string $template = 'templates/admin.html';

    public static function getRoutes()
    {
        return [
            "admin-clearcache" => ["/admin/clearcache", ["action" => "clearcache"]],
            "admin-config" => ["/admin/config", ["action" => "config"], ["GET", "POST"]],
            "admin-action" => ["/admin/{action:.*}", [], ["GET", "POST"]],
            "admin" => ["/admin"],
        ];
    }

    public function handle($request)
    {
        $admin = Config::get('enable_admin', false);
        if (empty($admin)) {
            return Response::redirect(PageHandler::link(['admin' => 0]));
        }
        if (is_string($admin) && $admin !== $request->getUserName()) {
            return Response::redirect(PageHandler::link(['admin' => 1]));
        }
        if (is_array($admin) && !in_array($request->getUserName(), $admin)) {
            return Response::redirect(PageHandler::link(['admin' => 2]));
        }

        $response = new Response();

        $action = $request->get('action', 'none');
        switch ($action) {
            case 'none':
                return $this->handleAdmin($request, $response);
            case 'clearcache':
                return $this->handleClearCache($request, $response);
            case 'config':
                return $this->handleUpdateConfig($request, $response);
            default:
                return $this->handleAction($request, $response);
        }
    }

    /**
     * Summary of handleAdmin
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handleAdmin($request, $response)
    {
        $content = 'Admin - TODO';
        $content .= '<ol>';
        $content .= '<li><a href="./admin/clearcache">Clear Cache</a></li>';
        $content .= '<li><a href="./admin/config">Update Config</a></li>';
        $content .= '<li><a href="./admin/action">Admin Action</a></li>';
        $content .= '</ol>';
        $data = [
            'title' => 'Admin Features',
            'content' => $content,
            'link' => PageHandler::link(),
            'home' => 'Home',
        ];
        return $response->setContent(Format::template($data, $this->template));
    }

    /**
     * Summary of handleClearCache
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handleClearCache($request, $response)
    {
        $data = [
            'title' => 'Clear Cache',
            'content' => 'Clear Thumbnail Cache - TODO',
            'link' => self::route('admin'),
            'home' => 'Admin',
        ];
        return $response->setContent(Format::template($data, $this->template));
    }

    /**
     * Summary of handleUpdateConfig
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handleUpdateConfig($request, $response)
    {
        $data = [
            'title' => 'Update Config',
            'content' => 'Update Config - TODO',
            'link' => self::route('admin'),
            'home' => 'Admin',
        ];
        return $response->setContent(Format::template($data, $this->template));
    }

    /**
     * Summary of handleAction
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handleAction($request, $response)
    {
        $data = [
            'title' => 'Admin Action',
            'content' => 'Admin Action - TODO',
            'link' => self::route('admin'),
            'home' => 'Admin',
        ];
        return $response->setContent(Format::template($data, $this->template));
    }
}
