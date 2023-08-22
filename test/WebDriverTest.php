<?php
/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * This test still uses a simulated WebDriverTestCase from (sauce/sausage)
 * to minimize the changes and see if it works...
 *
 * See https://github.com/php-webdriver/php-webdriver/blob/main/example.php
 * for better ways to use WebDriver (php-webdriver/webdriver) natively instead.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests;

require_once __DIR__ . '/config_test.php';
//use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Exception;

class WebDriverTest extends WebDriverTestCase
{
    public static string $serverUrl = 'http://host.docker.internal/cops/';
    /** @var RemoteWebDriver */
    public static $driver;
    /** @var string|null */
    public static $userAgent = null;  // Chrome by default, override here with 'Kindle/2.0'
    /** @var string|null */
    public static $template = 'default';

    /** @var array<mixed> */
    public static $browsers = [
        // run FF15 on Windows 8 on Sauce
        [
            'browserName' => 'firefox',
            'desiredCapabilities' => [
                'version' => '28',
                'platform' => 'Windows 8.1',
            ],
        ],
        // run IE11 on Windows 8 on Sauce
        [
            'browserName' => 'internet explorer',
            'desiredCapabilities' => [
                'version' => '11',
                'platform' => 'Windows 8.1',
            ],
        ],
        // run Safari 7 on Maverick on Sauce
        [
            'browserName' => 'safari',
            'desiredCapabilities' => [
                'version' => '7',
                'platform' => 'OS X 10.9',
            ],
        ],
        // run Mobile Safari on iOS
        [
            'browserName' => 'iphone',
            'desiredCapabilities' => [
                'app' => 'safari',
                'device' => 'iPhone 6',
                'version' => '9.2',
                'platform' => 'OS X 10.10',
            ],
        ],
        // run Mobile Browser on Android
        [
            'browserName' => 'Android',
            'desiredCapabilities' => [
                'version' => '5.1',
                'platform' => 'Linux',
            ],
        ],
        // run Chrome on Linux on Sauce
        [
            'browserName' => 'chrome',
            'desiredCapabilities' => [
                'version' => '33',
                'platform' => 'Linux',
          ],
        ],


        // run Chrome locally
        //array(
            //'browserName' => 'chrome',
            //'local' => true,
            //'sessionStrategy' => 'shared'
        //)
    ];


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$driver->manage()->window()->maximize();
    }

    public function setUp(): void
    {
        // trying to set cookie before navigating first triggers an error: invalid cookie domain
        $this->url(self::$serverUrl);
        // set default template in cookie
        $this->setTemplateName(self::$template);
    }

    /**
     * Summary of testScreenshots
     * @dataProvider providerCombinations
     * @param string $template
     * @param int $width
     * @param int $height
     * @return void
     */
    public function testScreenshots($name, $url, $template, $width, $height)
    {
        $this->setTemplateName($template);
        $this->setWindowSize($width, $height);
        $this->url($url);
        self::$driver->takeScreenshot("{$name}.{$template}-{$width}x{$height}.png");
    }

    protected function providerPages()
    {
        return [
            ['index', self::$serverUrl . '?page=index'],
            ['recent', self::$serverUrl . '?page=10'],
            ['allbooks', self::$serverUrl . '?page=4'],
            ['authors', self::$serverUrl . '?page=1'],
            ['customize', self::$serverUrl . '?page=19'],
            ['about', self::$serverUrl . '?page=16'],
        ];
    }

    /**
     * Summary of providerTemplateSizes
     * @return array<mixed>
     */
    protected function providerTemplateSizes()
    {
        return [
            ['default', 0, 0],
            ['bootstrap', 0, 0],
            ['bootstrap2', 0, 0],
            ['default', 320, 720],
            ['bootstrap', 320, 720],
            ['bootstrap2', 320, 720],
            ['default', 1920, 1080],
            ['bootstrap', 1920, 1080],
            ['bootstrap2', 1920, 1080],
        ];
    }

    /**
     * Summary of providerCombinations
     * @return array<mixed>
     */
    protected function providerCombinations()
    {
        $combinations = [];
        foreach ($this->providerTemplateSizes() as $options) {
            foreach ($this->providerPages() as $pages) {
                array_push($combinations, array_merge($pages, $options));
            }
        }
        return $combinations;
    }

    public function testHomepage(): void
    {
        $this->url(self::$serverUrl . 'index.php?page=index');

        $driver = $this;
        $title_test = function ($value) use ($driver) {
            $text = $driver->byXPath('//h1')->getText();
            return $text == $value;
        };

        //$this->spinAssert("Home Title", $title_test, [ "COPS" ]);
        $this->spinWait("Home Title", $title_test, [ "COPS" ]);
    }

    public function oldSetUp(): void
    {
        if (isset($_SERVER["TRAVIS_JOB_NUMBER"])) {
            $caps = self::$driver->getCapabilities();
            $caps['build'] = getenv("TRAVIS_JOB_NUMBER");
            $caps['tunnel-identifier'] = getenv("TRAVIS_JOB_NUMBER");
            $caps['idle-timeout'] = "180";
            //$this->setDesiredCapabilities($caps);
        }
        parent::setUp();
    }

    public function oldSetUpPage(): void
    {
        if (isset($_SERVER["TRAVIS_JOB_NUMBER"])) {
            $this->url('http://127.0.0.1:8080/index.php');
        } else {
            $this->url('http://cops-demo.slucas.fr/index.php');
        }

        $driver = $this;
        $title_test = function ($value) use ($driver) {
            $text = $driver->byXPath('//h1')->getText();
            return $text == $value;
        };

        $this->spinAssert("Home Title", $title_test, [ "COPS DEMO" ]);
    }

    /**
     * Summary of string_to_ascii
     * @param string $string
     * @return string
     */
    public function string_to_ascii($string)
    {
        $ascii = null;

        for ($i = 0; $i < strlen($string); $i++) {
            $ascii += ord($string[$i]);
        }

        return mb_detect_encoding($string) . "X" . $ascii;
    }

    // public function testTitle(): void
    // {
    // $driver = $this;
    // $title_test = function($value) use ($driver) {
    // $text = $driver->byXPath('//h1')->text ();
    // return $text == $value;
    // };

    // $author = $this->byXPath ('//h2[contains(text(), "Authors")]');
    // $author->click ();

    // $this->spinAssert("Author Title", $title_test, [ "AUTHORS" ]);
    // }

    // public function testCog(): void
    // {
    // $cog = $this->byId ("searchImage");

    // $search = $this->byName ("query");
    // $this->assertFalse ($search->displayed ());

    // $cog->click ();

    // $search = $this->byName ("query");
    // $this->assertTrue ($search->displayed ());
    // }

    public function testFilter(): void
    {
        $driver = $this;
        $title_test = function ($value) use ($driver) {
            $text = $driver->byXPath('//h1')->getText();
            return $text == $value;
        };

        $element_present = function ($using, $id) use ($driver) {
            $elements = $driver->elements($driver->using($using)->value($id));
            return count($elements) == 1;
        };

        // Click on the wrench to enable tag filtering
        $this->spinWait("", $element_present, [ "class name", 'fa-wrench']);
        $this->byClassName("fa-wrench")->click();

        $this->spinWait("", $element_present, [ "id", "html_tag_filter"]);
        $this->byId("html_tag_filter")->click();

        // Go back to home screen
        $this->byClassName("fa-home")->click();

        $this->spinAssert("Home Title", $title_test, [ "COPS" ]);

        // Go on the recent page
        $author = $this->byXPath('//h2[contains(text(), "Recent")]');
        $author->click();

        $this->spinAssert("Recent book title", $title_test, [ "RECENT ADDITIONS" ]);

        // Click on the cog to show tag filters - not available with server-side rendering
        try {
            $cog = $this->byId("searchImage");
        } catch (Exception $e) {
            self::$driver->takeScreenshot('oops.searchImage.png');
            throw $e;
        }
        $cog->click();
        sleep(1);
        // Filter on War & Military
        $filter = $this->byXPath('//li[contains(text(), "War")]');
        $filter->click();
        sleep(1);
        // Only one book
        $filtered = $this->elements($this->using('css selector')->value('*[class="books"]'));
        $this->assertEquals(1, count($filtered));
        $filter->click();
        sleep(1);
        // 13 book
        $filtered = $this->elements($this->using('css selector')->value('*[class="books"]'));
        $this->assertEquals(14, count($filtered));
    }

    /**
     * Summary of normalSearch
     * @param string $src
     * @param string $out
     * @return void
     */
    public function normalSearch($src, $out)
    {
        $driver = $this;
        $title_test = function ($value) use ($driver) {
            $text = $driver->byXPath('//h1')->getText();
            return $text == $value;
        };

        // Click on the cog to show the search - not needed with server-side rendering
        $cog = $this->byId("searchImage");
        $cog->click();
        //sleep (1);

        // Focus the input and type
        $this->waitUntil(function () {
            try {
                $this->byName("query");
                return true;
            } catch (Exception) {
                return null;
            }
        });
        self::$driver->wait(2, 200)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::name("query"))
        );
        $queryInput = $this->byName("query");
        // this returned an exception: element not interactable - waiting for visibility now
        $queryInput->click();
        $queryInput->sendKeys($src);
        $queryInput->submit();

        $this->spinWait("Home Title", $title_test, [ "SEARCH RESULT FOR *" . $out . "*" ]);
        //self::$driver->takeScreenshot('search-' . $out . '.png');
    }

    public function testSearchWithoutAccentuatedCharacters(): void
    {
        $this->normalSearch("ali", "ALI");
    }

    public function testSearchWithAccentuatedCharacters(): void
    {
        if ($this->getBrowser() == "Android") {
            $this->markTestIncomplete();
        }
        $this->normalSearch("é", "É");
    }
}
