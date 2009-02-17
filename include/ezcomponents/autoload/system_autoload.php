<?php
/**
 * Autoloader definition for the SystemInformation component.
 *
 * @copyright Copyright (C) 2005-2008 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.0.6
 * @filesource
 * @package SystemInformation
 */

return array(
    'ezcSystemInfoReaderCantScanOSException' => 'SystemInformation/system/exceptions/cant_scan.php',
    'ezcSystemInfoReader'                    => 'SystemInformation/system/interfaces/info_reader.php',
    'ezcSystemInfo'                          => 'SystemInformation/system/info.php',
    'ezcSystemInfoAccelerator'               => 'SystemInformation/system/structs/accelerator_info.php',
    'ezcSystemInfoFreeBsdReader'             => 'SystemInformation/system/readers/info_freebsd.php',
    'ezcSystemInfoLinuxReader'               => 'SystemInformation/system/readers/info_linux.php',
    'ezcSystemInfoMacReader'                 => 'SystemInformation/system/readers/info_mac.php',
    'ezcSystemInfoWindowsReader'             => 'SystemInformation/system/readers/info_windows.php',
);
?>
