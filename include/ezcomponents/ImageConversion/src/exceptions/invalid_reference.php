<?php
/**
 * File containing the ezcImageInvalidReferenceException.
 * 
 * @package ImageConversion
 * @version 1.3.5
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Thrown if no valid image reference could be found for an action (conversion,
 * filter, load, save,...).
 *
 * @package ImageConversion
 * @version 1.3.5
 */
class ezcImageInvalidReferenceException extends ezcImageException
{
    /**
     * Creates a new ezcImageInvalidReferenceException.
     * 
     * @param string $reason The reason.
     * @return void
     */
    function __construct( $reason = null )
    {
        $reasonPart = "";
        if ( $reason )
        {
            $reasonPart = " $reason";
        }
        parent::__construct( "No valid reference found for action.{$reasonPart}" );
    }
}

?>
