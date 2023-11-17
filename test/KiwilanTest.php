<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use Opis\JsonSchema\Validator;
use Opis\JsonSchema\Errors\ErrorFormatter;
//use SebLucas\Cops\Output\OPDSRenderer;
use SebLucas\Cops\Output\KiwilanOPDS as OPDSRenderer;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\Page;
use SebLucas\Cops\Pages\PageId;

class KiwilanTest extends TestCase
{
    public const OPDS_SCHEMAS = __DIR__ . "/schema/opds";
    public const READIUM_SCHEMAS = __DIR__ . "/schema/readium";
    public const FEED_SCHEMA = __DIR__ . "/schema/opds/feed.schema.json";
    public const TEST_FEED = __DIR__ . "/text.json";

    public static string $baseUrl = 'htt://localhost:8080/cops/';
    /** @var Validator */
    public static $validator;
    /** @var string */
    public static $schema;

    public static function setUpBeforeClass(): void
    {
        Config::set('full_url', static::$baseUrl);
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
            echo 'OPDS validation error';
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
            copy($feed, $feed . '.' . $this->getName());
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
            echo $this->getName() . ": page " . $currentPage->n . " has $numEntries of " . $currentPage->totalNumber . " entries\n";
        } else {
            echo $this->getName() . ": $numEntries entries\n";
        }
        return false;
    }

    public function testPageIndex(): void
    {
        $page = PageId::INDEX;

        Config::set('subtitle_default', "My subtitle");
        $request = new Request();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsValidator(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        $_SERVER ["HTTP_USER_AGENT"] = "XXX";
        Config::set('generate_invalid_opds_stream', "1");
        $request = new Request();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsValidator(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        unset($_SERVER['HTTP_USER_AGENT']);
        Config::set('generate_invalid_opds_stream', "0");
    }

    /**
     * @dataProvider providerPage
     * @param mixed $page
     * @param mixed $query
     * @return void
     */
    public function testMostPages($page, $query)
    {
        $request = new Request();
        $request->set('page', $page);
        $request->set('query', $query);
        $_SERVER['REQUEST_URI'] = OPDSRenderer::$endpoint . "?" . $request->query();

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }

    /**
     * Summary of providerPage
     * @return array<mixed>
     */
    public function providerPage()
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
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
                                              "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::INDEX;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testOpenSearchDescription(): void
    {
        $request = new Request();

        $OPDSRender = new OPDSRenderer();

        $response = $OPDSRender->getOpenSearch($request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        // OpenSearch is not a valid OPDS 2.0 feed
        $this->AssertFalse($this->opdsValidator(self::TEST_FEED, false));
        $this->markTestSkipped('OpenSearch is not a valid OPDS 2.0 feed');
    }

    public function testPageAuthorMultipleDatabase(): void
    {
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
                                              "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('db', "0");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

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
        $request = new Request();
        $request->set('id', "1");
        $request->set('n', "1");

        // First page

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        // Second page

        $request->set('n', "2");
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

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
        $request = new Request();
        $request->set('id', "1");
        $request->set('tag', "Short Stories");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

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
        $request = new Request();
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();
        $currentPage->idPage = null;

        $OPDSRender = new OPDSRenderer();

        $response = $OPDSRender->render($currentPage, $request);
        file_put_contents(self::TEST_FEED, $response->getContents());
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }
}
