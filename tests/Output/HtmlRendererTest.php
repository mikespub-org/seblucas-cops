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
use SebLucas\Cops\Calibre\Database;
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
            ["bootstrap"],
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
}
