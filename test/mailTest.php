<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once __DIR__ . '/config_test.php';
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Output\Mail;

class MailTest extends TestCase
{
    public function testCheckConfigurationOk(): void
    {
        $this->assertFalse(Mail::checkConfiguration());
    }

    public function testCheckConfigurationNull(): void
    {
        Config::set('mail_configuration', null);

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationNotArray(): void
    {
        Config::set('mail_configuration', "Test");

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationSmtpEmpty(): void
    {
        // reload test config
        require __DIR__ . '/config_test.php';
        $mailConfig = Config::get('mail_configuration');
        $mailConfig["smtp.host"] = "";
        Config::set('mail_configuration', $mailConfig);

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationEmailEmpty(): void
    {
        // reload test config
        require __DIR__ . '/config_test.php';
        $mailConfig = Config::get('mail_configuration');
        $mailConfig["address.from"] = "";
        Config::set('mail_configuration', $mailConfig);

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationEmailNotEmpty(): void
    {
        $email = "a";
        $mailConfig = Config::get('mail_configuration');
        $mailConfig["address.from"] = $email;
        Config::set('mail_configuration', $mailConfig);

        //$this->assertStringContainsString($email, $mailConfig["address.from"]);
        $this->assertFalse(Mail::checkConfiguration());

        // reload test config
        require __DIR__ . '/config_test.php';
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
        $this->assertFalse(Mail::checkRequest(12, "a@a.com"));
    }

    public function testCheckRequestNoData(): void
    {
        $this->assertStringStartsWith("No", Mail::checkRequest(null, "a@a.com"));
    }

    public function testCheckRequestNoEmail(): void
    {
        $this->assertStringStartsWith("No", Mail::checkRequest(12, ""));
    }

    public function testCheckRequestEmailNotValid(): void
    {
        $this->assertStringStartsWith("No", Mail::checkRequest(12, "a@b"));
    }

    public function testSendMailNotFound(): void
    {
        $this->assertStringStartsWith("No", Mail::sendMail(12, "a@a.com"));
    }

    public function testSendMailTooBig(): void
    {
        $old = Mail::$maxSize;
        Mail::$maxSize = 0;
        $this->assertStringStartsWith("No", Mail::sendMail(20, "a@a.com"));
        Mail::$maxSize = $old;
    }

    public function testSendMailSomeday(): void
    {
        // use dryRun to run preSend() but not actually Send()
        $error = Mail::sendMail(20, "a@a.com", true);
        $this->assertFalse($error);
    }
}
