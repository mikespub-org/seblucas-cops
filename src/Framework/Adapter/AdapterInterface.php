<?php

namespace SebLucas\Cops\Framework\Adapter;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\RouterInterface;

/**
 * Framework Adapter Interface
 * Provides a simplified interface for framework integration
 */
interface AdapterInterface
{
    public function getName(): string;
    public function handleRequest(RequestContext $context): Response;
    public function registerRoutes(): void;
    public function getRouter(): RouterInterface;
    public function getHandlerManager(): HandlerManager;
}
