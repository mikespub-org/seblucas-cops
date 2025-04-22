<?php

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Input\HasContextInterface;
use SebLucas\Cops\Input\HasContextTrait;
use SebLucas\Cops\Input\Route;

/**
 * Manages handler registration and creation in COPS
 */
class HandlerManager implements HasContextInterface
{
    use HasContextTrait;

    /** @var array<string, class-string<BaseHandler>> */
    private array $handlers = [];

    /** @var array<string, array<string>> */
    private array $middleware = [];

    /**
     * Create HandlerManager with optional initial handlers
     * @param array<string, class-string<BaseHandler>> $handlers
     */
    public function __construct(array $handlers = [])
    {
        // Register default handlers if none provided
        if (empty($handlers)) {
            $handlers = $this->getDefaultHandlers();
        }

        foreach ($handlers as $name => $class) {
            $this->registerHandler($name, $class);
        }
    }

    /**
     * Get default COPS handlers
     * @return array<string, class-string<BaseHandler>>
     */
    protected function getDefaultHandlers(): array
    {
        return [
            "html" => HtmlHandler::class,
            "feed" => FeedHandler::class,
            "json" => JsonHandler::class,
            "fetch" => FetchHandler::class,
            "read" => ReadHandler::class,
            "epubfs" => EpubFsHandler::class,
            "restapi" => RestApiHandler::class,
            "check" => CheckHandler::class,
            "opds" => OpdsHandler::class,
            "loader" => LoaderHandler::class,
            "zipper" => ZipperHandler::class,
            "calres" => CalResHandler::class,
            "zipfs" => ZipFsHandler::class,
            "mail" => MailHandler::class,
            "graphql" => GraphQLHandler::class,
            "tables" => TableHandler::class,
            "error" => ErrorHandler::class,
            "phpunit" => TestHandler::class,
            "admin" => AdminHandler::class,
            // ... other default handlers
        ];
    }

    /**
     * Register a new handler
     * @param string $name
     * @param class-string<BaseHandler> $handlerClass
     * @throws \InvalidArgumentException
     */
    public function registerHandler(string $name, string $handlerClass): void
    {
        if (!is_subclass_of($handlerClass, BaseHandler::class)) {
            throw new \InvalidArgumentException(
                sprintf('Handler class %s must extend BaseHandler', $handlerClass)
            );
        }

        $this->handlers[$name] = $handlerClass;
    }

    /**
     * Get all registered handlers
     * @return array<string, class-string<BaseHandler>>
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Register middleware for a handler
     * @throws \InvalidArgumentException
     */
    public function registerMiddleware(string $handlerName, string $middlewareClass): void
    {
        if (!isset($this->handlers[$handlerName])) {
            throw new \InvalidArgumentException("Unknown handler: $handlerName");
        }

        $this->middleware[$handlerName][] = $middlewareClass;
    }

    /**
     * Get handler class by name
     * @param string|class-string<BaseHandler> $name Handler name or class
     * @return class-string<BaseHandler>
     * @throws \RuntimeException
     */
    public function getHandlerClass(string $name): string
    {
        // Direct class name usage
        if (in_array($name, array_values($this->handlers))) {
            return $name;
        }
        // Handler by name
        elseif (isset($this->handlers[$name])) {
            return $this->handlers[$name];
        }
        // Invalid handler
        else {
            throw new \RuntimeException("Invalid handler name '$name'");
        }
    }

    /**
     * Create handler instance by name
     * @param string|class-string<BaseHandler> $name Handler name or class
     * @throws \RuntimeException
     */
    public function createHandler(string $name): BaseHandler
    {
        // Get handler class by name
        $handlerClass = $this->getHandlerClass($name);

        // Create handler instance with context
        $handler = new $handlerClass($this->getContext());

        // Apply middleware if any exists
        return $this->applyMiddleware($handler, $name);
    }

    /**
     * Apply middleware to handler if configured
     */
    protected function applyMiddleware(BaseHandler $handler, string $name): BaseHandler
    {
        $middleware = $this->middleware[$name] ?? [];
        if (empty($middleware)) {
            return $handler;
        }

        // @see https://www.php-fig.org/psr/psr-15/meta/#queue-based-request-handler
        $queue = new QueueBasedHandler($handler->getContext(), $handler);
        foreach ($middleware as $middlewareClass) {
            $queue->add(new $middlewareClass());
        }

        return $queue;
    }

    /**
     * Get routes for all handlers that support them
     * @return array<string, array<mixed>>
     */
    public function getRoutes(): array
    {
        $routes = [];
        foreach ($this->handlers as $handlerName => $handlerClass) {
            if (method_exists($handlerClass, 'getRoutes')) {
                $handlerRoutes = $this->addHandlerRoutes($handlerClass);
                $routes = array_merge($routes, $handlerRoutes);
            }
        }
        return $routes;
    }

    /**
     * Summary of addHandlerRoutes
     * @param class-string<BaseHandler> $handlerClass
     * @return array<string, array<mixed>>
     */
    public function addHandlerRoutes($handlerClass): array
    {
        // Skip invalid handler classes
        if (!method_exists($handlerClass, 'getRoutes')) {
            return [];
        }
        // Get routes from handler class
        $routes = $handlerClass::getRoutes();
        // Add handler class to route data
        return Route::addRoutes($routes, $handlerClass);
    }
}
