<?php
/**
 * @package Base
 * @subpackage Tests
 * @version 1.5
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

require_once( "base_test.php");
require_once( "base_init_test.php");
require_once( "features_test.php");
require_once( "base_options_test.php");
require_once( "struct_test.php");
require_once 'file_find_recursive_test.php';
require_once 'file_is_absolute_path.php';
require_once 'file_copy_recursive_test.php';
require_once 'file_remove_recursive_test.php';
require_once 'file_calculate_relative_path_test.php';

/**
 * @package Base
 * @subpackage Tests
 */
class ezcBaseSuite extends PHPUnit_Framework_TestSuite
{
	public function __construct()
	{
		parent::__construct();
        $this->setName("Base");

        $this->addTest( ezcBaseTest::suite() );
        $this->addTest( ezcBaseInitTest::suite() );
        $this->addTest( ezcBaseFeaturesTest::suite() );
        $this->addTest( ezcBaseOptionsTest::suite() );
        $this->addTest( ezcBaseStructTest::suite() );
        $this->addTest( ezcBaseFileCalculateRelativePathTest::suite() );
        $this->addTest( ezcBaseFileFindRecursiveTest::suite() );
        $this->addTest( ezcBaseFileIsAbsoluteTest::suite() );
        $this->addTest( ezcBaseFileCopyRecursiveTest::suite() );
        $this->addTest( ezcBaseFileRemoveRecursiveTest::suite() );
    }

    public static function suite()
    {
        return new ezcBaseSuite();
    }
}
?>
