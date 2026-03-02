<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Language;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Language\Normalizer;
use SebLucas\Cops\Language\Slugger;
use SebLucas\Cops\Language\Translation;

class LanguageTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testLocalize(): void
    {
        $this->assertEquals("Authors", localize("authors.title"));

        $this->assertEquals("unknow.key", localize("unknow.key"));
    }

    public function testLocalizeFr(): void
    {
        $acceptLanguage = "fr,fr-fr;q=0.8,en-us;q=0.5,en;q=0.3";
        $translator = new Translation($acceptLanguage);
        $this->assertEquals("Auteurs", $translator->localize("authors.title", -1, true));

        localize("authors.title", -1, true);
    }

    public function testLocalizeUnknown(): void
    {
        $acceptLanguage = "aa";
        $translator = new Translation($acceptLanguage);
        $this->assertEquals("Authors", $translator->localize("authors.title", -1, true));

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
        $translator = new Translation($acceptLanguage);
        [$lang, $lang_file] = $translator->getLangAndTranslationFile();
        $this->assertEquals($result, $lang);

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
        $translator = new Translation($acceptLanguage);
        $langs = array_keys($translator->getAcceptLanguages($acceptLanguage));
        $this->assertEquals($result, $langs[0]);

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

    public function testNormalizeWithSymfonyString(): void
    {
        if (class_exists('\Symfony\Component\String\UnicodeString')) {
            // @ignore class.notFound
            $input = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏŒÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïœðòóôõöùúûüýÿñ";
            $output = new \Symfony\Component\String\UnicodeString($input)->ascii();
            $expected = "AAAAAACEEEEIIIIOEOOOOOUUUUYaaaaaaceeeeiiiioedooooouuuuyyn";
            $this->assertEquals($expected, (string) $output);

            $input = "孙子兵法";
            $output = new \Symfony\Component\String\UnicodeString($input)->ascii();
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
