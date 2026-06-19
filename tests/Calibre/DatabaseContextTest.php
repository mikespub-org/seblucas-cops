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
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestConfig;
use SebLucas\Cops\Input\RequestContext;

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
        $context = new DatabaseContext(null, $config);

        $this->assertSame($config, $context->getConfig());
        $this->assertFalse($context->isMultipleDatabaseEnabled());
        $this->assertSame(['' => dirname(__DIR__) . "/BaseWithSomeBooks/"], $context->getDbList());
        $this->assertSame(dirname(__DIR__) . "/BaseWithSomeBooks/metadata.db", $context->getDbFileName());
        $this->assertSame('', $context->getDbName());
    }

    public function testRequestContextCachesDatabaseContextByDatabase(): void
    {
        $request = new Request();
        $context = new RequestContext($request);

        $databaseContextA = $context->getDatabaseContext(0);
        $databaseContextB = $context->getDatabaseContext(0);
        $databaseContextC = $context->getDatabaseContext(1);

        $this->assertSame($databaseContextA, $databaseContextB);
        $this->assertNotSame($databaseContextA, $databaseContextC);
    }

    public function testRequestContextInvalidatesDatabaseContextAfterUpdateConfig(): void
    {
        $request = new Request();
        $context = new RequestContext($request);

        $databaseContextA = $context->getDatabaseContext(0);
        $context->updateConfig();
        $databaseContextB = $context->getDatabaseContext(0);

        $this->assertNotSame($databaseContextA, $databaseContextB);
    }
}
