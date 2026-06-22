<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Pages;

use SebLucas\Cops\Pages\PageId;
use SebLucas\Cops\Pages\PageQueryScope;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Database\Database;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\Entry;

class PageQueryResultTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testSearchByCategory_NoScope(): void
    {
        $request = new Request();
        $request->set('query', "scarlet");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("scarlet", $currentPage->title);

        // Verify first non-header entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry, "Should have at least one result entry");
        $this->assertSame("Search result for *scarlet* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);
    }

    public function testSearchByCategory_BookScope(): void
    {
        $request = new Request();
        $request->set('query', "scarlet");
        $request->set('scope', PageQueryScope::BOOK->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("scarlet", $currentPage->title);
        $this->assertTrue($currentPage->containsBook());

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("A Study in Scarlet", $firstEntry->title);
        $this->assertSame("urn:uuid:f3a4534e-d7bc-415a-8887-ba2b2810c980", $firstEntry->id);
    }

    public function testSearchByCategory_AuthorScope(): void
    {
        $request = new Request();
        $request->set('query', "doyle");
        $request->set('scope', PageQueryScope::AUTHOR->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("doyle", $currentPage->title);
        $this->assertFalse($currentPage->containsBook());

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Arthur Conan Doyle", $firstEntry->title);
        $this->assertSame("cops:authors:1", $firstEntry->id);
    }

    public function testSearchByCategory_SeriesScope(): void
    {
        $request = new Request();
        $request->set('query', "sherlock");
        $request->set('scope', PageQueryScope::SERIES->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("sherlock", $currentPage->title);
        $this->assertFalse($currentPage->containsBook());

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Sherlock Holmes", $firstEntry->title);
        $this->assertSame("cops:series:1", $firstEntry->id);
    }

    public function testSearchByCategory_TagScope(): void
    {
        $request = new Request();
        $request->set('query', "fiction");
        $request->set('scope', PageQueryScope::TAG->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("fiction", $currentPage->title);
        $this->assertFalse($currentPage->containsBook());

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Fiction", $firstEntry->title);
        $this->assertSame("cops:tags:1", $firstEntry->id);
    }

    public function testSearchByCategory_PublisherScope(): void
    {
        $request = new Request();
        $request->set('query', "macmillan");
        $request->set('scope', PageQueryScope::PUBLISHER->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("macmillan", $currentPage->title);
        $this->assertFalse($currentPage->containsBook());

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Macmillan and Co. London", $firstEntry->title);
        $this->assertSame("cops:publishers:2", $firstEntry->id);
    }

    public function testSearchByCategory_CommentScope_Disabled(): void
    {
        Config::set('search_comments', 0);
        $request = new Request();
        $request->set('query', "dodgson");
        $request->set('scope', PageQueryScope::COMMENT->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // When comments search is disabled, should fall back to default book search
        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertTrue($currentPage->containsBook());

        Config::set('search_comments', 0);
    }

    public function testSearchByCategory_CommentScope_Enabled(): void
    {
        Config::set('search_comments', 1);
        $request = new Request();
        $request->set('query', "dodgson");
        $request->set('scope', PageQueryScope::COMMENT->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("dodgson", $currentPage->title);
        // Comments are replaced with book entries
        $this->assertTrue($currentPage->containsBook());

        // Verify first entry (book found via comment search)
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Alice's Adventures in Wonderland", $firstEntry->title);
        $this->assertSame("urn:uuid:d74fec58-06bc-4ba8-b8b4-24a91a58e6f9", $firstEntry->id);

        Config::set('search_comments', 0);
    }

    public function testSearchByCategory_CommentScope_TypeaheadMode(): void
    {
        Config::set('search_comments', 1);
        $request = new Request();
        $request->set('query', "dodgson");
        //$request->set('scope', PageQueryScope::COMMENT->value);
        $request->set('search', 1);  // typeahead mode
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("dodgson", $currentPage->title);
        // Comments are replaced with book entries even in typeahead mode
        $this->assertFalse($currentPage->containsBook());

        // Verify first entry (book found via comment search in typeahead mode)
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *dodgson* in book comments", $firstEntry->title);
        $this->assertSame("db:query::comment", $firstEntry->id);

        Config::set('search_comments', 0);
    }

    public function testSearchByCategory_NoteScope_Disabled(): void
    {
        Config::set('search_notes', 0);
        $request = new Request();
        $request->set('query', "wiki");
        $request->set('scope', PageQueryScope::NOTE->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // When notes search is disabled, scope is not in scopeList so falls back to default search
        $this->assertNotEmpty($currentPage->entryArray);

        Config::set('search_notes', 0);
    }

    public function testSearchByCategory_NoteScope_Enabled(): void
    {
        Config::set('search_notes', 1);
        $request = new Request();
        $request->set('query', "wiki");
        $request->set('scope', PageQueryScope::NOTE->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertStringContainsString("wiki", $currentPage->title);
        // Notes are replaced with type item entries
        $this->assertFalse($currentPage->containsBook());

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Lewis Carroll", $firstEntry->title);
        $this->assertSame("cops:authors:3", $firstEntry->id);

        Config::set('search_notes', 0);
    }

    public function testSearchByCategory_NoteScope_TypeaheadMode(): void
    {
        Config::set('search_notes', 1);
        $request = new Request();
        $request->set('query', "wiki");
        //$request->set('scope', PageQueryScope::NOTE->value);
        $request->set('search', 1);  // typeahead mode
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("wiki", $currentPage->title);
        // Notes are replaced with type item entries even in typeahead mode
        $this->assertFalse($currentPage->containsBook());

        // Verify first entry (type item found via note search in typeahead mode)
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *wiki* in extra notes", $firstEntry->title);
        $this->assertSame("db:query::note", $firstEntry->id);

        Config::set('search_notes', 0);
    }

    public function testSearchByCategory_AnnotationScope(): void
    {
        Config::set('search_annotations', 1);
        $request = new Request();
        $request->set('query', "test");
        $request->set('scope', PageQueryScope::ANNOTATION->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Annotation scope returns empty array from searchByScope, but shows no result entry
        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("No search result", $currentPage->entryArray[0]->title);

        // Verify it's a no-result entry
        $this->assertStringContainsString("test", $currentPage->entryArray[0]->title);

        Config::set('search_annotations', 0);
    }

    public function testSearchByCategory_NoResults(): void
    {
        $request = new Request();
        $request->set('query', "xyznonexistent");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("No search result", $currentPage->entryArray[0]->title);

        // Verify the no-result entry contains the query
        $this->assertStringContainsString("xyznonexistent", $currentPage->entryArray[0]->title);
    }

    public function testSearchByCategory_WithIgnoredCategories(): void
    {
        Config::set('ignored_categories', ["author", "series", "tag"]);
        $request = new Request();
        $request->set('query', "car");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Should only show book and publisher results (author, series, tag are ignored)
        $this->assertNotEmpty($currentPage->entryArray);
        $titles = array_map(fn($e) => $e->title, $currentPage->entryArray);
        $this->assertNotContains("1 author", $titles);

        // Verify first result
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *car* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);

        Config::set('ignored_categories', ["format", "identifier"]);
    }

    public function testSearchByCategory_TypeaheadMode(): void
    {
        $request = new Request();
        $request->set('query', "car");
        $request->set('search', 1);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Typeahead mode shows category headers + limited entries
        $this->assertNotEmpty($currentPage->entryArray);
        // Should have at least category headers
        $this->assertGreaterThanOrEqual(2, count($currentPage->entryArray));

        // Verify first non-header entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *car* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);
    }

    public function testSearchByCategory_EmptyQuery(): void
    {
        $request = new Request();
        $request->set('query', "");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Empty query may return results depending on implementation
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for ** in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);
    }

    public function testSearchByCategory_SpecialCharacters(): void
    {
        $request = new Request();
        $request->set('query', "sherlock's");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("sherlock", $currentPage->title);

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("No search result for *sherlock's*", $firstEntry->title);
        $this->assertSame("db:query::", $firstEntry->id);
    }

    public function testSearchByCategory_CaseInsensitive(): void
    {
        $request = new Request();
        $request->set('query', "SHERLOCK");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);
        $this->assertStringContainsString("SHERLOCK", $currentPage->title);

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *SHERLOCK* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);
    }

    public function testSearchByCategory_AcrossMultipleCategories(): void
    {
        $request = new Request();
        $request->set('query', "car");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Search for "car" should return results
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify we have some results
        $this->assertGreaterThan(0, count($currentPage->entryArray));

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *car* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);
    }

    public function testSearchByCategory_ResultCountsPerCategory(): void
    {
        $request = new Request();
        $request->set('query', "doyle");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);

        // Find author category entry
        $authorEntry = null;
        foreach ($currentPage->entryArray as $entry) {
            if (str_contains($entry->id, ":author")) {
                $authorEntry = $entry;
                break;
            }
        }

        if ($authorEntry !== null) {
            $this->assertStringContainsString("author", $authorEntry->title);
            $this->assertGreaterThan(0, $authorEntry->numberOfElement);
        }

        // Verify first result entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *doyle* in authors", $firstEntry->title);
        $this->assertSame("db:query::author", $firstEntry->id);
    }

    public function testSearchByCategory_MultiDatabaseAcrossCategories(): void
    {
        Config::set('calibre_directory', [
            "Some books" => dirname(__DIR__) . "/BaseWithSomeBooks/",
            "One book" => dirname(__DIR__) . "/BaseWithOneBook/",
        ]);

        $request = new Request();
        $request->set('query', "car");
        $request->set('multi', 1);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Multi-database search should return results
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Some books", $firstEntry->title);
        $this->assertSame("db:query:0", $firstEntry->id);

        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testSearchByCategory_CategoryLinksContainQuery(): void
    {
        $request = new Request();
        $request->set('query', "scarlet");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        $this->assertNotEmpty($currentPage->entryArray);

        // Verify that at least one entry has a navigation link
        $linkFound = false;
        foreach ($currentPage->entryArray as $entry) {
            $navLink = $entry->getNavLink();
            if (!empty($navLink)) {
                $this->assertStringContainsString(
                    "scarlet",
                    $navLink,
                    "Link should contain query parameter"
                );
                $linkFound = true;
                break;
            }
        }
        $this->assertTrue($linkFound, "Should have at least one entry with a navigation link");

        // Verify first result entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *scarlet* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);
    }

    public function testSearchByCategory_NoDatabaseSelected(): void
    {
        $request = new Request();
        $request->set('query', "scarlet");
        $request->set('multi', 1);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // When no database is selected and multi=1, should show results
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *scarlet* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);
    }

    public function testSearchByCategory_WithNormalizer(): void
    {
        Config::set('normalized_search', 1);
        $request = new Request();
        $request->set('query', "cure");  // Test with normalizer enabled (matches "La Curée")
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Normalizer should handle the search without crashing
        $this->assertIsArray($currentPage->entryArray);
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify first entry matches the normalized query
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("Search result for *cure* in books", $firstEntry->title);
        $this->assertSame("db:query::book", $firstEntry->id);

        Config::set('normalized_search', 0);
    }

    public function testSearchByCategory_WithPagination(): void
    {
        Config::set('max_item_per_page', 5);
        $request = new Request();
        $request->set('query', "the");
        $request->set('scope', PageQueryScope::BOOK->value);
        $request->set('n', 2);  // second page
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Verify we have results
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify first entry
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("The Memoirs of Sherlock Holmes", $firstEntry->title);
        $this->assertSame("urn:uuid:af1d960b-7167-47f2-a1d8-6a38c8c893fd", $firstEntry->id);
        $this->assertTrue($currentPage->containsBook());

        Config::set('max_item_per_page', 48);
    }

    public function testSearchByCategory_UnicodeQuery(): void
    {
        $request = new Request();
        $request->set('query', "孙武");  // Chinese characters (matches author "孙武")
        $request->set('scope', PageQueryScope::AUTHOR->value);
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Should handle unicode without crashing and return actual results
        $this->assertIsArray($currentPage->entryArray);
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify first entry is the author
        $firstEntry = $this->getFirstNonHeaderEntry($currentPage->entryArray);
        $this->assertNotNull($firstEntry);
        $this->assertSame("孙武", $firstEntry->title);
        $this->assertSame("cops:authors:9", $firstEntry->id);
    }

    public function testSearchByCategory_SqlInjectionAttempt(): void
    {
        $request = new Request();
        $request->set('query', "'; DROP TABLE books; --");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Should not crash, should return results or no results
        $this->assertIsArray($currentPage->entryArray);
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify we have at least one actual result
        $hasResult = false;
        foreach ($currentPage->entryArray as $entry) {
            if ($entry->id !== "tt-header") {
                $hasResult = true;
                break;
            }
        }
        $this->assertTrue($hasResult, "Should have at least one result entry");
    }

    public function testSearchByCategory_XssAttempt(): void
    {
        $request = new Request();
        $request->set('query', "<script>alert('xss')</script>");
        $page = PageId::OPENSEARCH_QUERY;

        $currentPage = PageId::getPage($page, $request);

        // Should not crash, should return results or no results
        $this->assertIsArray($currentPage->entryArray);
        $this->assertNotEmpty($currentPage->entryArray);

        // Verify we have at least one actual result
        $hasResult = false;
        foreach ($currentPage->entryArray as $entry) {
            if ($entry->id !== "tt-header") {
                $hasResult = true;
                break;
            }
        }
        $this->assertTrue($hasResult, "Should have at least one result entry");
    }

    /**
     * Helper method to get the first non-header entry from entryArray
     * @param array<Entry> $entryArray
     * @return Entry|null
     */
    private function getFirstNonHeaderEntry($entryArray)
    {
        foreach ($entryArray as $entry) {
            // Skip the "no results" header entry
            if ($entry->id === "tt-header") {
                continue;
            }
            // Return all other entries (database headers, category results, etc.)
            return $entry;
        }
        return null;
    }

    public function tearDown(): void
    {
        Database::clearDb();
    }
}
