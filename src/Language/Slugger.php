<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (https://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Language;

use Symfony\Component\String\Slugger\AsciiSlugger;

class Slugger extends AsciiSlugger
{
    /**
     * Explicit constructor for private property promotion in parent class (PHP0441)
     */
    public function __construct(?string $locale = null)
    {
        parent::__construct($locale);
    }

    // use slug()
}
