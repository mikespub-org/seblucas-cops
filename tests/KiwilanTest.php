<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use Opis\JsonSchema\Validator;
use Opis\JsonSchema\Errors\ErrorFormatter;
//use SebLucas\Cops\Output\OpdsRenderer;
use SebLucas\Cops\Output\KiwilanOPDS as OpdsRenderer;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework;
use SebLucas\Cops\Handlers\OpdsHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\Page;
use SebLucas\Cops\Pages\PageId;

#[RequiresMethod(Validator::class, '__construct')]
class KiwilanTest extends TestCase
{
    public const OPDS_SCHEMAS = __DIR__ . "/schema/opds";
    public const READIUM_SCHEMAS = __DIR__ . "/schema/readium";
    public const FEED_SCHEMA = __DIR__ . "/schema/opds/feed.schema.json";
    public const TEST_FEED = __DIR__ . "/text.json";

    public static string $baseUrl = 'http://localhost:8080/cops/';
    /** @var Validator */
    public static $validator;
    /** @var string */
    public static $schema;
    /** @var class-string */
    private static $handler = OpdsHandler::class;

    public static function setUpBeforeClass(): void
    {
        Config::set('full_url', self::$baseUrl);
        Route::setBaseUrl(null);
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();

        // See https://opis.io/json-schema/2.x/php-loader.html
        self::$validator = new Validator();
        self::$validator->setMaxErrors(5);

        $resolver = self::$validator->resolver();
        $resolver->registerPrefix('https://readium.org/webpub-manifest/schema/', self::READIUM_SCHEMAS);
        $resolver->registerPrefix('https://drafts.opds.io/schema/', self::OPDS_SCHEMAS);

        self::$schema = file_get_contents(self::FEED_SCHEMA);
    }

    public static function tearDownAfterClass(): void
    {
        Config::set('full_url', '');
        Route::setBaseUrl(null);
        if (!file_exists(self::TEST_FEED)) {
            return;
        }
        unlink(self::TEST_FEED);
    }

    /**
     * Summary of opdsValidator
     * @param string $feed
     * @param bool $expected expected result (default true)
     * @return bool
     */
    protected function opdsValidator($feed, $expected = true)
    {
        $data = json_decode(file_get_contents($feed));

        $result = self::$validator->validate($data, self::$schema);

        // See https://opis.io/json-schema/2.x/php-error-formatter.html
        if ($result->hasError()) {
            if (!$expected) {
                return false;
            }
            echo 'OPDS 2.0 validation error';
            echo ' for test ' . $this->name() . "\n";
            $error = $result->error();
            $formatter = new ErrorFormatter();
            // Print helper
            $print = function ($value) {
                echo json_encode(
                    $value,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                ), PHP_EOL;
                echo '-----------', PHP_EOL;
            };
            // default - multiple
            $print($formatter->format($error, true));
            //echo json_encode($data, JSON_PRETTY_PRINT);
            copy($feed, $feed . '.' . $this->name());
            return false;
        }
        return true;
    }

    /**
     * Summary of opdsCompleteValidation
     * @param string $feed
     * @return bool
     */
    protected function opdsCompleteValidation($feed)
    {
        return $this->opdsValidator($feed);
    }

    /**
     * Summary of checkEntries
     * @param Page $currentPage
     * @param string $feed
     * @return bool
     */
    protected function checkEntries($currentPage, $feed)
    {
        $hasPaging = $currentPage->isPaginated();
        $numEntries = count($currentPage->entryArray);
        $contents = json_decode(file_get_contents($feed), true, 512, JSON_THROW_ON_ERROR);
        if ($contents === null) {
            echo file_get_contents($feed);
            return false;
        }
        if ($hasPaging) {
            copy($feed, $feed . '.' . $this->name());
        }
        if ($currentPage->containsBook()) {
            if (count($contents['publications']) == $numEntries) {
                return true;
            }
        } else {
            if (count($contents['navigation']) == $numEntries) {
                return true;
            }
        }
        echo json_encode($contents, JSON_PRETTY_PRINT) . "\n";
        if ($hasPaging) {
            echo $this->name() . ": page " . $currentPage->n . " has $numEntries of " . $currentPage->totalNumber . " entries\n";
        } else {
            echo $this->name() . ": $numEntries entries\n";
        }
        return false;
    }

