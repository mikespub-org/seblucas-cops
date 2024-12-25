<?php

namespace SebLucas\Cops\Input;

/**
 * Trait for classes that have a context
 */
trait HasContextTrait
{
    protected RequestContext $context;

    /**
     * Summary of setContext
     * @param RequestContext $context
     * @return void
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Summary of getContext
     * @return RequestContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
