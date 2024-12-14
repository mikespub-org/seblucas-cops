<?php

/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL v2 or later (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

namespace SebLucas\Cops\Model;

/**
 * From https://drafts.opds.io/opds-2.0#53-acquisition-links
 * In OPDS 2.0, the concept of an Acquision Link is not as central as in OPDS 1.x
 * since publications can also be accessed through a manifest.
 * That said, for publications that are strictly accessible through a download
 * or require specific interactions, the concept remains.
 */
class LinkAcquisition extends LinkResource
{
    public const OPDS_ACQUISITION_TYPE = "http://opds-spec.org/acquisition";
}
