<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Input;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\RequestContext;
use SebLucas\Cops\Routing\UriGenerator;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;

class RequestContextTest extends TestCase
{
    private string $testConfigFile;

    protected function setUp(): void
    {
        // Reset UriGenerator state before each test
        UriGenerator::setLocale(null);
        // Clear any existing Translation instances to ensure clean state
        // Use reflection to access the protected static property
        $reflection = new \ReflectionClass(\SebLucas\Cops\Language\Translation::class);
        $property = $reflection->getProperty('instances');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    protected function tearDown(): void
    {
        // Clean up test config file if it exists
        if (file_exists($this->testConfigFile)) {
            unlink($this->testConfigFile);
        }
        // Reset UriGenerator state after each test
        UriGenerator::setLocale(null);
    }

    /**
     * Summary of testUpdateConfigChangesLocale
     * Verifies that calling updateConfig() updates the request locale based on user config
     * @return void
     */
    public function testUpdateConfigChangesLocale(): void
    {
        // Create a request with Accept-Language header set to French
        $request = new Request();
        $username = 'testuser';
        $request->serverParams = [
            'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.9,en;q=0.8',
            'PHP_AUTH_USER' => $username,
        ];

        // Create RequestContext - locale should be 'fr' from Accept-Language
        $context = new RequestContext($request);
        $this->assertEquals('fr', $request->locale);
        $this->assertEquals('fr', $context->getRequest()->locale);
        $this->testConfigFile = dirname(__DIR__, 2) . '/config/local.' . $username . '.php';
        $configContent = <<<'PHP'
            <?php
            if (!isset($config)) {
                $config = [];
            }
            $config['cops_language'] = 'de';
            PHP;
        file_put_contents($this->testConfigFile, $configContent);

        // Call updateConfig() with the username
        $context->updateConfig();

        // Verify locale has changed to 'de' based on user config
        $this->assertEquals('de', $request->locale);
        $this->assertEquals('de', $context->getRequest()->locale);
    }

    /**
     * Summary of testUpdateConfigWithDatabaseConfig
     * Verifies that database-specific config can also change the locale
     * @return void
     */
    public function testUpdateConfigWithDatabaseConfig(): void
    {
        // Create a request with Accept-Language header set to English
        $request = new Request();
        $request->serverParams = [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ];
        $request->set('db', 1);

        // Create RequestContext - locale should be 'en' from Accept-Language
        $context = new RequestContext($request);
        $this->assertEquals('en', $request->locale);

        // Create a temporary database config file with Spanish language setting
        $this->testConfigFile = dirname(__DIR__, 2) . '/config/local.db-1.php';
        $configContent = <<<'PHP'
            <?php
            if (!isset($config)) {
                $config = [];
            }
            $config['cops_language'] = 'es';
            PHP;
        file_put_contents($this->testConfigFile, $configContent);

        // Call updateConfig() without username (database config only)
        $context->updateConfig();

        // Verify locale has changed to 'es' based on database config
        $this->assertEquals('es', $request->locale);
        $this->assertEquals('es', $context->getRequest()->locale);
    }

    /**
     * Summary of testUpdateConfigWithUserAndDatabaseConfig
     * Verifies that database config overrides user config for locale
     * @return void
     */
    public function testUpdateConfigWithUserAndDatabaseConfig(): void
    {
        // Create a request with Accept-Language header set to English
        $request = new Request();
        $request->serverParams = [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        ];
        $request->set('db', 1);
        $request->setUserName('testuser');

        // Create RequestContext - locale should be 'en' from Accept-Language
        $context = new RequestContext($request);
        $this->assertEquals('en', $request->locale);

        // Create a temporary user config file with French language setting
        $userConfigFile = dirname(__DIR__, 2) . '/config/local.' . $request->getUserName() . '.php';
        $userConfigContent = <<<'PHP'
            <?php
            if (!isset($config)) {
                $config = [];
            }
            $config['cops_language'] = 'fr';
            PHP;
        file_put_contents($userConfigFile, $userConfigContent);

        // Create a temporary database config file with Italian language setting
        $this->testConfigFile = dirname(__DIR__, 2) . '/config/local.db-1.php';
        $dbConfigContent = <<<'PHP'
            <?php
            if (!isset($config)) {
                $config = [];
            }
            $config['cops_language'] = 'it';
            PHP;
        file_put_contents($this->testConfigFile, $dbConfigContent);

        // Call updateConfig() with username and database
        $context->updateConfig();

        // Verify locale has changed to 'it' based on database config (overrides user config)
        $this->assertEquals('it', $request->locale);
        $this->assertEquals('it', $context->getRequest()->locale);

        // Clean up user config file
        if (file_exists($userConfigFile)) {
            unlink($userConfigFile);
        }
    }

    /**
     * Summary of testUpdateConfigWithoutLanguageConfig
     * Verifies that locale remains from Accept-Language when no language config is set
     * @return void
     */
    public function testUpdateConfigWithoutLanguageConfig(): void
    {
        // Create a request with Accept-Language header set to Japanese
        $request = new Request();
        $request->serverParams = [
            'HTTP_ACCEPT_LANGUAGE' => 'ja-JP,ja;q=0.9,en;q=0.8',
        ];

        // Create RequestContext - locale should be 'ja' from Accept-Language
        $context = new RequestContext($request);
        $this->assertEquals('ja', $request->locale);

        // Create a temporary user config file WITHOUT language setting
        $username = 'testuser2';
        $this->testConfigFile = dirname(__DIR__, 2) . '/config/local.' . $username . '.php';
        $configContent = <<<'PHP'
            <?php
            if (!isset($config)) {
                $config = [];
            }
            $config['cops_title_default'] = 'My COPS';
            PHP;
        file_put_contents($this->testConfigFile, $configContent);

        // Call updateConfig() with the username
        $context->updateConfig();

        // Verify locale remains 'ja' from Accept-Language (no language config override)
        $this->assertEquals('ja', $request->locale);
        $this->assertEquals('ja', $context->getRequest()->locale);
    }

    /**
     * Summary of testUpdateConfigResetsLocale
     * Verifies that calling updateConfig() resets locale even if it was manually set
     * @return void
     */
    public function testUpdateConfigResetsLocale(): void
    {
        // Create a request with Accept-Language header set to English
        $request = new Request();
        $username = 'testuser3';
        $request->serverParams = [
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'PHP_AUTH_USER' => $username,
        ];

        // Create RequestContext
        $context = new RequestContext($request);
        $this->assertEquals('en', $request->locale);

        // Manually set locale to something else
        $request->locale = 'pt';
        $this->assertEquals('pt', $request->locale);
        $this->testConfigFile = dirname(__DIR__, 2) . '/config/local.' . $username . '.php';
        $configContent = <<<'PHP'
            <?php
            if (!isset($config)) {
                $config = [];
            }
            $config['cops_language'] = 'ru';
            PHP;
        file_put_contents($this->testConfigFile, $configContent);

        // Call updateConfig() - should reset locale and recalculate based on config
        $context->updateConfig();

        // Verify locale has been reset and recalculated to 'ru' from config
        $this->assertEquals('ru', $request->locale);
        $this->assertEquals('ru', $context->getRequest()->locale);
    }
}
