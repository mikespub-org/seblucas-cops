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
use SebLucas\Cops\Language\Normalizer;
use SebLucas\Cops\Language\Slugger;
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

    public function testNormalizeWithSymfonyString(): void
    {
        if (class_exists('\Symfony\Component\String\UnicodeString')) {
            // @ignore class.notFound
            $input = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏŒÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïœðòóôõöùúûüýÿñ";
            $output = (new \Symfony\Component\String\UnicodeString($input))->ascii();
            $expected = "AAAAAACEEEEIIIIOEOOOOOUUUUYaaaaaaceeeeiiiioedooooouuuuyyn";
            $this->assertEquals($expected, (string) $output);

            $input = "孙子兵法";
            $output = (new \Symfony\Component\String\UnicodeString($input))->ascii();
            $expected = "sun zi bing fa";
            $this->assertEquals($expected, (string) $output);
        } else {
            $this->markTestSkipped('No symfony/string installed');
        }
    }

    public function testAsciiSluggerWithIntl(): void
    {
        $slugger = new Slugger();
        $input = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏŒÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïœðòóôõöùúûüýÿñ";
        $output = $slugger->slug($input, '_');
        $expected = "AAAAAACEEEEIIIIOEOOOOOUUUUYaaaaaaceeeeiiiioedooooouuuuyyn";
        $this->assertEquals($expected, (string) $output);

        $input = "孙子兵法";
        $output = $slugger->slug($input, '_');
        $expected = "sun_zi_bing_fa";
        $this->assertEquals($expected, (string) $output);

        // handle umlauts with ö => oe etc.
        $locale = 'de';
        $slugger = new Slugger($locale);
        $input = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏŒÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïœðòóôõöùúûüýÿñ";
        $output = $slugger->slug($input, '_');
        $expected = "AAAAAEACEEEEIIIIOEOOOOOEUUUUEYaaaaaeaceeeeiiiioedoooooeuuuueyyn";
        $this->assertEquals($expected, (string) $output);

        $input = "孙子兵法";
        $output = $slugger->slug($input, '_');
        $expected = "sun_zi_bing_fa";
        $this->assertEquals($expected, (string) $output);
    }
}
