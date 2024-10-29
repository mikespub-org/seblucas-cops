<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Translation;
use Exception;

class BaseTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testLocalize(): void
    {
        $this->assertEquals("Authors", localize("authors.title"));

        $this->assertEquals("unknow.key", localize("unknow.key"));
    }

    public function testLocalizeFr(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3";
        $translator = new Translation($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $this->assertEquals("Auteurs", $translator->localize("authors.title", -1, true));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en";
        localize("authors.title", -1, true);
    }

    public function testLocalizeUnknown(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "aa";
        $translator = new Translation($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $this->assertEquals("Authors", $translator->localize("authors.title", -1, true));

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en";
        localize("authors.title", -1, true);
    }

    /**
     * @param mixed $acceptLanguage
     * @param mixed $result
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetLangAndTranslationFile')]
    public function testGetLangAndTranslationFile($acceptLanguage, $result)
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $acceptLanguage;
        $translator = new Translation($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        [$lang, $lang_file] = $translator->getLangAndTranslationFile();
        $this->assertEquals($result, $lang);

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en";
        localize("authors.title", -1, true);
    }

    /**
     * Summary of providerGetLangAndTranslationFile
     * @return array<mixed>
     */
    public static function providerGetLangAndTranslationFile()
    {
        return [
            ["en", "en"],
            ["fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3", "fr"],
            ["fr-FR", "fr"],
            ["pt,en-us;q=0.7,en;q=0.3", "en"],
            ["pt-br,pt;q=0.8,en-us;q=0.5,en;q=0.3", "pt_BR"],
            ["pt-pt,pt;q=0.8,en;q=0.5,en-us;q=0.3", "pt_PT"],
            ["zl", "en"],
        ];
    }

    /**
     * @param mixed $acceptLanguage
     * @param mixed $result
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetAcceptLanguages')]
    public function testGetAcceptLanguages($acceptLanguage, $result)
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $acceptLanguage;
        $translator = new Translation($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $langs = array_keys($translator->getAcceptLanguages($acceptLanguage));
        $this->assertEquals($result, $langs[0]);

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en";
        localize("authors.title", -1, true);
    }

    /**
     * Summary of providerGetAcceptLanguages
     * @return array<mixed>
     */
    public static function providerGetAcceptLanguages()
    {
        return [
            ["en", "en"],
            ["en-US", "en_US"],
            ["fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3", "fr"], // French locale with Firefox
            ["fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4", "fr_FR"], // French locale with Chrome
            ["fr-FR", "fr_FR"], // French locale with IE11
            ["pt-br,pt;q=0.8,en-us;q=0.5,en;q=0.3", "pt_BR"],
            ["zl", "zl"],
        ];
    }

    public function testBaseFunction(): void
    {
        $this->assertFalse(Database::isMultipleDatabaseEnabled());
        $this->assertEquals(["" => __DIR__ . "/BaseWithSomeBooks/"], Database::getDbList());

        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();

        $this->assertTrue(Database::isMultipleDatabaseEnabled());
        $this->assertEquals("Some books", Database::getDbName(0));
        $this->assertEquals("One book", Database::getDbName(1));
        $this->assertEquals(Config::get('calibre_directory'), Database::getDbList());

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testCheckDatabaseAvailability_1(): void
    {
        $this->assertTrue(Database::checkDatabaseAvailability(null));
    }

    public function testCheckDatabaseAvailability_2(): void
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();

        $this->assertTrue(Database::checkDatabaseAvailability(null));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Database <1> not found.
     */
    public function testCheckDatabaseAvailability_Exception1(): void
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/BaseWithSomeBooks/",
            "One book" => __DIR__ . "/OneBook/"]);
        Database::clearDb();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database <1> not found.');

        $this->assertTrue(Database::checkDatabaseAvailability(null));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Database <0> not found.
     */
    public function testCheckDatabaseAvailability_Exception2(): void
    {
        Config::set('calibre_directory', [
            "Some books" => __DIR__ . "/SomeBooks/",
            "One book" => __DIR__ . "/BaseWithOneBook/"]);
        Database::clearDb();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database <0> not found.');

        $this->assertTrue(Database::checkDatabaseAvailability(null));

        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    /*
    Test normalized utf8 string according to unicode.org output
    more here :
    http://unicode.org/cldr/utility/transform.jsp?a=Latin-ASCII&b=%C3%80%C3%81%C3%82%C3%83%C3%84%C3%85%C3%87%C3%88%C3%89%C3%8A%C3%8B%C3%8C%C3%8D%C3%8E%C3%8F%C5%92%C3%92%C3%93%C3%94%C3%95%C3%96%C3%99%C3%9A%C3%9B%C3%9C%C3%9D%C3%A0%C3%A1%C3%A2%C3%A3%C3%A4%C3%A5%C3%A7%C3%A8%C3%A9%C3%AA%C3%AB%C3%AC%C3%AD%C3%AE%C3%AF%C5%93%C3%B0%C3%B2%C3%B3%C3%B4%C3%B5%C3%B6%C3%B9%C3%BA%C3%BB%C3%BC%C3%BD%C3%BF%C3%B1
    */
    public function testNormalizeUtf8String(): void
    {
        $this->assertEquals(
            "AAAAAACEEEEIIIIOEOOOOOUUUUYaaaaaaceeeeiiiioedooooouuuuyyn",
            Translation::normalizeUtf8String("ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏŒÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïœðòóôõöùúûüýÿñ")
        );
    }

    public function testAsciiSluggerWithIntl(): void
    {
        if (class_exists('\Symfony\Component\String\Slugger\AsciiSlugger')) {
            // @ignore class.notFound
            $slugger = new \Symfony\Component\String\Slugger\AsciiSlugger();
            $input = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏŒÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïœðòóôõöùúûüýÿñ";
            $output = $slugger->slug($input, '_');

            $expected = "AAAAAACEEEEIIIIOEOOOOOUUUUYaaaaaaceeeeiiiioedooooouuuuyyn";
            $this->assertEquals($expected, (string) $output);
        } else {
            $this->markTestSkipped('No symfony/string installed');
        }
    }
}
