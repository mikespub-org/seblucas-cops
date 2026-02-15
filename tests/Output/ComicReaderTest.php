<?php

/**
 * COPS (Calibre OPDS PHP Server) test file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Tests\Output;

use SebLucas\Cops\Output\ComicReader;

require_once dirname(__DIR__, 2) . '/config/test.php';
use PHPUnit\Framework\TestCase;

class ComicReaderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // ...
    }

    public function testGetMetadata(): void
    {
        $filePath = dirname(__DIR__) . '/cba-cbam.cbz';
        $reader = new ComicReader();
        $metadata = $reader->getMetadata($filePath);
        $this->assertNotNull($metadata);
    }
}
