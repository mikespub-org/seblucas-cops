<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Handlers;

use SebLucas\Cops\Handlers\TestHandler;
use SebLucas\Cops\Output\Mail;
use SebLucas\Cops\Output\Response;

/**
 * Send books by email
 * URL format: index.php/mail (POST data and email)
 */
class MailHandler extends BaseHandler
{
    public const HANDLER = "mail";
    public const PREFIX = "/mail";

    public static function getRoutes()
    {
        return [
            "mail" => ["/mail", [], ["POST"]],
        ];
    }

    public function handle($request)
    {
        // set request handler to 'TestHandler' class to run preSend() but not actually Send()
        $dryRun = ($request->getHandler() === TestHandler::class) ? true : false;

        $mailer = new Mail();

        if ($error = $mailer->checkConfiguration()) {
            return Response::sendError($request, $error);
        }

        $idData = (int) $request->post("data");
        $emailDest = $request->post("email");
        if ($error = $mailer->checkRequest($idData, $emailDest)) {
            return Response::sendError($request, $error);
        }

        if ($error = $mailer->sendMail($idData, $emailDest, $request, $dryRun)) {
            $response = new Response('text/plain');
            return $response->setContent(localize("mail.messagenotsent") . $error);
        }

        $response = new Response('text/plain');
        return $response->setContent(localize("mail.messagesent"));
    }
}
