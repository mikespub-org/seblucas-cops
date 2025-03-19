<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Filter;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Language\Translation;
use SebLucas\Cops\Output\Response;

/**
 * Summary of Request
 * @todo class Request extends \Symfony\Component\HttpFoundation\Request ?
 */
class Request
{
    public const SYMFONY_REQUEST = '\Symfony\Component\HttpFoundation\Request';

    /** @var array<mixed> */
    public array $urlParams = [];
    /** @var array<mixed> */
    public array $serverParams = [];
    /** @var array<mixed> */
    public array $queryParams = [];
    /** @var array<mixed> */
    public array $postParams = [];
    /** @var array<mixed> */
    public array $cookieParams = [];
    /** @var array<mixed> */
    public array $fileParams = [];
    protected bool $parsed = true;
    /** @var string|null */
    public $content = null;
    /** @var string|null */
    public $locale = null;
    /** @var Session|null */
    public $session = null;
    public bool $invalid = false;

    /**
     * Summary of __construct
     * @param bool $parse used by build()
     */
    public function __construct($parse = true)
    {
        // Validate path early
        $this->validatePath();
        // @todo remove from constructor?
        $this->parseParams($parse);
    }

    /**
     * Summary of useServerSideRendering
     * @return bool|int
     */
    public function render()
    {
        return preg_match('/' . Config::get('server_side_render') . '/', $this->agent()) || $this->method() == 'POST';
    }

