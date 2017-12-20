<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// By default set the parameters to null
$title = isset($title) ? $title : null;
$customCSSs = isset($customCSSs) ? $customCSSs : null;
$customJSs = isset($customJSs) ? $customJSs : null;

// By default set the parameters to false
$jquery3 = isset($jquery3) ? $jquery3 : false;
$tablesorter = isset($tablesorter) ? $tablesorter : false;

/**
 * Print the given title of the page
 * NOTE: this is a required field, must be specified otherwise an error is shown
 */
function _printTitle($title)
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
function _generateCSSsInclude($CSSs)
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
 * Generates tags for the javascripts you want to include, the parameter could by a string or an array of strings
 */
function _generateJSsInclude($JSs)
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

?>
<!-- Header start -->
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php _printTitle($title); ?></title>

		<meta charset="UTF-8">

		<link rel="shortcut icon" type="image/x-icon" href="<?php echo base_url('skin/images/Vilesci.ico'); ?>" />
		<link rel="stylesheet"    type="text/css"     href="<?php echo base_url('skin/vilesci.css'); ?>" />

		<?php
			// --------------------------------------------------------------------------------------------------------
			// CSS

			// Eventually required CSS
			_generateCSSsInclude($customCSSs); // Eventually required CSS

			// Table sorter CSS
			if ($tablesorter === true) _generateCSSsInclude('skin/tablesort.css');

			// --------------------------------------------------------------------------------------------------------
			// Javascripts

			// JQuery V3
			if ($jquery3 === true) _generateJSsInclude('vendor/components/jquery/jquery.min.js');
			// Table sorter JS
			if ($tablesorter === true) _generateJSsInclude('vendor/christianbach/tablesorter/jquery.tablesorter.min.js');

			// Eventually required JS
			_generateJSsInclude($customJSs);
		?>

	</head>
<!-- Header end -->
