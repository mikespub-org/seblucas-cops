<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Framework;

use SebLucas\Cops\Framework\Framework;
use SebLucas\Cops\Handlers\CheckHandler;
use SebLucas\Cops\Middleware\TestMiddleware;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;

class FrameworkTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // ...
    }

    /**
     * Summary of getHandlers
     * @return array<array<mixed>>
     */
    public static function getHandlers(): array
    {
        $result = [];
        foreach (Framework::getHandlers() as $handler => $className) {
            array_push($result, [$handler, $className]);
        }
        return $result;
    }

    /**
     * Summary of testGetHandlers
     * @return void
     */
    public function testGetHandlers(): void
    {
        $handlers = $this->getHandlers();

        $expected = 19;
        $this->assertCount($expected, $handlers);
    }

    public function testGetRequest(): void
    {
        $path = '/check';
        $request = Framework::getRequest($path);

        $expected = CheckHandler::class;
        $this->assertEquals($expected, $request->getHandler());
    }

    public function testGetHandler(): void
    {
        $handler = Framework::createHandler('check');

        $expected = CheckHandler::class;
        $this->assertEquals($expected, $handler::class);
    }

    public function testRunHome(): void
    {
        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>COPS</title>";
        $this->assertStringContainsString($expected, $output);
    }

    public function testRunCheck(): void
    {
        $_SERVER['PATH_INFO'] = '/check';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>COPS Configuration Check</title>";
        $this->assertStringContainsString($expected, $output);

        unset($_SERVER['PATH_INFO']);
    }

    #[RequiresMethod('\Marsender\EPubLoader\RequestHandler', '__construct')]
    public function testRunLoader(): void
    {
        $_SERVER['PATH_INFO'] = '/loader';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>COPS Loader</title>";
        $this->assertStringContainsString($expected, $output);

        unset($_SERVER['PATH_INFO']);
    }

    public function testRunAdminDisabled(): void
    {
        $_SERVER['PATH_INFO'] = '/admin';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        // redirect with empty content
        $expected = "";
        $this->assertEquals($expected, $output);

        unset($_SERVER['PATH_INFO']);
    }

    public function testRunAdminEnabled(): void
    {
        // enable admin in test config
        Config::set('enable_admin', true);
        $_SERVER['PATH_INFO'] = '/admin';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>COPS - Admin Features</title>";
        $this->assertStringContainsString($expected, $output);

        // disable admin in test config
        Config::set('enable_admin', false);
        unset($_SERVER['PATH_INFO']);
    }

    public function testMiddleware(): void
    {
        $className = Framework::class;
        // test protected method using closure bind & call or use reflection
        // @see https://www.php.net/manual/en/closure.bind.php
        $addMiddleware = \Closure::bind(static function ($add = null) use ($className) {
            if (!empty($add)) {
                array_push($className::$middlewares, $add);
            } else {
                $className::$middlewares = [];
            }
            return $className::$middlewares;
        }, null, $className);

        $_SERVER['PATH_INFO'] = '/check/more';

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "'_route' => 'check-more'";
        $this->assertStringContainsString($expected, $output);

        // add test middleware to framework
        $middlewares = $addMiddleware(new TestMiddleware());
        $expected = 1;
        $this->assertCount($expected, $middlewares);

        ob_start();
        Framework::run();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "'_route' => 'check-more'";
        $this->assertStringContainsString($expected, $output);
        $expected = "'hello' => 'world'";
        $this->assertStringContainsString($expected, $output);
        $expected = "Goodbye!";
        $this->assertStringContainsString($expected, $output);

        // reset middleware again
        $middlewares = $addMiddleware(null);
        $expected = 0;
        $this->assertCount($expected, $middlewares);

        unset($_SERVER['PATH_INFO']);
    }

    /**
     * Test the 404 Not Found error handling path.
     */
    public function testRunNotFound(): void
    {
        $_SERVER['PATH_INFO'] = '/this-route-does-not-exist';

        // Capture error_log output to verify the error is logged
        $logFile = tempnam(sys_get_temp_dir(), 'cops_test_');
        ini_set('error_log', $logFile);

        ob_start();
        Framework::run();
        $output = ob_get_clean();

        // The ErrorHandler should output a "Invalid request path" message
        $this->assertStringContainsString('<h1>Error</h1>', $output);
        $this->assertStringContainsString('<p>COPS: Invalid request path &#039;/this-route-does-not-exist&#039;</p>', $output);

        // Check that the error was logged
        $this->assertStringContainsString("COPS: Invalid request path '/this-route-does-not-exist' from template", file_get_contents($logFile));
        unlink($logFile);

        unset($_SERVER['PATH_INFO']);
    }

    /**
     * Test the createRequest method's fallback to REDIRECT_PATH_INFO.
     */
    public function testCreateRequestWithRedirectPathInfo(): void
    {
        unset($_SERVER['PATH_INFO']);
        $_SERVER['REDIRECT_PATH_INFO'] = '/test/path';

        $request = Framework::getInstance()->getContext(true)->getRequest();

        $this->assertEquals('/test/path', $request->path());

        unset($_SERVER['REDIRECT_PATH_INFO']);
        unset($_SERVER['PATH_INFO']);
    }
}
