<?php
/**
 * File containing the abstract ezcGraphDataSetIntProperty class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Class for integer properties of datasets
 *
 * @version 1.3
 * @package Graph
 */
class ezcGraphDataSetIntProperty extends ezcGraphDataSetProperty
{
    /**
     * Converts value to an {@link ezcGraphColor} object
     * 
     * @param & $value 
     * @return void
     */
    protected function checkValue( &$value )
    {
        $value = (int) $value;
        return true;
    }
}

?>
