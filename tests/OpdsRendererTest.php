<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Output\OpdsRenderer;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;
use SebLucas\Cops\Pages\Page;
use SebLucas\Cops\Pages\PageId;

class OpdsRendererTest extends TestCase
{
    public const OPDS_RELAX_NG = __DIR__ . "/opds-relax-ng/opds_catalog_1_2.rng";
    public const OPENSEARCHDESCRIPTION_RELAX_NG = __DIR__ . "/opds-relax-ng/opensearchdescription.rng";
    public const JING_JAR = __DIR__ . "/jing.jar";
    public const OPDSVALIDATOR_JAR = __DIR__ . "/OPDSValidator.jar";
    public const TEST_FEED = __DIR__ . "/text.atom";
    private static string $handler = 'feed';

    public static function setUpBeforeClass(): void
    {
        Config::set('full_url', '/cops/');
        Route::setBaseUrl(null);
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
        // try out route urls
        //Config::set('use_route_urls', true);
    }

    public static function tearDownAfterClass(): void
    {
        Config::set('full_url', '');
        Route::setBaseUrl(null);
        if (!file_exists(self::TEST_FEED)) {
            return;
        }
        unlink(self::TEST_FEED);
        //Config::set('use_route_urls', null);
    }

    /**
     * Summary of jingValidateSchema
     * @param string $feed
     * @param string $relax
     * @param bool $expected expected result (default true)
     * @return bool
     */
    protected function jingValidateSchema($feed, $relax = self::OPDS_RELAX_NG, $expected = true)
    {
        $path = "";
        $code = null;
        $res = system($path . 'java -jar "' . self::JING_JAR . '" "' . $relax . '" "' . $feed . '" 2>&1', $code);
        if ($res != '') {
            if ($expected) {
                echo 'RelaxNG validation error: ' . $res;
            }
            return false;
            //} elseif (isset($code) && $code > 0) {
            //    echo 'Return code: '.strval($code);
            //    return false;
        } else {
            return true;
        }
    }

    /**
     * Summary of opdsValidator
     * @param string $feed
     * @param bool $expected expected result (default true)
     * @return bool
     */
    protected function opdsValidator($feed, $expected = true)
    {
        $oldcwd = getcwd(); // Save the old working directory
        chdir(__DIR__);
        $path = "";
        $res = system($path . 'java -jar "' . self::OPDSVALIDATOR_JAR . '" -v 1.2 "' . $feed . '" 2>&1');
        chdir($oldcwd);
        if ($res != '') {
            if ($expected) {
                copy($feed, $feed . '.bad');
                echo 'OPDS validation error: ' . $res;
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * Summary of opdsCompleteValidation
     * @param string $feed
     * @param bool $expected expected result (default true)
     * @return bool
     */
    protected function opdsCompleteValidation($feed, $expected = true)
    {
        return $this->jingValidateSchema($feed, self::OPDS_RELAX_NG, $expected) && $this->opdsValidator($feed, $expected);
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
        $xml = simplexml_load_file($feed);
        if ($xml === false) {
            echo file_get_contents($feed);
            return false;
        }
        if ($hasPaging) {
            copy($feed, $feed . '.' . $this->name());
        }
        if ($currentPage->containsBook()) {
            if (count($xml->entry) == $numEntries) {
                return true;
            }
        } else {
            if (count($xml->entry) == $numEntries) {
                return true;
            }
        }
        echo $xml->asXML();
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
        $request = Request::build(['page' => $page], self::$handler);

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->jingValidateSchema(self::TEST_FEED));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        $_SERVER ["HTTP_USER_AGENT"] = "XXX";
        Config::set('generate_invalid_opds_stream', "1");
        $request = Request::build(['page' => $page], self::$handler);

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertFalse($this->jingValidateSchema(self::TEST_FEED, self::OPDS_RELAX_NG, false));
        $this->AssertFalse($this->opdsValidator(self::TEST_FEED, false));
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
        $request = Request::build(['page' => $page], self::$handler);
        $request->set('query', $query);
        //$_SERVER['REQUEST_URI'] = OpdsRenderer::$endpoint . "?" . $request->query();

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
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
        $request = Request::build(['page' => $page], self::$handler);
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testOpenSearchDescription(): void
    {
        $request = Request::build(['page', PageId::OPENSEARCH], self::$handler);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->getOpenSearch($request));
        $this->AssertTrue($this->jingValidateSchema(self::TEST_FEED, self::OPENSEARCHDESCRIPTION_RELAX_NG));
    }

    public function testPageAuthorMultipleDatabase(): void
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = PageId::AUTHOR_DETAIL;
        $request = Request::build(['page' => $page], self::$handler);
        $request->set('id', "1");
        $request->set('db', "0");

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testPageAuthorsDetail(): void
    {
        $page = PageId::AUTHOR_DETAIL;

        Config::set('max_item_per_page', 2);
        $request = Request::build(['page' => $page], self::$handler);
        $request->set('id', "1");
        $request->set('n', "1");

        // First page

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        // Second page

        $request->set('n', "2");
        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        // No pagination
        Config::set('max_item_per_page', -1);
    }

    public function testPageAuthorsDetail_WithFacets(): void
    {
        $page = PageId::AUTHOR_DETAIL;

        Config::set('books_filter', ["Only Short Stories" => "Short Stories", "No Short Stories" => "!Short Stories"]);
        $request = Request::build(['page' => $page], self::$handler);
        $request->set('id', "1");
        $request->set('tag', "Short Stories");

        $currentPage = PageId::getPage($page, $request);

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        Config::set('books_filter', []);
    }

    public function testPageAuthorsDetail_WithoutAnyId(): void
    {
        $page = PageId::AUTHOR_DETAIL;
        $_SERVER['REQUEST_URI'] = "index.php?XXXX";
        $request = Request::build(['page' => $page], self::$handler);
        $request->set('id', "1");

        $currentPage = PageId::getPage($page, $request);
        $currentPage->idPage = null;

        $OPDSRender = new OpdsRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));
        $this->AssertTrue($this->checkEntries($currentPage, self::TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }

    public function testFeedHandler(): void
    {
        $page = PageId::ALL_RECENT_BOOKS;
        $request = Request::build(['page' => $page]);
        $handler = Framework::getHandler('feed');

        ob_start();
        $response = $handler->handle($request);
        $response->send();
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = "<title>Recent additions</title>";
        $this->assertStringContainsString($expected, $output);
    }
}
