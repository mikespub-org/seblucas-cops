<?php

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Routing\UriGenerator;

/**
 * Trait for classes that have a handler
 * @todo replace class-string and static calls with handler instance and method calls someday
 */
trait HasRouteTrait
{
    /** @var class-string<BaseHandler> */
    protected $handler;

    /**
     * Summary of setHandler
     * @param class-string<BaseHandler> $handler
     * @return void
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * Summary of getHandler
     * @return class-string<BaseHandler>
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Summary of getRoute
     * @param string $routeName
     * @param array<mixed> $params
     * @return string|null
     */
    public function getRoute($routeName, $params = [])
    {
        return $this->handler::route($routeName, $params);
    }

    /**
     * Summary of getLink
     * @param array<mixed> $params
     * @return string
     */
    public function getLink($params = [])
    {
        return $this->handler::link($params);
    }

    /**
     * Summary of getResource
     * @param string $className
     * @param array<mixed> $params
     * @return string
     */
    public function getResource(string $className, array $params = []): string
    {
        return RestApiHandler::resource($className, $params);
    }

    /**
     * Summary of getPath
     * @param string $path relative to base dir
     * @param array<mixed> $params (optional)
     * @return string
     */
    public function getPath($path = '', $params = [])
    {
        return UriGenerator::path($path, $params);
    }

    /**
     * Summary of getHandlerRoute
     * @todo replace with handler instance and method call
     * @param class-string $handler
     * @param string $routeName
     * @param array<mixed> $params
     * @return mixed
     */
    public static function getHandlerRoute($handler, $routeName, $params = [])
    {
        return $handler::route($routeName, $params);
    }
}
