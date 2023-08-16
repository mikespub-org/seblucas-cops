<?php
/**
 * @todo These are the methods that haven't been integrated with EPub here...
 */

use PHPUnit\Framework\TestCase;
use SebLucas\EPubMeta\Other;

class OtherTest extends TestCase
{
    protected Other $epub;

    /**
     * @throws Exception
     * @return void
     */
    public function testTitlePage()
    {
        // read current cover
        $this->epub->addCoverImageTitlePage();
        $this->epub->save();
        $spine = $this->epub->getSpine();
        $titlePage = $spine->first();

        $this->assertEquals('epubli-epub-titlepage.xhtml', $titlePage->getHref());
        $this->assertEquals('epubli-epub-titlepage', $titlePage->getId());
        $this->assertEquals('application/xhtml+xml', (string)$titlePage->getMediaType());

        // We expect an empty string since there is only an image but no text on that page.
        $this->assertEmpty(trim($titlePage->getContents()));
    }
}
