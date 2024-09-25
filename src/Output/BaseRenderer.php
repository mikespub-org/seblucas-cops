<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Request;

/**
 * Base Renderer
 */
abstract class BaseRenderer
{
    /** @var Request */
    protected $request;
    /** @var Response */
    protected $response;

    /**
     * Summary of __construct
     * @param ?Request $request
     * @param ?Response $response
     */
    public function __construct($request = null, $response = null)
    {
        if (!empty($request)) {
            $this->setRequest($request);
        }
        if (!empty($response)) {
            $this->setResponse($response);
        }
    }

    /**
     * Summary of setRequest
     * @param Request $request
     * @return void
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Summary of setResponse
     * @param Response $response
     * @return void
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}
