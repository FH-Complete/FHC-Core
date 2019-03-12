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
 * Gets the output of the var_dump function and print it out
 * via the error log. It also removes the end line characters
 */
function var_dump_to_error_log($parameter)
{
	ob_start();
	var_dump($parameter); // KEEP IT!!!
	$ob_get_contents = ob_get_contents();
	ob_end_clean();
	error_log(str_replace("\n", '', $ob_get_contents)); // KEEP IT!!!
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

/**
 * Returns true if the given string is empty
 * Empty means that the parameter string is null or made of space, tab, vertical tab, line feed, carriage return
 * and form feed characters.
 */
function isEmptyString($string)
{
	return ($string == null) || ($string != null && ctype_space($string) === true);
}

/**
 * Returns true if the given array is empty
 * Empty means that is null, or is not null and it is not an array, or it is an array but without elements
 */
function isEmptyArray($array)
{
	return ($array == null) || ($array != null && !is_array($array) || (is_array($array) && count($array) == 0));
}

/**
 * The function returns the number of business days between two dates and it skips the (static) holidays
 * @param string $startDate Date (YYYY-MM-DD) to start counting from (included)
 * @param string $endDate Date (YYYY-MM-DD) to end counting (included)
 * @param array $dynamic_holidays Optional. Static holidays that have the same date every year are included automatically.
 *                                You can give an array with dates (YYYY-MM-DD) that should be included furthermore
 * @return integer Number of working days between $startDate and $endDate
 */
function getWorkingDays($startDate, $endDate, $dynamic_holidays = array())
{
	//Get year of $startDate
	$startYear = substr($startDate, 0, 4);
	//Get year of $endDate
	$endYear = substr($endDate, 0, 4);

	$datediff = $endYear - $startYear;
	$austrian_holidays = array();
	for ($i = 0; $i <= $datediff; $i++)
	{
		$austrian_holidays[] = $startYear.'-01-01'; // Neujahr
		$austrian_holidays[] = $startYear.'-01-06'; // 3 Könige
		$austrian_holidays[] = $startYear.'-05-01'; // Staatsfeiertag
		$austrian_holidays[] = $startYear.'-08-15'; // Maria Himmelfahrt
		$austrian_holidays[] = $startYear.'-10-26'; // Nationalfeiertag
		$austrian_holidays[] = $startYear.'-11-01'; // Allerheiligen
		$austrian_holidays[] = $startYear.'-12-08'; // Maria Empfängnis
		$austrian_holidays[] = $startYear.'-12-25'; // Weihnachten
		$austrian_holidays[] = $startYear.'-12-26'; // Stefanitag
		$startYear++;
	}
	if (count($dynamic_holidays) > 0)
	{
		$austrian_holidays = array_merge($austrian_holidays, $dynamic_holidays);
	}
	// do strtotime calculations just once
	$endDate = strtotime($endDate);
	$startDate = strtotime($startDate);

	//The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
	//We add one to inlude both dates in the interval.
	$days = ($endDate - $startDate) / 86400 + 1;

	$no_full_weeks = floor($days / 7);
	$no_remaining_days = fmod($days, 7);

	//It will return 1 if it's Monday,.. ,7 for Sunday
	$the_first_day_of_week = date("N", $startDate);
	$the_last_day_of_week = date("N", $endDate);

	//---->The two can be equal in leap years when february has 29 days, the equal sign is added here
	//In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
	if ($the_first_day_of_week <= $the_last_day_of_week)
	{
		if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
		if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
	}
	else {
		// (edit by Tokes to fix an edge case where the start day was a Sunday
		// and the end day was NOT a Saturday)

		// the day of the week for start is later than the day of the week for end
		if ($the_first_day_of_week == 7) {
			// if the start date is a Sunday, then we definitely subtract 1 day
			$no_remaining_days--;

			if ($the_last_day_of_week == 6) {
				// if the end date is a Saturday, then we subtract another day
				$no_remaining_days--;
			}
		}
		else {
			// the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
			// so we skip an entire weekend and subtract 2 days
			$no_remaining_days -= 2;
		}
	}

	//The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
	//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
	$workingDays = $no_full_weeks * 5;
	if ($no_remaining_days > 0 )
	{
		$workingDays += $no_remaining_days;
	}

	//We subtract the holidays
	foreach($austrian_holidays as $key=>$value)
	{
		$time_stamp=strtotime($value);
		//If the holiday doesn't fall in weekend
		if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
			$workingDays--;
	}

	return $workingDays;
}
