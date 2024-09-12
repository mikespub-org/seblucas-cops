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
use SebLucas\Cops\Output\Response;

/**
 * Send books by email
 * URL format: index.php/mail (POST data and email)
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
        // set request handler to 'phpunit' to run preSend() but not actually Send()
        $dryRun = ($request->getHandler() === 'phpunit') ? true : false;

        $mailer = new Mail();

        if ($error = $mailer->checkConfiguration()) {
            // this will call exit()
            Response::sendError($request, $error);
        }

        $idData = (int) $request->post("data");
        $emailDest = $request->post("email");
        if ($error = $mailer->checkRequest($idData, $emailDest)) {
            // this will call exit()
            Response::sendError($request, $error);
        }

        if ($error = $mailer->sendMail($idData, $emailDest, $request, $dryRun)) {
            $response = new Response('text/plain');
            $response->sendData(localize("mail.messagenotsent") . $error);
            return;
        }

        $response = new Response('text/plain');
        $response->sendData(localize("mail.messagesent"));
    }
}
