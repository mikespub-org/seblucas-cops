<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
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
        $_SERVER['PHP_AUTH_USER'] = 'xxx';
        $_SERVER['PHP_AUTH_PW'] = 'secret';
        $this->assertTrue(User::verifyLogin($_SERVER));

        Config::set('basic_authentication', null);
    }

    public function testLoginEnabledAndWrong(): void
    {
        Config::set('basic_authentication', [ "username" => "xxx", "password" => "secret"]);
        $_SERVER['PHP_AUTH_USER'] = 'xyz';
        $_SERVER['PHP_AUTH_PW'] = 'wrong';
        $this->assertFalse(User::verifyLogin($_SERVER));

        Config::set('basic_authentication', null);
    }

    public function testLoginWithUserDb(): void
    {
        Config::set('basic_authentication', __DIR__ . "/BaseWithSomeBooks/users.db");
        $_SERVER['PHP_AUTH_USER'] = 'admin';
        $_SERVER['PHP_AUTH_PW'] = 'admin';
        $this->assertTrue(User::verifyLogin($_SERVER));

        Config::set('basic_authentication', null);
    }

    public function testLoginWithUserDbWrong(): void
    {
        Config::set('basic_authentication', __DIR__ . "/BaseWithSomeBooks/users.db");
        $_SERVER['PHP_AUTH_USER'] = 'user';
        $_SERVER['PHP_AUTH_PW'] = 'pass';
        $this->assertFalse(User::verifyLogin($_SERVER));

        Config::set('basic_authentication', null);
    }

    public function testLoginWithInvalidUserDb(): void
    {
        Config::set('basic_authentication', __DIR__ . "/BaseWithSomeBooks/no-users.db");
        $_SERVER['PHP_AUTH_USER'] = 'admin';
        $_SERVER['PHP_AUTH_PW'] = 'admin';
        $this->assertFalse(User::verifyLogin($_SERVER));

        Config::set('basic_authentication', null);
    }

    public function testRemoteUserWithUserDb(): void
    {
        Config::set('http_auth_user', "REMOTE_USER");
        Config::set('calibre_user_database', __DIR__ . "/BaseWithSomeBooks/users.db");
        $_SERVER['REMOTE_USER'] = 'admin';
        $request = new Request();
        $this->assertEquals('admin', $request->getUserName());

        $user = User::getInstanceByName('admin');
        $this->assertEquals('admin', $user->name);
        $this->assertEquals(['library_restrictions' => []], $user->restriction);

        Config::set('http_auth_user', "PHP_AUTH_USER");
        Config::set('calibre_user_database', null);
    }
}
