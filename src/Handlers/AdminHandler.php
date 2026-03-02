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
use SebLucas\Cops\Output\TwigTemplate;
use SebLucas\Cops\Calibre\Database;
use Exception;
use JsonException;
use Pdo\Sqlite;

/**
 * Summary of AdminHandler - @todo
 */
class AdminHandler extends BaseHandler
{
    public const HANDLER = "admin";
    public const PREFIX = "/admin";
    public const PARAMLIST = ["action"];

    protected string $templateDir = 'templates/admin';
    /** @var array<string, string> */
    protected array $tooltips = [];

    public static function getRoutes()
    {
        return [
            "admin-clearcache" => ["/admin/clearcache", ["action" => "clearcache"]],
            "admin-config" => ["/admin/config", ["action" => "config"], ["GET", "POST"]],
            "admin-checkbooks" => ["/admin/checkbooks", ["action" => "checkbooks"]],
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
            case 'checkbooks':
                return $this->handleCheckBooks($request, $response);
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
        $writable = $this->isLocalConfigWritable();

        $actions = [];
        $actions[] = [
            'url' => self::route('admin-clearcache'),
            'title' => 'Clear Thumbnail Cache',
            'description' => 'with ' . $count . ' files (' . $size . ' MB)',
        ];
        $config_desc = 'with ' . count($updated) . ' modified config settings';
        if (!$writable) {
            $config_desc .= ' (read-only)';
        }
        $actions[] = [
            'url' => self::route('admin-config'),
            'title' => 'Edit Local Config',
            'description' => $config_desc,
        ];
        $actions[] = [
            'url' => self::route('admin-checkbooks'),
            'title' => 'Check Books',
            'description' => 'Verify that all book files exist on disk.',
        ];
        $actions[] = [
            'url' => self::route('admin-action'),
            'title' => 'Admin Action',
            'description' => 'A placeholder for future actions.',
        ];

        $data = [
            'title' => 'Admin Features',
            'actions' => $actions,
            'link' => PageHandler::link(),
            'home' => 'Home',
        ];
        return $response->setContent($this->getContent($request, $data));
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
        return $response->setContent($this->getContent($request, $data));
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
        $posted = [];
        if ($request->method() == "POST" && !empty($request->postParams)) {
            $posted = $request->postParams;
        }
        $changes = [];
        $errors = 0;
        $default = Config::getDefaultConfig();
        $local = $this->getUpdatedConfig(Config::getLocalConfig(), $default);
        foreach ($posted as $key => $value) {
            if (!array_key_exists($key, $default)) {
                continue;
            }
            if (is_bool($default[$key])) {
                $value = in_array($value, ["on", "1", "true"]);
            } elseif (!is_string($default[$key])) {
                if ($value === "") {
                    $value = $default[$key];
                } else {
                    try {
                        $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException) {
                        $errors += 1;
                        $changes[$key] = '<span style="color: red;">Invalid JSON value!</span>';
                        $value = $local[$key] ?? $default[$key];
                        // send error here
                    }
                }
            }
            // skip unchanged others
            if ($value === $default[$key] && !array_key_exists($key, $local)) {
                continue;
            }
            if (!array_key_exists($key, $local) || $value !== $local[$key]) {
                $changes[$key] = 'Value updated.';
            }
            // update local values based on posted
            $local[$key] = $value;
        }
        $original = [];
        $updated = [];
        foreach ($local as $key => $value) {
            if (array_key_exists($key, $default) && $default[$key] === $value) {
                $original[$key] = $value;
            } else {
                $updated[$key] = $value;
            }
        }
        $others = array_filter($default, function ($key) use ($updated, $original) {
            return !array_key_exists($key, $updated) && !array_key_exists($key, $original);
        }, ARRAY_FILTER_USE_KEY);
        ksort($updated);
        ksort($original);
        ksort($others);

        $writable = $this->isLocalConfigWritable();
        if ($writable && !$errors && !empty($changes)) {
            $this->saveLocalConfig($updated);
        }

        $data = [
            'title' => 'Edit Local Config',
            'writable' => $writable,
            'errors' => $errors,
            'changes' => $changes,
            'updated' => $updated,
            'original' => $original,
            'others' => $others,
            'default' => $default,
            'tooltips' => $this->getTooltips(),
            'files' => Config::listLocalConfigFiles(),
            'link' => self::route('admin'),
            'home' => 'Admin',
        ];
        return $response->setContent($this->getContent($request, $data, 'config.html'));
    }

    /**
     * Summary of getLocalConfigPath
     * @return string
     */
    protected function getLocalConfigPath()
    {
        return dirname(__DIR__, 2) . '/config/local.php';
    }

    /**
     * Summary of isLocalConfigWritable
     * @return bool
     */
    protected function isLocalConfigWritable()
    {
        $filepath = $this->getLocalConfigPath();
        if (!file_exists($filepath) || !is_writable($filepath)) {
            return false;
        }
        return true;
    }

    /**
     * Summary of getUpdatedConfig
     * @param ?array<string, mixed> $local
     * @param ?array<string, mixed> $default
     * @return array<string, mixed>
     */
    protected function getUpdatedConfig($local = null, $default = null)
    {
        $local ??= Config::getLocalConfig();
        $default ??= Config::getDefaultConfig();
        $updated = [];
        foreach ($local as $key => $value) {
            if (!array_key_exists($key, $default) || $default[$key] !== $value) {
                $updated[$key] = $value;
            }
        }
        return $updated;
    }

    /**
     * Summary of saveLocalConfig
     * @param array<string, mixed> $updated
     * @return void
     */
    protected function saveLocalConfig($updated)
    {
        $output = '';
        foreach ($updated as $key => $value) {
            $title = $this->getTooltip($key);
            if (!empty($title)) {
                $output .= "/*\n * ";
                $output .= str_replace("\n", "\n * ", trim($title));
                $output .= "\n */\n";
            }
            $output .= "\$config['$key'] = " . Format::export($value) . ";\n\n";
        }
        $filepath = $this->getLocalConfigPath();
        $header = $this->getConfigHeader();
        file_put_contents($filepath, $header . $output);
    }

    /**
     * Summary of getConfigHeader
     * @return string
     */
    protected function getConfigHeader()
    {
        return '<?php

if (!isset($config)) {
    $config = [];
}

/*
 ***************************************************
 * Please read config/default.php for all possible
 * configuration items
 * For changes in config/default.php see CHANGELOG.md
 ***************************************************
 */

';
    }

    /**
     * Summary of getTooltip
     * @param string $key
     * @return string
     */
    protected function getTooltip($key)
    {
        if (empty($this->tooltips)) {
            $this->getTooltips();
        }
        return $this->tooltips[$key] ?? '';
    }

    /**
     * Summary of getTooltips
     * @return array<string, string>
     */
    protected function getTooltips()
    {
        if (empty($this->tooltips)) {
            require dirname(__DIR__, 2) . '/config/tooltips.php';  // NOSONAR
            /** @var array<string, string> $tooltips */
            $this->tooltips = $tooltips;
        }
        return $this->tooltips;
    }

    /**
     * Summary of handleCheckBooks
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handleCheckBooks($request, $response)
    {
        $i = 0;
        $content = '<strong>Missing Books:</strong><ul>';
        foreach (Database::getDbList() as $name => $database) {
            $content .= "<li>Database $i: $name $database\n<ul>\n";
            try {
                $db = new Sqlite('sqlite:' . Database::getDbFileName($i));
                $result = $db->prepare('select books.path || "/" || data.name || "." || lower (format) as fullpath from data join books on data.book = books.id');
                $result->execute();
                while ($post = $result->fetchObject()) {
                    if (!is_file(Database::getDbDirectory($i) . $post->fullpath)) {
                        $content .= '<li>' . Database::getDbDirectory($i) . $post->fullpath . '</li>';
                    }
                }
            } catch (Exception $e) {
                $content .= '<li>' . $name . ' Exception detail : ' . $e . '</li>';
            }
            $content .= '</ul></li>';
            $i++;
        }
        $content .= '</ul>';
        $data = [
            'title' => 'Check Books',
            'content' => $content,
            'link' => self::route('admin'),
            'home' => 'Admin',
        ];
        return $response->setContent($this->getContent($request, $data));
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
        return $response->setContent($this->getContent($request, $data));
    }

    /**
     * Summary of getContent
     * @param Request $request
     * @param array<string, mixed> $data
     * @param string $name = index.html
     * @return string
     */
    public function getContent($request, $data, $name = 'index.html')
    {
        $request->cookieParams['template'] = basename($this->templateDir);
        $template = new TwigTemplate($request);
        $twig = $template->getTwigEnvironment($this->templateDir);
        $getTypeFunction = new \Twig\TwigFunction('get_type', function ($value) {
            if (is_iterable($value)) {
                return 'array';
            }
            return gettype($value);
        });
        $twig->addFunction($getTypeFunction);
        return $twig->render($name, ['it' => $data]);
    }
}
