<?php
/**
 * ezcImageConversionSuite
 *
 * @package ImageConversion
 * @subpackage Tests
 * @version 1.3.5
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

require_once 'converter_test.php';
require_once 'transformation_test.php';
require_once 'handlergd_test.php';
require_once 'filtersgd_test.php';
require_once 'handlershell_test.php';
require_once 'filtersshell_test.php';
require_once 'save_options_test.php';

class ezcImageConversionSuite extends PHPUnit_Framework_TestSuite
{
    public function __construct()
    {
        parent::__construct();
        $this->setName( "ImageConversion" );

        $this->addTest( ezcImageConversionConverterTest::suite() );
        $this->addTest( ezcImageConversionTransformationTest::suite() );

        $this->addTest( ezcImageConversionHandlerGdTest::suite() );
        $this->addTest( ezcImageConversionFiltersGdTest::suite() );

        $this->addTest( ezcImageConversionHandlerShellTest::suite() );
        $this->addTest( ezcImageConversionFiltersShellTest::suite() );

        $this->addTest( ezcImageConversionSaveOptionsTest::suite() );
    }

    public static function suite()
    {
        return new ezcImageConversionSuite( "ezcImageConversionSuite" );
    }
}
?>
