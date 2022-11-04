<?php
/**
 * Copyright (C) 2022 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------
// Collection of utility functions for general purpose
// ------------------------------------------------------------------------

/**
 * Generates a new token for diffent use cases. Default token length is 64
 * - Reading messages
 * - Forgotten password
 * - etc
 * Returns null on failure
 */
function generateToken($length = 64)
{
	$token = null;
	$firstGeneratedToken = null;

	// For PHP 7 you can use random_bytes()
	if (function_exists('random_bytes'))
	{
		try
		{
			$firstGeneratedToken = random_bytes($length); // try to generates cryptographically secure pseudo-random bytes...
		}
		catch (Exception $e)
		{
			// If fails $firstGeneratedToken is set to null
			$firstGeneratedToken = null;
		}
	}
	// For PHP >= 5.3 and < 7 and openssl is available
	elseif (function_exists('openssl_random_pseudo_bytes'))
	{
		$firstGeneratedToken = openssl_random_pseudo_bytes($length, $strong);
		// If the token generation ended with errors OR the generated token is NOT strong enough
		if ($firstGeneratedToken == false || $strong == false) $firstGeneratedToken = null; // $firstGeneratedToken is set to null
	}

	if ($firstGeneratedToken != null) // If everything was fine
	{
		// base64 is about 33% longer, so we need to truncate the result
		$token = strtr(substr(base64_encode($firstGeneratedToken), 0, $length), '+/=', '-_,');
	}

	// Fallback to mt_rand if:
	// php < 5.3
	// OR no openssl is available
	// OR openssl_random_pseudo_bytes used an algorithm that is cryptographically NOT strong
	// OR one of the previous methods failed
	if ($token == null)
	{
		$token = ''; // set $token as an empty string
	    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz/+';
	    $charactersLength = strlen($characters) - 1;

	    // Select some random characters
	    for ($i = 0; $i < $length; $i++) $token .= $characters[mt_rand(0, $charactersLength)];
	}

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
	if (strrpos($path, '/') < strlen($path) - 1) $path .= '/';

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

	// If path is a directory
	if (is_dir($path))
	{
		// NOTE: Used @ to prevent ugly error messages
		$dirHandler = @opendir($path);

		// Successfully opened
		if ($dirHandler !== false)
		{
			// Reads all file system entries present in path
			while (($entry = readdir($dirHandler)) !== false)
			{
				// If entry is a directory but not the current and subdirectories should be loaded
				if ($subdir === true && $entry != '.' && $entry != '..' && is_dir($path.$entry))
				{
					$tmpPaths[] = $path.$entry.'/';
				}
				// If no resources are specified and the current file system entry is a file
				if ($resources == null && is_file($path.$entry))
				{
					// Name without php extension
					$tmpName = str_replace('.php', '', $entry);

					// If the current entry is a php file store the name without extension
					if ($entry != $tmpName) $tmpResources[] = $tmpName;
				}
			}
			closedir($dirHandler);
		}
	}

	// Loops through the resources
	foreach ($tmpResources as $tmpResource)
	{
		// Loops through the paths
		foreach ($tmpPaths as $tmpPath)
		{
			$fileName = $tmpPath.$tmpResource.'.php'; // Php extension
			if (file_exists($fileName)) include_once($fileName);
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
 * The function checks if a date is a working day (true) or a holiday (false)
 * @param string $date Date (YYYY-MM-DD) to check if working day or holiday
 * @param string $days Optional. Number of days to deduct from $date
 * @return boolean True, if $date is a working day, false if not
 */
function isDateWorkingDay($date, $days = null)
{
	// Array of static Austrian holidays
	$austrian_holidays = array();
	//Get year of $resultDate
	$startYear = substr($date, 0, 4);

	$austrian_holidays[] = $startYear.'-01-01'; // Neujahr
	$austrian_holidays[] = $startYear.'-01-06'; // 3 Könige
	$austrian_holidays[] = $startYear.'-05-01'; // Staatsfeiertag
	$austrian_holidays[] = $startYear.'-08-15'; // Maria Himmelfahrt
	$austrian_holidays[] = $startYear.'-10-26'; // Nationalfeiertag
	$austrian_holidays[] = $startYear.'-11-01'; // Allerheiligen
	$austrian_holidays[] = $startYear.'-12-08'; // Maria Empfängnis
	$austrian_holidays[] = $startYear.'-12-25'; // Weihnachten
	$austrian_holidays[] = $startYear.'-12-26'; // Stefanitag

	$ostersonntag = date("Y-m-d", easter_date($startYear));

	//Ostermontag
	$austrian_holidays[] = date("Y-m-d", strtotime($ostersonntag. "+ 1 days")); // Ostersonntag + 1

	//Christi Himmelfahrt
	$austrian_holidays[] = date("Y-m-d", strtotime($ostersonntag. "+ 39 days")); // Ostersonntag + 39

	//Pfingstmontag
	$austrian_holidays[] = date("Y-m-d", strtotime($ostersonntag. "+ 50 days")); // Ostersonntag + 50

	//Fronleichnam
	$austrian_holidays[] = date("Y-m-d", strtotime($ostersonntag. "+ 60 days")); // Ostersonntag + 60

	if ($days != '')
	{
		if (date("w", strtotime("$date -".$days." days")) == 0
			|| date("w", strtotime("$date -".$days." days")) == 6
			|| in_array(date("Y-m-d", strtotime("$date -".$days." days")), $austrian_holidays))
			return false;
		else
			return true;
	}
	else
	{
		if (date("w", strtotime($date)) == 0
			|| date("w", strtotime($date)) == 6
			|| in_array(date("Y-m-d", strtotime($date)), $austrian_holidays))
			return false;
		else
			return true;
	}
}

/**
 * Parse the given text using the given data parameter
 * Use the CI parser which performs simple text substitution for pseudo-variable
 */
function parseText($text, $data)
{
	$ci =& get_instance(); // get CI instance
	$ci->load->library('parser'); // Loads CI parser library

	return $ci->parser->parse_string($text, $data, true);
}

/**
 * Parse the given template using the given data parameter
 * Use the CI parser which performs simple text substitution for pseudo-variable
 */
function parseTemplate($template, $data)
{
	$ci =& get_instance(); // get CI instance
	$ci->load->library('parser'); // Loads CI parser library

	return $ci->parser->parse($template, $data, true);
}

/**
 * Terminate immediately the execution of the current script.
 * If message parameter is given then:
 * - logs the given message in CI logs
 * - prints the given message to standard output
 * Otherwise terminate with the generic error
 */
function terminateWithError($message = null)
{
	if (!isEmptyString($message))
	{
		$ci =& get_instance(); // get CI instance
		$ci->load->library('LogLib'); // Loads LogLib

		$ci->loglib->logError($message);

		exit($message);
	}

	exit(EXIT_ERROR);
}

/**
 * Checks if the current user is logged by checking that the AuthLib is loaded and
 * it is present the authentication object in session
 * NOTE: it is placed here instead of being placed in the helper hlp_authentication_helper
 *		because hlp_authentication_helper is loaded after the authentication.
 *		It is very useful to use this function even in those parts of the code that are accessible
 *		even when a user is NOT authenticated!!!
 *		If and only if this function returns true, then all the functions present in hlp_authentication_helper can be used!
 */
function isLogged()
{
	$ci =& get_instance(); // get CI instance

	return isset($ci->authlib) && $ci->authlib->getAuthObj() != null;
}

/**
 * Konvertiert Problematische Sonderzeichen in Strings fuer
 * Accountnamen und EMail-Aliase
 *
 * @param $str
 * @return bereinigter String
 */
function sanitizeProblemChars($str)
{
	$enc = 'UTF-8';

	$acentos = array(
		'A' => '/&Agrave;|&Aacute;|&Acirc;|&Atilde;|&Aring;|&Abreve;|Ǎ/',
		'Ae' => '/&Auml;/',
		'a' => '/&agrave;|&aacute;|&acirc;|&atilde;|&aring;|&abreve;|ǎ/',
		'ae' => '/&auml;/',
		'C' => '/&Ccedil;|&Ccaron;/',
		'c' => '/&ccedil;|&ccaron;/',
		'E' => '/&Egrave;|&Eacute;|&Ecirc;|&Euml;/',
		'e' => '/&egrave;|&eacute;|&ecirc;|&euml;/',
		'I' => '/&Igrave;|&Iacute;|&Icirc;|&Iuml;|Ǐ/',
		'i' => '/&igrave;|&iacute;|&icirc;|&iuml;|ǐ/',
		'N' => '/&Ntilde;|&Ncaron;|&ncaron;/',
		'n' => '/&ntilde;/',
		'O' => '/&Ograve;|&Oacute;|&Ocirc;|&Otilde;|Ǒ/',
		'Oe' => '/&Ouml;/',
		'o' => '/&ograve;|&oacute;|&ocirc;|&otilde;|ǒ/',
		'oe' => '/&ouml;/',
		'R' => '/&Rcaron;/',
		'r' => '/&rcaron;/',
		'S' => '/&Scaron;/',
		's' => '/&scaron;/',
		'T' => '/&Tcaron;/',
		't' => '/&tcaron;/',
		'U' => '/&Ugrave;|&Uacute;|&Ucirc;|&Ubreve;|Ǔ/',
		'Ue' => '/&Uuml;/',
		'u' => '/&ugrave;|&uacute;|&ucirc;|&ubreve;|ǔ/',
		'ue' => '/&uuml;/',
		'Y' => '/&Yacute;/',
		'y' => '/&yacute;|&yuml;/',
		'Z' => '/&Zcaron;/',
		'z' => '/&zcaron;/',
		'a.' => '/&ordf;/',
		'o.' => '/&ordm;/',
		'ss' => '/&szlig;/'
	);

	return preg_replace($acentos, array_keys($acentos), htmlentities($str, ENT_NOQUOTES | ENT_HTML5, $enc));
}

/**
 *
 */
function findResource($path, $resource, $subdir = false, $extraDir = null)
{
	// Place a / character at the and of the string if not present
	if (strrpos($path, '/') < strlen($path) - 1) $path .= '/';

	// Loads in $tmpPaths path and eventually the subdirectories
	$tmpPaths = array($path);
	if (is_dir($path))
	{
		// NOTE: Used @ to prevent ugly error messages
		$dirHandler = @opendir($path);

		// Successfully opened
		if ($dirHandler !== false)
		{
			// Reads all file system entries present in path
			while (($entry = readdir($dirHandler)) !== false)
			{
				// If entry is a directory but not the current and subdirectories should be loaded
				if ($subdir === true && $entry != '.' && $entry != '..' && is_dir($path.$entry))
				{
					if ($extraDir == null)
					{
						$tmpPaths[] = $path.$entry.'/';
					}
					else
					{
						$tmpPaths[] = $path.$entry.'/'.$extraDir.'/';
					}
				}
			}
			closedir($dirHandler);
		}
	}

	// Loops through the paths
	foreach ($tmpPaths as $tmpPath)
	{
		$fileName = $tmpPath.$resource.'.php'; // Php extension
		if (file_exists($fileName)) return $fileName;
	}

	return null;
}

