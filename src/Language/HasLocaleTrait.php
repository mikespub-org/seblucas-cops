<?php

namespace SebLucas\Cops\Language;

/**
 * Trait for classes that use localize()
 */
trait HasLocaleTrait
{
    /** @var string */
    protected $locale;

    /**
     * Summary of setLocale
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Summary of getHandler
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Summary of localize
     * @param string $phrase
     * @param int $count
     * @return string
     */
    public function localize($phrase, $count = -1)
    {
        return Translation::getInstance($this->locale)->localize($phrase, $count);
    }
}
