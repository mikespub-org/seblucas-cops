<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     mikespub <mikespub@users.noreply.github.com>
 */

namespace SebLucas\Cops\Tests\Handlers;

require_once dirname(__DIR__, 2) . '/config/test.php';

use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\BookShelf;
use SebLucas\Cops\Handlers\ShelvesHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Input\SessionInterface;
use SebLucas\Cops\Output\Response;

class ShelvesHandlerTest extends TestCase
{
    private static string $tempDir;

    public static function setUpBeforeClass(): void
    {
        self::$tempDir = sys_get_temp_dir() . '/cops_test_shelves_handler_' . bin2hex(random_bytes(4)) . '/';
        mkdir(self::$tempDir, 0o750, true);
        Config::set('shelves_directory', self::$tempDir);
    }

    public static function tearDownAfterClass(): void
    {
        if (is_dir(self::$tempDir)) {
            foreach (glob(self::$tempDir . '{,.}*', GLOB_BRACE) ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir(self::$tempDir);
        }
        Config::set('use_server_shelves', '');
    }

    protected function setUp(): void
    {
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);
    }

    protected function tearDown(): void
    {
        foreach (glob(self::$tempDir . '{,.}*', GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testCreateReturnsJsonForJsonRequests(): void
    {
        $response = $this->handle(
            ['action' => 'create', 'name' => 'SciFi'],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertSame(
            ['success' => true, 'action' => 'create', 'shelf' => 'SciFi'],
            json_decode($response->getContent(), true)
        );
    }

    public function testBrowserCreateRedirectsToShelfPage(): void
    {
        $response = $this->handle(
            ['action' => 'create'],
            ['REQUEST_METHOD' => 'POST'],
            ['name' => 'SciFi']
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('vendor/bin/index.php/shelves?shelf=SciFi', $response->getHeader('Location'));
        $this->assertTrue(new BookShelf()->hasShelf('SciFi'));
    }

    public function testExternalReturnUrlIsNotRedirectedTo(): void
    {
        $response = $this->handle(
            ['action' => 'create', 'return' => 'https://example.invalid/'],
            ['REQUEST_METHOD' => 'POST'],
            ['name' => 'SciFi']
        );

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringNotContainsString('example.invalid', (string) $response->getHeader('Location'));
    }

    public function testSwitchResolvesHashedShelfAlias(): void
    {
        $shelf = new BookShelf();
        $shelf->load();
        $shelf->createShelf('To Read');

        $response = $this->handle(
            ['action' => 'switch', BookShelf::URL_PARAM => md5('To Read')],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertSame('To Read', json_decode($response->getContent(), true)['shelf']);
        $this->assertSame('To Read', new BookShelf()->getActiveShelfName());
    }

    public function testToggleAddAndRemoveBookActions(): void
    {
        $toggleOn = $this->handle(
            ['action' => 'toggle', 'id' => 17],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertTrue(json_decode($toggleOn->getContent(), true)['inShelf']);

        $added = $this->handle(
            ['action' => 'add', 'id' => 19],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertTrue(json_decode($added->getContent(), true)['success']);

        $toggleOff = $this->handle(
            ['action' => 'toggle', 'id' => 17],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertFalse(json_decode($toggleOff->getContent(), true)['inShelf']);

        $removed = $this->handle(
            ['action' => 'remove', 'id' => 19],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertTrue(json_decode($removed->getContent(), true)['success']);
        $this->assertSame([], new BookShelf()->getBooksInShelf());
    }

    public function testRenameAndDeleteResolveHashedShelfAlias(): void
    {
        $shelf = new BookShelf();
        $shelf->load();
        $shelf->createShelf('SciFi');

        $renamed = $this->handle(
            ['action' => 'rename', BookShelf::URL_PARAM => md5('SciFi'), 'name' => 'Speculative Fiction'],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertTrue(json_decode($renamed->getContent(), true)['success']);

        $deleted = $this->handle(
            ['action' => 'delete', BookShelf::URL_PARAM => md5('Speculative Fiction')],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertTrue(json_decode($deleted->getContent(), true)['success']);
        $this->assertFalse(new BookShelf()->hasShelf('Speculative Fiction'));
    }

    public function testExportReturnsAttachment(): void
    {
        $response = $this->handle(['action' => 'export']);

        $this->assertSame('attachment; filename="cops_shelves_export.json"', $response->getHeader('Content-Disposition'));
        $this->assertJson((string) $response->getContent());
    }

    public function testImportReturnsJsonResult(): void
    {
        $data = json_encode([
            'activeShelf' => 'Imported',
            'shelves' => ['Imported' => [['id' => 17, 'db' => 0]]],
        ]);
        $response = $this->handle(
            ['action' => 'import', 'mode' => 'new', 'data' => $data],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertSame(
            ['success' => true, 'action' => 'import', 'mode' => 'new'],
            json_decode($response->getContent(), true)
        );
        $this->assertCount(1, new BookShelf()->getBooksInShelf('Imported'));
    }

    public function testSessionStartsOnlyForSessionStorage(): void
    {
        $sharedSession = $this->createMock(SessionInterface::class);
        $sharedSession->expects($this->never())->method('start');
        $sharedContext = new RequestContext(Request::build(['action' => 'unknown'], ShelvesHandler::class));
        $sharedContext->setSession($sharedSession);
        new ShelvesHandler($sharedContext)->handle($sharedContext->getRequest());

        Config::set('use_server_shelves', BookShelf::MODE_SESSION);
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('start')->willReturn(true);
        $session->method('getId')->willReturn('shelves-handler-test-session');
        $context = new RequestContext(Request::build(['action' => 'unknown'], ShelvesHandler::class));
        $context->setSession($session);
        new ShelvesHandler($context)->handle($context->getRequest());
    }

    /** @param array<string, mixed> $params
     * @param array<string, mixed> $server
     * @param array<string, mixed> $post
     */
    private function handle(array $params, array $server = [], array $post = []): Response
    {
        $server += ['REQUEST_METHOD' => 'GET'];
        $context = new RequestContext(Request::build($params, ShelvesHandler::class, $server, $post));
        return new ShelvesHandler($context)->handle($context->getRequest());
    }
}
