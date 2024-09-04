<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Output;

/**
 * Base Renderer
 */
abstract class BaseRenderer
{
    /** @var ?Response */
    protected $response;

    /**
     * Summary of __construct
     * @param ?Response $response
     */
    public function __construct($response = null)
    {
        $this->response = $response;
    }
}
