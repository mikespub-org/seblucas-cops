<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Middleware;

use SebLucas\Cops\Middleware\AuthMiddleware;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\User;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Output\Response;
use SebLucas\Cops\Routing\UriGenerator;

class AuthMiddlewareTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testBasicAuthWithoutCreds(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $context = new RequestContext($request);
        $result = AuthMiddleware::checkBasicAuthentication($request, $context);
        Config::set('basic_authentication', null);

        $this->assertFalse($result);
        $this->assertEquals(null, $request->getUserName());
    }

    public function testBasicAuthWithCreds(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $request->serverParams = [
            'PHP_AUTH_USER' => 'xxx',
            'PHP_AUTH_PW' => 'secret',
        ];
        $context = new RequestContext($request);
        $result = AuthMiddleware::checkBasicAuthentication($request, $context);
        Config::set('basic_authentication', null);

        $this->assertTrue($result);
        $this->assertEquals('xxx', $request->getUserName());
    }

    public function testBasicAuthWithCredsWrong(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $request->serverParams = [
            'PHP_AUTH_USER' => 'xxx',
            'PHP_AUTH_PW' => 'wrong',
        ];
        $context = new RequestContext($request);
        $result = AuthMiddleware::checkBasicAuthentication($request, $context);
        Config::set('basic_authentication', null);

        $this->assertFalse($result);
        $this->assertEquals(null, $request->getUserName());
    }

    public function testBasicAuthWithUserDb(): void
    {
        Config::set('basic_authentication', dirname(__DIR__) . "/BaseWithSomeBooks/users.db");
        $request = new Request();
        $request->serverParams = [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'admin',
        ];
        $context = new RequestContext($request);
        $result = AuthMiddleware::checkBasicAuthentication($request, $context);
        Config::set('basic_authentication', null);

        $this->assertTrue($result);
        $this->assertEquals('admin', $request->getUserName());
    }

    public function testBasicAuthWithUserDbWrong(): void
    {
        Config::set('basic_authentication', dirname(__DIR__) . "/BaseWithSomeBooks/users.db");
        $request = new Request();
        $request->serverParams = [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'pass',
        ];
        $context = new RequestContext($request);
        $result = AuthMiddleware::checkBasicAuthentication($request, $context);
        Config::set('basic_authentication', null);

        $this->assertFalse($result);
        $this->assertEquals(null, $request->getUserName());
    }

    public function testBasicAuthWithUserDbInvalid(): void
    {
        Config::set('basic_authentication', dirname(__DIR__) . "/BaseWithSomeBooks/no-users.db");
        $request = new Request();
        $request->serverParams = [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'admin',
        ];
        $context = new RequestContext($request);
        $result = AuthMiddleware::checkBasicAuthentication($request, $context);
        Config::set('basic_authentication', null);

        $this->assertFalse($result);
        $this->assertEquals(null, $request->getUserName());
    }

    public function testUserAuthUnauthorized(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $context = new RequestContext($request);
        $response = AuthMiddleware::checkUserAuthentication($request, $context);
        Config::set('basic_authentication', null);

        $this->assertInstanceOf(Response::class, $response);
        $expected = [
            'WWW-Authenticate' => 'Basic realm="COPS Authentication"',
        ];
        $this->assertEquals($expected, $response->getHeaders());
        $expected = 401;
        $this->assertEquals($expected, $response->getStatusCode());
    }

    public function testFormAuthWithoutCreds(): void
    {
        Config::set('form_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $context = new RequestContext($request);
        // make sure we start with a clean session here
        $session = $context->getSession();
        $session->set('user', null);
        $result = AuthMiddleware::checkFormAuthentication($request, $context);
        Config::set('form_authentication', null);

        $this->assertFalse($result);
        $this->assertEquals(null, $request->getUserName());
        $session = $context->getSession();
        $this->assertEquals(null, $session->get('user'));
    }

    public function testFormAuthWithCreds(): void
    {
        Config::set('form_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $request->postParams = [
            'username' => 'xxx',
            'password' => 'secret',
        ];
        $context = new RequestContext($request);
        // make sure we start with a clean session here
        $session = $context->getSession();
        $session->set('user', null);
        $result = AuthMiddleware::checkFormAuthentication($request, $context);
        Config::set('form_authentication', null);

        $this->assertTrue($result);
        $this->assertEquals('xxx', $request->getUserName());
        $session = $context->getSession();
        $this->assertEquals('xxx', $session->get('user'));
    }

    public function testFormAuthWithCredsWrong(): void
    {
        Config::set('form_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $request->postParams = [
            'username' => 'xxx',
            'password' => 'wrong',
        ];
        $context = new RequestContext($request);
        // make sure we start with a clean session here
        $session = $context->getSession();
        $session->set('user', null);
        $result = AuthMiddleware::checkFormAuthentication($request, $context);
        Config::set('form_authentication', null);

        $this->assertFalse($result);
        $this->assertEquals(null, $request->getUserName());
        $session = $context->getSession();
        $this->assertEquals(null, $session->get('user'));
    }

    public function testFormAuthWithSession(): void
    {
        Config::set('form_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $context = new RequestContext($request);
        // make sure we use an existing session here
        $session = $context->getSession();
        $session->set('user', 'xxx');
        $result = AuthMiddleware::checkFormAuthentication($request, $context);
        Config::set('form_authentication', null);

        $this->assertTrue($result);
        $this->assertEquals('xxx', $request->getUserName());
        $session = $context->getSession();
        $this->assertEquals('xxx', $session->get('user'));
    }

    public function testUserAuthRedirect(): void
    {
        Config::set('full_url', 'http://test:123/cops/');
        UriGenerator::setBaseUrl(null);
        Config::set('form_authentication', [ "username" => "xxx", "password" => "secret"]);
        $request = new Request();
        $context = new RequestContext($request);
        // make sure we start with a clean session here
        $session = $context->getSession();
        $session->set('user', null);
        $response = AuthMiddleware::checkUserAuthentication($request, $context);
        Config::set('form_authentication', null);
        Config::set('full_url', '');
        UriGenerator::setBaseUrl(null);

        $this->assertInstanceOf(Response::class, $response);
        $expected = [
            'Location' => 'http://test:123/cops/login.html',
        ];
        $this->assertEquals($expected, $response->getHeaders());
    }

    public function testRemoteUserWithUserDb(): void
    {
        Config::set('http_auth_user', "REMOTE_USER");
        Config::set('calibre_user_database', dirname(__DIR__) . "/BaseWithSomeBooks/users.db");
        $request = new Request();
        $request->serverParams = [
            'REMOTE_USER' => 'admin',
        ];
        $context = new RequestContext($request);
        $result = AuthMiddleware::checkBasicAuthentication($request, $context);

        $this->assertTrue($result);
        $this->assertEquals('admin', $request->getUserName());
        Config::set('http_auth_user', "PHP_AUTH_USER");

        $user = User::getInstanceByName('admin');
        $this->assertEquals('admin', $user->name);
        $this->assertEquals(['library_restrictions' => []], $user->restriction);
        Config::set('calibre_user_database', null);
    }
}
