<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org//licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use DOMDocument;
use JsonException;

class Format
{
    /**
     * This method is a direct copy-paste from
     * http://tmont.com/blargh/2010/1/string-format-in-php
     * @param string $format
     * @return string
     */
    public static function str_format($format)
    {
        $args = func_get_args();
        $format = array_shift($args);

        preg_match_all('/(?=\{)\{(\d+)\}(?!\})/', (string) $format, $matches, PREG_OFFSET_CAPTURE);
        $offset = 0;
        foreach ($matches[1] as $data) {
            $i = $data[0];
            $format = substr_replace($format, (string) $args[(int) $i], $offset + $data[1] - 1, 2 + strlen($i));
            $offset += strlen((string) $args[(int) $i]) - 2 - strlen($i);
        }

        return $format;
    }

    /**
     * Summary of template
     * @param ?array<mixed> $data
     * @param string $template
     * @return string
     */
    public static function template($data, $template)
    {
        // replace {{=it.key}} (= doT syntax) and {{it.key}} (= twig syntax) with value
        $pattern = [];
        $replace = [];
        foreach ($data as $key => $value) {
            array_push($pattern, '/\{\{=?\s*it\.' . $key . '\s*\}\}/');
            array_push($replace, $value);
        }
        $filecontent = file_get_contents($template);
        return preg_replace($pattern, $replace, $filecontent);
    }

    /**
     * Summary of json
     * @param mixed $data
     * @param int|null $flags
     * @return string
     */
    public static function json($data, $flags = null)
    {
        $flags ??= JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
        try {
            return json_encode($data, $flags);
        } catch (JsonException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Export array in more readable format
     * @param mixed $data
     * @return string
     */
    public static function export($data)
    {
        $content = var_export($data, true) . ',';
        // replace arrays with short format
        $content = str_replace(['array (', '),'], ['[', '],'], $content);
        // remove numeric keys from arrays
        $content = preg_replace('/^(\s*)\d+ => /m', '$1', $content);
        // strip empty lines after removing keys
        $content = preg_replace('/\n\s+\n/', "\n", $content);
        // collapse empty arrays
        $content = preg_replace('/\[\s+\]/', '[]', $content);
        // start array values on same line as key
        $content = preg_replace('/ => \n\s*\[/', ' => [', $content);
        return trim($content, ',');
    }

    /**
     * Summary of xml2xhtml
     * @param string $xml
     * @return ?string
     */
    public static function xml2xhtml($xml)
    {
        return preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', function ($m) {
            $xhtml_tags = ['br', 'hr', 'input', 'frame', 'img', 'area', 'link', 'col', 'base', 'basefont', 'param'];
            if (in_array($m[1], $xhtml_tags)) {
                return '<' . $m[1] . $m[2] . ' />';
            } else {
                return '<' . $m[1] . $m[2] . '></' . $m[1] . '>';
            }
        }, $xml);
    }

    /**
     * Summary of display_xml_error
     * @param mixed $error
     * @return string
     */
    public static function display_xml_error($error)
    {
        $return = '';
        $return .= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= 'Warning ' . $error->code . ': ';
                break;
            case LIBXML_ERR_ERROR:
                $return .= 'Error ' . $error->code . ': ';
                break;
            case LIBXML_ERR_FATAL:
                $return .= 'Fatal Error ' . $error->code . ': ';
                break;
        }

        $return .= trim($error->message) .
                "\n  Line: " . $error->line .
                "\n  Column: " . $error->column;

        if ($error->file) {
            $return .= "\n  File: " . $error->file;
        }

        return "$return\n\n--------------------------------------------\n\n";
    }

    /**
     * Summary of are_libxml_errors_ok
     * @return bool
     */
    public static function are_libxml_errors_ok()
    {
        $errors = libxml_get_errors();

        foreach ($errors as $error) {
            if ($error->code == 801) {
                return false;
            }
        }
        return true;
    }

    /**
     * Summary of html2xhtml
     * @param string $html
     * @return string
     */
    public static function html2xhtml($html)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);

        $doc->loadHTML('<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' .
                            $html . '</body></html>'); // Load the HTML
        $output = $doc->saveXML($doc->documentElement); // Transform to an Ansi xml stream
        $output = self::xml2xhtml($output);
        if (preg_match('#<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></meta></head><body>(.*)</body></html>#ms', (string) $output, $matches)) {
            $output = $matches [1]; // Remove <html><body>
        }
        /*
        // In case of error with summary, use it to debug
        $errors = libxml_get_errors();

        foreach ($errors as $error) {
            $output .= self::display_xml_error($error);
        }
        */

        if (!self::are_libxml_errors_ok()) {
            $output = 'HTML code not valid.';
        }

        libxml_use_internal_errors(false);
        return $output;
    }
}
