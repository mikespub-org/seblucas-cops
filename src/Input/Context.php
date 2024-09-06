<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Input;

/**
 * Summary of Context
 */
class Context
{
    /**
     * Summary of request
     * @var Request
     */
    protected Request $request;

    /**
     * Summary of __construct
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Summary of getRequest
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
