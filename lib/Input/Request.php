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
     * @var array
     */
    public $urlParams = [];
    private $queryString = null;

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
        global $config;
        return preg_match('/' . $config['cops_server_side_render'] . '/', $this->agent());
    }

    /**
     * Summary of query
     * @return mixed
     */
    public function query()
    {
        return $this->queryString;
    }

    /**
     * Summary of agent
     * @return mixed
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
     * @return mixed
     */
    public function language()
    {
        return $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
    }

    /**
     * Summary of path
     * @return mixed
     */
    public function path()
    {
        return $_SERVER['PATH_INFO'] ?? null;
    }

    /**
     * Summary of script
     * @return mixed
     */
    public function script()
    {
        return $_SERVER['SCRIPT_NAME'] ?? null;
    }

    /**
     * Summary of uri
     * @return mixed
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
     * @param mixed $name
     * @param mixed $default
     * @param mixed $pattern
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
     * @param mixed $name
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
     * @param mixed $name
     * @return mixed
     */
    public function post($name)
    {
        return $_POST[$name] ?? null;
    }

    /**
     * Summary of request
     * @param mixed $name
     * @return mixed
     */
    public function request($name)
    {
        return $_REQUEST[$name] ?? null;
    }

    /**
     * Summary of server
     * @param mixed $name
     * @return mixed
     */
    public function server($name)
    {
        return $_SERVER[$name] ?? null;
    }

    /**
     * Summary of session
     * @param mixed $name
     * @return mixed
     */
    public function session($name)
    {
        return $_SESSION[$name] ?? null;
    }

    /**
     * Summary of cookie
     * @param mixed $name
     * @return mixed
     */
    public function cookie($name)
    {
        return $_COOKIE[$name] ?? null;
    }

    /**
     * Summary of files
     * @param mixed $name
     * @return mixed
     */
    public function files($name)
    {
        return $_FILES[$name] ?? null;
    }

    /**
     * Summary of option
     * @param mixed $option
     * @return mixed
     */
    public function option($option)
    {
        global $config;
        if (isset($_COOKIE[$option])) {
            if (isset($config ['cops_' . $option]) && is_array($config ['cops_' . $option])) {
                return explode(',', $_COOKIE[$option]);
            } elseif (!preg_match('/[^A-Za-z0-9\-_.@]/', $_COOKIE[$option])) {
                return $_COOKIE[$option];
            }
        }
        if (isset($config ['cops_' . $option])) {
            return $config ['cops_' . $option];
        }

        return '';
    }

    /**
     * Summary of style
     * @return string
     */
    public function style()
    {
        global $config;
        $style = $this->option('style');
        if (!preg_match('/[^A-Za-z0-9\-_]/', $style)) {
            return 'templates/' . $this->template() . '/styles/style-' . $this->option('style') . '.css';
        }
        return 'templates/' . $config['cops_template'] . '/styles/style-' . $config['cops_template'] . '.css';
    }

    /**
     * Summary of template
     * @return mixed
     */
    public function template()
    {
        global $config;
        $template = $this->option('template');
        if (!preg_match('/[^A-Za-z0-9\-_]/', $template) && is_dir("templates/{$template}/")) {
            return $template;
        }
        return $config['cops_template'];
    }

    public function getSorted($default = null)
    {
        return $this->get('sort', $default, '/^\w+(\s+(asc|desc)|)$/i');
        // ?? $this->option('sort');
    }

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
     * Summary of verifyLogin
     * @return bool
     */
    public static function verifyLogin($serverVars = null)
    {
        global $config;
        $serverVars ??= $_SERVER;
        if (isset($config['cops_basic_authentication']) &&
          is_array($config['cops_basic_authentication'])) {
            if (!isset($serverVars['PHP_AUTH_USER']) ||
              (isset($serverVars['PHP_AUTH_USER']) &&
                ($serverVars['PHP_AUTH_USER'] != $config['cops_basic_authentication']['username'] ||
                  $serverVars['PHP_AUTH_PW'] != $config['cops_basic_authentication']['password']))) {
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
     * @param array $params ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
     * @param ?array $server
     * @param ?array $cookie
     * @param ?array $config
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
