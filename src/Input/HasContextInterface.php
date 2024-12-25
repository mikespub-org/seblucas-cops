<?php

namespace SebLucas\Cops\Input;

/**
 * Interface for classes that have a context
 * @see HasContextTrait
 */
interface HasContextInterface
{
    /**
     * Summary of setContext
     * @param RequestContext $context
     * @return void
     */
    public function setContext($context);

    /**
     * Summary of getContext
     * @return RequestContext
     */
    public function getContext();
}
