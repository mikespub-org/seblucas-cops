<?php

/**
 * COPS (Calibre OPDS PHP Server) enum file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
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
        return localize("search.result.{$this->value}");
    }

    public function title(): string
    {
        return match ($this) {
            self::AUTHOR => localize("authors.title"),
            self::BOOK => localize("bookword.title"),
            self::FORMAT => localize("formats.title"),
            self::IDENTIFIER => localize("identifiers.title"),
            self::LANGUAGE => localize("languages.title"),
            self::PUBLISHER => localize("publishers.title"),
            self::RATING => localize("ratings.title"),
            self::SERIES => localize("series.title"),
            self::TAG => localize("tags.title"),
            self::LIBRARIES => localize("libraries.title"),
        };
    }
}
