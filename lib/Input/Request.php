<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Output\Format;

/**
 * Summary of Request
 */
class Request
{
    /**
     * Summary of urlParams
     * @var array<mixed>
     */
    public $urlParams = [];
    protected string $queryString = '';
    protected bool $online = true;

    /**
     * Summary of __construct
     * @param bool $online
     */
    public function __construct($online = true)
    {
        $this->online = $online;
        $this->init();
    }

    /**
     * Summary of useServerSideRendering
     * @return bool|int
     */
    public function render()
    {
        return preg_match('/' . Config::get('server_side_render') . '/', $this->agent());
    }

    /**
     * Summary of query
     * @return string
     */
    public function query()
    {
        return $this->queryString;
    }

    /**
     * Summary of agent
     * @return string
     */
    public function agent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return "";
    }

    /**
     * Summary of language
     * @return ?string
     */
    public function language()
    {
        return $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
    }

    /**
     * Summary of path
     * @param string $default
     * @return string
     */
    public function path($default = '')
    {
        return $_SERVER['PATH_INFO'] ?? $default;
    }

    /**
     * Summary of script
     * @return ?string
     */
    public function script()
    {
        return $_SERVER['SCRIPT_NAME'] ?? null;
    }

    /**
     * Summary of uri
     * @return ?string
     */
    public function uri()
    {
        return $_SERVER['REQUEST_URI'] ?? null;
    }

    /**
     * Summary of init
     * @return void
     */
    public function init()
    {
        $this->urlParams = [];
        $this->queryString = '';
        if (!$this->online) {
            return;
        }
        $path = $this->path();
        if (!empty(Config::get('use_route_urls'))) {
            $params = Route::match($path);
            if (is_null($params)) {
                // this will call exit()
                $this->notFound();
            }
            foreach ($params as $name => $value) {
                $this->urlParams[$name] = $value;
            }
        } elseif (!empty($path)) {
            // make unexpected pathinfo visible for now...
            http_response_code(500);
            echo 'Unexpected PATH_INFO in request';
            return;
        }
        if (!empty($_GET)) {
            foreach ($_GET as $name => $value) {
                // remove ajax timestamp for jQuery cache = false
                if ($name == '_') {
                    continue;
                }
                $this->urlParams[$name] = $_GET[$name];
            }
        }
        // @todo get virtual library from option (see customize)
        if (!isset($this->urlParams['vl']) && !empty($this->option('virtual_library'))) {
            $this->urlParams['vl'] = $this->option('virtual_library');
        }
        $this->queryString = $_SERVER['QUERY_STRING'] ?? '';
    }

    /**
     * Summary of hasFilter
     * @return bool
     */
    public function hasFilter()
    {
        // see list of acceptable filter params in Filter.php
        $find = Filter::URL_PARAMS;
        return !empty(array_intersect_key($find, $this->urlParams));
    }

    /**
     * Summary of getFilterParams
     * @return array<mixed>
     */
    public function getFilterParams()
    {
        // see list of acceptable filter params in Filter.php
        $find = Filter::URL_PARAMS;
        return array_intersect_key($this->urlParams, $find);
    }

    /**
     * Summary of get
     * @param string $name
     * @param mixed $default
     * @param ?string $pattern
     * @return mixed
     */
    public function get($name, $default = null, $pattern = null)
    {
        if (!empty($this->urlParams) && isset($this->urlParams[$name]) && $this->urlParams[$name] != '') {
            if (!isset($pattern) || preg_match($pattern, $this->urlParams[$name])) {
                return $this->urlParams[$name];
            }
        }
        return $default;
    }

    /**
     * Summary of set
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
        $this->urlParams[$name] = $value;
        $this->queryString = http_build_query($this->urlParams);
    }

    /**
     * Summary of post
     * @param string $name
     * @return mixed
     */
    public function post($name)
    {
        return $_POST[$name] ?? null;
    }

    /**
     * Summary of request
     * @param string $name
     * @return mixed
     */
    public function request($name)
    {
        return $_REQUEST[$name] ?? null;
    }

    /**
     * Summary of server
     * @param string $name
     * @return mixed
     */
    public function server($name)
    {
        return $_SERVER[$name] ?? null;
    }

    /**
     * Summary of session
     * @param string $name
     * @return mixed
     */
    public function session($name)
    {
        return $_SESSION[$name] ?? null;
    }

    /**
     * Summary of cookie
     * @param string $name
     * @return mixed
     */
    public function cookie($name)
    {
        return $_COOKIE[$name] ?? null;
    }

    /**
     * Summary of files
     * @param string $name
     * @return mixed
     */
    public function files($name)
    {
        return $_FILES[$name] ?? null;
    }

    /**
     * Summary of option
     * @param string $option
     * @return mixed
     */
    public function option($option)
    {
        if (isset($_COOKIE[$option])) {
            if (!is_null(Config::get($option)) && is_array(Config::get($option))) {
                return explode(',', $_COOKIE[$option]);
            } elseif (!preg_match('/[^A-Za-z0-9\-_.@()]/', $_COOKIE[$option])) {
                return $_COOKIE[$option];
            }
        }
        if (!is_null(Config::get($option))) {
            return Config::get($option);
        }

        return '';
    }

    /**
     * Summary of style
     * @return string
     */
    public function style()
    {
        $style = $this->option('style');
        if (!preg_match('/[^A-Za-z0-9\-_]/', $style)) {
            return 'templates/' . $this->template() . '/styles/style-' . $this->option('style') . '.css';
        }
        return 'templates/' . Config::get('template') . '/styles/style-' . Config::get('style') . '.css';
    }

    /**
     * Summary of template
     * @return string
     */
    public function template()
    {
        $template = $this->option('template');
        if (!preg_match('/[^A-Za-z0-9\-_]/', $template) && is_dir("templates/{$template}/")) {
            return $template;
        }
        return Config::get('template');
    }

    /**
     * Summary of getIntOrNull
     * @param string $name
     * @return ?int
     */
    protected function getIntOrNull($name)
    {
        $value = $this->get($name, null, '/^\d+$/');
        if (!is_null($value)) {
            return (int) $value;
        }
        return null;
    }

    /**
     * Summary of getId
     * @param string $name
     * @return ?int
     */
    public function getId($name = 'id')
    {
        return $this->getIntOrNull($name);
    }

    /**
     * Summary of database
     * @return ?int
     */
    public function database()
    {
        return $this->getIntOrNull('db');
    }

    /**
     * Summary of getVirtualLibrary
     * @param bool $strip
     * @return int|string|null
     */
    public function getVirtualLibrary($strip = false)
    {
        $libraryId = $this->get('vl', null);
        if (empty($libraryId)) {
            return null;
        }
        // URL format: ...&vl=2.Short_Stories_in_English
        if ($strip && str_contains($libraryId, '.')) {
            [$libraryId, $slug] = explode('.', $libraryId);
        }
        return $libraryId;
    }

    /**
     * Summary of getSorted
     * @param ?string $default
     * @return ?string
     */
    public function getSorted($default = null)
    {
        return $this->get('sort', $default, '/^\w+(\s+(asc|desc)|)$/i');
        // ?? $this->option('sort');
    }

    /**
     * Summary of getCurrentUrl
     * @param ?string $endpoint
     * @return string
     */
    public function getCurrentUrl($endpoint = null)
    {
        $endpoint ??= $this->getEndpoint(Config::ENDPOINT['index']);
        $pathInfo = $this->path();
        $queryString = $this->query();
        if (empty($queryString)) {
            return Route::url($endpoint . $pathInfo);
        }
        return Route::url($endpoint . $pathInfo) . '?' . $queryString;
    }

    /**
     * Summary of getEndpoint
     * @param string $default
     * @return string
     */
    public function getEndpoint($default)
    {
        $script = explode("/", $this->script() ?? "/" . $default);
        $link = array_pop($script);
        // see former LinkNavigation
        if (preg_match("/(bookdetail|getJSON).php/", $link)) {
            return $default;
        }
        return $link;
    }

    /**
     * Summary of getApiKey
     * @return string
     */
    public function getApiKey()
    {
        // If you send header X-Api-Key from your client, the web server will turn this into HTTP_X_API_KEY
        // For Nginx proxy configurations you may need to add something like this:
        // proxy_set_header X-Api-Key $http_x_api_key;
        return $this->server('HTTP_X_API_KEY') ?? '';
    }

    /**
     * Summary of hasValidApiKey
     * @return bool
     */
    public function hasValidApiKey()
    {
        if (empty(Config::get('api_key')) || Config::get('api_key') !== $this->getApiKey()) {
            return false;
        }
        return true;
    }

    /**
     * Summary of isFeed
     * @return bool
     */
    public function isFeed()
    {
        $endpoint = $this->getEndpoint(Config::ENDPOINT['index']);
        if ($endpoint == Config::ENDPOINT['feed']) {
            return true;
        }
        return false;
    }

    /**
     * Summary of verifyLogin
     * @param array<mixed> $serverVars
     * @return bool
     */
    public static function verifyLogin($serverVars = null)
    {
        $serverVars ??= $_SERVER;
        if (!is_null(Config::get('basic_authentication')) &&
          is_array(Config::get('basic_authentication'))) {
            $basicAuth = Config::get('basic_authentication');
            if (!isset($serverVars['PHP_AUTH_USER']) ||
              (($serverVars['PHP_AUTH_USER'] != $basicAuth['username'] ||
                $serverVars['PHP_AUTH_PW'] != $basicAuth['password']))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Summary of notFound
     * @param string|null $home
     * @param string|null $error
     * @param array<string, mixed> $params
     * @return never
     */
    public static function notFound($home = null, $error = null, $params = [])
    {
        header(($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1') . ' 404 Not Found');
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;
        // default to script basename or null if undefined
        $home ??= basename($_SERVER['SCRIPT_NAME'] ?? '') ?: null;
        $data = ['link' => Route::url($home, null, $params)];
        if (!empty($error)) {
            $data['error'] = htmlspecialchars($error);
            $template = 'templates/error.html';
        } else {
            $template = 'templates/notfound.html';
        }
        echo Format::template($data, $template);
        exit;
    }

    /**
     * Summary of build
     * @param array<mixed> $params ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
     * @param ?array<mixed> $server
     * @param ?array<mixed> $cookie
     * @param ?array<mixed> $config
     * @return Request
     */
    public static function build($params, $server = null, $cookie = null, $config = null)
    {
        // ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
        $request = new self(false);
        $request->urlParams = $params;
        $request->queryString = http_build_query($request->urlParams);
        return $request;
    }
}
