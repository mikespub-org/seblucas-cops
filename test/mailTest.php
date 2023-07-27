<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require_once(dirname(__FILE__) . "/config_test.php");
use PHPUnit\Framework\TestCase;
use SebLucas\Cops\Output\Mail;

class MailTest extends TestCase
{
    public function testCheckConfigurationOk()
    {
        $this->assertFalse(Mail::checkConfiguration());
    }

    public function testCheckConfigurationNull()
    {
        global $config;
        $config['cops_mail_configuration'] = null;

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationNotArray()
    {
        global $config;
        $config['cops_mail_configuration'] = "Test";

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationSmtpEmpty()
    {
        global $config;
        require(dirname(__FILE__) . "/config_test.php");
        $config['cops_mail_configuration']["smtp.host"] = "";

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationEmailEmpty()
    {
        global $config;
        require(dirname(__FILE__) . "/config_test.php");
        $config['cops_mail_configuration']["address.from"] = "";

        $this->assertStringStartsWith("NOK", Mail::checkConfiguration());
    }

    public function testCheckConfigurationEmailNotEmpty()
    {
        global $config;
        $email = "a";
        $config['cops_mail_configuration']["address.from"] = $email;

        $this->assertStringContainsString($email, $config['cops_mail_configuration']["address.from"]);
    }

    public function testCheckConfigurationEmailNotValid()
    {
        global $config;
        $email = "a";
        $this->assertDoesNotMatchRegularExpression('/^.+\@\S+\.\S+$/', $email);
    }

    public function testCheckConfigurationEmailValid()
    {
        global $config;
        $email = "a@a.com";
        $this->assertMatchesRegularExpression('/^.+\@\S+\.\S+$/', $email);
    }

    public function testCheckRequest()
    {
        $this->assertFalse(Mail::checkRequest(12, "a@a.com"));
    }

    public function testCheckRequestNoData()
    {
        $this->assertStringStartsWith("No", Mail::checkRequest(null, "a@a.com"));
    }

    public function testCheckRequestNoEmail()
    {
        $this->assertStringStartsWith("No", Mail::checkRequest(12, null));
    }

    public function testCheckRequestEmailNotValid()
    {
        $this->assertStringStartsWith("No", Mail::checkRequest(12, "a@b"));
    }

    public function testSendMailNotFound()
    {
        $this->assertStringStartsWith("No", Mail::sendMail(12, "a@a.com"));
    }

    public function testSendMailTooBig()
    {
        $old = Mail::$maxSize;
        Mail::$maxSize = 0;
        $this->assertStringStartsWith("No", Mail::sendMail(20, "a@a.com"));
        Mail::$maxSize = $old;
    }

    public function testSendMailSomeday()
    {
        $this->assertStringStartsWith("Mailer Error:", Mail::sendMail(20, "a@a.com"));
    }
}
