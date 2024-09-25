<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Output\Format;
use SebLucas\Cops\Output\Response;
use Exception;
use PDO;

/**
 * Summary of CheckHandler
 */
class CheckHandler extends BaseHandler
{
    public const HANDLER = "check";

    public static function getRoutes()
    {
        return [
            "/check/{more:.*}" => [static::PARAM => static::HANDLER],
            "/check" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        $more = $request->get('more');
        if ($more) {
            return $this->handleMore($request);
        }

        try {
            return $this->checkConfig($request);
        } catch (Exception $e) {
            echo "<h2>Error in normal handling:</h2>";
            echo "<h4>" . $e->getMessage() . "<h4><br/>";
            include 'checkconfig.php';
        }
    }

    /**
     * Summary of checkConfig
     * @param Request $request
     * @return Response
     */
    public function checkConfig($request)
    {
        $data = [];
        $data['style'] = Route::path($request->style());
        $data['err']   = $request->get('err', -1);
        $data['error'] = $this->getError($data['err']);
        $data['phpversion'] = $this->getPhpVersion();
        foreach ($this->getExtensions() as $extension => $message) {
            $data['extension_' . $extension] = $message;
        }
        $data['baseurl'] = $this->getBaseURL($request);
        $data['render'] = $this->getRender($request);
        $data['agent'] = $this->getAgent($request);
        $data['full']  = $request->get('full');
        $data['databases'] = $this->getDatabases($data['full']);

        $template = dirname(__DIR__, 2) . '/templates/checkconfig.html';

        $response = new Response('text/html;charset=utf-8');
        return $response->setContent(Format::template($data, $template));
    }

    /**
     * Summary of getMessage
     * @param string $title
     * @param string $message
     * @return string
     */
    public function getMessage($title, $message)
    {
        return '
        <article class="frontpage">
            <h2>' . $title . '</h2>
            <h4>' . $message . '</h4>
        </article>';
    }

    /**
     * Summary of getError
     * @param mixed $err
     * @return string
     */
    public function getError($err)
    {
        switch ($err) {
            case 1:
                $title = 'You\'ve been redirected because COPS is not configured properly';
                $message = 'Database error';
                return $this->getMessage($title, $message);
        }
        return '';
    }

    /**
     * Summary of getPhpVersion
     * @return string
     */
    public function getPhpVersion()
    {
        if (defined('PHP_VERSION_ID')) {
            if (PHP_VERSION_ID >= 80200) {
                return 'OK (' . PHP_VERSION . ')';
            }
            return 'Please install PHP >= 8.2 (' . PHP_VERSION . ')';
        }
        return 'Please install PHP >= 8.2';
    }

    /**
     * Summary of getExtensions
     * @return array<string, string>
     */
    public function getExtensions()
    {
        $extensions = [];
        if (extension_loaded('gd') && function_exists('gd_info')) {
            $extensions['gd'] = 'OK';
        } else {
            $extensions['gd'] = 'Please install the php-gd extension and make sure it\'s enabled';
        }
        if (extension_loaded('pdo_sqlite')) {
            $extensions['sqlite'] = 'OK';
        } else {
            $extensions['sqlite'] = 'Please install the php-sqlite / php-sqlite3 extension and make sure it\'s enabled';
        }
        if (extension_loaded('libxml')) {
            $extensions['libxml'] = 'OK';
        } else {
            $extensions['libxml'] = 'Please make sure libxml is enabled';
        }
        if (extension_loaded('dom')) {
            $extensions['dom'] = 'OK';
        } else {
            $extensions['dom'] = 'Please install the php-xml extension and make sure DOM is enabled';
        }
        if (extension_loaded('xmlwriter')) {
            $extensions['xmlwriter'] = 'OK';
        } else {
            $extensions['xmlwriter'] = 'Please install the php-xml extension and make sure XMLWriter is enabled';
        }
        if (extension_loaded('json')) {
            $extensions['json'] = 'OK';
        } else {
            $extensions['json'] = 'Please install the php-json extension and make sure it\'s enabled';
        }
        if (extension_loaded('mbstring')) {
            $extensions['mbstring'] = 'OK';
        } else {
            $extensions['mbstring'] = 'Please install the php-mbstring extension and make sure it\'s enabled';
        }
        if (extension_loaded('intl')) {
            $extensions['intl'] = 'OK';
        } else {
            $extensions['intl'] = 'Please install the php-intl extension and make sure it\'s enabled';
        }
        if (class_exists('Normalizer', $autoload = false)) {
            $extensions['Normalizer'] = 'OK';
        } else {
            $extensions['Normalizer'] = 'Please make sure intl is enabled in your php.ini';
        }
        if (extension_loaded('zlib')) {
            $extensions['zlib'] = 'OK';
        } else {
            $extensions['zlib'] = 'Please make sure zlib is enabled';
        }
        return $extensions;
    }

    /**
     * Summary of getBaseURL
     * @param Request $request
     * @return string
     */
    public function getBaseURL($request)
    {
        $base = dirname((string) $request->script());
        if (!str_ends_with($base, '/')) {
            $base .= '/';
        }
        $result = '';
        $result .= 'Base URL detected by the script: ' . $base . '<br>';
        $result .= 'Full URL specified in $config[\'cops_full_url\']: ' . Config::get('full_url') . '<br>';
        if (Route::hasTrustedProxies()) {
            $result .= 'Trusted proxies configured: ' . Config::get('trusted_proxies') . '<br>';
            $result .= 'Trusted headers configured: ' . json_encode(Config::get('trusted_headers')) . '<br>';
            foreach (Config::get('trusted_headers') as $name) {
                $header = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
                $result .= $header . ': ' . ($request->server($header) ?? '') . '<br>';
            }
            $result .= 'Base URL via trusted proxies: ' . Route::base() . '<br>';
        }
        $result .= 'REMOTE_ADDR: ' . ($request->server('REMOTE_ADDR') ?? '') . '<br>';
        $result .= '<br>';
        $result .= 'SCRIPT_NAME: ' . ($request->server('SCRIPT_NAME') ?? '') . '<br>';
        $result .= 'HTTP_HOST: ' . ($request->server('HTTP_HOST') ?? '') . '<br>';
        $result .= 'SERVER_NAME: ' . ($request->server('SERVER_NAME') ?? '') . '<br>';
        $result .= 'SERVER_ADDR: ' . ($request->server('SERVER_ADDR') ?? '') . '<br>';
        $result .= 'SERVER_PORT: ' . ($request->server('SERVER_PORT') ?? '') . '<br>';
        $result .= 'REQUEST_SCHEME: ' . ($request->server('REQUEST_SCHEME') ?? '') . '<br>';
        $result .= 'REQUEST_URI: ' . ($request->server('REQUEST_URI') ?? '') . '<br>';
        return $result;
    }

    /**
     * Summary of getRender
     * @param Request $request
     * @return string
     */
    public function getRender($request)
    {
        if ($request->render()) {
            return 'Server side rendering';
        }
        return 'Client side rendering';
    }

    /**
     * Summary of getAgent
     * @param Request $request
     * @return string
     */
    public function getAgent($request)
    {
        return $request->agent();
    }

    /**
     * Summary of getDatabases
     * @param mixed $full
     * @return string
     */
    public function getDatabases($full)
    {
        $i = 0;
        $result = '';
        foreach (Database::getDbList() as $name => $database) {
            $title = 'Check if Calibre database path is not an URL';
            if (!preg_match('#^http#', $database)) {
                $message = $name . ' OK';
            } else {
                $message = $name . ' Calibre path has to be local (no URL allowed)';
            }
            $result .= $this->getMessage($title, $message);

            $title = 'Check if Calibre database file exists and is readable';
            if (is_readable(Database::getDbFileName($i))) {
                $message = $name . ' OK';
            } else {
                $message = $name . ' File ' . Database::getDbFileName($i) . ' not found,
Please check
<ul>
<li>Value of $config[\'calibre_directory\'] in config/local.php <strong>(Does it end with a \'/\'?)</strong></li>
<li>Value of <a href="http://php.net/manual/en/ini.core.php#ini.open-basedir">open_basedir</a> in your php.ini</li>
<li>The access rights of the Calibre Database</li>
<li>Synology users please read <a href="https://github.com/seblucas/cops/wiki/Howto---Synology">this</a></li>
<li>Note that hosting your Calibre Library in /home is almost impossible due to access rights restriction</li>
</ul>';
            }
            $result .= $this->getMessage($title, $message);

            if (!is_readable(Database::getDbFileName($i))) {
                $i++;
                continue;
            }
            $title = 'Check if Calibre database file can be opened with PHP';
            try {
                $db = new PDO('sqlite:' . Database::getDbFileName($i));
                $message = $name . ' OK';
            } catch (Exception $e) {
                $message = $name . ' If the file is readable, check your php configuration. Exception detail : ' . $e;
            }
            $result .= $this->getMessage($title, $message);

            $title = 'Check if Calibre database file contains at least some of the needed tables';
            try {
                $db = new PDO('sqlite:' . Database::getDbFileName($i));
                $count = $db->query('select count(*) FROM sqlite_master WHERE type="table" AND name in ("books", "authors", "tags", "series")')->fetchColumn();
                if ($count == 4) {
                    $message = $name . ' OK';
                } else {
                    $message = $name . ' Not all Calibre tables were found. Are you sure you\'re using the correct database.';
                }
            } catch (Exception $e) {
                $message = $name . ' If the file is readable, check your php configuration. Exception detail : ' . $e;
            }
            $result .= $this->getMessage($title, $message);

            if (!$full) {
                $i++;
                continue;
            }
            $title = 'Check if all Calibre books are found';
            $message = "This option has been disabled by default - uncomment if you are sure you want to do this...";
            /**
            try {
                $db = new PDO('sqlite:' . Database::getDbFileName($i));
                $result = $db->prepare('select books.path || "/" || data.name || "." || lower (format) as fullpath from data join books on data.book = books.id');
                $result->execute();
                while ($post = $result->fetchObject()) {
                    if (!is_file(Database::getDbDirectory($i) . $post->fullpath)) {
                        echo '<p>' . Database::getDbDirectory($i) . $post->fullpath . '</p>';
                    }
                }
            } catch (Exception $e) {
                echo $name . ' If the file is readable, check your php configuration. Exception detail : ' . $e;
            }
            */
            $result .= $this->getMessage($title, $message);
            $i++;
        }
        return $result;
    }

    /**
     * Summary of handleMore
     * @param Request $request
     * @return Response
     */
    public function handleMore($request)
    {
        $message = date(DATE_COOKIE) . "\n\n";
        $message .= var_export($request, true);

        $response = new Response('text/plain;charset=utf-8');
        return $response->setContent($message);
    }
}
