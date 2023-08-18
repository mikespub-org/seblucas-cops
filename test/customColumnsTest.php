<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\CustomColumnTypeBool;
use SebLucas\Cops\Calibre\CustomColumnTypeComment;
use SebLucas\Cops\Calibre\CustomColumnTypeDate;
use SebLucas\Cops\Calibre\CustomColumnTypeEnumeration;
use SebLucas\Cops\Calibre\CustomColumnTypeFloat;
use SebLucas\Cops\Calibre\CustomColumnTypeInteger;
use SebLucas\Cops\Calibre\CustomColumnTypeRating;
use SebLucas\Cops\Calibre\CustomColumnTypeSeries;
use SebLucas\Cops\Calibre\CustomColumnTypeText;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\EntryBook;
use SebLucas\Cops\Output\JSONRenderer;
use SebLucas\Cops\Pages\PageId;

class CustomColumnTest extends TestCase
{
    private static string $endpoint = 'phpunit';

    public static function setUpBeforeClass(): void
    {
        Config::set('show_not_set_filter', []);
    }

    public function testColumnType01(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(8);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_01"));

        $this->assertEquals(8, $coltype->customId);
        $this->assertEquals("custom_01", $coltype->columnTitle);
        $this->assertEquals("text", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeText::class, get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=8", $coltype->getUri());
        $this->assertEquals("cops:custom:8", $coltype->getEntryId());
        $this->assertEquals("custom_01", $coltype->getTitle());
        $this->assertEquals("Custom column example 01 (text)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 01 (text)", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType01b(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01b"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(16);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_01b"));

        $this->assertEquals(16, $coltype->customId);
        $this->assertEquals("custom_01b", $coltype->columnTitle);
        $this->assertEquals("text", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeText::class, get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=16", $coltype->getUri());
        $this->assertEquals("cops:custom:16", $coltype->getEntryId());
        $this->assertEquals("custom_01b", $coltype->getTitle());
        $this->assertEquals(null, $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column 'custom_01b'", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType02(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_02"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(6);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_02"));

        $this->assertEquals(6, $coltype->customId);
        $this->assertEquals("custom_02", $coltype->columnTitle);
        $this->assertEquals("csv", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeText::class, get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=6", $coltype->getUri());
        $this->assertEquals("cops:custom:6", $coltype->getEntryId());
        $this->assertEquals("custom_02", $coltype->getTitle());
        $this->assertEquals("Custom column example 02 (csv)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 02 (csv)", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType03(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_03"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(7);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_03"));

        $this->assertEquals(7, $coltype->customId);
        $this->assertEquals("custom_03", $coltype->columnTitle);
        $this->assertEquals("comments", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeComment::class, get_class($coltype));

        $this->assertEquals("?page=14&custom=7", $coltype->getUri());
        $this->assertEquals("cops:custom:7", $coltype->getEntryId());
        $this->assertEquals("custom_03", $coltype->getTitle());
        $this->assertEquals("Custom column example 03 (long_text)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 03 (long_text)", $coltype->getContent());
        $this->assertEquals(false, $coltype->isSearchable());
    }

    public function testColumnType04(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_04"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(4);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_04"));

        $this->assertEquals(4, $coltype->customId);
        $this->assertEquals("custom_04", $coltype->columnTitle);
        $this->assertEquals("series", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeSeries::class, get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=4", $coltype->getUri());
        $this->assertEquals("cops:custom:4", $coltype->getEntryId());
        $this->assertEquals("custom_04", $coltype->getTitle());
        $this->assertEquals("Custom column example 04 (series_text)", $coltype->getDatabaseDescription());
        $count = $coltype->getDistinctValueCount();
        $this->assertEquals("Alphabetical index of the 3 series", $coltype->getContent($count));
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType05(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_05"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(5);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_05"));

        $this->assertEquals(5, $coltype->customId);
        $this->assertEquals("custom_05", $coltype->columnTitle);
        $this->assertEquals("enumeration", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeEnumeration::class, get_class($coltype));

        $this->assertCount(4, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=5", $coltype->getUri());
        $this->assertEquals("cops:custom:5", $coltype->getEntryId());
        $this->assertEquals("custom_05", $coltype->getTitle());
        $this->assertEquals("Custom column example 05 (enum)", $coltype->getDatabaseDescription());
        $count = $coltype->getDistinctValueCount();
        $this->assertEquals("Alphabetical index of the 4 values", $coltype->getContent($count));
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType06(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_06"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(12);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_06"));

        $this->assertEquals(12, $coltype->customId);
        $this->assertEquals("custom_06", $coltype->columnTitle);
        $this->assertEquals("datetime", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeDate::class, get_class($coltype));

        $this->assertCount(5, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=12", $coltype->getUri());
        $this->assertEquals("cops:custom:12", $coltype->getEntryId());
        $this->assertEquals("custom_06", $coltype->getTitle());
        $this->assertEquals("Custom column example 06 (date)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 06 (date)", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType07(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_07"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(14);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_07"));

        $this->assertEquals(14, $coltype->customId);
        $this->assertEquals("custom_07", $coltype->columnTitle);
        $this->assertEquals("float", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeFloat::class, get_class($coltype));

        $this->assertCount(6, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=14", $coltype->getUri());
        $this->assertEquals("cops:custom:14", $coltype->getEntryId());
        $this->assertEquals("custom_07", $coltype->getTitle());
        $this->assertEquals("Custom column example 07 (float)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 07 (float)", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType08(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_08"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(10);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_08"));

        $this->assertEquals(10, $coltype->customId);
        $this->assertEquals("custom_08", $coltype->columnTitle);
        $this->assertEquals("int", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeInteger::class, get_class($coltype));

        $this->assertCount(4, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=10", $coltype->getUri());
        $this->assertEquals("cops:custom:10", $coltype->getEntryId());
        $this->assertEquals("custom_08", $coltype->getTitle());
        $this->assertEquals("Custom column example 08 (int)", $coltype->getDatabaseDescription());
        $this->assertEquals("Custom column example 08 (int)", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType09(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_09"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(9);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_09"));

        $this->assertEquals(9, $coltype->customId);
        $this->assertEquals("custom_09", $coltype->columnTitle);
        $this->assertEquals("rating", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeRating::class, get_class($coltype));

        $this->assertCount(6, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=9", $coltype->getUri());
        $this->assertEquals("cops:custom:9", $coltype->getEntryId());
        $this->assertEquals("custom_09", $coltype->getTitle());
        $this->assertEquals("Custom column example 09 (rating)", $coltype->getDatabaseDescription());
        $this->assertEquals("Index of ratings", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType10(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_10"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(11);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_10"));

        $this->assertEquals(11, $coltype->customId);
        $this->assertEquals("custom_10", $coltype->columnTitle);
        $this->assertEquals("bool", $coltype->datatype);
        $this->assertEquals(CustomColumnTypeBool::class, get_class($coltype));

        $this->assertCount(3, $coltype->getAllCustomValues());
        $this->assertEquals("?page=14&custom=11", $coltype->getUri());
        $this->assertEquals("cops:custom:11", $coltype->getEntryId());
        $this->assertEquals("custom_10", $coltype->getTitle());
        $this->assertEquals("Custom column example 10 (bool)", $coltype->getDatabaseDescription());
        $this->assertEquals("Index of a boolean value", $coltype->getContent());
        $this->assertEquals(true, $coltype->isSearchable());
    }

    public function testColumnType11(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_11"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(15);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_11"));

        $this->assertEquals(null, $coltype);
    }

    public function testColumnType12(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_12"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByCustomID(13);

        $this->assertEquals($coltype, CustomColumnType::createByLookup("custom_12"));

        $this->assertEquals(null, $coltype);
    }

    public function testInvalidColumn1(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_12"]);
        Database::clearDb();

        $catch = false;
        try {
            CustomColumnType::createByCustomID(999);
        } catch (Exception $e) {
            $catch = true;
        }

        $this->assertTrue($catch);
    }

    public function testInvalidColumn2(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_12"]);
        Database::clearDb();

        $coltype = CustomColumnType::createByLookup("__ERR__");

        $this->assertEquals(null, $coltype);
    }

    public function testIndexTypeAll(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(15, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_01", $currentPage->entryArray[ 4]->title);
        $this->assertEquals("custom_02", $currentPage->entryArray[ 5]->title);
        $this->assertEquals("custom_04", $currentPage->entryArray[ 6]->title);
        $this->assertEquals("custom_05", $currentPage->entryArray[ 7]->title);
        $this->assertEquals("custom_06", $currentPage->entryArray[ 8]->title);
        $this->assertEquals("custom_07", $currentPage->entryArray[ 9]->title);
        $this->assertEquals("custom_08", $currentPage->entryArray[10]->title);
        $this->assertEquals("custom_09", $currentPage->entryArray[11]->title);
        $this->assertEquals("custom_10", $currentPage->entryArray[12]->title);
    }

    public function testIndexType01(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_01", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:8", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 01 (text)", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("text", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(8)->getCount());
    }

    public function testIndexType02(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_02"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_02", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:6", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 02 (csv)", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("csv", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(6)->getCount());
    }

    public function testIndexType03(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_03"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(6, $currentPage->entryArray); // Authors, Series, Publishers, Languages, All, Recent
    }

    public function testIndexType04(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_04"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_04", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:4", $currentPage->entryArray[4]->id);
        $this->assertEquals("Alphabetical index of the 3 series", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("series", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(4)->getCount());
    }

    public function testIndexType05(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_05"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_05", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:5", $currentPage->entryArray[4]->id);
        $this->assertEquals("Alphabetical index of the 4 values", $currentPage->entryArray[4]->content);
        $this->assertEquals(4, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("enumeration", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(5)->getCount());
    }

    public function testIndexType06(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_06"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_06", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:12", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 06 (date)", $currentPage->entryArray[4]->content);
        $this->assertEquals(5, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("datetime", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(12)->getCount());
    }

    public function testIndexType07(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_07"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_07", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:14", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 07 (float)", $currentPage->entryArray[4]->content);
        $this->assertEquals(6, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("float", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(14)->getCount());
    }

    public function testIndexType08(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_08"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_08", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:10", $currentPage->entryArray[4]->id);
        $this->assertEquals("Custom column example 08 (int)", $currentPage->entryArray[4]->content);
        $this->assertEquals(4, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("int", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(10)->getCount());
    }

    public function testIndexType09(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_09"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_09", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:9", $currentPage->entryArray[4]->id);
        $this->assertEquals("Index of ratings", $currentPage->entryArray[4]->content);
        $this->assertEquals(6, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("rating", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(9)->getCount());
    }

    public function testIndexType10(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_10"]);
        Database::clearDb();
        $request = new Request();

        $currentPage = PageId::getPage(PageId::INDEX, $request);
        $currentPage->InitializeContent();

        $this->assertCount(7, $currentPage->entryArray); // Authors, Series, Publishers, Languages, custom, All, Recent
        $this->assertEquals("custom_10", $currentPage->entryArray[4]->title);
        $this->assertEquals("cops:custom:11", $currentPage->entryArray[4]->id);
        $this->assertEquals("Index of a boolean value", $currentPage->entryArray[4]->content);
        $this->assertEquals(3, $currentPage->entryArray[4]->numberOfElement);
        $this->assertEquals("bool", $currentPage->entryArray[4]->contentType);
        $this->assertEquals($currentPage->entryArray[4], CustomColumnType::createByCustomID(11)->getCount());
    }

    public function testAllCustomsType01(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 8);

        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_01", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:8:3", $currentPage->entryArray[0]->id);
        $this->assertEquals("other_text", $currentPage->entryArray[0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=8&id=3", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:8:1", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:8:2", $currentPage->entryArray[2]->id);
    }

    public function testAllCustomsType02(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 6);

        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_02", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:6:1", $currentPage->entryArray[0]->id);
        $this->assertEquals("a", $currentPage->entryArray[0]->title);
        $this->assertEquals("6 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=6&id=1", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:6:2", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:6:3", $currentPage->entryArray[2]->id);
    }

    public function testAllCustomsType04(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 4);

        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_04", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:4:4", $currentPage->entryArray[0]->id);
        $this->assertEquals("GroupA", $currentPage->entryArray[0]->title);
        $this->assertEquals("2 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=4&id=4", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:4:5", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:4:6", $currentPage->entryArray[2]->id);
    }

    public function testAllCustomsType05(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 5);

        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_05", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("cops:custom:5:3", $currentPage->entryArray[0]->id);
        $this->assertEquals("val01", $currentPage->entryArray[0]->title);
        $this->assertEquals("2 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=5&id=3", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:5:4", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:5:5", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:5:6", $currentPage->entryArray[3]->id);
    }

    public function testAllCustomsType06(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 12);

        Config::set('custom_date_split_year', '0');
        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_06", $currentPage->title);
        $this->assertCount(5, $currentPage->entryArray);
        $this->assertEquals("cops:custom:12:2000-01-01", $currentPage->entryArray[0]->id);
        $this->assertEquals("2000-01-01", $currentPage->entryArray[0]->title);
        $this->assertEquals("2 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=12&id=2000-01-01", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:12:2000-01-02", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:12:2000-01-03", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:12:2016-04-20", $currentPage->entryArray[3]->id);
        $this->assertEquals("cops:custom:12:2016-04-24", $currentPage->entryArray[4]->id);

        Config::set('custom_date_split_year', '1');
        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_06", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("cops:custom:12:year:2000", $currentPage->entryArray[0]->id);
        $this->assertEquals("2000", $currentPage->entryArray[0]->title);
        $this->assertEquals("4 books", $currentPage->entryArray[0]->content);
        // switched to using PAGE_DETAIL instead of PAGE_ALL
        $this->assertEquals("phpunit?page=15&custom=12&year=2000", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:12:year:2016", $currentPage->entryArray[1]->id);

        Config::set('custom_date_split_year', '0');
    }

    public function testCustomDetailType06_Year(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();

        Config::set('custom_date_split_year', '1');
        $request = new Request();
        $request->set('custom', 12);
        $request->set('year', "2000");

        $currentPage = PageId::getPage(PageId::CUSTOM_DETAIL, $request);
        $currentPage->InitializeContent();

        // we have entries for different dates in year 2000
        $this->assertEquals("2000", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        /** @var EntryBook $entry */
        $entry = $currentPage->entryArray[0];
        $this->assertEquals("The Quantum Thief", $entry->title);
        $columns = $entry->book->getCustomColumnValues(['custom_06']);
        $this->assertEquals("2000-01-01", $columns[0]->id);
        /** @var EntryBook $entry */
        $entry = $currentPage->entryArray[3];
        $this->assertEquals("Shadow Puppets", $entry->title);
        $columns = $entry->book->getCustomColumnValues(['custom_06']);
        $this->assertEquals("2000-01-03", $columns[0]->id);

        Config::set('custom_date_split_year', '0');
        $request = new Request();
        $request->set('custom', 12);
        $request->set('id', "2000-01-01");

        $currentPage = PageId::getPage(PageId::CUSTOM_DETAIL, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("2000-01-01", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertEquals("The Quantum Thief", $currentPage->entryArray[0]->title);
    }

    public function testAllCustomsType07(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 14);

        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_07", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("cops:custom:14:-99", $currentPage->entryArray[0]->id);
        $this->assertEquals(-99.0, $currentPage->entryArray[0]->title);
        $this->assertEquals("1 book", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=14&id=-99", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:14:0", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:14:0.1", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:14:0.2", $currentPage->entryArray[3]->id);
        $this->assertEquals("cops:custom:14:11", $currentPage->entryArray[4]->id);
        $this->assertEquals("cops:custom:14:100000", $currentPage->entryArray[5]->id);
    }

    public function testAllCustomsType08(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 10);

        Config::set('custom_integer_split_range', '0');
        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_08", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertEquals("cops:custom:10:-2", $currentPage->entryArray[0]->id);
        $this->assertEquals(-2, $currentPage->entryArray[0]->title);
        $this->assertEquals("3 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=10&id=-2", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:10:-1", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:10:1", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:10:2", $currentPage->entryArray[3]->id);

        Config::set('custom_integer_split_range', '4');
        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_08", $currentPage->title);
        $this->assertCount(2, $currentPage->entryArray);
        $this->assertEquals("cops:custom:10:range:-2--1", $currentPage->entryArray[0]->id);
        $this->assertEquals("-2--1", $currentPage->entryArray[0]->title);
        $this->assertEquals("4 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=10&range=-2--1", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:10:range:1-2", $currentPage->entryArray[1]->id);

        Config::set('custom_integer_split_range', '0');
    }

    public function testCustomDetailType08_Range(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();

        Config::set('custom_integer_split_range', '4');
        $request = new Request();
        $request->set('custom', 10);
        $request->set('range', "-2--1");

        $currentPage = PageId::getPage(PageId::CUSTOM_DETAIL, $request);
        $currentPage->InitializeContent();

        // we have entries for different integers in range -2 to -1
        $this->assertEquals("-2--1", $currentPage->title);
        $this->assertCount(4, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        /** @var EntryBook $entry */
        $entry = $currentPage->entryArray[0];
        $this->assertEquals("Earth Unaware", $entry->title);
        $columns = $entry->book->getCustomColumnValues(['custom_08']);
        $this->assertEquals(-2, $columns[0]->id);
        /** @var EntryBook $entry */
        $entry = $currentPage->entryArray[3];
        $this->assertEquals("Earth Afire", $entry->title);
        $columns = $entry->book->getCustomColumnValues(['custom_08']);
        $this->assertEquals(-1, $columns[0]->id);

        Config::set('custom_integer_split_range', '0');
        $request = new Request();
        $request->set('custom', 10);
        $request->set('id', "-2");

        $currentPage = PageId::getPage(PageId::CUSTOM_DETAIL, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("-2", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());
        $this->assertEquals("Earth Unaware", $currentPage->entryArray[0]->title);
    }

    public function testAllCustomsType09(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 9);

        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_09", $currentPage->title);
        $this->assertCount(6, $currentPage->entryArray);
        $this->assertEquals("cops:custom:9:0", $currentPage->entryArray[0]->id);
        $this->assertEquals("No Stars", $currentPage->entryArray[0]->title);
        $this->assertEquals("12 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=9&id=0", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:9:2", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:9:4", $currentPage->entryArray[2]->id);
        $this->assertEquals("cops:custom:9:6", $currentPage->entryArray[3]->id);
        $this->assertEquals("cops:custom:9:8", $currentPage->entryArray[4]->id);
        $this->assertEquals("cops:custom:9:10", $currentPage->entryArray[5]->id);
    }

    public function testAllCustomsType10(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 11);

        $currentPage = PageId::getPage(PageId::ALL_CUSTOMS, $request);
        $currentPage->InitializeContent();

        $this->assertEquals("custom_10", $currentPage->title);
        $this->assertCount(3, $currentPage->entryArray);
        $this->assertEquals("cops:custom:11:-1", $currentPage->entryArray[0]->id);
        $this->assertEquals("Not Set", $currentPage->entryArray[0]->title);
        $this->assertEquals("9 books", $currentPage->entryArray[0]->content);
        $this->assertEquals("phpunit?page=15&custom=11&id=-1", $currentPage->entryArray[0]->getNavLink(self::$endpoint));
        $this->assertEquals("cops:custom:11:0", $currentPage->entryArray[1]->id);
        $this->assertEquals("cops:custom:11:1", $currentPage->entryArray[2]->id);
    }

    public function testAllCustomColumns(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        $columns = CustomColumnType::getAllCustomColumns();

        $check = [
           'id' => 8,
           'label' => 'custom_01',
           'name' => 'custom_01',
           'datatype' => 'text',
           'display' => '{"use_decorations": 0, "description": "Custom column example 01 (text)"}',
           'is_multiple' => 0,
           'normalized' => 1,
        ];

        $this->assertCount(16, $columns);
        $this->assertEquals($check, $columns["custom_01"]);
    }

    public function testDetailTypeAllEntryIDs(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11"]);
        Database::clearDb();
        $request = new Request();
        $request->set('custom', 11);
        $request->set('id', "0");

        $currentPage = PageId::getPage(PageId::CUSTOM_DETAIL, $request);
        $currentPage->InitializeContent();

        /** @var EntryBook[] $entries */
        $entries = $currentPage->entryArray;

        $this->assertCount(6, $entries);

        $customcolumnValues = $entries[0]->book->getCustomColumnValues(Config::get('calibre_custom_column'));

        $this->assertCount(10, $customcolumnValues);

        $this->assertEquals("cops:custom:8:1", $customcolumnValues[0]->getEntryId());
        $this->assertEquals("cops:custom:6:3", $customcolumnValues[1]->getEntryId());
        $this->assertEquals("cops:custom:7:3", $customcolumnValues[2]->getEntryId());
        $this->assertEquals("cops:custom:4:4", $customcolumnValues[3]->getEntryId());
        $this->assertEquals("cops:custom:5:6", $customcolumnValues[4]->getEntryId());
        $this->assertEquals("cops:custom:12:2016-04-24", $customcolumnValues[5]->getEntryId());
        $this->assertEquals("cops:custom:14:11", $customcolumnValues[6]->getEntryId());
        $this->assertEquals("cops:custom:10:-2", $customcolumnValues[7]->getEntryId());
        $this->assertEquals("cops:custom:9:2", $customcolumnValues[8]->getEntryId());
        $this->assertEquals("cops:custom:11:0", $customcolumnValues[9]->getEntryId());

        Config::set('calibre_custom_column', []);
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testRenderCustomColumns(): void
    {
        $_SERVER["HTTP_USER_AGENT"] = "Firefox";
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11"]);
        Config::set('calibre_custom_column_list', ["custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11"]);
        Config::set('calibre_custom_column_preview', ["custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11"]);
        Database::clearDb();

        $book = Book::getBookById(223);
        $json = JSONRenderer::getBookContentArray($book, self::$endpoint);

        /* @var CustomColumn[] $custom */
        $custom = $json["customcolumns_list"];

        $this->assertEquals("custom_01", $custom[0]['customColumnType']['columnTitle']);
        $this->assertEquals("text_2", $custom[0]['htmlvalue']);

        $this->assertEquals("custom_02", $custom[1]['customColumnType']['columnTitle']);
        $this->assertEquals("a", $custom[1]['htmlvalue']);

        $this->assertEquals("custom_03", $custom[2]['customColumnType']['columnTitle']);
        $this->assertEquals("<div>Not Set</div>", $custom[2]['htmlvalue']);

        $this->assertEquals("custom_04", $custom[3]['customColumnType']['columnTitle']);
        $this->assertEquals("", $custom[3]['htmlvalue']);

        $this->assertEquals("custom_05", $custom[4]['customColumnType']['columnTitle']);
        $this->assertEquals("val05", $custom[4]['htmlvalue']);

        $this->assertEquals("custom_06", $custom[5]['customColumnType']['columnTitle']);
        $this->assertEquals("Not Set", $custom[5]['htmlvalue']);

        $this->assertEquals("custom_07", $custom[6]['customColumnType']['columnTitle']);
        $this->assertEquals("100000", $custom[6]['htmlvalue']);

        $this->assertEquals("custom_08", $custom[7]['customColumnType']['columnTitle']);
        $this->assertEquals("Not Set", $custom[7]['htmlvalue']);

        $this->assertEquals("custom_09", $custom[8]['customColumnType']['columnTitle']);
        $this->assertEquals("Not Set", $custom[8]['htmlvalue']);

        $this->assertEquals("custom_10", $custom[9]['customColumnType']['columnTitle']);
        $this->assertEquals("No", $custom[9]['htmlvalue']);

        $_SERVER["HTTP_USER_AGENT"] = "";
        Config::set('calibre_custom_column_list', []);
        Config::set('calibre_custom_column_preview', []);
    }

    public function testQueries(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11"]);
        Database::clearDb();
        $request = new Request();
        $booklist = new BookList($request);

        [$query, $params] = CustomColumnType::createByLookup("custom_01")->getCustom("1")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(5, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_01"], true);
        $this->assertEquals("sample_text", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_02")->getCustom("3")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(4, $entryArray);
        // handle case where we have several values, e.g. array of text for type 2 (csv)
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_02"], true);
        $this->assertEquals("a,c", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_03")->getCustom("3")->getQuery();
        [$entryArray, $totalNumber] =  $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(1, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_03"], true);
        $this->assertEquals("<div><p>simple test no formatting</p></div>", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_04")->getCustom("4")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(2, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_04"], true);
        $this->assertEquals("GroupA [1]", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_05")->getCustom("6")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(6, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_05"], true);
        $this->assertEquals("val05", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_06")->getCustom("2016-04-24")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(6, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_06"], true);
        $this->assertEquals("2016-04-24", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_07")->getCustom("11.0")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(2, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_07"], true);
        $this->assertEquals("11", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_08")->getCustom("-2")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(3, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_08"], true);
        $this->assertEquals("-2", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_09")->getCustom("0")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(12, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_09"], true);
        $this->assertEquals("Not Set", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_09")->getCustom("2")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(4, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_09"], true);
        $this->assertEquals("1 Star", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_10")->getCustom("-1")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(9, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_10"], true);
        $this->assertEquals("Not Set", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_10")->getCustom("0")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(6, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_10"], true);
        $this->assertEquals("No", $custom[0]['htmlvalue']);

        [$query, $params] = CustomColumnType::createByLookup("custom_10")->getCustom("1")->getQuery();
        [$entryArray, $totalNumber] = $booklist->getEntryArray($query, $params, 1);
        $this->assertCount(7, $entryArray);
        $custom = $entryArray[0]->book->getCustomColumnValues(["custom_10"], true);
        $this->assertEquals("Yes", $custom[0]['htmlvalue']);
    }

    public function testGetQuery(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_custom_column', ["custom_01", "custom_02", "custom_03", "custom_04", "custom_05", "custom_06", "custom_07", "custom_08", "custom_09", "custom_10", "custom_11"]);
        Database::clearDb();

        $custom = CustomColumnType::createByLookup("custom_01")->getCustom("1");
        $this->assertEquals($custom->customColumnType->getQuery("1"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_02")->getCustom("3");
        $this->assertEquals($custom->customColumnType->getQuery("3"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_03")->getCustom("3");
        $this->assertEquals($custom->customColumnType->getQuery("3"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_04")->getCustom("4");
        $this->assertEquals($custom->customColumnType->getQuery("4"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_05")->getCustom("6");
        $this->assertEquals($custom->customColumnType->getQuery("6"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_06")->getCustom("2016-04-24");
        $this->assertEquals($custom->customColumnType->getQuery("2016-04-24"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_07")->getCustom("11.0");
        $this->assertEquals($custom->customColumnType->getQuery("11.0"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_08")->getCustom("-2");
        $this->assertEquals($custom->customColumnType->getQuery("-2"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_09")->getCustom("0");
        $this->assertEquals($custom->customColumnType->getQuery("0"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_09")->getCustom("1");
        $this->assertEquals($custom->customColumnType->getQuery("1"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_10")->getCustom("-1");
        $this->assertEquals($custom->customColumnType->getQuery("-1"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_10")->getCustom("0");
        $this->assertEquals($custom->customColumnType->getQuery("0"), $custom->getQuery());

        $custom = CustomColumnType::createByLookup("custom_10")->getCustom("1");
        $this->assertEquals($custom->customColumnType->getQuery("1"), $custom->getQuery());
    }

    public function tearDown(): void
    {
        Config::set('calibre_custom_column', []);
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Database::clearDb();
    }
}
