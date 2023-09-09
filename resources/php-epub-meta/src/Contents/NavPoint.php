<?php

namespace SebLucas\EPubMeta\Contents;

/**
 * An EPUB TOC navigation point.
 *
 * @author Simon Schrape <simon@epubli.com>
 */
class NavPoint
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $class;
    /** @var int */
    protected $playOrder;
    /** @var string */
    protected $navLabel;
    /** @var string */
    protected $contentSourceFile;
    /** @var string */
    protected $contentSourceFragment;
    /** @var NavPointList */
    protected $children;

    /**
     * @param string $id
     * @param string $class
     * @param int $playOrder
     * @param string $label
     * @param string $contentSource
     */
    public function __construct($id, $class, $playOrder, $label, $contentSource)
    {
        $this->id = $id;
        $this->class = $class;
        $this->playOrder = $playOrder;
        $this->navLabel = $label;
        $contentSourceParts = explode('#', $contentSource, 2);
        $this->contentSourceFile = $contentSourceParts[0];
        $this->contentSourceFragment = $contentSourceParts[1] ?? null;
        $this->children = new NavPointList();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return int
     */
    public function getPlayOrder()
    {
        return $this->playOrder;
    }

    /**
     * @return string
     */
    public function getNavLabel()
    {
        return $this->navLabel;
    }

    /**
     * @return string
     */
    public function getContentSource()
    {
        return $this->contentSourceFile.($this->contentSourceFragment ? '#'.$this->contentSourceFragment : '');
    }

    /**
     * @return string
     */
    public function getContentSourceFile()
    {
        return $this->contentSourceFile;
    }

    /**
     * @return string
     */
    public function getContentSourceFragment()
    {
        return $this->contentSourceFragment;
    }

    /**
     * @return NavPointList
     */
    public function getChildren()
    {
        return $this->children;
    }
}
