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
 * Generates tags for the style sheets you want to include, the parameter could by a string or an array of strings
 */
function generateCSSsInclude($CSSs)
{
	$cssLink = '<link rel="stylesheet" type="text/css" href="%s" />';

	if (isset($CSSs))
	{
		$tmpCSSs = is_array($CSSs) ? $CSSs : array($CSSs);

		for ($tmpCSSsCounter = 0; $tmpCSSsCounter < count($tmpCSSs); $tmpCSSsCounter++)
		{
			$toPrint = sprintf($cssLink, base_url($tmpCSSs[$tmpCSSsCounter])).PHP_EOL;

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

	if (isset($JSs))
	{
		$tmpJSs = is_array($JSs) ? $JSs : array($JSs);

		for ($tmpJSsCounter = 0; $tmpJSsCounter < count($tmpJSs); $tmpJSsCounter++)
		{
			$toPrint = sprintf($jsInclude, base_url($tmpJSs[$tmpJSsCounter])).PHP_EOL;

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

	foreach ($aktive_addons as $addon)
	{
		$hookfile = DOC_ROOT.'addons/'.$addon.'/hooks.config.inc.php';
		if (file_exists($hookfile))
		{
			include($hookfile);
			if (key_exists($calledFrom, $js_hooks))
			{
				foreach ($js_hooks[$calledFrom] as $js_file)
					generateJSsInclude('addons/'.$addon.'/'.$js_file);
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
