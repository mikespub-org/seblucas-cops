<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

use SebLucas\Cops\Output\OPDSRenderer;

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\Page;

define("OPDS_RELAX_NG", dirname(__FILE__) . "/opds-relax-ng/opds_catalog_1_2.rng");
define("OPENSEARCHDESCRIPTION_RELAX_NG", dirname(__FILE__) . "/opds-relax-ng/opensearchdescription.rng");
define("JING_JAR", dirname(__FILE__) . "/jing.jar");
define("OPDSVALIDATOR_JAR", dirname(__FILE__) . "/OPDSValidator.jar");
define("TEST_FEED", dirname(__FILE__) . "/text.atom");

class OpdsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        global $config;
        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Database::clearDb();
    }

    public static function tearDownAfterClass(): void
    {
        if (!file_exists(TEST_FEED)) {
            return;
        }
        unlink(TEST_FEED);
    }

    public function jingValidateSchema($feed, $relax = OPDS_RELAX_NG)
    {
        $path = "";
        $code = null;
        $res = system($path . 'java -jar "' . JING_JAR . '" "' . $relax . '" "' . $feed . '"', $code);
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

    public function opdsValidator($feed)
    {
        $oldcwd = getcwd(); // Save the old working directory
        chdir("test");
        $path = "";
        $res = system($path . 'java -jar "' . OPDSVALIDATOR_JAR . '" -v 1.2 "' . $feed . '"');
        chdir($oldcwd);
        if ($res != '') {
            echo 'OPDS validation error: '.$res;
            return false;
        } else {
            return true;
        }
    }

    public function opdsCompleteValidation($feed)
    {
        return $this->jingValidateSchema($feed) && $this->opdsValidator($feed);
    }

    public function testPageIndex()
    {
        global $config;
        $page = Page::INDEX;

        $config['cops_subtitle_default'] = "My subtitle";
        $request = new Request();

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->jingValidateSchema(TEST_FEED));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        $_SERVER ["HTTP_USER_AGENT"] = "XXX";
        $config['cops_generate_invalid_opds_stream'] = "1";
        $request = new Request();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertFalse($this->jingValidateSchema(TEST_FEED));
        $this->AssertFalse($this->opdsValidator(TEST_FEED));

        unset($_SERVER['HTTP_USER_AGENT']);
        $config['cops_generate_invalid_opds_stream'] = "0";
    }

    /**
     * @dataProvider providerPage
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

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }

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

    public function testPageIndexMultipleDatabase()
    {
        global $config;
        $config['calibre_directory'] = ["Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/"];
        Database::clearDb();
        $page = Page::INDEX;
        $request = new Request();
        $request->set('id', "1");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Database::clearDb();
    }

    public function testOpenSearchDescription()
    {
        $request = new Request();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->getOpenSearch($request));
        $this->AssertTrue($this->jingValidateSchema(TEST_FEED, OPENSEARCHDESCRIPTION_RELAX_NG));
    }

    public function testPageAuthorMultipleDatabase()
    {
        global $config;
        $config['calibre_directory'] = ["Some books" => dirname(__FILE__) . "/BaseWithSomeBooks/",
                                              "One book" => dirname(__FILE__) . "/BaseWithOneBook/"];
        Database::clearDb();
        $page = Page::AUTHOR_DETAIL;
        $request = new Request();
        $request->set('id', "1");
        $request->set('db', "0");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        $config['calibre_directory'] = dirname(__FILE__) . "/BaseWithSomeBooks/";
        Database::clearDb();
    }

    public function testPageAuthorsDetail()
    {
        global $config;
        $page = Page::AUTHOR_DETAIL;

        $config['cops_max_item_per_page'] = 2;
        $request = new Request();
        $request->set('id', "1");
        $request->set('n', "1");

        // First page

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        // Second page

        $request->set('n', "2");
        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        // No pagination
        $config['cops_max_item_per_page'] = -1;
    }

    public function testPageAuthorsDetail_WithFacets()
    {
        global $config;
        $page = Page::AUTHOR_DETAIL;

        $config['cops_books_filter'] = ["Only Short Stories" => "Short Stories", "No Short Stories" => "!Short Stories"];
        $request = new Request();
        $request->set('id', "1");
        $request->set('tag', "Short Stories");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        $config['cops_books_filter'] = [];
    }

    public function testPageAuthorsDetail_WithoutAnyId()
    {
        global $config;
        $page = Page::AUTHOR_DETAIL;
        $_SERVER['REQUEST_URI'] = "index.php?XXXX";
        $request = new Request();
        $request->set('id', "1");

        $currentPage = Page::getPage($page, $request);
        $currentPage->InitializeContent();
        $currentPage->idPage = null;

        $OPDSRender = new OPDSRenderer();

        file_put_contents(TEST_FEED, $OPDSRender->render($currentPage, $request));
        $this->AssertTrue($this->opdsCompleteValidation(TEST_FEED));

        unset($_SERVER['REQUEST_URI']);
    }
}
