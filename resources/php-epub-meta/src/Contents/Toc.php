<?php

namespace SebLucas\EPubMeta\Contents;

/**
 * EPUB TOC structure for EPUB 2
 *
 * @author Simon Schrape <simon@epubli.com>
 */
class Toc
{
    /** @var string */
    protected $docTitle;
    /** @var string */
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