    /**
     * Summary of method
     * @return string
     */
    public function method()
    {
        return $this->server('REQUEST_METHOD') ?? 'GET';
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
     * Summary of locale
     * @return string
     */
    public function locale()
    {
        if (!isset($this->locale)) {
            $translator = new Translation($this->language());
            [$this->locale, ] = $translator->getLangAndTranslationFile();
        }
        return $this->locale;
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
     * Validate request path
     */
    public function validatePath(): void
    {
        // check for relative paths somewhere in templates
        if (str_contains($this->path(), '/index.php')) {
            $error = "Invalid relative path '{$this->path()}' from '{$this->template()}' template";
            error_log("COPS: " . $error);
            // this will call exit()
            $response = Response::sendError($this, $error);
            $response->send();
            exit;
        }
    }

    /**
     * Summary of parseParams
     * @param bool $parse used by build()
     * @return void
     */
    public function parseParams($parse = true)
    {
        $this->parsed = $parse;
        $this->urlParams = [];
        if (!$this->parsed) {
            return;
        }
        $this->serverParams = $_SERVER;
        $this->queryParams = $_GET;
        $this->postParams = $_POST;
        $this->cookieParams = $_COOKIE;
        $this->fileParams = $_FILES;
        // @todo move to RequestContext
        // $this->matchRoute();
        if (!empty($this->queryParams)) {
            foreach ($this->queryParams as $name => $value) {
                // remove ajax timestamp for jQuery cache = false
                // remove internal _handler, _route, _resource etc. params
                if (str_starts_with($name, '_')) {
                    continue;
                }
                $this->urlParams[$name] = $value;
            }
        }
        // get virtual library from option (see customize)
        if (!isset($this->urlParams['vl']) && !empty($this->option('virtual_library'))) {
            if (!Database::isMultipleDatabaseEnabled()) {
                $this->urlParams['vl'] = $this->option('virtual_library');
            }
        }
        if (!empty(Config::get('calibre_user_database'))) {
            $user = $this->getUserName();
            // @todo use restriction etc. from Calibre user database
        }
    }

    /**
     * Summary of matchRoute
     * @todo move to RequestContext
     * @return void
     */
    public function matchRoute()
    {
        $path = $this->path();
        // set route param in request once we find matching route
        $params = Route::match($path);
        if (is_null($params)) {
            error_log("COPS: Invalid request path '$path' from template " . $this->template());
            // delay reporting error until we're back in Framework
            $this->invalid = true;
            $params = [];
        }
        $this->updateFromMatch($params);
    }

    /**
     * Update request parameters after route matching
     * @param array<mixed> $params from Route::match()
     */
    public function updateFromMatch($params): void
    {
        $default = Route::getHandler('html');
        if (empty($params[Route::HANDLER_PARAM])) {
            $params[Route::HANDLER_PARAM] = $default;
        }
        // JsonHandler uses same routes as HtmlHandler - see util.js
        if ($params[Route::HANDLER_PARAM] == $default && $this->isAjax()) {
            $params[Route::HANDLER_PARAM] = Route::getHandler('json');
        }
        foreach ($params as $name => $value) {
            $this->urlParams[$name] = $value;
        }
    }

    /**
     * Set params for match of /handler/{path:.*} with default page handler - see RestApiHandler, FeedHandler etc.
     * @param array<mixed> $params from Route::match('/' . $params['path'])
     * @param bool $clearPath remove 'path' from urlParams if not set here
     * @return Request
     */
    public function setParams($params, $clearPath = true)
    {
        foreach ($params as $param => $value) {
            $this->set($param, $value);
        }
        // remove /handler/{path:.*} param from current request
        if ($clearPath && empty($params['path']) && $this->get('path')) {
            $this->set('path', null);
        }
        return $this;
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
     * @param string $key
     * @param mixed $default
     * @param ?string $pattern
     * @return mixed
     */
    public function get($key, $default = null, $pattern = null): mixed
    {
        if (!empty($this->urlParams) && isset($this->urlParams[$key]) && $this->urlParams[$key] != '') {
            if (!isset($pattern) || preg_match($pattern, (string) $this->urlParams[$key])) {
                return $this->urlParams[$key];
            }
        }
        return $default;
    }

    /**
     * Summary of set
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function set($name, $value)
    {
        $this->urlParams[$name] = $value;
        return $this;
    }

    /**
     * Summary of post
     * @param string $name
     * @return mixed
     */
    public function post($name)
    {
        return $this->postParams[$name] ?? null;
    }

    /**
     * Summary of server
     * @param string $name
     * @return mixed
     */
    public function server($name)
    {
        return $this->serverParams[$name] ?? null;
    }

    /**
     * Summary of session
     * @param string $name
     * @deprecated 3.5.7 use Session() instead
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
        return $this->cookieParams[$name] ?? null;
    }

    /**
     * Summary of files
     * @param string $name
     * @return mixed
     */
    public function files($name)
    {
        return $this->fileParams[$name] ?? null;
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
                return explode(',', (string) $this->cookie($option));
            } elseif (!preg_match('/[^A-Za-z0-9\-_.@()]/', (string) $this->cookie($option))) {
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
        if (!preg_match('/[^A-Za-z0-9\-_]/', (string) $style)) {
            return 'templates/' . $this->template() . '/styles/style-' . $style . '.css';
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
        if (!preg_match('/[^A-Za-z0-9\-_]/', (string) $template) && is_dir("templates/{$template}/")) {
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
        if ($strip && str_contains((string) $libraryId, '.')) {
            [$libraryId, $slug] = explode('.', (string) $libraryId);
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
     * Get handler class corresponding to _handler param
     * @todo move to RequestContext
     * @return class-string<BaseHandler>
     */
    public function getHandler()
    {
        // we have a handler already
        if (!empty($this->urlParams[Route::HANDLER_PARAM])) {
            return Route::getHandler($this->urlParams[Route::HANDLER_PARAM]);
        }
        if ($this->isAjax()) {
            return Route::getHandler('json');
        }
        // use default handler
        return Route::getHandler('html');
    }

    /**
     * Summary of getCleanParams
     * @return array<string, mixed>
     */
    public function getCleanParams()
    {
        $params = $this->urlParams;
        // keep title for route links
        //unset($params['title']);
        unset($params['_']);
        unset($params['n']);
        unset($params['complete']);
        // override in $handler::route() etc. if needed
        //unset($params[Route::HANDLER_PARAM]);
        //unset($params[Route::ROUTE_PARAM]);
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
     * Summary of getSession
     * @return Session|null
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Summary of setSession
     * @param Session|null $session
     * @return void
     */
    public function setSession($session)
    {
        $this->session = $session;
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
        // set in parseParams() based on isAjax()
        $handler = $this->getHandler();
        if ($handler::HANDLER == 'json') {
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
        // set in parseParams() based on Route::match()
        $handler = $this->getHandler();
        if (in_array($handler::HANDLER, ['feed', 'opds'])) {
            return true;
        }
        return false;
    }

    /**
     * Summary of build
     * @param array<mixed> $params ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
     * @param class-string|null $handler
     * @param ?array<mixed> $server
     * @param ?array<mixed> $post
     * @param ?array<mixed> $cookies
     * @param ?array<mixed> $files
     * @return Request
     */
    public static function build($params = [], $handler = null, $server = null, $post = null, $cookies = null, $files = null)
    {
        // ['db' => $db, 'page' => $pageId, 'id' => $id, 'query' => $query, 'n' => $n]
        if (!empty($handler)) {
            // @todo double-check we use an actual class-string here
            $handler = Route::getHandler($handler);
            $params[Route::HANDLER_PARAM] ??= $handler;
        }
        $request = new self(false);
        $request->setParams($params, false);
        $request->queryParams = $params;
        if (isset($server)) {
            $request->serverParams = $server;
        }
        if (isset($post)) {
            $request->postParams = $post;
        }
        if (isset($cookies)) {
            $request->cookieParams = $cookies;
        }
        if (isset($files)) {
            $request->fileParams = $files;
        }
        return $request;
    }
}
