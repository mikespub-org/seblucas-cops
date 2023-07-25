<?php

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Calibre\Book;
use SebLucas\Cops\Input\Request;

require_once dirname(__FILE__) . '/config.php';
/** @var array $config */

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    public static $maxSize = 10 * 1024 * 1024;

    public static function checkConfiguration()
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

    public static function checkRequest($idData, $emailDest)
    {
        if (empty($idData)) {
            return 'No data sent.';
        }
        if (empty($emailDest)) {
            return 'No email sent.';
        }
        # Validate emailDest
        if (!filter_var($emailDest, FILTER_VALIDATE_EMAIL)) {
            return 'No valid email. ' . $emailDest . " is an unsupported email address. Update the email address on the settings page.";
        }
        return false;
    }

    public static function sendMail($idData, $emailDest)
    {
        global $config;

        $book = Book::getBookByDataId($idData);
        $data = $book->getDataById($idData);

        if (!file_exists($data->getLocalPath())) {
            return 'No email sent. Attachment not found';
        }
        if (filesize($data->getLocalPath()) > self::$maxSize) {
            return 'No email sent. Attachment too big';
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

        $mail->AddAddress($emailDest);

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
            return 'Mailer Error: ' . $mail->ErrorInfo;
        }
        return false;
    }
}

if (php_sapi_name() === 'cli') {
    return;
}

global $config;

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
