<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * This setup assumes that you have a local PHP webserver that serves COPS under /cops/
 * for example by creating a sym-link from /home/.../seblucas-cops to /var/www/html/cops
 *
 * $ sudo ln -s /home/.../seblucas-cops /var/www/html/cops
 *
 * You should adapt the $serverUrl below if you have a different configuration.
 *
 * This test uses HttpBrowser (symfony/browser-kit) to simulate browser requests.
 * It does not execute or even understand Javascript, so any client-side handling
 * has to be done inside the tests.
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Handlers\JsonHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Route;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

#[RequiresMethod(HttpBrowser::class, '__construct')]
class BrowserKitTest extends TestCase
{
    public static string $baseDir = '/cops/';
    public static string $serverUrl = 'http://localhost';
    /** @var array<mixed> */
    public static array $localConfig = [];
    /** @var HttpBrowser */
    public $browser;
    /** @var ?string */
    public $userAgent = 'Kindle/2.0';  // Chrome by default, override here with 'Kindle/2.0'
    /** @var ?string */
    public $template = 'default';

    public static function setUpBeforeClass(): void
    {
        // get config/local.php as used by webserver
        $config = [];
        include dirname(__DIR__) . '/config/default.php';
        include dirname(__DIR__) . '/config/local.php';
        self::$localConfig = $config;
    }

    public function setUp(): void
    {
        $this->userAgent ??= 'Chrome';
        $this->createBrowser($this->template, $this->userAgent);
        Config::set('full_url', self::$baseDir);
        Route::setBaseUrl(null);
    }

    public function tearDown(): void
    {
        Config::set('full_url', '');
        Route::setBaseUrl(null);
    }

    /**
     * Summary of createBrowser
     * @param string $template
     * @param string $userAgent
     * @return void
     */
    protected function createBrowser($template = 'default', $userAgent = 'Chrome')
    {
        $this->template = $template;
        $this->userAgent = $userAgent;
        //$options = ['headers' => ['User-Agent' => 'BrowserKit Test for ' . $userAgent]];
        $options = [];
        $cookie = new Cookie('template', $template, (string) strtotime('+1 day'));
        $cookieJar = new CookieJar();
        $cookieJar->set($cookie);
        $this->browser = new HttpBrowser(HttpClient::create($options), null, $cookieJar);
        $this->browser->setServerParameter('HTTP_USER_AGENT', 'BrowserKit Test for ' . $userAgent);
    }

    /**
     * Summary of url
     * @param string $uri
     * @param array<mixed> $params
     * @return Crawler
     */
    protected function url($uri, $params = [])
    {
        $url = self::$serverUrl . $uri;
        //$crawler = $this->browser->request('GET', $url, $params, [], ['HTTP_COOKIE' => new Cookie('template', $this->template)]);
        $crawler = $this->browser->request('GET', $url, $params);
        $this->checkResponse();
        return $crawler;
    }

    /**
     * Summary of ajax
     * @param string $uri
     * @param array<mixed> $params
     * @return array<mixed>
     */
    protected function ajax($uri, $params = [])
    {
        $url = self::$serverUrl . $uri;
        $crawler = $this->browser->xmlHttpRequest('GET', $url, $params);
        $response = $this->checkResponse();
        return $response->toArray();
    }

    /**
     * Summary of json
     * @param string $uri
     * @param array<mixed> $params
     * @return array<mixed>
     */
    protected function json($uri, $params = [])
    {
        $url = self::$serverUrl . $uri;
        $crawler = $this->browser->jsonRequest('GET', $url, $params);
        $response = $this->checkResponse();
        return $response->toArray();
    }

    /**
     * Summary of checkResponse
     * @param int $status
     * @param ?string $expected
     * @return Response
     */
    protected function checkResponse($status = 200, $expected = null)
    {
        /** @var Response */
        $response = $this->browser->getResponse();
        $this->assertEquals($status, $response->getStatusCode());
        if ($expected) {
            $content = $response->getContent();
            $this->assertStringContainsString($expected, $content);
        }
        return $response;
    }

