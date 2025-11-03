<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Calibre;

use SebLucas\Cops\Calibre\User;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class UserTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testRemoteUserWithUserDb(): void
    {
        Config::set('http_auth_user', "REMOTE_USER");
        Config::set('calibre_user_database', dirname(__DIR__) . "/BaseWithSomeBooks/users.db");
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
