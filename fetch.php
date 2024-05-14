<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint to fetch book covers or files
 * URL format: fetch.php?id={bookId}&type={type}&data={idData}&view={viewOnly}
 *          or fetch.php?id={bookId}&thumb={thumb} for book cover thumbnails
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

Framework::run('fetch');
