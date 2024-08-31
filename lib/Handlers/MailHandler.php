<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Output\Mail;

/**
 * Send books by email
 * URL format: sendtomail.php (POST data and email)
 */
class MailHandler extends BaseHandler
{
    public const HANDLER = "mail";

    public static function getRoutes()
    {
        return [
            "/mail" => [static::PARAM => static::HANDLER],
        ];
    }

    public function handle($request)
    {
        if ($error = Mail::checkConfiguration()) {
            echo $error;
            return;
        }

        $idData = (int) $request->post("data");
        $emailDest = $request->post("email");
        if ($error = Mail::checkRequest($idData, $emailDest)) {
            echo $error;
            return;
        }

        // set request handler to 'phpunit' to run preSend() but not actually Send()
        $dryRun = ($request->getHandler() === 'phpunit') ? true : false;
        if ($error = Mail::sendMail($idData, $emailDest, $request, $dryRun)) {
            echo localize("mail.messagenotsent");
            echo $error;
            return;
        }

        echo localize("mail.messagesent");
    }
}
