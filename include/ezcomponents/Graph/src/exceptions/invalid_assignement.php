<?php
/**
 * File containing the ezcGraphInvalidAssignementException class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Exception thrown, when trying a property cannot be set for a data point, but
 * only for data sets.
 *
 * @package Graph
 * @version 1.3
 */
class ezcGraphInvalidAssignementException extends ezcGraphException
{
    /**
     * Constructor
     * 
     * @return void
     * @ignore
     */
    public function __construct()
    {
        parent::__construct( "This cannot be set on data points, but only for data sets." );
    }
}

?>
