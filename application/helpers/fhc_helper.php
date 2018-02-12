<?php

/**
 * FH-Complete
 *
 * @package		FHC-Helper
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license		GPLv3
 * @since		Version 1.0.0
 */

/**
 * FHC Helper
 *
 * @subpackage	Helpers
 * @category	Helpers
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------
// Collection of utility functions for general purpose
// ------------------------------------------------------------------------

/**
 * generateToken() - generates a new token for diffent use
 * - reading Messages from external
 * - forgotten Password
 *
 * @return  string
 */
function generateToken($length = 64)
{
	// For PHP 7 you can use random_bytes()
	if (function_exists('random_bytes'))
	{
		$token = base64_encode(random_bytes($length));
		//base64 is about 33% longer, so we need to truncate the result
		return strtr(substr($token, 0, $length), '+/=', '-_,');
	}

	// for PHP >=5.3 and <7
	if (function_exists('openssl_random_pseudo_bytes'))
	{
        $token = base64_encode(openssl_random_pseudo_bytes($length, $strong));
		// is the token strong enough?
        if($strong == true)
			return strtr(substr($token, 0, $length), '+/=', '-_,');
    }

    //fallback to mt_rand if php < 5.3 or no openssl available
    $characters = '0123456789';
    $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/+';
    $charactersLength = strlen($characters)-1;
    $token = '';
    //select some random characters
    for ($i = 0; $i < $length; $i++)
        $token .= $characters[mt_rand(0, $charactersLength)];

    return $token;
}

/**
 * var_dump_to_error_log()
 * Gets the output of the var_dump function and print it out
 * via the error log. It also removes the end line characters
 */
function var_dump_to_error_log($parameter)
{
	ob_start();
	var_dump($parameter);
	$ob_get_contents = ob_get_contents();
	ob_end_clean();
	error_log(str_replace("\n", '', $ob_get_contents));
}

/**
 * Utility function to include only once one or more php files
 * It can load more php files and also search in the subdirectories (one level only)
 * Ex:
 * - loadResource('/var/www/htdocs'): it loads all the php files present in path (no subdirectories)
 * - loadResource('/var/www/htdocs', null, true): it loads all the php files present path and
 *   subdirectories (one level only)
 * - loadResource('/var/www/htdocs', 'file1'): it loads the file file1.php present in path
 * - loadResource('/var/www/htdocs', 'file1', true): it loads the file file1.php present in path
 *   or in the subdirs (one level only)
 * - loadResource('/var/www/htdocs', array('file1', 'file2', 'file3')): it loads the files file1.php,
 *   file2.php and file3.php present in path
 * - loadResource('/var/www/htdocs', array('file1', 'file2', 'file3'), true): it loads the files
 *   file1.php, file2.php and file3.php present in path or in the subdirectories (one level only)
 */
function loadResource($path, $resources = null, $subdir = false)
{
	// Place a / character at the and of the string if not present
	if (strrpos($path, '/') < strlen($path) - 1)
	{
		$path .= '/';
	}

	// Loads in $tmpResources all the given resources
	$tmpResources = $resources;
	if ($resources == null)
	{
		$tmpResources = array();
	}
	elseif (!is_array($resources))
	{
		$tmpResources = array($resources);
	}

	// Loads in $tmpPaths path and eventually the subdirectories
	$tmpPaths = array($path);
	// NOTE: Used @ to prevent ugly error messages
	if (is_dir($path) && ($dirHandler = @opendir($path)) !== false)
	{
		// Reads all file system entries present in path
		while (($entry = readdir($dirHandler)) !== false)
		{
			// If entry is a directory but not the current and subdirectories should be loaded
			if ($subdir === true && $entry != '.' && $entry != '..' && is_dir($entry))
			{
				$tmpPaths[] = $entry;
			}
			// If no resources are specified and the current file system entry is a file
			if ($resources == null && is_file($path.$entry))
			{
				// If the current entry is a php file store the name without extension
				if ($entry != ($tmpName = str_replace('.php', '', $entry)))
				{
					$tmpResources[] = $tmpName;
				}
			}
		}
		closedir($dirHandler);
	}

	// Loops through the resources
	foreach ($tmpResources as $tmpResource)
	{
		// Loops through the paths
		foreach ($tmpPaths as $tmpPath)
		{
			$fileName = $tmpPath.$tmpResource.'.php'; // Php extension
			if (file_exists($fileName))
			{
				include_once($fileName);
			}
		}
	}
}
