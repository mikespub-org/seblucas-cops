<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Input\Session;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Input\Config;

class SessionTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testSessionConnected(): void
    {
        $session = new Session();

        // session is not started -> no session id
        $expected = '';
        $sessionId = session_id();
        $this->assertEquals($expected, $sessionId);

        $expected = Config::get('session_name');
        $sessionName = session_name();
        $this->assertEquals($expected, $sessionName);

        // session is started -> session id
        $session->start();
        $sessionId = session_id();
        $this->assertNotEquals($expected, $sessionId);

        $expected = null;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);

        $session->set('connected', 0);

        $expected = 0;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);

        $custom = [
            'template' => 'default',
            'style' => 'default',
        ];
        $session->set('custom', $custom);

        file_put_contents(__DIR__ . '/text.sessionid', $sessionId);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testSessionConnected')]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testSessionRestore(): void
    {
        $session = new Session();

        $sessionId = file_get_contents(__DIR__ . '/text.sessionid');
        $session->restore($sessionId);

        $expected = 0;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);

        $expected = [
            'template' => 'default',
            'style' => 'default',
        ];
        $custom = $session->get('custom');
        $this->assertEquals($expected, $custom);

        $expected = session_id();
        $this->assertEquals($expected, $sessionId);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testSessionRestore')]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testSessionRegenerate(): void
    {
        $session = new Session();

        $sessionId = file_get_contents(__DIR__ . '/text.sessionid');
        $session->restore($sessionId);

        $expected = 0;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);

        $session->regenerate();

        // session regenerated -> new session id
        $expected = session_id();
        $this->assertNotEquals($expected, $sessionId);

        // session regenerated -> session data kept
        $expected = 0;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);

        // force expires on next start()
        $session->set('expires', time() - 24 * 60 * 60);

        file_put_contents(__DIR__ . '/text.sessionid', $expected);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testSessionRegenerate')]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testSessionExpires(): void
    {
        $session = new Session();

        $sessionId = file_get_contents(__DIR__ . '/text.sessionid');
        $session->restore($sessionId);

        // session expired -> new session id
        $expected = session_id();
        $this->assertNotEquals($expected, $sessionId);

        // session expired -> reset expires
        $expected = time();
        $this->assertGreaterThan($expected, $session->get('expires'));
        $sessionId = $expected;

        // session expired -> session data kept
        $expected = 0;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);

        file_put_contents(__DIR__ . '/text.sessionid', $sessionId);
    }

    #[\PHPUnit\Framework\Attributes\Depends('testSessionExpires')]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testSessionDestroy(): void
    {
        $session = new Session();

        $sessionId = file_get_contents(__DIR__ . '/text.sessionid');
        $session->restore($sessionId);

        $expected = 0;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);

        $session->regenerate(true);

        // session regenerated -> new session id
        $expected = session_id();
        $this->assertNotEquals($expected, $sessionId);

        // session destroyed - no session data
        $expected = null;
        $connected = $session->get('connected');
        $this->assertEquals($expected, $connected);
    }
}
