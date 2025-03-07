<?php

namespace SebLucas\Cops\Framework;

use SebLucas\Cops\Framework\Adapter\AdapterInterface;
use SebLucas\Cops\Framework\Adapter\CustomAdapter;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;

/**
 * COPS framework implementation - @todo
 */
class FrameworkTodo
{
    protected static ?self $instance = null;
    protected RequestContext $context;

    public function __construct(
        protected readonly AdapterInterface $adapter = new CustomAdapter()
    ) {
        $request = $this->createRequest();
        // Register routes via adapter before creating context
        $this->adapter->registerRoutes();
        $this->context = new RequestContext(
            $request,
            $this->adapter->getHandlerManager(),
            $this->adapter->getRouter()
        );
    }

    public function getContext(): RequestContext
    {
        return $this->context;
    }

    protected function handleRequest(): void
    {
        try {
            $response = $this->adapter->handleRequest($this->context);
            $response->send();
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    protected function createRequest(): Request
    {
        if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REDIRECT_PATH_INFO'])) {
            $_SERVER['PATH_INFO'] = $_SERVER['REDIRECT_PATH_INFO'];
        }
        return new Request();
    }

    protected function handleError(\Throwable $e): void
    {
        error_log("COPS error: " . $e->getMessage());
        try {
            $handler = $this->adapter->getHandlerManager()->createHandler('error');
            $response = $handler->handle(new Request());
            if ($response instanceof Response) {
                $response->send();
            }
        } catch (\Exception $e2) {
            http_response_code(500);
            echo "Internal Server Error";
        }
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getHandlerManager(): HandlerManager
    {
        return self::getInstance()->adapter->getHandlerManager();
    }

    public static function getRouter(): RouterInterface
    {
        return self::getInstance()->adapter->getRouter();
    }

    public static function run(): void
    {
        self::getInstance()->handleRequest();
    }
}
