<?php

/**
 * Summary of to_file
 * @param string $input
 * @return string
 */
function to_file($input)
{
    $input = str_replace(' ', '_', $input);
    $input = str_replace('__', '_', $input);
    $input = str_replace(',_', ',', $input);
    $input = str_replace('_,', ',', $input);
    $input = str_replace('-_', '-', $input);
    $input = str_replace('_-', '-', $input);
    $input = str_replace(',', '__', $input);
    return $input;
}

/**
 * Summary of book_output
 * @param string $input
 * @return string
 */
function book_output($input)
{
    $input = str_replace('__', ',', $input);
    $input = str_replace('_', ' ', $input);
    $input = str_replace(',', ', ', $input);
    $input = str_replace('-', ' - ', $input);
    [$author, $title] = explode('-', $input, 2);
    $author = trim($author);
    $title  = trim($title);

    if (!$title) {
        $title  = $author;
        $author = '';
    }

    return '<span class="title">' . htmlspecialchars($title) . '</span>' .
           '<span class="author">' . htmlspecialchars($author) . '</author>';
}
