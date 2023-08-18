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
    private string $queryString = '';

    /**
     * Summary of __construct
     */
    public function __construct()
    {
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
     * @return string|null
     */
    public function language()
    {
        return $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
    }

    /**
     * Summary of path
     * @return string|null
     */
    public function path()
    {
        return $_SERVER['PATH_INFO'] ?? null;
    }

    /**
     * Summary of script
     * @return string|null
     */
    public function script()
    {
        return $_SERVER['SCRIPT_NAME'] ?? null;
    }

    /**
     * Summary of uri
     * @return string|null
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
        if (!empty($_GET)) {
            foreach ($_GET as $name => $value) {
                $this->urlParams[$name] = $_GET[$name];
            }
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
     * Summary of get
     * @param string $name
     * @param mixed $default
     * @param string|null $pattern
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
            } elseif (!preg_match('/[^A-Za-z0-9\-_.@]/', $_COOKIE[$option])) {
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
     * @return mixed
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
     * Summary of getSorted
     * @param string|null $default
     * @return mixed
     */
    public function getSorted($default = null)
    {
        return $this->get('sort', $default, '/^\w+(\s+(asc|desc)|)$/i');
        // ?? $this->option('sort');
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
     * @return void
     */
    public static function notFound()
    {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        header('Status: 404 Not Found');

        $_SERVER['REDIRECT_STATUS'] = 404;
    }

    /**
     * Summary of build
     * @param array<mixed> $params ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
     * @param array<mixed>|null $server
     * @param array<mixed>|null $cookie
     * @param array<mixed>|null $config
     * @return Request
     */
    public static function build($params, $server = null, $cookie = null, $config = null)
    {
        // ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
        $request = new self();
        $request->urlParams = $params;
        $request->queryString = http_build_query($request->urlParams);
        return $request;
    }
}
