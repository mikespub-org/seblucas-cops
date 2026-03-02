<?php

namespace SebLucas\Cops\Framework\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Input\Request as CopsRequest;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Routing\RouterInterface;
use Slim\Routing\RouteContext;

/**
 * A generic, invokable action to handle all COPS routes within a Slim application.
 */
class CopsAction
{
    public function __construct(
        private readonly HandlerManager $copsManager,
        private readonly RouterInterface $copsRouter
    ) {}

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Get handler name and defaults from route arguments, which are set by the adapter
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $defaults = $route->getArgument('_defaults', []);
        $handlerName = $defaults['_handler'] ?? 'html';

        // 1. Convert PSR-7 Request to COPS Request
        $copsRequest = new CopsRequest(false);
        $copsRequest->serverParams = $request->getServerParams();
        $copsRequest->setPath($request->getUri()->getPath());
        $copsRequest->urlParams = array_merge($defaults, $args, $request->getQueryParams());
        // @todo $copsRequest->setUserName() if authenticated by framework

        // We need to set a context on the handler manager for it to create a handler
        $context = new RequestContext($copsRequest, $this->copsManager, $this->copsRouter);
        // @todo load user- and/or database-dependent config here?
        //$context->updateConfig();
        $this->copsManager->setContext($context);

        // 2. Resolve and handle the request using COPS components
        $handler = $this->copsManager->createHandler($handlerName);
        $copsResponse = $handler->handle($copsRequest);

        // 3. Convert COPS Response to PSR-7 Response
        $response->getBody()->write($copsResponse->getContent());
        $response = $response->withStatus($copsResponse->getStatusCode());
        foreach ($copsResponse->getHeaders() as $headerName => $headerValues) {
            $response = $response->withHeader($headerName, implode(', ', $headerValues));
        }

        return $response;
    }
}
