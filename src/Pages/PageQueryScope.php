<?php

/**
 * COPS (Calibre OPDS PHP Server) enum file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Pages;

enum PageQueryScope: string
{
    case AUTHOR = "author";
    case BOOK = "book";
    case FORMAT = "format";
    case IDENTIFIER = "identifier";
    case LANGUAGE = "language";
    case PUBLISHER = "publisher";
    case RATING = "rating";
    case SERIES = "series";
    case TAG = "tag";
    case LIBRARIES = "libraries";
    case ALLBOOKS = "allbooks";
    case RECENT = "recent";

    /**
     * Summary of in_array
     * @param array<mixed> $values
     * @return bool
     */
    public function in_array($values): bool
    {
        return in_array($this->value, $values);
    }

    public function result(): string
    {
        return "search.result.{$this->value}";
    }

    public function title(): string
    {
        return match ($this) {
            self::AUTHOR => "authors.title",
            self::BOOK => "bookword.title",
            self::FORMAT => "formats.title",
            self::IDENTIFIER => "identifiers.title",
            self::LANGUAGE => "languages.title",
            self::PUBLISHER => "publishers.title",
            self::RATING => "ratings.title",
            self::SERIES => "series.title",
            self::TAG => "tags.title",
            self::LIBRARIES => "libraries.title",
            self::ALLBOOKS => "allbooks.title",
            self::RECENT => "recent.title",
        };
    }
}
