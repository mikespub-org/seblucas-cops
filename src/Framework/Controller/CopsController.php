<?php

namespace SebLucas\Cops\Framework\Controller;

use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * A generic controller to handle all COPS routes within a Symfony application.
 */
class CopsController
{
    public function __construct(private readonly HandlerManager $copsManager, private readonly RouterInterface $copsRouter) {}

    public function __invoke(SymfonyRequest $request): SymfonyResponse
    {
        // 1. Convert Symfony Request to COPS Request
        $copsRequest = new CopsRequest(false);
        $copsRequest->serverParams = $request->headers->all();
        $copsRequest->setPath($request->getPathInfo());

        // Get defaults and route params from Symfony request attributes
        $defaults = $request->attributes->get('_route_params', []);
        $handlerName = $request->attributes->get('_handler', 'html');

        $copsRequest->urlParams = array_merge(
            $defaults,
            $request->attributes->all(),
            $request->query->all(),
        );
        // @todo $copsRequest->setUserName() if authenticated by framework

        // We need to set a context on the handler manager for it to create a handler
        $context = new RequestContext($copsRequest, $this->copsManager, $this->copsRouter);
        // @todo load user- and/or database-dependent config here?
        //$context->updateConfig();
        $this->copsManager->setContext($context);

        // 2. Resolve and handle the request using COPS components
        $handler = $this->copsManager->createHandler($handlerName);
        $copsResponse = $handler->handle($copsRequest);

        // 3. Convert COPS Response to Symfony Response
        return new SymfonyResponse(
            $copsResponse->getContent(),
            $copsResponse->getStatusCode(),
            $copsResponse->getHeaders(),
        );
    }
}
