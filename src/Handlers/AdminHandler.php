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
        $cachePath = Config::get('thumbnail_cache_directory');
        [$count, $size] = $this->getCacheSize($cachePath);
        $size = $size > 0 ? sprintf('%.3f', $size / 1024 / 1024) : $size;
        $updated = $this->getUpdatedConfig();
        $content = 'Admin - TODO';
        $content .= '<ol>';
        $content .= '<li><a href="./admin/clearcache">Clear Thumbnail Cache</a> with ' . $count . ' files (' . $size . ' MB)</li>';
        $content .= '<li><a href="./admin/config">Edit Local Config</a> with ' . count($updated) . ' modified config settings</li>';
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
        $cachePath = Config::get('thumbnail_cache_directory');
        if (empty($cachePath)) {
            $content = 'Clear Thumbnail Cache - no cache directory';
        } elseif (!is_dir($cachePath)) {
            $content = 'Clear Thumbnail Cache - invalid cache directory';
        } else {
            [$count, $size] = $this->getCacheSize($cachePath, true);
            $size = $size > 0 ? sprintf('%.3f', $size / 1024 / 1024) : $size;
            $content = 'Clear Thumbnail Cache - DONE with ' . $count . ' files (' . $size . ' MB)';
        }
        $data = [
            'title' => 'Clear Cache',
            'content' => $content,
            'link' => self::route('admin'),
            'home' => 'Admin',
        ];
        return $response->setContent(Format::template($data, $this->template));
    }

    /**
     * Summary of getCacheSize
     * @param string $cachePath
     * @param bool $delete default false
     * @return array{0: int, 1: int}
     * @see \SebLucas\Cops\Calibre\Cover::getThumbnailCachePath()
     */
    public function getCacheSize($cachePath, $delete = false)
    {
        if (empty($cachePath)) {
            return [0, 0];
        }
        if (!is_dir($cachePath)) {
            return [-1, -1];
        }
        $count = 0;
        $size = 0;
        // cache/db-0/0/12/34567-89ab-cdef-0123-456789abcdef-...jpg
        foreach (glob($cachePath . '/db-*/?/??/*.{jpg,png}', \GLOB_BRACE) as $filePath) {
            if ($delete && unlink($filePath)) {
                continue;
            }
            $count += 1;
            $size += filesize($filePath);
        }
        return [$count, $size];
    }

    /**
     * Summary of handleUpdateConfig
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handleUpdateConfig($request, $response)
    {
        $default = $this->getDefaultConfig();
        $local = $this->getLocalConfig();
        $updated = [];
        foreach ($local as $key => $value) {
            if (!array_key_exists($key, $default) || $default[$key] !== $value) {
                $updated[$key] = $value;
            }
        }
        $original = array_diff_assoc($local, $updated);
        $content = 'Edit Local Config - TODO with ' . count($updated) . ' modified config settings';
        $content .= '<p>Modified:</p><pre>' . json_encode($updated, JSON_PRETTY_PRINT) . '</pre>';
        $content .= '<p>Unchanged:</p><pre>' . json_encode($original, JSON_PRETTY_PRINT) . '</pre>';
        $content .= '<p>Local:</p><pre>' . json_encode($local, JSON_PRETTY_PRINT) . '</pre>';
        $content .= '<p>Default:</p><pre>' . json_encode($default, JSON_PRETTY_PRINT) . '</pre>';
        $data = [
            'title' => 'Edit Local Config',
            'content' => $content,
            'link' => self::route('admin'),
            'home' => 'Admin',
        ];
        return $response->setContent(Format::template($data, $this->template));
    }

    /**
     * Summary of getDefaultConfig
     * @return array<string, mixed>
     */
    protected function getDefaultConfig()
    {
        $config = [];
        $filepath = dirname(__DIR__, 2) . '/config/default.php';
        require $filepath;
        return $config;
    }

    /**
     * Summary of getLocalConfig
     * @return array<string, mixed>
     */
    protected function getLocalConfig()
    {
        $config = [];
        $filepath = dirname(__DIR__, 2) . '/config/local.php';
        if (file_exists($filepath)) {
            require $filepath;
        }
        return $config;
    }

    /**
     * Summary of getUpdatedConfig
     * @param ?array<string, mixed> $local
     * @param ?array<string, mixed> $default
     * @return array<string, mixed>
     */
    protected function getUpdatedConfig($local = null, $default = null)
    {
        $local ??= $this->getLocalConfig();
        $default ??= $this->getDefaultConfig();
        $updated = [];
        foreach ($local as $key => $value) {
            if (!array_key_exists($key, $default) || $default[$key] !== $value) {
                $updated[$key] = $value;
            }
        }
        return $updated;
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
