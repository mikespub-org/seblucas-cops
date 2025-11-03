<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Middleware;

use SebLucas\Cops\Handlers\BaseHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\UriGenerator;
use SebLucas\Cops\Calibre\User;

/**
 * Summary of AuthMiddleware
 */
class AuthMiddleware extends BaseMiddleware
{
    /**
     * Check user authentation before handling request
     * @param Request $request
     * @param BaseHandler $handler
     * @return Response|void
     */
    public function process($request, $handler)
    {
        $response = static::checkUserAuthentication($request, $handler->getContext());
        if (!empty($response)) {
            return $response;
        }
        // do something with $response after $handler
        return $handler->handle($request);
    }

    /**
     * Check user authentication + call updateConfig() or prepare fail Response (302 or 401)
     * @param Request $request
     * @param RequestContext $context
     * @return Response|null
     */
    public static function checkUserAuthentication($request, $context)
    {
        // check basic authentication
        if (static::checkBasicAuthentication($request, $context)) {
            // check form authentication
            if (static::checkFormAuthentication($request, $context)) {
                // load user- and/or database-dependent config here!
                $context->updateConfig();
                return null;
            }
            return Response::redirect(UriGenerator::path('login.html'));
        }
        return Response::unauthorized();
    }

    /**
     * Check basic authentication
     * @param Request $request
     * @param RequestContext $context not used here
     * @return bool
     */
    public static function checkBasicAuthentication($request, $context)
    {
        $basicAuth = Config::get('basic_authentication');
        // we don't use basic authentication - assume valid
        if (empty($basicAuth)) {
            return true;
        }

        $serverVars = $request->serverParams;
        // check basic authentication with standard PHP variables
        if (empty($serverVars['PHP_AUTH_USER']) || empty($serverVars['PHP_AUTH_PW'])) {
            $request->setUserName(null);
            return false;
        }
        $isAuthenticated = false;
        if (is_array($basicAuth)) {
            // format: ["username" => "xxx", "password" => "secret"]
            $isAuthenticated = User::checkAuthArray($basicAuth, $serverVars['PHP_AUTH_USER'], $serverVars['PHP_AUTH_PW']);
        } elseif (is_string($basicAuth)) {
            // format: "/config/.config/calibre/server-users.sqlite"
            $isAuthenticated = User::checkAuthDatabase($basicAuth, $serverVars['PHP_AUTH_USER'], $serverVars['PHP_AUTH_PW']);
        }
        if (!$isAuthenticated) {
            $request->setUserName(null);
        }
        return $isAuthenticated;
    }

    /**
     * Check form authentication - using session here
     * @param Request $request
     * @param RequestContext $context
     * @return bool
     */
    public static function checkFormAuthentication($request, $context)
    {
        $formAuth = Config::get('form_authentication');
        // we don't use form authentication - assume valid
        if (empty($formAuth)) {
            return true;
        }
        // we need a session for form authentication
        $session = $context->getSession();
        $session->start();
        $username = $session->get('user');
        // we have a session user
        if (!empty($username)) {
            // set the session user as remote user in request for handlers
            $request->setUserName($username);
            $request->setSession($session);
            return true;
        }
        $requestVars = $request->postParams;
        // check form authentication without post params = invalid
        if (empty($requestVars['username']) || empty($requestVars['password'])) {
            $request->setSession($session);
            return false;
        }
        $isAuthenticated = false;
        if (is_array($formAuth)) {
            // format: ["username" => "xxx", "password" => "secret"]
            $isAuthenticated = User::checkAuthArray($formAuth, $requestVars['username'], $requestVars['password']);
        } elseif (is_string($formAuth)) {
            // format: "/config/.config/calibre/server-users.sqlite"
            $isAuthenticated = User::checkAuthDatabase($formAuth, $requestVars['username'], $requestVars['password']);
        }
        if ($isAuthenticated) {
            $session->set('user', $requestVars['username']);
            // set the session user as remote user in request for handlers
            $request->setUserName($requestVars['username']);
            $request->setSession($session);
            return true;
        }
        $request->setSession($session);
        return false;
    }

    /**
     * Check proxy authentication - not used here
     * @param Request $request
     * @param RequestContext $context not used here
     * @return bool
     */
    public static function checkProxyAuthentication($request, $context)
    {
        $username = $request->getUserName();
        $basicAuth = Config::get('basic_authentication');
        // we have username and don't use basic authentication - assume valid
        if (!empty($username) && empty($basicAuth)) {
            return true;
        }
        return false;
    }
}
