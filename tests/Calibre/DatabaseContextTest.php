<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Calibre;

require_once dirname(__DIR__, 2) . '/config/test.php';

use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\DatabaseContext;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\RequestConfig;

class DatabaseContextTest extends TestCase
{
    public function setUp(): void
    {
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
