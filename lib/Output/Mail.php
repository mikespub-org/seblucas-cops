<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use PHPMailer\PHPMailer\PHPMailer;
use SebLucas\Cops\Calibre\Book;

class Mail
{
    public static int $maxSize = 10 * 1024 * 1024;

    /**
     * Summary of checkConfiguration
     * @return bool|string
     */
    public static function checkConfiguration()
    {
        $mailConfig = Config::get('mail_configuration');

        if (is_null($mailConfig) ||
            !is_array($mailConfig) ||
            empty($mailConfig["smtp.host"]) ||
            empty($mailConfig["address.from"])) {
            return "NOK. bad configuration.";
        }
        return false;
    }

    /**
     * Summary of checkRequest
     * @param mixed $idData
     * @param string $emailDest
     * @return bool|string
     */
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

    /**
     * Summary of sendMail
     * @param mixed $idData
     * @param string $emailDest
     * @param Request $request
     * @param bool $dryRun
     * @return bool|string
     */
    public static function sendMail($idData, $emailDest, $request, $dryRun = false)
    {
        $book = Book::getBookByDataId($idData, $request->database());
        if (!$book) {
            return 'No email sent. Unknown book data';
        }
        $data = $book->getDataById($idData);

        if (!file_exists($data->getLocalPath())) {
            return 'No email sent. Attachment not found';
        }
        if (filesize($data->getLocalPath()) > static::$maxSize) {
            return 'No email sent. Attachment too big';
        }

        $mailConfig = Config::get('mail_configuration');

        $mail = new PHPMailer();

        $mail->IsSMTP();
        $mail->Timeout = 30; // 30 seconds as some files can be big
        $mail->Host = $mailConfig["smtp.host"];
        if (!empty($mailConfig["smtp.secure"])) {
            $mail->SMTPSecure = $mailConfig["smtp.secure"];
            $mail->Port = 465;
        }
        $mail->SMTPAuth = !empty($mailConfig["smtp.username"]);
        if (!empty($mailConfig["smtp.username"])) {
            $mail->Username = $mailConfig["smtp.username"];
        }
        if (!empty($mailConfig["smtp.password"])) {
            $mail->Password = $mailConfig["smtp.password"];
        }
        if (!empty($mailConfig["smtp.secure"])) {
            $mail->SMTPSecure = $mailConfig["smtp.secure"];
        }
        if (!empty($mailConfig["smtp.port"])) {
            $mail->Port = $mailConfig["smtp.port"];
        }

        $mail->From = $mailConfig["address.from"];
        $mail->FromName = Config::get('title_default');

        $mail->AddAddress($emailDest);

        $mail->AddAttachment($data->getLocalPath());

        $mail->IsHTML(true);
        $mail->CharSet = "UTF-8";
        $mail->Subject = 'Sent by COPS : ';
        if (!empty($mailConfig["subject"])) {
            $mail->Subject = $mailConfig["subject"];
        }
        $mail->Subject .= $data->getUpdatedFilename();
        $mail->Body    = "<h1>" . $book->title . "</h1><h2>" . $book->getAuthorsName() . "</h2>" . $book->getComment();
        $mail->AltBody = "Sent by COPS";

        if ($dryRun) {
            if (!$mail->preSend()) {
                return 'Mailer Error: ' . $mail->ErrorInfo;
            }
            return false;
        }
        if (!$mail->Send()) {
            return 'Mailer Error: ' . $mail->ErrorInfo;
        }
        return false;
    }
}
