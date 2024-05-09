<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint to send books by email
 * URL format: sendtomail.php (POST data and email)
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Mail;

require_once __DIR__ . '/config.php';

if ($error = Mail::checkConfiguration()) {
    echo $error;
    return;
}

$request = new Request();
$idData = (int) $request->post("data");
$emailDest = $request->post("email");
if ($error = Mail::checkRequest($idData, $emailDest)) {
    echo $error;
    return;
}

if ($error = Mail::sendMail($idData, $emailDest, $request)) {
    echo localize("mail.messagenotsent");
    echo $error;
    return;
}

echo localize("mail.messagesent");
