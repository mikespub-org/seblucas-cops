<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\Routing;
use SebLucas\Cops\Routing\UriGenerator;

/**
 * Summary of RequestContext
 */
class RequestContext
{
    private Request $request;
    private HandlerManager $manager;
    private RouterInterface $router;
    /** @var array<mixed> */
    private ?array $matchParams = null;
    private ?BaseHandler $handler = null;
    private Config $config;
    private string $locale;
    private ?Session $session = null;

    public function __construct(Request $request, ?HandlerManager $manager = null, ?RouterInterface $router = null)
    {
        $this->request = $request;
        $this->manager = $manager ?? new HandlerManager();
        $this->router = $router ?? new Routing();
        $this->config = new Config();
        $this->locale = $this->request->locale();
        $this->initializeContext();
    }

    protected function initializeContext(): void
    {
        $this->manager->setContext($this);
        // set locale for Route Slugger
        UriGenerator::setLocale($this->locale);
        UriGenerator::setScriptName((string) $this->request->script());
    }

    /**
     * Summary of matchRequest
     * @return array<mixed>
     */
    public function matchRequest(): array
    {
        if (isset($this->matchParams)) {
            return $this->matchParams;
        }

        try {
            $path = $this->request->path();
            $method = $this->request->method();

            // Try to match the route
            $this->matchParams = $this->router->match($path, $method);
            if (!isset($this->matchParams)) {
                error_log("COPS: Invalid request path '$path' from template " . $this->request->template());
                // delay reporting error until we're back in Framework
                $this->request->invalid = true;
                $this->matchParams = [];
            }

            // Update request with matched parameters
            $this->updateRequest($this->matchParams);

            return $this->matchParams;
        } catch (\Throwable $e) {
            // Return default route match for error handling
            return [
                Request::HANDLER_PARAM => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update request parameters after route matching
     * @param array<mixed> $params from router match()
     */
    public function updateRequest($params): Request
    {
        $default = $this->manager->getHandlerClass('html');
        if (empty($params[Request::HANDLER_PARAM])) {
            $params[Request::HANDLER_PARAM] = $default;
        }
        // JsonHandler uses same routes as HtmlHandler - see util.js
        if ($params[Request::HANDLER_PARAM] == $default && $this->request->isAjax()) {
            $params[Request::HANDLER_PARAM] = $this->manager->getHandlerClass('json');
        }
        foreach ($params as $name => $value) {
            $this->request->set($name, $value);
        }
        return $this->request;
    }

    /**
     * Load user- and/or database-specific config after request match & update + authentication
     * @see SebLucas\Cops\Middleware\AuthMiddleware::checkUserAuthentication()
     */
    public function updateConfig(): Config
    {
        // first load user-specific config in case they have their own database(s)
        $username = $this->request->getUserName();
        if (!empty($username)) {
            $config = Config::getUserConfig($username);
            if (!empty($config)) {
                Config::load($config);
                $this->config = new Config();
            }
        }
        // then load database- (and user-) specific config
        $database = $this->request->database();
        $config = Config::getDatabaseConfig($database, $username);
        if (!empty($config)) {
            Config::load($config);
            $this->config = new Config();
        }
        return $this->config;
    }

    public function resolveHandler(): BaseHandler
    {
        if ($this->handler) {
            return $this->handler;
        }

        // Get handler name based on request state
        $handlerName = $this->resolveHandlerName();

        try {
            $this->handler = $this->manager->createHandler($handlerName);
            return $this->handler;
        } catch (\RuntimeException $e) {
            // Fallback to error handler
            //return $this->createErrorHandler($e);
            $this->handler = $this->manager->createHandler('error');
            return $this->handler;
        }
    }

    protected function resolveHandlerName(): string
    {
        // 1. Check if request is invalid
        if ($this->request->invalid) {
            return 'error';
        }

        // 2. Check matched route handler
        if ($this->matchParams && !empty($this->matchParams[Request::HANDLER_PARAM])) {
            return $this->matchParams[Request::HANDLER_PARAM];
        }

        // 3. Check request parameters
        if (!empty($this->request->urlParams[Request::HANDLER_PARAM])) {
            $name = $this->request->urlParams[Request::HANDLER_PARAM];
            return $name;
        }

        // 4. Content negotiation
        if ($this->request->isJson() || $this->request->isAjax()) {
            return 'json';
        }

        // 5. Default handler
        return 'html';
    }

    /**
     * Summary of createHandler
     * @param class-string<BaseHandler> $handlerClass
     * @return BaseHandler
     */
    protected function createHandler(string $handlerClass): BaseHandler
    {
        // Create handler instance with context
        $handler = new $handlerClass($this);

        return $handler;
    }

    /**
     * Summary of generateUrl
     * @param string $name
     * @param array<mixed> $params
     * @return string
     */
    public function generateUrl(string $name, array $params = []): string
    {
        // Merge current route parameters with new ones
        //if ($this->matchParams) {
        //    $params = array_merge($this->matchParams, $params);
        //}

        try {
            return $this->router->generate($name, $params);
        } catch (\Throwable $e) {
            // Fallback to query string URL
            return $this->generateQueryStringUrl($params);
        }
    }

    /**
     * Summary of generateQueryStringUrl
     * @param array<mixed> $params
     * @return string
     */
    protected function generateQueryStringUrl(array $params): string
    {
        $baseUrl = UriGenerator::absolute('');
        $queryString = http_build_query($params);
        return $baseUrl . ($queryString ? '?' . $queryString : '');
    }

    // Accessor methods
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getHandler(): BaseHandler
    {
        return $this->handler;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getSession(): Session
    {
        $this->session ??= new Session();
        return $this->session;
    }

    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    public function getHandlerManager(): HandlerManager
    {
        return $this->manager;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * Summary of getRoutes
     * @return array<string, mixed>
     */
    public function getRoutes(): array
    {
        return $this->router->getRouteCollection()?->all() ?? [];
    }
}
