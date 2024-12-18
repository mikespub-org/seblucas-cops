<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

/**
 * Summary of Context
 */
class Context
{
    protected Request $request;
    /** @var class-string */
    protected $handler;

    /**
     * Summary of __construct
     * @param Request $request
     * @param ?class-string $handler
     */
    public function __construct(Request $request, $handler = null)
    {
        $this->request = $request;
        $this->handler = $handler ?? $request->getHandler();
    }

    /**
     * Summary of getRequest
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Summary of getHandler
     * @return class-string
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
