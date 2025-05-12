<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Model;

/**
 * From https://drafts.opds.io/opds-2.0#23-images
 * While previous versions of OPDS relied on link relations to identify visual representations,
 * OPDS 2.0 introduces a dedicated collection role for that purpose.
 * An images collection is meant to support responsive images across all types of devices. It must be a compact collection.
 * Link Objects in images may include any number of image format, resolution or aspect ratio.
 * At least one image resource must use one of the following formats: image/jpeg, image/avif, image/png or image/gif.
 */
class LinkImage extends LinkResource
{
    public const OPDS_THUMBNAIL_TYPE = "http://opds-spec.org/image/thumbnail";
    public const OPDS_IMAGE_TYPE = "http://opds-spec.org/image";

    /** @var ?int */
    public $width = null;
    /** @var ?int */
    public $height = null;

    /**
     * Summary of getImageSize
     * @return void
     */
    public function getImageSize()
    {
        $size = getimagesize($this->filepath);
        if (empty($size)) {
            return;
        }
        $this->width = (int) $size[0];
        $this->height = (int) $size[1];
    }

    /**
     * Summary of getWidth
     * @return int|null
     */
    public function getWidth()
    {
        if (!isset($this->filepath)) {
            return $this->width;
        }
        if (!isset($this->width)) {
            $this->getImageSize();
        }
        return $this->width;
    }

    /**
     * Summary of setWidth
     * @param ?int $width
     * @return self
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Summary of getHeight
     * @return int|null
     */
    public function getHeight()
    {
        if (!isset($this->filepath)) {
            return $this->height;
        }
        if (!isset($this->height)) {
            $this->getImageSize();
        }
        return $this->height;
    }

    /**
     * Summary of setHeight
     * @param ?int $height
     * @return self
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }
}
