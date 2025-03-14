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
use SebLucas\Cops\Calibre\CustomColumn;
use SebLucas\Cops\Calibre\CustomColumnType;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Calibre\Serie;
use SebLucas\Cops\Calibre\Tag;
use SebLucas\Cops\Calibre\BaseList;
use SebLucas\Cops\Calibre\BookList;
use SebLucas\Cops\Handlers\JsonHandler;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;

class HierarchyTest extends TestCase
{
    /** @var class-string */
    private static $handler = JsonHandler::class;

    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithCustomColumns/");
        Config::set('calibre_categories_using_hierarchy', ['series', 'tags']);
        Database::clearDb();
    }

    public static function tearDownAfterClass(): void
    {
        Config::set('calibre_directory', __DIR__ . "/BaseWithSomeBooks/");
        Config::set('calibre_categories_using_hierarchy', []);
        Database::clearDb();
    }

    /**
     * Summary of testSeriesHierarchy
     * ```
     * - The Enderverse
     *   |-- Ender's Saga (5)
     *   |-- The First Formic War (2)
     *   |-- The Shadow Series (2)
     * - ...
     * - Quantum (3)
     * ```
     * @return void
     */
    public function testSeriesHierarchy(): void
    {
        // The Enderverse
        /** @var Serie $serie */
        $serie = Serie::getInstanceById(37);
        $this->assertEquals("37", $serie->id);
        $this->assertEquals("The Enderverse", $serie->name);

        // Ender's Saga, The First Formic War, The Shadow Series
        $children = $serie->getChildEntries();
        $this->assertCount(3, $children);
        $this->assertEquals("The Enderverse.Ender's Saga", $children[0]->title);
        $this->assertEquals("The Enderverse.The First Formic War", $children[1]->title);
        $this->assertEquals("The Enderverse.The Shadow Series", $children[2]->title);

        // no books in this series
        $request = Request::build();
        $booklist = new BookList($request);
        [$entries, $count] = $booklist->getBooksByInstance($serie, 1);
        $this->assertCount(0, $entries);

        // books in child series
        $request = Request::build();
        $booklist = new BookList($request);
        [$entries, $count] = $booklist->getBooksByInstanceOrChildren($serie, 1);
        $this->assertCount(12, $entries);

        // Ender's Saga
        $serie = Serie::getInstanceById(18);
        $this->assertEquals("18", $serie->id);
        $this->assertEquals("The Enderverse.Ender's Saga", $serie->name);

        // The First Formic War, The Shadow Series
        $siblings = $serie->getSiblingEntries();
        $this->assertCount(2, $siblings);
        $this->assertEquals("The Enderverse.The First Formic War", $siblings[0]->title);
        $this->assertEquals("The Enderverse.The Shadow Series", $siblings[1]->title);

        // The Enderverse, ..., Quantum (top-level series)
        $baselist = new BaseList(Serie::class, $request);
        $entries = $baselist->browseAllEntries();
        $this->assertCount(4, $entries);
        $this->assertEquals("The Enderverse", $entries[0]->title);
        $this->assertEquals("Quantum", $entries[3]->title);

        // The Enderverse, Ender's Saga, ..., Quantum (all series)
        $entries = $baselist->browseAllEntries(1, true);
        $this->assertCount(7, $entries);
        $this->assertEquals("The Enderverse", $entries[0]->title);
        $this->assertEquals("The Enderverse.Ender's Saga", $entries[1]->title);
        $this->assertEquals("Quantum", $entries[6]->title);
    }

    /**
     * Summary of testTagHierarchy
     * ```
     * - Flat12 (2)
     * - Flat3 (1)
     * - Tree
     *   |-- More
     *   |   |-- Tag2 (1)
     *   |   |-- Tag3 (1)
     *   |-- Tag1 (1)
     * ```
     * @return void
     */
    public function testTagHierarchy(): void
    {
        // Tree with hierarchy
        /** @var Tag $tag */
        $tag = Tag::getInstanceById(7);
        $this->assertEquals("7", $tag->id);
        $this->assertEquals("Tree", $tag->name);

        // Tree.More, Tree.Tag1 (top-level children)
        $children = $tag->getChildEntries();
        $this->assertCount(2, $children);
        $this->assertEquals("Tree.More", $children[0]->title);
        $this->assertEquals("Tree.Tag1", $children[1]->title);

        // Tree.More, Tree.More.Tag2, Tree.More.Tag3, Tree.Tag1 (all children)
        $children = $tag->getChildEntries(true);
        $this->assertCount(4, $children);
        $this->assertEquals("Tree.More", $children[0]->title);
        $this->assertEquals("Tree.More.Tag2", $children[1]->title);
        $this->assertEquals("Tree.More.Tag3", $children[2]->title);
        $this->assertEquals("Tree.Tag1", $children[3]->title);

        // Tree.More
        $tag = Tag::getInstanceById(6);
        $this->assertEquals("6", $tag->id);
        $this->assertEquals("Tree.More", $tag->name);

        // Tree.More.Tag2, Tree.More.Tag3
        $children = $tag->getChildEntries();
        $this->assertCount(2, $children);
        $this->assertEquals("Tree.More.Tag2", $children[0]->title);
        $this->assertEquals("Tree.More.Tag3", $children[1]->title);

        // Tree.Tag1
        $siblings = $tag->getSiblingEntries();
        $this->assertCount(1, $siblings);
        $this->assertEquals("Tree.Tag1", $siblings[0]->title);

        // no books with this tag
        $request = Request::build();
        $booklist = new BookList($request);
        [$entries, $count] = $booklist->getBooksByInstance($tag, 1);
        $this->assertCount(0, $entries);

        // books with child tags
        $request = Request::build();
        $booklist = new BookList($request);
        [$entries, $count] = $booklist->getBooksByInstanceOrChildren($tag, 1);
        $this->assertCount(2, $entries);

        // Flat12, Flat3, Tree (top-level tags)
        $baselist = new BaseList(Tag::class, $request);
        $entries = $baselist->browseAllEntries();
        $this->assertCount(3, $entries);
        $this->assertEquals("Flat12", $entries[0]->title);
        $this->assertEquals("Flat3", $entries[1]->title);
        $this->assertEquals("Tree", $entries[2]->title);

        // Flat12, Flat3, Tree, Tree.More, ..., Tree.Tag1 (all tags)
        $entries = $baselist->browseAllEntries(1, true);
        $this->assertCount(7, $entries);
        $this->assertEquals("Flat12", $entries[0]->title);
        $this->assertEquals("Flat3", $entries[1]->title);
        $this->assertEquals("Tree", $entries[2]->title);
        $this->assertEquals("Tree.More", $entries[3]->title);
        $this->assertEquals("Tree.Tag1", $entries[6]->title);
    }

    public function testTagHierarchyFlat(): void
    {
        // Flat12 without hierarchy
        /** @var Tag $tag */
        $tag = Tag::getInstanceById(4);
        $this->assertEquals("4", $tag->id);
        $this->assertEquals("Flat12", $tag->name);

        $children = $tag->getChildEntries();
        $this->assertCount(0, $children);

        $siblings = $tag->getSiblingEntries();
        $this->assertCount(0, $siblings);

        // books with this tag
        $request = Request::build();
        $booklist = new BookList($request);
        [$entries, $count] = $booklist->getBooksByInstance($tag, 1);
        $this->assertCount(2, $entries);

        // books with child tags
        $request = Request::build();
        $booklist = new BookList($request);
        [$entries, $count] = $booklist->getBooksByInstanceOrChildren($tag, 1);
        $this->assertCount(2, $entries);
    }

    /**
     * Summary of testCustomHierarchy
     * ```
     * - a (6)
     * - b (2)
     * - c (4)
     * - d (1)
     *   |-- e (1)
     *   |   |-- f (1)
     *   |-- g (1)
     * - h
     *   |-- i (1)
     * ```
     * @return void
     */
    public function testCustomHierarchy(): void
    {
        // for hierarchical custom columns like Fiction, Fiction.Historical, Fiction.Romance etc.
        Config::set('calibre_categories_using_hierarchy', ['custom_02']);

        $custom = CustomColumn::createCustom(6, 5);
        $this->assertEquals("custom_02", $custom->customColumnType->getTitle());
        $this->assertEquals("d", $custom->getTitle());

        $children = $custom->getChildEntries();
        $this->assertCount(2, $children);
        $this->assertEquals("cops:custom:6:4", $children[0]->id);
        $this->assertEquals("d.e", $children[0]->title);

        $children = $custom->getChildEntries(true);
        $this->assertCount(3, $children);
        $this->assertEquals("cops:custom:6:4", $children[0]->id);
        $this->assertEquals("d.e", $children[0]->title);
        $this->assertEquals("cops:custom:6:6", $children[1]->id);
        $this->assertEquals("d.e.f", $children[1]->title);
        $this->assertEquals("cops:custom:6:7", $children[2]->id);
        $this->assertEquals("d.g", $children[2]->title);

        $custom = CustomColumn::createCustom(6, 6);
        $this->assertEquals("custom_02", $custom->customColumnType->getTitle());
        $this->assertEquals("d.e.f", $custom->getTitle());

        $parent = $custom->getParentEntry();
        $this->assertEquals("cops:custom:6:4", $parent->id);
        $this->assertEquals("d.e", $parent->title);

        $columnType = CustomColumnType::createByCustomID(6);
        $columnType->setHandler(static::$handler);
        $entries = $columnType->browseAllCustomValues();
        $this->assertCount(5, $entries);
        $this->assertEquals("a", $entries[0]->title);
        $this->assertEquals("b", $entries[1]->title);
        $this->assertEquals("c", $entries[2]->title);
        $this->assertEquals("d", $entries[3]->title);
        $this->assertEquals("h", $entries[4]->title);

        $entries = $columnType->browseAllCustomValues(-1, null, true);
        $this->assertCount(9, $entries);
        $this->assertEquals("d.e", $entries[4]->title);
        $this->assertEquals("d.e.f", $entries[5]->title);

        Config::set('calibre_categories_using_hierarchy', []);
    }
}
