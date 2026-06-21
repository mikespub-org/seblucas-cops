<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Database;

require_once dirname(__DIR__, 2) . '/config/test.php';

use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Database\Database;
use SebLucas\Cops\Database\DatabaseContext;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\RequestConfig;

class DatabaseContextTest extends TestCase
{
    public function setUp(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testBaseFunction(): void
    {
        $dbContext = new DatabaseContext();
        $this->assertFalse($dbContext->isMultipleDatabaseEnabled());
        $this->assertEquals(["" => dirname(__DIR__) . "/BaseWithSomeBooks/"], $dbContext->getDbList());

        Config::set('calibre_directory', [
            "Some books" => dirname(__DIR__) . "/BaseWithSomeBooks/",
            "One book" => dirname(__DIR__) . "/BaseWithOneBook/"]);
        Database::clearDb();
        $dbContext = new DatabaseContext();

        $this->assertTrue($dbContext->isMultipleDatabaseEnabled());
        $this->assertEquals(Config::get('calibre_directory'), $dbContext->getDbList());
        $dbContext->setDatabase(0);
        $this->assertEquals("Some books", $dbContext->getDbName());
        $dbContext->setDatabase(1);
        $this->assertEquals("One book", $dbContext->getDbName());

        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testDatabaseContextDelegatesHelpers(): void
    {
        $config = new RequestConfig();
        $dbContext = new DatabaseContext(null, $config);

        $this->assertSame($config, $dbContext->getConfig());
        $this->assertFalse($dbContext->isMultipleDatabaseEnabled());
        $this->assertSame(['' => dirname(__DIR__) . "/BaseWithSomeBooks/"], $dbContext->getDbList());
        $this->assertSame(dirname(__DIR__) . "/BaseWithSomeBooks/metadata.db", $dbContext->getDbFileName());
        $this->assertSame('', $dbContext->getDbName());
    }
}
