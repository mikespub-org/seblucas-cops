<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Handlers;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Database\Database;
use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Handlers\HandlerManager;
use SebLucas\Cops\Handlers\TableHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;

class TableHandlerTest extends TestCase
{
    private static HandlerManager $manager;
    private static RequestContext $context;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Config::set('enable_admin', true);
        Database::clearDb();
        $framework = Framework::getInstance();
        self::$manager = $framework->getHandlerManager();
        self::$context = $framework->getContext();
    }

    public static function tearDownAfterClass(): void
    {
        Config::set('enable_admin', false);
    }

    public function testGetRoutes(): void
    {
        $routes = TableHandler::getRoutes();

        $this->assertArrayHasKey('tables-db-name', $routes);
        $this->assertArrayHasKey('tables-db', $routes);
        $this->assertArrayHasKey('tables', $routes);
        $this->assertArrayHasKey('editor-static', $routes);
        $this->assertArrayHasKey('editor', $routes);
        $this->assertArrayHasKey('adminer-static', $routes);
        $this->assertArrayHasKey('adminer', $routes);
    }

    public function testGetMiddleware(): void
    {
        $middleware = TableHandler::getMiddleware();

        $this->assertContains(\SebLucas\Cops\Middleware\AdminMiddleware::class, $middleware);
    }

    public function testHandleWithNoParams(): void
    {
        $request = Request::build([]);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Databases', $response->getContent());
    }

    public function testHandleWithDbOnly(): void
    {
        $request = Request::build(['db' => '0']);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Database', $response->getContent());
    }

    public function testHandleWithDbAndName(): void
    {
        $request = Request::build(['db' => '0', 'name' => 'books']);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Table books', $response->getContent());
    }

    public function testHandleWithEditorParam(): void
    {
        $this->markTestSkipped('Editor requires external adminer/editor files');
        /**
        $request = Request::build(['db' => '0', 'editor' => '1']);
        $handler = self::$manager->createHandler('tables', self::$context);

        ob_start();
        $response = $handler->handle($request);
        $output = ob_end_clean();

        $this->assertEquals(200, $response->getStatusCode());
         */
    }

    public function testHandleWithAdminerParam(): void
    {
        $this->markTestSkipped('Adminer requires external adminer/editor files');
    }

    public function testShowTableWithFromParam(): void
    {
        $request = Request::build(['db' => '0', 'name' => 'books', 'from' => 'authors.3']);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Table books', $response->getContent());
        $this->assertStringContainsString('authors=3', $response->getContent());
    }

    public function testShowTableWithIdParam(): void
    {
        $request = Request::build(['db' => '0', 'name' => 'books', 'id' => '1']);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Table books', $response->getContent());
    }

    public function testShowTableWithForeignKey(): void
    {
        $request = Request::build(['db' => '0', 'name' => 'books']);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Table books', $response->getContent());
    }

    public function testShowDbTables(): void
    {
        $request = Request::build(['db' => '0']);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Database', $response->getContent());
        $this->assertStringContainsString('books', $response->getContent());
    }

    public function testShowDatabases(): void
    {
        $request = Request::build([]);
        $handler = self::$manager->createHandler('tables', self::$context);

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Databases', $response->getContent());
    }
}
