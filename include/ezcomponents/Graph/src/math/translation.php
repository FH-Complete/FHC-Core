<?php
/**
 * File containing the ezcGraphTranslation class
 *
 * @package Graph
 * @version 1.3
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */
/**
 * Class creating translation matrices from given movements
 *
 * @version 1.3
 * @package Graph
 * @access private
 */
class ezcGraphTranslation extends ezcGraphTransformation
{
    /**
     * Constructor
     * 
     * @param float $x 
     * @param float $y 
     * @return void
     * @ignore
     */
    public function __construct( $x = 0., $y = 0. )
    {
        parent::__construct( array( 
            array( 1, 0, $x ),
            array( 0, 1, $y ),
            array( 0, 0, 1 ),
        ) );
    }
}

?>
