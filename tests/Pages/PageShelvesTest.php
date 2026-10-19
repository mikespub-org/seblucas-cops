<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Pages;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\PageShelves;

class PageShelvesTest extends TestCase
{
    public function testGetPageI18n(): void
    {
        $request = new Request();
        /** @var PageShelves $page */
        $page = PageId::getPage(PageId::SHELVES, $request);

        $this->assertInstanceOf(PageShelves::class, $page);

        $i18n = $page->getPageI18n();

        // all JS-only camelCase shelf* keys from tools/i18n-inventory.csv
        $expectedKeys = array_keys(PageShelves::getPageI18nMap());
        $this->assertEquals($expectedKeys, array_keys($i18n));

        // spot-check localized values (en) incl. {0} placeholders
        $this->assertEquals('Add to Shelf', $i18n['shelfAddTitle']);
        $this->assertEquals('Delete shelf "{0}"?', $i18n['shelfDeleteConfirm']);
        $this->assertEquals('Books in "{0}"', $i18n['shelfBooksInTitle']);
        $this->assertEquals('Shelves', $i18n['shelvesTitle']);

        // no untranslated dot-key leakage (value must not equal its dot key)
        $map = PageShelves::getPageI18nMap();
        foreach ($i18n as $camelKey => $value) {
            $this->assertNotEquals($map[$camelKey], $value, "untranslated value for {$camelKey}");
            $this->assertNotEmpty($value, "empty value for {$camelKey}");
        }
    }
}
