<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Input;

use SebLucas\Cops\Input\Config;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\HtmlRenderer;

class ConfigTest extends TestCase
{
    public function testCheckConfigurationCalibreDirectory(): void
    {
        $this->assertTrue(is_string(Config::get('calibre_directory')));
    }

    public function testCheckConfigurationOPDSTHumbnailHeight(): void
    {
        $this->assertTrue(is_int((int) Config::get('opds_thumbnail_height')));
    }

    public function testCheckConfigurationHTMLTHumbnailHeight(): void
    {
        $this->assertTrue(is_int((int) Config::get('html_thumbnail_height')));
    }

    public function testCheckConfigurationPreferedFormat(): void
    {
        $this->assertTrue(is_array(Config::get('prefered_format')));
    }

    public function testCheckConfigurationGenerateInvalidOPDSStream(): void
    {
        $this->assertTrue(is_int((int) Config::get('generate_invalid_opds_stream')));
    }

    public function testCheckConfigurationMaxItemPerPage(): void
    {
        $this->assertTrue(is_int((int) Config::get('max_item_per_page')));
    }

    public function testCheckConfigurationAuthorSplitFirstLetter(): void
    {
        $this->assertTrue(is_int((int) Config::get('author_split_first_letter')));
    }

    public function testCheckConfigurationTitlesSplitFirstLetter(): void
    {
        $this->assertTrue(is_int((int) Config::get('titles_split_first_letter')));
    }

    public function testCheckConfigurationPublisherSplitFirstLetter(): void
    {
        $this->assertTrue(is_int((int) Config::get('publisher_split_first_letter')));
    }

    public function testCheckConfigurationSeriesSplitFirstLetter(): void
    {
        $this->assertTrue(is_int((int) Config::get('series_split_first_letter')));
    }

    public function testCheckConfigurationTagSplitFirstLetter(): void
    {
        $this->assertTrue(is_int((int) Config::get('tag_split_first_letter')));
    }

    public function testCheckConfigurationCopsUseFancyapps(): void
    {
        $this->assertTrue(is_int((int) Config::get('use_fancyapps')));
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
        $this->assertTrue(is_int((int) Config::get('provide_kepub')));
    }

    public function testCheckConfigurationMailConfig(): void
    {
        $this->assertTrue(is_array(Config::get('mail_configuration')));
    }

    public function testCheckConfiguratioHTMLTagFilter(): void
    {
        $this->assertTrue(is_int((int) Config::get('html_tag_filter')));
    }

    public function testCheckConfigurationIgnoredCategories(): void
    {
        $this->assertTrue(is_array(Config::get('ignored_categories')));
    }

    public function testCheckConfigurationTemplate(): void
    {
        $templateName = 'bootstrap';

        Config::set('template', $templateName);
        $request = new Request();

        $renderer = new HtmlRenderer();
        $head = $renderer->render($request);

        $this->assertStringContainsString($templateName . ".min.css", $head);
        $this->assertStringContainsString($templateName . ".min.js", $head);
    }
}
