<?php
/**
 * File containing the ezcGraphInvalidDataException class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Exception thrown when invalid data is provided, which cannot be rendered 
 * for some reason.
 *
 * @package Graph
 * @version 1.3
 */
class ezcGraphInvalidDataException extends ezcGraphException
{
    /**
     * Constructor
     * 
     * @param string $message
     * @return void
     * @ignore
     */
    public function __construct( $message )
    {
        parent::__construct( "You provided unusable data: '$message'." );
    }
}

?>
