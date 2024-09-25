<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint to fetch book covers or files or extra data
 * URL format: fetch.php?id={bookId}&type={type}&data={idData}&view={viewOnly}
 *          or fetch.php?id={bookId}&thumb={thumb} for book cover thumbnails
 *          or fetch.php?id={bookId}&file={file} for extra data file for this book
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 * @deprecated 3.1.0 use index.php/fetch instead
 */

$link = str_replace('fetch.php', 'index.php/fetch', $_SERVER['REQUEST_URI'] ?? '');
header('Location: ' . $link);
