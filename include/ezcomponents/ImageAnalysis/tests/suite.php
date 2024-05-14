<?php
/**
* ezcImageAnalysisSuite
*
* @package ImageAnalysis
* @subpackage Tests
* @version 1.1.3
* @copyright Copyright (C) 2005, 2006 eZ systems as. All rights reserved.
* @license LGPL {@link http://www.gnu.org/copyleft/lesser.html}
*/

/**
* Require test suite for ImageAnalyzer class.
*/
require_once 'analyzer_test.php';

/**
* Test suite for ImageAnalysis package.
*
* @package ImageAnalysis
* @subpackage Tests
*/
class ezcImageAnalysisSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( "ImageAnalysis" );
        $this->addTest( ezcImageAnalysisAnalyzerTest::suite() );
    }

    public static function suite()
    {
        return new ezcImageAnalysisSuite( "ezcImageAnalysisSuite" );
    }
}
?>
