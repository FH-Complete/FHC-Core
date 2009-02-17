<?php
/**
 * File containing the ezcImageFileNameInvalidException.
 * 
 * @package ImageConversion
 * @version 1.3.5
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Thrown if a given file name contains illegal characters (', ", $).
 *
 * @package ImageConversion
 * @version 1.3.5
 */
class ezcImageFileNameInvalidException extends ezcImageException
{
    /**
     * Creates a new ezcImageFileNameInvalidException.
     * 
     * @param string $file The invalid file name.
     * @return void
     */
    function __construct( $file )
    {
        parent::__construct( "The file name '{$file}' contains an illegal character (', \", $)." );
    }
}

?>
