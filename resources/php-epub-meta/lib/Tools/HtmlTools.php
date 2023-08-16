<?php

namespace SebLucas\EPubMeta\Tools;

/**
 * From Epubli\Common\Tools - see https://github.com/epubli/common
 * @author Epubli Developers <devs@epubli.com>
 */
class HtmlTools
{
    /**
     * @param string $html
     * @return string
     */
    public static function convertEntitiesNamedToNumeric($html)
    {
        return strtr($html, include(__DIR__ . '/htmlEntityMap.php'));
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function isBlockLevelElement($name)
    {
        return in_array($name, include(__DIR__ . '/htmlBlockLevelElements.php'));
    }

    /**
     * performs a tag-aware truncation of (html-) strings, preserving tag integrity
     * @param array<string>|string $html
     * @param int|string $length
     * @return bool|string
     */
    public static function truncate($html, $length = "20%")
    {
        $htmls = is_array($html) ? $html : [$html];
        foreach ($htmls as &$htmlString) {
            if (is_string($length)) {
                $length = trim($length);
                /* interpret percentage value */
                if (substr($length,-1)=='%') {
                    $length = (int) (strlen($htmlString) * intval(substr($length,0,-1)) / 100);
                }
            }
            $htmlString = substr($htmlString, 0, $length);
            /* eliminate trailing truncated tag fragment if present */
            $htmlString = preg_replace('/<[^>]*$/is','',$htmlString);
        }

        return is_array($html) ? $htmls : array_pop($htmls);
    }

    /**
     * strips all occurring html tags from $html (which can either be a string or an array of strings),
     * preserving all content enclosed by all tags in $keep and
     * dumping the content residing in all tags listed in $drop
     * @param array<string>|string $html
     * @param array<string> $keep
     * @param array<string> $drop
     * @return array<string>|string
     */
    public static function stripHtmlTags(
        $html,
        $keep =
        ['title', 'br', 'p', 'h1','h2','h3','h4','h5','span','div','i','strong','b', 'table', 'td', 'th', 'tr'],
        $drop =
        ['head','style']
    )
    {
        $htmls = is_array($html) ? $html : [$html];
        foreach ($htmls as &$htmlString) {
            foreach ($drop as $dumpTag) {
                $htmlString = preg_replace("/<$dumpTag.*$dumpTag>/is", "\n", $htmlString);
            }
            $htmlString = preg_replace("/[\n\r ]{2,}/i", "\n", $htmlString);
            $htmlString = preg_replace("/[\n|\r]/i", '<br />', $htmlString);

            /* @TODO: remove style tags and only keep body content (drop head) */
            $tempFunc = function ($matches) use ($keep) {
                $htmlNode = "<" . $matches[1] . ">" . strip_tags($matches[2]) . "</" . $matches[1] . ">";
                if (in_array($matches[1], $keep)) {
                    return " " . $htmlNode . " ";
                } else {
                    return "";
                }
            };

            $allowedTags = implode("|", array_values($keep));
            $regExp = '@<(' . $allowedTags . ')[^>]*?>(.*?)<\/\1>@i';
            $htmlString = preg_replace_callback($regExp, $tempFunc, $htmlString);

            $htmlString = strip_tags($htmlString, "<" . implode("><", $keep) . ">");
        }
        /* preserve injected variable cast type (string|array) when returning processed entity */
        return is_array($html) ? $htmls : array_pop($htmls);
    }
}