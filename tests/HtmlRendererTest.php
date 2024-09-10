<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Output\DotPHPTemplate;
use SebLucas\Cops\Output\HtmlRenderer;
use SebLucas\Cops\Output\TwigTemplate;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\JsonRenderer;
use SebLucas\Cops\Pages\PageId;
use DOMDocument;

class HtmlRendererTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        $_GET = [];
    }

    /**
     * The function for the head of the HTML catalog
     * @param mixed $templateName
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerDotTemplate')]
    public function testDotHeader($templateName)
    {
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $_COOKIE["template"] = $templateName;
        $request = new Request();
        $html = new HtmlRenderer();
        $data = $html->getTemplateData($request);
        $template = new DotPHPTemplate($request);
        $tpl = $template->getDotTemplate(dirname(__DIR__) . '/templates/' . $templateName . '/file.html');

        $head = $tpl($data);
        $this->assertStringContainsString("<head>", $head);
        $this->assertStringContainsString("</head>", $head);

        unset($_COOKIE["template"]);
    }

    /**
     * FALSE is returned if the create_function failed (meaning there was a syntax error)
     * @param mixed $templateName
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerDotTemplate')]
    public function testDotServerSide($templateName)
    {
        $_COOKIE["template"] = $templateName;
        $request = new Request();
        $template = new DotPHPTemplate($request);
        $this->assertNull($template->serverSide(null, $templateName));

        $json = new JsonRenderer();
        $data = $json->getJson($request, true);
        $output = $template->serverSide($data, $templateName);

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

        unset($_COOKIE['template']);
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
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        if (Config::get('use_route_urls')) {
            $expected = "index.php/recent?complete=1";
        } else {
            $expected = "index.php?page=10&complete=1";
        }
        $this->assertStringContainsString($expected, $output);
    }

    public function testRenderDotServerSide(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = "Kindle/1.0";

        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "Alice&#039;s Adventures in Wonderland";
        $this->assertStringContainsString($expected, $output);

        unset($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * Summary of testTwigServerSide
     * @param mixed $templateName
     * @return void
     */
    public function testTwigServerSide($templateName = 'twigged')
    {
        $_COOKIE["template"] = $templateName;
        $request = new Request();
        $template = new TwigTemplate($request);
        $twig = $template->getTwigEnvironment('templates/' . $templateName);
        $this->assertNull($template->serverSide($twig, null, $templateName));

        $json = new JsonRenderer();
        $data = $json->getJson($request, true);
        $output = $template->serverSide($twig, $data, $templateName);

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

        unset($_COOKIE['template']);
    }

    public function testRenderTwig(): void
    {
        $_COOKIE['template'] = "twigged";

        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        if (Config::get('use_route_urls')) {
            $expected = "index.php/recent?complete=1";
        } else {
            $expected = "index.php?page=10&complete=1";
        }
        $this->assertStringContainsString($expected, $output);

        unset($_COOKIE['template']);
    }

    public function testRenderTwigServerSide(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = "Kindle/1.0";
        $_COOKIE['template'] = "twigged";

        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);

        $html = new HtmlRenderer();
        $output = $html->render($request);

        $expected = "Alice&#039;s Adventures in Wonderland";
        $this->assertStringContainsString($expected, $output);

        unset($_COOKIE['template']);
        unset($_SERVER['HTTP_USER_AGENT']);
    }
}