<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\DotPHPTemplate;
use SebLucas\Cops\Output\HtmlRenderer;
use SebLucas\Cops\Output\TwigTemplate;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Database\Database;
use SebLucas\Cops\Calibre\BookShelf;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Pages\PageId;
use DOMDocument;
use DOMXPath;

class HtmlRendererTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    /**
     * The function for the head of the HTML catalog
     * @param mixed $templateName
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerDotTemplate')]
    public function testDotHeader($templateName)
    {
        $server = ["HTTP_USER_AGENT" => "Firefox"];
        $cookie = ["template" => $templateName];
        $request = Request::build([], null, $server, null, $cookie);
        $html = new HtmlRenderer();
        $data = $html->getTemplateData($request);
        $template = new DotPHPTemplate($request);
        $tpl = $template->getDotTemplate(dirname(__DIR__, 2) . '/templates/' . $templateName . '/file.html');

        $head = $tpl($data);
        $this->assertStringContainsString("<head>", $head);
        $this->assertStringContainsString("</head>", $head);
    }

    /**
     * FALSE is returned if the create_function failed (meaning there was a syntax error)
     * @param mixed $templateName
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerDotTemplate')]
    public function testDotServerSide($templateName)
    {
        $server = ["HTTP_USER_AGENT" => "Firefox"];
        $cookie = ["template" => $templateName];
        $request = Request::build([], null, $server, null, $cookie);
        $template = new DotPHPTemplate($request);
        $this->assertNull($template->serverSide(null));

        $json = new JsonRenderer();
        $data = $json->getJson($request, true);
        // Note: this will trigger warning for undefined array key in bootstrap5 - see it.c.config.ignored_categories.* checks in header.html
        $output = $template->serverSide($data);

        $old = libxml_use_internal_errors(true);
        $html = new DOMDocument();
        $html->loadHTML("<html><head></head><body>" . $output . "</body></html>");
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            // ignore invalid tags from HTML5 which libxml doesn't know about
            if ($error->code == 801) {
                continue;
            }
            $this->fail("Error parsing output for server-side rendering of template " . $templateName . "\n" . $error->message . "\n" . $output);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($old);
    }

    /**
     * Summary of providerDotTemplate
     * @return array<mixed>
     */
    public static function providerDotTemplate()
    {
        return [
            ["bootstrap2"],
            ["bootstrap5"],
            ["default"],
        ];
    }

    public function testRenderDot(): void
    {
        $server = ["HTTP_USER_AGENT" => "Firefox"];
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(["page" => $page], null, $server);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "index.php/recent?complete=1";
        $this->assertStringContainsString($expected, $output);
    }

    public function testRenderDotServerSide(): void
    {
        $server = ["HTTP_USER_AGENT" => "Kindle/1.0"];
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(["page" => $page], null, $server);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "Alice&#039;s Adventures in Wonderland";
        $this->assertStringContainsString($expected, $output);
    }

    /**
     * Summary of testTwigServerSide
     * @param mixed $templateName
     * @return void
     */
    public function testTwigServerSide($templateName = 'twigged')
    {
        $server = [];
        $cookie = ["template" => $templateName];
        $request = Request::build([], null, $server, null, $cookie);
        $template = new TwigTemplate($request);
        $this->assertNull($template->serverSide(null));

        $json = new JsonRenderer();
        $data = $json->getJson($request, true);
        $output = $template->serverSide($data);

        $old = libxml_use_internal_errors(true);
        $html = new DOMDocument();
        $html->loadHTML("<html><head></head><body>" . $output . "</body></html>");
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            // ignore invalid tags from HTML5 which libxml doesn't know about
            if ($error->code == 801) {
                continue;
            }
            $this->fail("Error parsing output for server-side rendering of template " . $templateName . "\n" . $error->message . "\n" . $output);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($old);
    }

    /**
     * Summary of testTwigRenderBlock
     * @param string $templateName
     * @param string $name
     * @param string $block
     * @return void
     */
    public function testTwigRenderBlock($templateName = 'twigged', $name = 'mainlist.html', $block = 'main'): void
    {
        $server = ["HTTP_USER_AGENT" => "Firefox"];
        $cookie = ["template" => $templateName];
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(["page" => $page], null, $server, null, $cookie);

        $template = new TwigTemplate($request);

        $json = new JsonRenderer();
        $data = $json->getJson($request, true);

        $expected = "recent.html";
        $name = $template->getTemplateName($data);
        $this->assertEquals($expected, $name);

        $output = $template->renderBlock($data, $name, $block);

        $old = libxml_use_internal_errors(true);
        $html = new DOMDocument();
        $html->loadHTML("<html><head></head><body>" . $output . "</body></html>");
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            // ignore invalid tags from HTML5 which libxml doesn't know about
            if ($error->code == 801) {
                continue;
            }
            $this->fail("Error parsing output for server-side rendering of template " . $templateName . "\n" . $error->message . "\n" . $output);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($old);

        $finder = new DomXPath($html);
        $classname = "books";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

        $expected = 16;
        $this->assertCount($expected, $nodes);
    }

    public function testRenderTwig(): void
    {
        $server = ["HTTP_USER_AGENT" => "Firefox"];
        $cookie = ["template" => "twigged"];
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(["page" => $page], null, $server, null, $cookie);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "index.php/recent?complete=1";
        $this->assertStringContainsString($expected, $output);
    }

    public function testRenderTwigServerSide(): void
    {
        $server = ["HTTP_USER_AGENT" => "Kindle/1.0"];
        $cookie = ["template" => "twigged"];
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(["page" => $page], null, $server, null, $cookie);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "Alice&#039;s Adventures in Wonderland";
        $this->assertStringContainsString($expected, $output);
    }

    public function testTwigShelfProvidersAreMutuallyExclusive(): void
    {
        Config::set('use_server_shelves', 'shared');
        $cookie = ['template' => 'twigged', 'use_client_shelves' => '1'];
        $request = Request::build([], null, ['HTTP_USER_AGENT' => 'Firefox'], null, $cookie);

        $templateData = new HtmlRenderer()->getTemplateData($request);
        $jsonData = new JsonRenderer()->getJson($request, true);

        $this->assertTrue($templateData['use_server_shelves']);
        $this->assertFalse($templateData['use_client_shelves']);
        $this->assertTrue($jsonData['c']['config']['use_server_shelves']);
        $this->assertFalse($jsonData['c']['config']['use_client_shelves']);

        Config::set('use_server_shelves', '');
        $clientRequest = Request::build([], null, ['HTTP_USER_AGENT' => 'Firefox'], null, $cookie);
        $clientTemplateData = new HtmlRenderer()->getTemplateData($clientRequest);
        $clientJsonData = new JsonRenderer()->getJson($clientRequest, true);

        $this->assertFalse($clientTemplateData['use_server_shelves']);
        $this->assertTrue($clientTemplateData['use_client_shelves']);
        $this->assertFalse($clientJsonData['c']['config']['use_server_shelves']);
        $this->assertTrue($clientJsonData['c']['config']['use_client_shelves']);
        Config::set('use_server_shelves', '');
    }

    public function testTwigBookIncludesServerShelfStateAndReturnUrl(): void
    {
        $shelvesDirectory = sys_get_temp_dir() . '/cops_test_twig_shelves_' . bin2hex(random_bytes(4)) . '/';
        mkdir($shelvesDirectory, 0o750, true);
        Config::set('shelves_directory', $shelvesDirectory);
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);

        try {
            $shelf = new BookShelf();
            $shelf->load();
            $shelf->addBook(17, 0);

            $request = Request::build(
                ['page' => PageId::BOOK_DETAIL, 'id' => 17],
                null,
                ['HTTP_USER_AGENT' => 'Firefox'],
                null,
                ['template' => 'twigged']
            );
            $data = new JsonRenderer()->getJson($request, true);

            $this->assertTrue($data['book']['inServerShelf']);
            $this->assertSame(17, $data['book']['id']);
            $this->assertSame(0, $data['book']['databaseId']);
            $this->assertSame('Favorites', $data['book']['shelfname']);
            $this->assertStringContainsString('/shelves/toggle', $data['book']['toggleurl']);
            $this->assertStringContainsString('/books/17', $data['book']['returnurl']);

            $template = new TwigTemplate($request);
            $detailHtml = $template->renderBlock($data, 'bookdetail.html', 'main');
            $this->assertStringContainsString('server-shelf-toggle-form', $detailHtml);
            $this->assertStringNotContainsString('shelf-toggle-btn', $detailHtml);

            $listRequest = Request::build(
                ['page' => PageId::ALL_RECENT_BOOKS],
                null,
                ['HTTP_USER_AGENT' => 'Firefox'],
                null,
                ['template' => 'twigged']
            );
            $listData = new JsonRenderer()->getJson($listRequest, true);
            $listTemplate = new TwigTemplate($listRequest);
            $listHtml = $listTemplate->renderBlock($listData, 'booklist.html', 'content');
            $this->assertStringContainsString('server-shelf-toggle-form', $listHtml);
            $this->assertStringNotContainsString('shelf-toggle-btn', $listHtml);

            Config::set('use_server_shelves', '');
            $clientRequest = Request::build(
                ['page' => PageId::ALL_RECENT_BOOKS],
                null,
                ['HTTP_USER_AGENT' => 'Firefox'],
                null,
                ['template' => 'twigged', 'use_client_shelves' => '1']
            );
            $clientData = new JsonRenderer()->getJson($clientRequest, true);
            $clientTemplate = new TwigTemplate($clientRequest);
            $clientHtml = $clientTemplate->renderBlock($clientData, 'booklist.html', 'content');
            $this->assertStringContainsString('shelf-toggle-btn', $clientHtml);
            $this->assertStringNotContainsString('server-shelf-toggle-form', $clientHtml);
        } finally {
            foreach (glob($shelvesDirectory . '{,.}*', GLOB_BRACE) ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($shelvesDirectory);
            Config::set('use_server_shelves', '');
        }
    }

    public function testTwigServerShelfPageRendersFormsInBothModes(): void
    {
        $shelvesDirectory = sys_get_temp_dir() . '/cops_test_twig_shelf_page_' . bin2hex(random_bytes(4)) . '/';
        mkdir($shelvesDirectory, 0o750, true);
        Config::set('shelves_directory', $shelvesDirectory);
        Config::set('use_server_shelves', BookShelf::MODE_SHARED);

        try {
            $shelf = new BookShelf();
            $shelf->load();
            $shelf->createShelf('To Read');
            $shelf->addBook(17, 0, 'To Read');

            $cookie = ['template' => 'twigged'];
            $serverRequest = Request::build(
                ['page' => PageId::SHELVES, 'shelf' => 'To Read'],
                null,
                ['HTTP_USER_AGENT' => 'Kindle/1.0'],
                null,
                $cookie
            );
            $serverData = new JsonRenderer()->getJson($serverRequest, true);
            $serverHtml = new TwigTemplate($serverRequest)->serverSide($serverData);

            $clientRequest = Request::build(
                ['page' => PageId::SHELVES, 'shelf' => 'To Read'],
                null,
                ['HTTP_USER_AGENT' => 'Firefox'],
                null,
                $cookie
            );
            $clientData = new JsonRenderer()->getJson($clientRequest, true);
            $clientTemplate = new TwigTemplate($clientRequest);
            $clientHtml = $clientTemplate->renderBlock($clientData, 'shelves.html', 'main');

            foreach ([$serverHtml, $clientHtml] as $html) {
                $this->assertStringContainsString('id="server-shelves"', $html);
                $this->assertStringContainsString('server-shelf-toggle-form', $html);
                $this->assertStringContainsString('multipart/form-data', $html);
                $this->assertStringContainsString('/shelves/export', $html);
            }
            $this->assertSame(1, $serverData['totalNumber']);
            $this->assertSame(1, $clientData['totalNumber']);
            $this->assertSame('To Read', $serverData['activeShelf']);
            $this->assertTrue($serverData['entries'][0]['book']['inServerShelf']);
        } finally {
            foreach (glob($shelvesDirectory . '{,.}*', GLOB_BRACE) ?: [] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($shelvesDirectory);
            Config::set('use_server_shelves', '');
        }
    }
}
