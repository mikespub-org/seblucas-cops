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
    /** @var array<mixed> */
    public $urlParams = [];
    protected string $queryString = '';
    protected string $pathInfo = '';
    protected bool $parsed = true;
    protected ?string $content = null;

    /**
     * Summary of __construct
     * @param bool $parse
     */
    public function __construct($parse = true)
    {
        $this->parseParams($parse);
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
        return $this->server('HTTP_USER_AGENT') ?? "";
    }

    /**
     * Summary of language
     * @return ?string
     */
    public function language()
    {
        return $this->server('HTTP_ACCEPT_LANGUAGE');
    }

    /**
     * Summary of path
     * @param string $default
     * @return string
     */
    public function path($default = '')
    {
        return $this->server('PATH_INFO') ?? $default;
    }

    /**
     * Summary of script
     * @return ?string
     */
    public function script()
    {
        return $this->server('SCRIPT_NAME');
    }

    /**
     * Summary of uri
     * @return ?string
     */
    public function uri()
    {
        return $this->server('REQUEST_URI');
    }

    /**
     * Summary of parseParams
     * @param bool $parse
     * @return void
     */
    public function parseParams($parse = true)
    {
        $this->parsed = $parse;
        $this->urlParams = [];
        $this->queryString = '';
        if (!$this->parsed) {
            return;
        }
        $path = $this->path();
        if (!empty(Config::get('use_route_urls'))) {
            $params = Route::match($path);
            if (is_null($params)) {
                // this will call exit()
                $this->notFound();
            }
            // @todo handle 'json' routes correctly - see util.js
            if (empty($params[Route::HANDLER_PARAM]) && $this->isAjax()) {
                $params[Route::HANDLER_PARAM] = 'json';
            }
            foreach ($params as $name => $value) {
                $this->urlParams[$name] = $value;
            }
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
        // get virtual library from option (see customize)
        if (!isset($this->urlParams['vl']) && !empty($this->option('virtual_library'))) {
            $this->urlParams['vl'] = $this->option('virtual_library');
        }
        if (!empty(Config::get('calibre_user_database'))) {
            $user = $this->getUserName();
            // @todo use restriction etc. from Calibre user database
        }
        $this->queryString = $_SERVER['QUERY_STRING'] ?? '';
        $this->pathInfo = $_SERVER['PATH_INFO'] ?? '';
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
     * Summary of content
     * @return mixed
     */
    public function content()
    {
        if (!isset($this->content)) {
            $this->content = file_get_contents('php://input');
        }
        return $this->content;
    }

    /**
     * Summary of option
     * @param string $option
     * @return mixed
     */
    public function option($option)
    {
        if (!is_null($this->cookie($option))) {
            if (!is_null(Config::get($option)) && is_array(Config::get($option))) {
                return explode(',', $this->cookie($option));
            } elseif (!preg_match('/[^A-Za-z0-9\-_.@()]/', $this->cookie($option))) {
                return $this->cookie($option);
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
     * Summary of getUserName
     * @param ?string $name
     * @return string|null
     */
    public function getUserName($name = null)
    {
        $http_auth_user = Config::get('http_auth_user', 'PHP_AUTH_USER');
        $name ??= $this->server($http_auth_user);
        return $name;
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
     * @param ?string $handler
     * @return string
     */
    public function getCurrentUrl($handler = null)
    {
        $handler ??= $this->getHandler();
        $pathInfo = $this->path();
        $queryString = $this->query();
        if (empty($queryString)) {
            return Route::link($handler) . $pathInfo;
        }
        return Route::link($handler) . $pathInfo . '?' . $queryString;
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
     * Summary of getHandler
     * @param string $default
     * @return string
     */
    public function getHandler($default = 'index')
    {
        // we have a handler already
        if (!empty($this->urlParams[Route::HANDLER_PARAM])) {
            return $this->urlParams[Route::HANDLER_PARAM];
        }
        // try to find handler via endpoint
        $endpoint = $this->getEndpoint(Config::ENDPOINT[$default]);
        $flipped = array_flip(Config::ENDPOINT);
        if (!empty($flipped[$endpoint])) {
            return $flipped[$endpoint];
        }
        // @todo do not use front.php here yet
        if ($endpoint == 'front.php') {
            return $default;
        }
        // for phpunit tests
        if ($endpoint == 'phpunit' || $endpoint == 'Standard input code') {
            return $default;
        }
        // how did we end up here?
        throw new \Exception('Unknown handler for endpoint ' . htmlspecialchars($endpoint));
        //return $default;
    }

    /**
     * Summary of getCleanParams
     * @return array<string, mixed>
     */
    public function getCleanParams()
    {
        $params = $this->urlParams;
        unset($params['title']);
        unset($params['_']);
        unset($params['n']);
        unset($params['complete']);
        //unset($params[Route::HANDLER_PARAM]);
        return $params;
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
     * Summary of isAjax
     * @return bool
     */
    public function isAjax()
    {
        // for jquery etc. if passed along by proxy
        if ($this->server('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest') {
            return true;
        }
        // for fetch etc. if Accept header is specified
        if (str_contains($this->server('HTTP_ACCEPT') ?? '', 'application/json')) {
            return true;
        }
        // @todo https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode
        return false;
    }

    /**
     * Summary of isJson
     * @return bool
     */
    public function isJson()
    {
        // using actual getJSON.php endpoint
        $endpoint = $this->getEndpoint(Config::ENDPOINT['json']);
        if ($endpoint == Config::ENDPOINT['json']) {
            return true;
        }
        // set in parseParams() based on isAjax()
        if (!empty($this->urlParams[Route::HANDLER_PARAM]) && $this->urlParams[Route::HANDLER_PARAM] == 'json') {
            return true;
        }
        return false;
    }

    /**
     * Summary of isFeed
     * @return bool
     */
    public function isFeed()
    {
        // using actual feed.php or opds.php endpoint
        $endpoint = $this->getEndpoint(Config::ENDPOINT['index']);
        if (in_array($endpoint, [Config::ENDPOINT['feed'], Config::ENDPOINT['opds']])) {
            return true;
        }
        // set in parseParams() based on Route::match()
        if (!empty($this->urlParams[Route::HANDLER_PARAM]) &&
            in_array($this->urlParams[Route::HANDLER_PARAM], ['feed', 'opds'])) {
            return true;
        }
        return false;
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
     * @param string $handler
     * @param ?array<mixed> $server
     * @param ?array<mixed> $cookie
     * @param ?array<mixed> $config
     * @return Request
     */
    public static function build($params = [], $handler = '', $server = null, $cookie = null, $config = null)
    {
        // ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
        if (!empty($handler)) {
            $params[Route::HANDLER_PARAM] ??= $handler;
        }
        $request = new self(false);
        $request->urlParams = $params;
        $request->queryString = http_build_query($request->urlParams);
        return $request;
    }
}
