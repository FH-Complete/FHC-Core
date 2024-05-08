<?php
/**
 * File containing the ezcGraphNoSuchElementException class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Exception thrown when trying to access a non existing chart element.
 *
 * @package Graph
 * @version 1.3
 */
class ezcGraphNoSuchElementException extends ezcGraphException
{
    /**
     * Constructor
     * 
     * @param string $name
     * @return void
     * @ignore
     */
    public function __construct( $name )
    {
        parent::__construct( "No chart element with name '{$name}' found." );
    }
}

?>
