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
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Exception;

class BrowserKitTest extends TestCase
{
    public static string $serverUrl = 'http://localhost/cops/';
    /** @var HttpBrowser */
    public $browser;
    /** @var string|null */
    public $userAgent = 'Kindle/2.0';  // Chrome by default, override here with 'Kindle/2.0'
    /** @var string|null */
    public $template = 'default';

    public function setUp(): void
    {
        $this->userAgent ??= 'Chrome';
        $this->createBrowser($this->template, $this->userAgent);
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
     * @param string|null $expected
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
    protected function providerTemplates()
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
     * @dataProvider providerTemplates
     * @param string $template
     * @return void
     */
    public function testClientSideRendering($template = 'default'): void
    {
        $this->createBrowser($template, 'Chrome');

        $uri = 'index.php?page=index';
        $crawler = $this->url('index.php?page=index');

        $uri = 'getJSON.php?page=index&complete=1';
        $expected = 'initiateAjax ("' . $uri . '", "' . $template . '");';
        $script = $crawler->filterXPath('//head/script[not(@src)]')->text();
        $this->assertStringContainsString($expected, $script);

        $expected = 11;
        $result = $this->ajax($uri);
        $this->assertCount($expected, $result['entries']);

        // From util.js: index.php?page=9&current={0}&query={1}&db={2} -> replace ("index", "getJSON")
        // ...
        // see checkJsonSearch()
        // ...
    }

    /**
     * Summary of testServerSideRendering
     * @dataProvider providerTemplates
     * @param string $template
     * @param string $xpath
     * @return void
     */
    public function testServerSideRendering($template = 'default', $xpath = '//body/div/section/article'): void
    {
        $this->createBrowser($template, 'Kindle/2.0');

        $crawler = $this->url('index.php?page=index');

        $expected = 11;
        $articles = $crawler->filterXPath($xpath);
        $this->assertCount($expected, $articles);

        $button = $crawler->selectButton('searchButton');
        $form = $button->form();
        //$expected = ['page' => '9', 'query' => ''];
        $this->assertArrayHasKey('query', $form->getValues());

        // for bootstrap & bootstrap2 the correct URI is set in util.js and there is no hidden 'page' field
        if (!$form->has('page')) {
            $query = 'ali';
            $expected = [
                [
                    'class' => 'tt-header',
                    'title' => 'Search result for *ali* in books',
                    'content' => '2 books',
                    'navlink' => 'index.php?page=9&query=ali&db=&scope=book',
                    'number' => 2,
                ],
            ];
            $this->checkJsonSearch($query, $expected);

        } else {
            //$form['page'] = '9';
            $form['query'] = 'ali';
            $crawler = $this->browser->submit($form);
            $this->checkResponse();

            $articles = $crawler->filterXPath($xpath);
            $this->assertCount(1, $articles);
            $this->assertEquals('Search result for *ali* in books', $articles->filterXPath('//h2')->text());
            $this->assertEquals('2 books', $articles->filterXPath('//h4')->text());
            $this->assertEquals('index.php?page=9&query=ali&db=&scope=book', $articles->filterXPath('//a')->attr('href'));
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
        $uri = 'getJSON.php?page=9&current=index&query=' . $query . '&db=';
        $result = $this->ajax($uri);

        $this->assertEquals($expectedEntries, $result['entries']);
    }
}
