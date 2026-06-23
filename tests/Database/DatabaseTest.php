<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Database;

use SebLucas\Cops\Database\DatabaseException;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Database\Database;
use SebLucas\Cops\Input\Config;
use Exception;

class DatabaseTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testCheckDatabaseAvailability_1(): void
    {
        $this->assertTrue(Database::checkDatabaseAvailability(null));
    }

    public function testCheckDatabaseAvailability_2(): void
    {
        Config::set('calibre_directory', [
            "Some books" => dirname(__DIR__) . "/BaseWithSomeBooks/",
            "One book" => dirname(__DIR__) . "/BaseWithOneBook/"]);
        Database::clearDb();

        $this->assertTrue(Database::checkDatabaseAvailability(null));

        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testCheckDatabaseAvailability_Exception1(): void
    {
        Config::set('calibre_directory', [
            "Some books" => dirname(__DIR__) . "/BaseWithSomeBooks/",
            "One book" => dirname(__DIR__) . "/OneBook/"]);
        Database::clearDb();

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database <1> not found');

        $this->assertTrue(Database::checkDatabaseAvailability(null));

        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testCheckDatabaseAvailability_Exception2(): void
    {
        Config::set('calibre_directory', [
            "Some books" => dirname(__DIR__) . "/SomeBooks/",
            "One book" => dirname(__DIR__) . "/BaseWithOneBook/"]);
        Database::clearDb();

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Database <0> not found');

        $this->assertTrue(Database::checkDatabaseAvailability(null));

        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }
}
