<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Framework;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Middleware\AuthMiddleware;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;
use SebLucas\Cops\Routing\RouteCollection;
use SebLucas\Cops\Routing\Routing;
use SebLucas\Cops\Handlers\QueueBasedHandler;

/**
 * Minimal Framework
 */
class Framework
{
    /** @var class-string */
    protected static $routerClass = Routing::class;
    /** @var Framework|null */
    protected static ?self $instance = null;
    /** @var RouterInterface|null */
    protected static $router = null;
    /** @var HandlerManager|null */
    protected static $handlerManager = null;
    /** @var array<mixed> */
    protected static $middlewares = [
        // Use authentication middleware with updateConfig()
        AuthMiddleware::class,
    ];

    protected ?RequestContext $context = null;
    protected HandlerManager $manager;
    protected RouterInterface $routerInstance;

    /**
     * Create a new Framework instance
     */
    public function __construct(?HandlerManager $manager = null, ?RouterInterface $router = null)
    {
        $this->manager = $manager ?? new HandlerManager();
        $this->routerInstance = $router ?? new self::$routerClass();
        // Keep static instances in sync for facade methods
        self::$handlerManager = $this->manager;
        self::$router = $this->routerInstance;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Run application with default handler
     */
    public static function run(string $name = 'html'): void
    {
        self::getInstance()->context = null;
        self::getInstance()->handleRequest();
    }

    /**
     * Handle request using context and handler
     */
    public function handleRequest(): void
    {
        $context = null;
        try {
            $context = $this->getContext();

            // Match route and get handler
            $params = $context->matchRequest();
            $handler = $context->resolveHandler();

            // Apply middleware - incl. AuthMiddleware with updateConfig()
            if (!empty(self::$middlewares)) {
                $queue = new QueueBasedHandler($context, $handler);
                foreach (self::$middlewares as $middleware) {
                    $queue->add(new $middleware());
                }
                $handler = $queue;
            }

            // Handle request and send response
            $response = $handler->handle($context->getRequest());
            if ($response instanceof Response) {
                //$response->prepare($request);
                $response->send();
            }

        } catch (\Throwable $e) {
            $this->handleError($e, $context?->getRequest());
        }
    }

    protected function handleError(\Throwable $e, ?Request $request): void
    {
        error_log("COPS error: " . $e->getMessage());
        $handler = $this->manager->createHandler('error');
        $response = $handler->handle($request ?? new Request());
        if ($response instanceof Response) {
            $response->send();
        }
    }

    /**
     * Summary of getContext
     * @return RequestContext
     */
    public function getContext(bool $reset = false)
    {
        if (!isset($this->context) || $reset) {
            // initialize routes if needed
            $this->initializeRoutes();
            $request = $this->createRequest();
            $this->context = new RequestContext(
                $request,
                $this->manager,
                $this->routerInstance,
            );
        }
        return $this->context;
    }

    /**
     * Create request instance
     * @return Request
     */
    public function createRequest()
    {
        // when using Apache .htaccess redirect
        if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REDIRECT_PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'];
        }
        $request = new Request();
        return $request;
    }

    /**
     * Add middleware - can be instance or static
     * @param class-string $middlewareClass
     */
    public function addMiddleware(string $middlewareClass): self
    {
        self::$middlewares[] = $middlewareClass;
        return $this;
    }

    /**
     * Initialize routes
     */
    protected function initializeRoutes(): void
    {
        $routes = new RouteCollection($this->manager);
        $this->routerInstance = new self::$routerClass($routes);
        self::$router = $this->routerInstance;
    }

    /**
     * Get request instance with optional path and params
     * @param array<string, mixed> $params
     * @return Request
     */
    public static function getRequest(string $path = '', array $params = [])
    {
        $framework = self::getInstance();
        // reset context for static calls in tests
        $context = $framework->getContext(true);
        // set path and params in request
        if (!empty($path)) {
            $context->getRequest()->setPath($path);
        }
        if (!empty($params)) {
            $context->getRequest()->setParams($params, false);
        }
        // match route and update request with matched parameters
        $params = $context->matchRequest();
        // return request
        return $context->getRequest();
    }

    /**
     * Summary of getHandlers
     * @return array<string, class-string>
     */
    public static function getHandlers()
    {
        return self::getHandlerManager()->getHandlers();
    }

    /**
     * Create handler instance based on name or class-string
     * @param string|class-string<BaseHandler> $name
     * @return mixed
     */
    public static function createHandler($name)
    {
        return self::getHandlerManager()->createHandler($name);
    }

    /**
     * Summary of getHandlerManager
     * @return HandlerManager
     */
    public static function getHandlerManager()
    {
        if (!isset(self::$handlerManager)) {
            self::getInstance();
        }
        return self::$handlerManager;
    }

    /**
     * Summary of getRouter
     * @return RouterInterface
     */
    public static function getRouter()
    {
        if (!isset(self::$router)) {
            self::getInstance();
        }
        return self::$router;
    }
}
