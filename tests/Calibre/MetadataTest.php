<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Calibre;

use SebLucas\Cops\Calibre\Metadata;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Calibre\Database;
use SebLucas\Cops\Handlers\HtmlHandler;
use SebLucas\Cops\Input\Config;

class MetadataTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Config::set('calibre_directory', dirname(__DIR__) . "/BaseWithSomeBooks/");
        Database::clearDb();
    }

    public function testUpdateBookFromComic(): void
    {
        $book = Book::getBookById(17);
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $book = Metadata::updateBookFromComic($book, $filePath);

        $expected = $this->getExpectedComicInfo();
        $this->assertEquals($expected['title'], $book->getTitle());
        $this->assertEquals($expected['pubdate'], $book->pubdate);
        $this->assertStringContainsString($expected['comment'], $book->getComment());
        $this->assertEquals($expected['authors'][0]['name'], $book->getAuthors()[0]->name);
        $this->assertEquals($expected['publisher']['name'], $book->getPublisher()->name);
        $this->assertEquals($expected['serie']['name'], $book->getSerie()->name);
        $this->assertEquals($expected['seriesIndex'], $book->seriesIndex);
        $this->assertEquals($expected['tags'][0]['name'], $book->getTags()[0]->name);
        $this->assertEquals($expected['identifiers'][0]['uri'], $book->getIdentifiers()[0]->uri);
        $this->assertEquals($expected['languages'], $book->getLanguages());
        $this->assertEquals($expected['pages'], $book->getPages());
    }

    /**
     * @return array<string, mixed>
     */
    protected function getExpectedComicInfo(): array
    {
        return [
            "title" => "You Had One Job",
            "pubdate" => "2020-10-1",
            "comment" => "THE RETURN OF THE NEW FANTASTIC FOUR?!",
            "authors" => [
                [
                    "name" => "Dan Slott",
                    "sort" => "Dan Slott",
                ],
            ],
            "publisher" => [
                "name" => "Marvel",
            ],
            "serie" => [
                "name" => "Fantastic Four",
            ],
            "seriesIndex" => 22,
            "tags" => [
                [
                    "name" => "Superhero",
                ],
            ],
            "identifiers" => [
                [
                    "type" => "url",
                    "uri" => "https://comicvine.gamespot.com/fantastic-four-22-you-had-one-job/4000-787351/",
                ],
            ],
            "languages" => "en",
            "pages" => "24",
        ];
    }
}
