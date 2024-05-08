<?php
/**
 * File containing the ezcImageInvalidFilterParameterException.
 * 
 * @package ImageConversion
 * @version 1.3.5
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Thrown if the given filter failed.
 *
 * @package ImageConversion
 * @version 1.3.5
 */
class ezcImageInvalidFilterParameterException extends ezcImageException
{
    /**
     * Creates a new ezcImageInvalidFilterParameterException.
     * 
     * @param string $filterName    Name of the filter.
     * @param string $parameterName Affected parameter.
     * @param string $actualValue   Received value.
     * @param string $expectedRange Expected value range.
     * @return void
     */
    function __construct( $filterName, $parameterName, $actualValue, $expectedRange = null )
    {
        $actualValue = var_export( $actualValue, true );
        $message = "Wrong value '{$actualValue}' submitted for parameter '{$parameterName}' of filter '{$filterName}'.";
        if ( $expectedRange !== null )
        {
            $message .= " Expected parameter to be in range '{$expectedRange}'.";
        }
        parent::__construct( $message );
    }
}

?>
