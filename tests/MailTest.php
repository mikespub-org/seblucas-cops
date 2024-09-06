<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Tests;

use SebLucas\Cops\Output\Mail;

require_once dirname(__DIR__) . '/config/test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Framework;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Input\Route;

class MailTest extends TestCase
{
    public function testCheckConfigurationOk(): void
    {
        $mailer = new Mail();
        $this->assertFalse($mailer->checkConfiguration());
    }

    public function testCheckConfigurationNull(): void
    {
        Config::set('mail_configuration', null);

        $mailer = new Mail();
        $this->assertStringStartsWith("NOK", $mailer->checkConfiguration());
    }

    public function testCheckConfigurationNotArray(): void
    {
        Config::set('mail_configuration', "Test");

        $mailer = new Mail();
        $this->assertStringStartsWith("NOK", $mailer->checkConfiguration());
    }

    public function testCheckConfigurationSmtpEmpty(): void
    {
        // reload test config
        require dirname(__DIR__) . '/config/test.php';
        $mailConfig = Config::get('mail_configuration');
        $mailConfig["smtp.host"] = "";
        Config::set('mail_configuration', $mailConfig);

        $mailer = new Mail();
        $this->assertStringStartsWith("NOK", $mailer->checkConfiguration());
    }

    public function testCheckConfigurationEmailEmpty(): void
    {
        // reload test config
        require dirname(__DIR__) . '/config/test.php';
        $mailConfig = Config::get('mail_configuration');
        $mailConfig["address.from"] = "";
        Config::set('mail_configuration', $mailConfig);

        $mailer = new Mail();
        $this->assertStringStartsWith("NOK", $mailer->checkConfiguration());
    }

    public function testCheckConfigurationEmailNotEmpty(): void
    {
        $email = "a";
        $mailConfig = Config::get('mail_configuration');
        $mailConfig["address.from"] = $email;
        Config::set('mail_configuration', $mailConfig);

        $mailer = new Mail();
        //$this->assertStringContainsString($email, $mailConfig["address.from"]);
        $this->assertFalse($mailer->checkConfiguration());

        // reload test config
        require dirname(__DIR__) . '/config/test.php';
    }

    public function testCheckConfigurationEmailNotValid(): void
    {
        $email = "a";
        $this->assertDoesNotMatchRegularExpression('/^.+\@\S+\.\S+$/', $email);
    }

    public function testCheckConfigurationEmailValid(): void
    {
        $email = "a@a.com";
        $this->assertMatchesRegularExpression('/^.+\@\S+\.\S+$/', $email);
    }

    public function testCheckRequest(): void
    {
        $mailer = new Mail();
        $this->assertFalse($mailer->checkRequest(12, "a@a.com"));
    }

    public function testCheckRequestNoData(): void
    {
        $mailer = new Mail();
        $this->assertStringStartsWith("No", $mailer->checkRequest(null, "a@a.com"));
    }

    public function testCheckRequestNoEmail(): void
    {
        $mailer = new Mail();
        $this->assertStringStartsWith("No", $mailer->checkRequest(12, ""));
    }

    public function testCheckRequestEmailNotValid(): void
    {
        $mailer = new Mail();
        $this->assertStringStartsWith("No", $mailer->checkRequest(12, "a@b"));
    }

    public function testSendMailNotFound(): void
    {
        $request = Request::build();
        $mailer = new Mail();
        $this->assertStringStartsWith("No", $mailer->sendMail(12, "a@a.com", $request));
    }

    public function testSendMailTooBig(): void
    {
        $old = Mail::$maxSize;
        Mail::$maxSize = 0;
        $request = Request::build();
        $mailer = new Mail();
        $this->assertStringStartsWith("No", $mailer->sendMail(20, "a@a.com", $request));
        Mail::$maxSize = $old;
    }

    public function testSendMailSomeday(): void
    {
        $request = Request::build();
        $mailer = new Mail();
        // use dryRun to run preSend() but not actually Send()
        $error = $mailer->sendMail(20, "a@a.com", $request, true);
        $this->assertFalse($error);
    }

    public function testMailHandler(): void
    {
        $_POST = ['data' => '20', 'email' => 'a@a.com'];
        // set request handler to 'phpunit' to run preSend() but not actually Send()
        $_GET = [Route::HANDLER_PARAM => 'phpunit'];
        $request = new Request();
        $handler = Framework::getHandler('mail');

        ob_start();
        $handler->handle($request);
        $headers = headers_list();
        $output = ob_get_clean();

        $expected = localize("mail.messagesent");
        $this->assertEquals($expected, $output);

        $_GET = [];
        $_POST = [];
    }
}
