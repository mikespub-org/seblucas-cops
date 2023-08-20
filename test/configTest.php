<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */
namespace SebLucas\Cops\Tests;

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\JSONRenderer;
use SebLucas\Template\doT;

class ConfigTest extends TestCase
{
    public function testCheckConfigurationCalibreDirectory(): void
    {
        $this->assertTrue(is_string(Config::get('calibre_directory')));
    }

    public function testCheckConfigurationOPDSTHumbnailHeight(): void
    {
        $this->assertTrue(is_int((int)Config::get('opds_thumbnail_height')));
    }

    public function testCheckConfigurationHTMLTHumbnailHeight(): void
    {
        $this->assertTrue(is_int((int)Config::get('html_thumbnail_height')));
    }

    public function testCheckConfigurationPreferedFormat(): void
    {
        $this->assertTrue(is_array(Config::get('prefered_format')));
    }

    public function testCheckConfigurationUseUrlRewiting(): void
    {
        $this->assertTrue(is_int((int)Config::get('use_url_rewriting')));
    }

    public function testCheckConfigurationGenerateInvalidOPDSStream(): void
    {
        $this->assertTrue(is_int((int)Config::get('generate_invalid_opds_stream')));
    }

    public function testCheckConfigurationMaxItemPerPage(): void
    {
        $this->assertTrue(is_int((int)Config::get('max_item_per_page')));
    }

    public function testCheckConfigurationAuthorSplitFirstLetter(): void
    {
        $this->assertTrue(is_int((int)Config::get('author_split_first_letter')));
    }

    public function testCheckConfigurationTitlesSplitFirstLetter(): void
    {
        $this->assertTrue(is_int((int)Config::get('titles_split_first_letter')));
    }

    public function testCheckConfigurationCopsUseFancyapps(): void
    {
        $this->assertTrue(is_int((int)Config::get('use_fancyapps')));
    }

    public function testCheckConfigurationCopsBooksFilter(): void
    {
        $this->assertTrue(is_array(Config::get('books_filter')));
    }

    public function testCheckConfigurationCalibreCustomColumn(): void
    {
        $this->assertTrue(is_array(Config::get('calibre_custom_column')));
    }

    public function testCheckConfigurationCalibreCustomColumnList(): void
    {
        $this->assertTrue(is_array(Config::get('calibre_custom_column_list')));
    }

    public function testCheckConfigurationCalibreCustomColumnPreview(): void
    {
        $this->assertTrue(is_array(Config::get('calibre_custom_column_preview')));
    }

    public function testCheckConfigurationProvideKepub(): void
    {
        $this->assertTrue(is_int((int)Config::get('provide_kepub')));
    }

    public function testCheckConfigurationMailConfig(): void
    {
        $this->assertTrue(is_array(Config::get('mail_configuration')));
    }

    public function testCheckConfiguratioHTMLTagFilter(): void
    {
        $this->assertTrue(is_int((int)Config::get('html_tag_filter')));
    }

    public function testCheckConfigurationIgnoredCategories(): void
    {
        $this->assertTrue(is_array(Config::get('ignored_categories')));
    }

    public function testCheckConfigurationTemplate(): void
    {
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        $templateName = 'bootstrap';

        Config::set('template', $templateName);
        $request = new Request();

        $headcontent = file_get_contents(__DIR__ . '/../templates/' . Config::get('template') . '/file.html');
        $template = new doT();
        $tpl = $template->template($headcontent, null);
        $data = ["title"                 => Config::get('title_default'),
            "version"               => Config::VERSION,
            "opds_url"              => Config::get('full_url') . Config::ENDPOINT["feed"],
            "customHeader"          => "",
            "template"              => Config::get('template'),
            "server_side_rendering" => $request->render(),
            "current_css"           => $request->style(),
            "favico"                => Config::get('icon'),
            "getjson_url"           => JSONRenderer::getCurrentUrl($request->query())];

        $head = $tpl($data);

        $this->assertStringContainsString($templateName.".min.css", $head);
        $this->assertStringContainsString($templateName.".min.js", $head);
    }
}
