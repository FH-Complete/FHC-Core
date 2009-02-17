<?php
/**
 * File containing the ezcImageMimeTypeUnsupportedException.
 * 
 * @package ImageConversion
 * @version 1.3.5
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Thrown if a requested MIME type is not supported for input, output or input/output.
 *
 * @package ImageConversion
 * @version 1.3.5
 */
class ezcImageMimeTypeUnsupportedException extends ezcImageException
{
    /**
     * Creates a new ezcImageMimeTypeUnsupportedException.
     * 
     * @param string $mimeType  Affected mime type.
     * @param string $direction "input" or "output".
     * @return void
     */
    function __construct( $mimeType, $direction )
    {
        parent::__construct( "Converter does not support MIME type '{$mimeType}' for '{$direction}'." );
    }
}

?>
