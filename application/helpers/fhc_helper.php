<?php

/**
 * FH-Complete
 *
 * @package	FHC-Helper
 * @author	FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license	GPLv3
 * @link	https://fhcomplete.org
 * @since	Version 1.0.0
 * @filesource
 */
if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FHC Helper
 *
 * @package		FH-Complete
 * @subpackage	Helpers
 * @category	Helpers
 * @author		FHC-Team
 * @link		http://fhcomplete.org/user_guide/helpers/fhcauth_helper.html
 */

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
	if(function_exists('random_bytes')) 
	{
		$token = base64_encode(random_bytes($length));
		//base64 is about 33% longer, so we need to truncate the result
		return strtr(substr($token, 0, $length), '+/=', '-_,');
	}

	// for PHP >=5.3 and <7
	if(function_exists('openssl_random_pseudo_bytes')) 
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
 * 
 */
function loadResource($path, $resources = null, $subdir = false)
{
	$_ci = & get_instance();
	
	// 
	if (strrpos($path, '/') < strlen($path) - 1)
	{
		$path .= '/';
	}
	
	// 
	$tmpResources = $resources;
	if ($resources == null)
	{
		$tmpResources = array();
	}
	else if (!is_array($resources))
	{
		$tmpResources = array($resources);
	}
	
	// 
	$tmpPaths = array($path);
	// NOTE: Used @ to prevent ugly error messages
	if (is_dir($path) && ($dirHandler = @opendir($path)) !== false)
	{
		while (($entry = readdir($dirHandler)) !== false)
		{
			// 
			if ($subdir === true && $entry != '.' && $entry != '..' && is_dir($entry))
			{
				$tmpPaths[] = $entry;
			}
			// 
			if ($resources == null && is_file($path.$entry))
			{
				if ($entry != ($tmpName = str_replace('.php', '', $entry)))
				{
					$tmpResources[] = $tmpName;
				}
			}
		}
		closedir($dirHandler);
	}
	
	// 
	foreach($tmpResources as $tmpResource)
	{
		foreach($tmpPaths as $tmpPath)
		{
			$fileName = $tmpPath.$tmpResource.'.php';
			if (file_exists($fileName))
			{
				include_once($fileName);
			}
		}
	}
}