<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

use SebLucas\Cops\Output\OPDSRenderer;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\Page;

class OpdsTest extends TestCase
{
    public const OPDS_RELAX_NG = __DIR__ . "/opds-relax-ng/opds_catalog_1_2.rng";
    public const OPENSEARCHDESCRIPTION_RELAX_NG = __DIR__ . "/opds-relax-ng/opensearchdescription.rng";
    public const JING_JAR = __DIR__ . "/jing.jar";
    public const OPDSVALIDATOR_JAR = __DIR__ . "/OPDSValidator.jar";
    public const TEST_FEED = __DIR__ . "/text.atom";

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public static function tearDownAfterClass(): void
    {
        if (!file_exists(self::TEST_FEED)) {
            return;
        }
        unlink(self::TEST_FEED);
    }

    /**
     * Summary of jingValidateSchema
     * @param mixed $feed
     * @param mixed $relax
     * @return bool
     */
    public function jingValidateSchema($feed, $relax = self::OPDS_RELAX_NG)
    {
        $path = "";
        $code = null;
        $res = system($path . 'java -jar "' . self::JING_JAR . '" "' . $relax . '" "' . $feed . '"', $code);
        if ($res != '') {
            echo 'RelaxNG validation error: '.$res;
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
     * @param mixed $feed
     * @return bool
     */
    public function opdsValidator($feed)
    {
        $oldcwd = getcwd(); // Save the old working directory
        chdir("test");
        $path = "";
        $res = system($path . 'java -jar "' . self::OPDSVALIDATOR_JAR . '" -v 1.2 "' . $feed . '"');
        chdir($oldcwd);
        if ($res != '') {
            echo 'OPDS validation error: '.$res;
            return false;
        } else {
            return true;
        }
    }

    /**
     * Summary of opdsCompleteValidation
     * @param mixed $feed
     * @return bool
     */
    public function opdsCompleteValidation($feed)
    {
        return $this->jingValidateSchema($feed) && $this->opdsValidator($feed);
    }

    public function testPageIndex(): void
    {
        $page = Page::INDEX;

        Config::set('subtitle_default', "My subtitle");
        $request = new Request();

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->jingValidateSchema(self::TEST_FEED));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        $_SERVER ["HTTP_USER_AGENT"] = "XXX";
        Config::set('generate_invalid_opds_stream', "1");
        $request = new Request();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertFalse($this->jingValidateSchema(self::TEST_FEED));
        $this->AssertFalse($this->opdsValidator(self::TEST_FEED));

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

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }

    /**
     * Summary of providerPage
     * @return array<mixed>
     */
    public function providerPage()
    {
        return [
            [Page::OPENSEARCH, "car"],
            [Page::ALL_AUTHORS, null],
            [Page::ALL_SERIES, null],
            [Page::ALL_TAGS, null],
            [Page::ALL_PUBLISHERS, null],
            [Page::ALL_LANGUAGES, null],
            [Page::ALL_RECENT_BOOKS, null],
            [Page::ALL_BOOKS, null],
        ];
    }

    public function testPageIndexMultipleDatabase(): void
    {
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
                                              "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = Page::INDEX;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testOpenSearchDescription(): void
    {
        $request = new Request();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->getOpenSearch($request));
        $this->AssertTrue($this->jingValidateSchema(self::TEST_FEED, self::OPENSEARCHDESCRIPTION_RELAX_NG));
    }

    public function testPageAuthorMultipleDatabase(): void
    {
        Config::set('calibre_directory', ["Some books" => __DIR__ . "/BaseWithSomeBooks/",
                                              "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();
        $page = Page::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('db', "0");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testPageAuthorsDetail(): void
    {
        $page = Page::AUTHOR_DETAIL;

        Config::set('max_item_per_page', 2);
        $request = new Request();
        $request->set('id', "1");
        $request->set('n', "1");

        // First page

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        // Second page

        $request->set('n', "2");
        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        // No pagination
        Config::set('max_item_per_page', -1);
    }

    public function testPageAuthorsDetail_WithFacets(): void
    {
        $page = Page::AUTHOR_DETAIL;

        Config::set('books_filter', ["Only Short Stories" => "Short Stories", "No Short Stories" => "!Short Stories"]);
        $request = new Request();
        $request->set('id', "1");
        $request->set('tag', "Short Stories");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        Config::set('books_filter', []);
    }

    public function testPageAuthorsDetail_WithoutAnyId(): void
    {
        $page = Page::AUTHOR_DETAIL;
        $_SERVER['REQUEST_URI'] = "index.php?XXXX";
        $request = new Request();
        $request->set('id', "1");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();
        $currentPage->idPage = null;

        $OPDSRender = new OPDSRenderer();

        file_put_contents(self::TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(self::TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }
}
