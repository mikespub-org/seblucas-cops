<?php

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\Mail;

require_once __DIR__ . '/config.php';

if ($error = Mail::checkConfiguration()) {
    echo $error;
    exit;
}

$request = new Request();
$idData = (int) $request->post("data");
$emailDest = $request->post("email");
if ($error = Mail::checkRequest($idData, $emailDest)) {
    echo $error;
    exit;
}

if ($error = Mail::sendMail($idData, $emailDest)) {
    echo localize("mail.messagenotsent");
    echo $error;
    exit;
}

echo localize("mail.messagesent");