    public function testPageIndex(): void
    {
        $page = PageId::INDEX;

        Config::set('subtitle_default', "My subtitle");
        $request = self::$handler::request(['page' => $page]);

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsValidator(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        $_SERVER ["HTTP_USER_AGENT"] = "XXX";
        Config::set('generate_invalid_opds_stream', "1");
        $request = self::$handler::request(['page' => $page]);

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsValidator(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        unset($_SERVER['HTTP_USER_AGENT']);
        Config::set('generate_invalid_opds_stream', "0");
    }

    /**
     * @param mixed $page
     * @param mixed $query
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerPage')]
    public function testMostPages($page, $query)
    {
        $request = self::$handler::request(['page' => $page]);
        $request->set('query', $query);
        //$_SERVER['REQUEST_URI'] = OpdsRenderer::$endpoint . "?" . $request->query();

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        //unset($_SERVER['REQUEST_URI']);
    }

    /**
     * Summary of providerPage
     * @return array<mixed>
     */
    public static function providerPage()
    {
        return [
            [PageId::OPENSEARCH, "car"],
            [PageId::ALL_AUTHORS, null],
            [PageId::ALL_SERIES, null],
            [PageId::ALL_TAGS, null],
            [PageId::ALL_PUBLISHERS, null],
            [PageId::ALL_LANGUAGES, null],
            [PageId::ALL_RECENT_BOOKS, null],
            [PageId::ALL_BOOKS, null],
        ];
    }

    public function testPageIndexMultipleDatabase(): void
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::INDEX;
        $request = self::$handler::request(['page' => $page]);
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testOpenSearchDescription(): void
    {
        $request = self::$handler::request(['page', PageId::OPENSEARCH]);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->getOpenSearch($request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        // OpenSearch is not a valid OPDS 2.0 feed
        $this->AssertFalse($this->opdsValidator(self::TEST_FEED, false));
        $this->markTestSkipped('OpenSearch is not a valid OPDS 2.0 feed');
    }

    public function testPageAuthorMultipleDatabase(): void
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::AUTHOR_DETAIL;
        $request = self::$handler::request(['page' => $page]);
        $request->set('id', "1");
        $request->set('db', "0");

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testPageAuthorsDetail(): void
    {
        $page = PageId::AUTHOR_DETAIL;

        Config::set('max_item_per_page', 2);
        $request = self::$handler::request(['page' => $page]);
        $request->set('id', "1");
        $request->set('n', "1");

        // First page

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        // Second page

        $request->set('n', "2");
        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        // No pagination
        Config::set('max_item_per_page', -1);
    }

    public function testPageAuthorsDetail_WithFacets(): void
    {
        $page = PageId::AUTHOR_DETAIL;

        Config::set('books_filter', ["Only Short Stories" => "Short Stories", "No Short Stories" => "!Short Stories"]);
        $request = self::$handler::request(['page' => $page]);
        $request->set('id', "1");
        $request->set('tag', "Short Stories");

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        Config::set('books_filter', []);
    }

    public function testPageAuthorsDetail_WithoutAnyId(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $_SERVER['REQUEST_URI'] = "index.php?XXXX";
        $request = self::$handler::request(['page' => $page]);
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->idPage = null;

        $OPDSRender = new OpdsRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }

    public function testOpdsHandler(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);
        $handler = Framework::createHandler('opds');

        ob_start();
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $result = json_decode($output, true);

        $expected = "Calibre OPDS: Recent additions";
        $this->assertEquals($expected, $result['metadata']['title']);
    }
}
