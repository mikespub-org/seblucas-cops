<?php
/**
 * COPS (Calibre OPDS PHP Server) test case
 *
 * This TestCase simulates the old WebDriverTestCase used by the SauceTest.
 * It simulates methods from Selenium2TestCase (phpunit/phpunit-selenium) and
 * WebDriverTestCase (sauce/sausage) - but only those used in the original test.
 *
 * See https://github.com/php-webdriver/php-webdriver/blob/main/example.php
 * for better ways to use WebDriver (php-webdriver/webdriver) natively instead.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Exception;

class WebDriverTestCase extends TestCase
{
    /** @var RemoteWebDriver */
    public static $driver;
    /** @var string|null */
    public static $userAgent = null;

    public static function setUpBeforeClass(): void
    {
        $host = 'http://localhost:4444';
        // use Chrome by default, or override above with 'Kindle/2.0'
        $capabilities = DesiredCapabilities::chrome();
        if (static::$userAgent) {
            $options = new ChromeOptions();
            $options->addArguments(['--user-agent="' . static::$userAgent . '"']);
            $capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $options);
        }
        static::$driver = RemoteWebDriver::create($host, $capabilities);
    }

    public static function tearDownAfterClass(): void
    {
        static::$driver->quit();
    }

    /**
     * Summary of setWindowSize
     * @param int $width
     * @param int $height
     * @return void
     */
    protected static function setWindowSize($width, $height)
    {
        if ($width > 0 && $height > 0) {
            static::$driver->manage()->window()->setSize(new WebDriverDimension($width, $height));
            return;
        }
        if ($width < 0 && $height < 0) {
            static::$driver->manage()->window()->maximize();
            return;
        }
    }

    /**
     * Summary of setTemplateName
     * @param string $template
     * @return void
     */
    protected static function setTemplateName($template)
    {
        // set default template in cookie
        //$domain = parse_url(self::$serverUrl, PHP_URL_HOST);
        //$path = parse_url(self::$serverUrl, PHP_URL_PATH);
        //self::$driver->manage()->addCookie(['name' => 'template', 'value' => $template, 'domain' => $domain]);
        static::$driver->manage()->addCookie(['name' => 'template', 'value' => $template]);
    }

    /** see https://github.com/giorgiosironi/phpunit-selenium/blob/master/PHPUnit/Extensions/Selenium2TestCase/Element/Accessor.php */

    /**
     * Summary of byClassname
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byClassname($value)
    {
        return static::$driver->findElement(
            WebDriverBy::className($value)
        );
    }

    /**
     * Summary of byCssSelector
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byCssSelector($value)
    {
        return static::$driver->findElement(
            WebDriverBy::cssSelector($value)
        );
    }

    /**
     * Summary of byId
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byId($value)
    {
        return static::$driver->findElement(
            WebDriverBy::id($value)
        );
    }

    /**
     * Summary of byLinkText
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byLinkText($value)
    {
        return static::$driver->findElement(
            WebDriverBy::linkText($value)
        );
    }

    /**
     * Summary of byPartialLinkText
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byPartialLinkText($value)
    {
        return static::$driver->findElement(
            WebDriverBy::partialLinkText($value)
        );
    }

    /**
     * Summary of byName
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byName($value)
    {
        return static::$driver->findElement(
            WebDriverBy::name($value)
        );
    }

    /**
     * Summary of byTag
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byTag($value)
    {
        return static::$driver->findElement(
            WebDriverBy::tagName($value)
        );
    }

    /**
     * Summary of byXPath
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byXPath($value)
    {
        return static::$driver->findElement(
            WebDriverBy::xpath($value)
        );
    }

    /**
     * Summary of elements
     * @param WebDriverBy $criteria
     * @return array<RemoteWebElement>
     */
    protected function elements($criteria)
    {
        $elements = static::$driver->findElements($criteria);
        return $elements;
    }

    /**
     * Summary of using
     * @param string $strategy
     * @return object
     */
    protected function using($strategy)
    {
        $dummy = new class ($strategy) {
            private string $strategy;

            public function __construct(string $strategy)
            {
                $this->strategy = $strategy;
            }

            /**
             * Summary of value
             * @param string $value
             * @throws \Exception
             * @return WebDriverBy
             */
            public function value($value)
            {
                switch ($this->strategy) {
                    case 'class name':
                        return WebDriverBy::className($value);
                    case 'css selector':
                        return WebDriverBy::cssSelector($value);
                    case 'id':
                        return WebDriverBy::id($value);
                    case 'link text':
                        return WebDriverBy::linkText($value);
                    case 'partial link text':
                        return WebDriverBy::partialLinkText($value);
                    case 'name':
                        return WebDriverBy::name($value);
                    case 'tag name':
                        return WebDriverBy::tagName($value);
                    case 'xpath':
                        return WebDriverBy::xpath($value);
                    default:
                        throw new Exception('Unknown strategy ' . $this->strategy);
                }
            }
        };

        return $dummy;
    }

    /** see https://github.com/giorgiosironi/phpunit-selenium/blob/master/PHPUnit/Extensions/Selenium2TestCase.php */

    /**
     * Summary of getBrowser
     * @return string
     */
    protected function getBrowser()
    {
        $cap = static::$driver->getCapabilities();
        return $cap->getBrowserName();
    }

    /** see https://github.com/giorgiosironi/phpunit-selenium/blob/master/PHPUnit/Extensions/Selenium2TestCase/WaitUntil.php */

    /**
     * Summary of waitUntil
     * @param callable $callback
     * @param int $timeout in seconds
     * @param int $sleepInterval in milliseconds
     * @throws \Exception
     * @return mixed
     */
    protected function waitUntil($callback, $timeout = 20, $sleepInterval = 100)
    {
        $sleepInterval *= 1000;
        $endTime = microtime(true) + $timeout;
        $lastException = null;

        while (true) {
            try {
                $result = call_user_func($callback);
                if (!is_null($result)) {
                    return $result;
                }
            } catch (Exception $e) {
                $lastException = $e;
            }

            if (microtime(true) > $endTime) {
                $message = "Timed out after {$timeout} second" . ($timeout != 1 ? 's' : '');
                throw new Exception($message, 21, $lastException);
            }

            usleep($sleepInterval);
        }
    }

    /** see https://github.com/jlipps/sausage/blob/master/src/Sauce/Sausage/TestCase.php */

    /**
     * Summary of byCss
     * @param string $value
     * @return RemoteWebElement
     */
    protected function byCss($value)
    {
        return $this->byCssSelector($value);
    }

    /**
     * Summary of spinAssert
     * @param string $msg
     * @param callable $test
     * @param array<mixed> $args
     * @param int $timeout
     * @return array<mixed>
     */
    protected function spinAssert($msg, $test, $args=[], $timeout=20)
    {
        // wait until the target page is loaded
        $result = static::$driver->wait($timeout)->until(
            function ($driver) use ($test, $args) {
                return call_user_func_array($test, $args);
            },
            $msg
        );
        //WebDriverExpectedCondition::titleContains('Revision history')
        return [$result, $msg];
    }

    /**
     * Summary of spinWait
     * @param string $msg
     * @param callable $test
     * @param array<mixed> $args
     * @param int $timeout
     * @return void
     */
    protected function spinWait($msg, $test, $args=[], $timeout=20)
    {
        [$result, $msg] = $this->spinAssert($msg, $test, $args, $timeout);
        $this->assertTrue($result, $msg);
    }

    /**
     * Summary of url
     * @param string $url
     * @return void
     */
    protected function url($url)
    {
        static::$driver->get($url);
    }
}
