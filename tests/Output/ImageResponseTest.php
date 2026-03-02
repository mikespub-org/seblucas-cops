<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\ImageResponse;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;

class ImageResponseTest extends TestCase
{
    public function testGetImageFromData(): void
    {
        // Test with string data
        $response = new ImageResponse();
        $data = 'fake image data';

        $result = $response->getImageFromData($data);
        $this->assertEquals($data, $result->getContent());

        // Test with callback data
        $response = new ImageResponse();
        $callback = function () {
            return 'callback data';
        };

        $result = $response->getImageFromData($callback);
        $this->assertEquals($callback, $result->getCallback());
        $this->assertNull($result->getContent());
    }

    public function testGetThumbFromData(): void
    {
        // Create a small valid image string
        $im = imagecreatetruecolor(10, 10);
        ob_start();
        imagejpeg($im);
        $data = ob_get_clean();

        // Test with string data
        $response = new ImageResponse();
        $response->width = 5;

        $result = $response->getThumbFromData($data, null);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ImageResponse::class, $result);
        $this->assertIsString($result->getContent());

        // Test with callback data
        $response = new ImageResponse();
        $response->width = 5;

        $callback = function () use ($data) {
            return $data;
        };
        $result = $response->getThumbFromData($callback, null);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ImageResponse::class, $result);
        $this->assertIsString($result->getContent());
        $this->assertNull($result->getCallback());
    }
}
