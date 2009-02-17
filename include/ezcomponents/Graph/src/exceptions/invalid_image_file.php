<?php
/**
 * File containing the ezcGraphInvalidImageFileException class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Exception thrown when a file can not be used as a image file.
 *
 * @package Graph
 * @version 1.3
 */
class ezcGraphInvalidImageFileException extends ezcGraphException
{
    /**
     * Constructor
     * 
     * @param string $image
     * @return void
     * @ignore
     */
    public function __construct( $image )
    {
        parent::__construct( "File '{$image}' is not a valid image." );
    }
}

?>
