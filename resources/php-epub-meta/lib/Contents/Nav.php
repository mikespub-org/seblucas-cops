<?php

namespace SebLucas\EPubMeta\Contents;

/**
 * EPUB NAV structure for EPUB 3
 *
 * @author Simon Schrape <simon@epubli.com>
 */
class Nav
{
    /** @var string from main document */
    protected $docTitle;
    /** @var string from main document */
    protected $docAuthor;
    /** @var NavPointList */
    protected $navMap;

    /**
     * Summary of __construct
     * @param string $title
     * @param string $author
     */
    public function __construct($title, $author)
    {
        $this->docTitle = $title;
        $this->docAuthor = $author;
        $this->navMap = new NavPointList();
    }

    /**
     * @return string
     */
    public function getDocTitle()
    {
        return $this->docTitle;
    }

    /**
     * @return string
     */
    public function getDocAuthor()
    {
        return $this->docAuthor;
    }

    /**
     * @return NavPointList
     */
    public function getNavMap()
    {
        return $this->navMap;
    }

    /**
     * @param string $file
     * @return array|NavPoint[]
     */
    public function findNavPointsForFile($file)
    {
        return $this->getNavMap()->findNavPointsForFile($file);
    }
}
