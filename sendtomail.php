<?php

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/base.php';
/** @var array $config */

use PHPMailer\PHPMailer\PHPMailer;

function checkConfiguration()
{
    global $config;

    if (is_null($config['cops_mail_configuration']) ||
        !is_array($config['cops_mail_configuration']) ||
        empty($config['cops_mail_configuration']["smtp.host"]) ||
        empty($config['cops_mail_configuration']["address.from"])) {
        return "NOK. bad configuration.";
    }
    return false;
}

function checkRequest($idData, $emailDest)
{
    if (empty($idData)) {
        return 'No data sent.';
    }
    if (empty($emailDest)) {
        return 'No email sent.';
    }
    return false;
}

if (php_sapi_name() === 'cli') {
    return;
}

global $config;

if ($error = checkConfiguration()) {
    echo $error;
    exit;
}

$idData = (int)$_REQUEST["data"];
$emailDest = $_REQUEST["email"];
if ($error = checkRequest($idData, $emailDest)) {
    echo $error;
    exit;
}

$book = Book::getBookByDataId($idData);
$data = $book->getDataById($idData);

if (filesize($data->getLocalPath()) > 10 * 1024 * 1024) {
    echo 'Attachment too big';
    exit;
}

$mail = new PHPMailer();

$mail->IsSMTP();
$mail->Timeout = 30; // 30 seconds as some files can be big
$mail->Host = $config['cops_mail_configuration']["smtp.host"];
if (!empty($config['cops_mail_configuration']["smtp.secure"])) {
    $mail->SMTPSecure = $config['cops_mail_configuration']["smtp.secure"];
    $mail->Port = 465;
}
$mail->SMTPAuth = !empty($config['cops_mail_configuration']["smtp.username"]);
if (!empty($config['cops_mail_configuration']["smtp.username"])) {
    $mail->Username = $config['cops_mail_configuration']["smtp.username"];
}
if (!empty($config['cops_mail_configuration']["smtp.password"])) {
    $mail->Password = $config['cops_mail_configuration']["smtp.password"];
}
if (!empty($config['cops_mail_configuration']["smtp.secure"])) {
    $mail->SMTPSecure = $config['cops_mail_configuration']["smtp.secure"];
}
if (!empty($config['cops_mail_configuration']["smtp.port"])) {
    $mail->Port = $config['cops_mail_configuration']["smtp.port"];
}

$mail->From = $config['cops_mail_configuration']["address.from"];
$mail->FromName = $config['cops_title_default'];

# Limit sending to 5 email addresses
$emailAddresses = explode(";", $emailDest);
if(count($emailAddresses) <= 5){
    foreach ($emailAddresses as $emailAddress) {
        if (empty($emailAddress)) {
            continue;
        }
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            echo  $emailAddress . " is an unsupported email address. Update the email address on the settings page.";
            exit;
        }
        $mail->AddAddress($emailAddress);
    }
} else {
    echo "You can send to a maximum of 5 email addresses. Please update your email address list.";
    exit;
}

$mail->AddAttachment($data->getLocalPath());

$mail->IsHTML(true);
$mail->CharSet = "UTF-8";
$mail->Subject = 'Sent by COPS : ';
if (!empty($config['cops_mail_configuration']["subject"])) {
    $mail->Subject = $config['cops_mail_configuration']["subject"];
}
$mail->Subject .= $data->getUpdatedFilename();
$mail->Body    = "<h1>" . $book->title . "</h1><h2>" . $book->getAuthorsName() . "</h2>" . $book->getComment();
$mail->AltBody = "Sent by COPS";

if (!$mail->Send()) {
    echo localize("mail.messagenotsent");
    echo 'Mailer Error: ' . $mail->ErrorInfo;
    exit;
}

echo localize("mail.messagesent");