    /**
     * Summary of providerTemplates
     * @return array<mixed>
     */
    public static function providerTemplates()
    {
        return [
            ['default', '//body/div/section/article'],
            // using partial match - could use //div[contains(concat(' ',normalize-space(@class),' '),' foobar ')]
            ['bootstrap', '//div[contains(@class, "panel-default")]'],
            // using exact match
            ['bootstrap2', '//div[@class="panel panel-default"]'],
        ];
    }

    /**
     * Summary of testClientSideRendering
     * @param string $template
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerTemplates')]
    public function testClientSideRendering($template = 'default'): void
    {
        $this->createBrowser($template, 'Chrome');

        $uri = HtmlHandler::index();
        $crawler = $this->url($uri);

        $uri = JsonHandler::link() . '/index?complete=1';

        $expected = 'initiateAjax ("' . $uri . '", "' . $template . '", "' . Route::path("templates") . '");';
        $script = $crawler->filterXPath('//head/script[not(@src)]')->text();
        $this->assertStringContainsString($expected, $script);

        // with standard local.php.example
        $expected = 8;
        $result = $this->ajax($uri);
        $this->assertCount($expected, $result['entries']);

        // From util.js: index.php?page=9&current={0}&query={1}&db={2} -> replace ("index", "getJSON")
        // ...
        // see checkJsonSearch()
        // ...
    }

    /**
     * Summary of testServerSideRendering
     * @param string $template
     * @param string $xpath
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerTemplates')]
    public function testServerSideRendering($template = 'default', $xpath = '//body/div/section/article'): void
    {
        $this->createBrowser($template, 'Kindle/2.0');

        $uri = HtmlHandler::index();
        $crawler = $this->url($uri);

        // with standard local.php.example
        $expected = 8;
        $articles = $crawler->filterXPath($xpath);
        $this->assertCount($expected, $articles);

        $button = $crawler->selectButton('searchButton');
        $form = $button->form();
        //$expected = ['page' => '9', 'query' => ''];
        $this->assertArrayHasKey('query', $form->getValues());

        // for bootstrap & bootstrap2 the correct URI was set in util.js and there was no hidden 'page' field
        if (!$form->has('page')) {
            $query = 'ali';
            $expected = [
                [
                    'class' => 'tt-header',
                    'title' => 'Search result for *ali* in books',
                    'content' => '2 books',
                    'navlink' => 'index.php?page=9&query=ali&scope=book',
                    'number' => 2,
                ],
            ];
            $this->checkJsonSearch($query, $expected);

        } else {
            $form['page'] = '9';
            $form['query'] = 'ali';
            //$form['scope'] = 'book';
            $crawler = $this->browser->submit($form);
            $this->checkResponse();

            $articles = $crawler->filterXPath($xpath);
            $this->assertCount(1, $articles);
            if ($template == 'bootstrap2') {
                $this->assertEquals('Search result for *ali* in books 2', $articles->filterXPath('//div[@class="panel-body"]')->text());
            } else {
                $this->assertEquals('Search result for *ali* in books', $articles->filterXPath('//h2')->text());
            }
            if ($template == 'bootstrap') {
                $this->assertEquals('2', $articles->filterXPath('//span')->text());
            }
            if ($template == 'default') {
                $this->assertEquals('2 books', $articles->filterXPath('//h4')->text());
            }
            $this->assertEquals(Route::absolute('/search/ali/book'), $articles->filterXPath('//a')->attr('href'));
        }
    }

    /**
     * Summary of checkJsonSearch
     * @param string $query
     * @param array<mixed> $expectedEntries
     * @return void
     */
    protected function checkJsonSearch($query, $expectedEntries)
    {
        // From util.js: index.php?page=9&current={0}&query={1}&db={2} -> replace ("index", "getJSON")
        $uri = 'index.php?page=9&current=index&query=' . $query . '&db=';
        $result = $this->ajax($uri);

        $this->assertEquals($expectedEntries, $result['entries']);
    }
}
