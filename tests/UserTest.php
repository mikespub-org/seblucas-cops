<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Calibre\User;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class UserTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testLoginEnabledWithoutCreds(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $this->assertFalse(User::verifyLogin());

        Config::set('basic_authentication', null);
    }

    public function testLoginEnabledAndLoggingIn(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $server = [
            'PHP_AUTH_USER' => 'xxx',
            'PHP_AUTH_PW' => 'secret',
        ];
        $this->assertTrue(User::verifyLogin($server));

        Config::set('basic_authentication', null);
    }

    public function testLoginEnabledAndWrong(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $server = [
            'PHP_AUTH_USER' => 'xyz',
            'PHP_AUTH_PW' => 'wrong',
        ];
        $this->assertFalse(User::verifyLogin($server));

        Config::set('basic_authentication', null);
    }

    public function testLoginWithUserDb(): void
    {
        Config::set('basic_authentication', __DIR__ . "/BaseWithSomeBooks/users.db");
        $server = [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'admin',
        ];
        $this->assertTrue(User::verifyLogin($server));

        Config::set('basic_authentication', null);
    }

    public function testLoginWithUserDbWrong(): void
    {
        Config::set('basic_authentication', __DIR__ . "/BaseWithSomeBooks/users.db");
        $server = [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'pass',
        ];
        $this->assertFalse(User::verifyLogin($server));

        Config::set('basic_authentication', null);
    }

    public function testLoginWithInvalidUserDb(): void
    {
        Config::set('basic_authentication', __DIR__ . "/BaseWithSomeBooks/no-users.db");
        $server = [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'admin',
        ];
        $this->assertFalse(User::verifyLogin($server));

        Config::set('basic_authentication', null);
    }

    public function testRemoteUserWithUserDb(): void
    {
        Config::set('http_auth_user', "REMOTE_USER");
        Config::set('calibre_user_database', __DIR__ . "/BaseWithSomeBooks/users.db");
        $server = ['REMOTE_USER' => 'admin'];
        $request = Request::build([], null, $server);
        $this->assertEquals('admin', $request->getUserName());

        $user = User::getInstanceByName('admin');
        $this->assertEquals('admin', $user->name);
        $this->assertEquals(['library_restrictions' => []], $user->restriction);

        Config::set('http_auth_user', "PHP_AUTH_USER");
        Config::set('calibre_user_database', null);
    }
}
