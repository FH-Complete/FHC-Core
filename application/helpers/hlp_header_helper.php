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
// Functions needed in the view FHC-Header
// ------------------------------------------------------------------------

/**
 * Print the given title of the page
 * NOTE: this is a required field, must be specified otherwise an error is shown
 */
function printPageTitle($title)
{
	if ($title != null)
	{
		echo $title;
	}
	else
	{
		show_error('The title for this page is not set');
	}
}

/**
 * Print the meta tag http-equiv refresh having as content the value of the given parameter
 */
function printRefreshMeta($refresh)
{
	if ($refresh != null)
	{
		if (is_numeric($refresh) && $refresh > 0)
		{
			echo '<meta http-equiv="refresh" content="'.$refresh.'">';
		}
		else
		{
			show_error('The provided refresh parameter has to be a number greater then 0');
		}
	}
}

/**
 * Generates tags for the style sheets you want to include, the parameter could by a string or an array of strings
 */
function generateCSSsInclude($CSSs)
{
	$cssLink = '<link rel="stylesheet" type="text/css" href="%s" />';

	$ci =& get_instance();
	$cachetoken = '?'.$ci->config->item('fhcomplete_build_version');

	if (isset($CSSs))
	{
		$tmpCSSs = is_array($CSSs) ? $CSSs : array($CSSs);

		for ($tmpCSSsCounter = 0; $tmpCSSsCounter < count($tmpCSSs); $tmpCSSsCounter++)
		{
			$toPrint = sprintf($cssLink, base_url($tmpCSSs[$tmpCSSsCounter]).$cachetoken).PHP_EOL;

			if ($tmpCSSsCounter > 0) $toPrint = "\t\t".$toPrint;

			echo $toPrint;
		}
	}
}

/**
 * Generates global JS-Object to pass parms to other javascripts
 */
function generateJSDataStorageObject($indexPage, $calledPath, $calledMethod)
{
	$user_language = getUserLanguage();

	$toPrint = "\n";
	$toPrint .= '<script type="text/javascript">';
	$toPrint .= '
		var FHC_JS_DATA_STORAGE_OBJECT = {
			app_root: "'.APP_ROOT.'",
			ci_router: "'.$indexPage.'",
			called_path: "'.$calledPath.'",
			called_method: "'.$calledMethod.'"
		};';
	$toPrint .= "\n";
	$toPrint .= '</script>';
	$toPrint .= "\n\n";

	echo $toPrint;
}

/**
 * Generates global JS-Object to pass phrases to other javascripts
 */
function generateJSPhrasesStorageObject($phrases)
{
	$ci =& get_instance();
	$ci->load->library('PhrasesLib', array($phrases), 'pj');

	$toPrint = "\n";
	$toPrint .= '<script type="text/javascript">';
	$toPrint .= "\n";
	$toPrint .= '	var FHC_JS_PHRASES_STORAGE_OBJECT = '.$ci->pj->getJSON().';';
	$toPrint .= "\n";
	$toPrint .= '</script>';
	$toPrint .= "\n\n";

	echo $toPrint;
}

/**
 * Generates tags for the javascripts you want to include, the parameter could by a string or an array of strings
 */
function generateJSsInclude($JSs)
{
	$jsInclude = '<script type="text/javascript" src="%s"></script>';

	$ci =& get_instance();
	$cachetoken = '?'.$ci->config->item('fhcomplete_build_version');

	if (isset($JSs))
	{
		$tmpJSs = is_array($JSs) ? $JSs : array($JSs);

		for ($tmpJSsCounter = 0; $tmpJSsCounter < count($tmpJSs); $tmpJSsCounter++)
		{
			$toPrint = sprintf($jsInclude, base_url($tmpJSs[$tmpJSsCounter].$cachetoken)).PHP_EOL;

			if ($tmpJSsCounter > 0) $toPrint = "\t\t".$toPrint;

			echo $toPrint;
		}
	}
}

/**
 * Generates tags for the javascript modules you want to include, the parameter could by a string or an array of strings
 */
function generateJSModulesInclude($JSModules)
{
	$jsInclude = '<script type="module" src="%s"></script>';

	$ci =& get_instance();
	$cachetoken = '?'.$ci->config->item('fhcomplete_build_version');

	if (isset($JSModules))
	{
		$tmpJSs = is_array($JSModules) ? $JSModules : array($JSModules);

		for ($tmpJSsCounter = 0; $tmpJSsCounter < count($tmpJSs); $tmpJSsCounter++)
		{
			$toPrint = sprintf($jsInclude, base_url($tmpJSs[$tmpJSsCounter].$cachetoken)).PHP_EOL;

			if ($tmpJSsCounter > 0) $toPrint = "\t\t".$toPrint;

			echo $toPrint;
		}
	}
}

/**
 * Generates all the includes needed by the Addons
 */
function generateAddonsJSsInclude($calledFrom)
{
	$aktive_addons = array_filter(explode(";", ACTIVE_ADDONS));

	// For each active addon
	foreach ($aktive_addons as $addon)
	{
		// Build the path to the hook file
		$hookfile = DOC_ROOT.'addons/'.$addon.'/hooks.config.inc.php';

		// If the hook file exists
		if (file_exists($hookfile))
		{
			$js_hooks = array(); // default value

			include($hookfile); // include the hook file where the array js_hooks should be setup

			// If it contains the provided key calledFrom
			if (key_exists($calledFrom, $js_hooks))
			{
				foreach ($js_hooks[$calledFrom] as $js_file)
				{
					generateJSsInclude('addons/'.$addon.'/'.$js_file);
				}
			}
		}
	}
}

/**
 * This function merely print some useful HTML to help some vacuous browsers to handle modern JS features
 */
function generateBackwardCompatibleJSMsIe($js)
{
	echo "<!--[if lt IE 9]>\n";
	echo '	<script type="text/javascript" src="'.$js.'"></script>'."\n";
	echo "<![endif]-->\n";
}

