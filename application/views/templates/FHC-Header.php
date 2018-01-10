<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// By default set the parameters to null
$title = isset($title) ? $title : null;
$customCSSs = isset($customCSSs) ? $customCSSs : null;
$customJSs = isset($customJSs) ? $customJSs : null;

// By default set the parameters to false
$jquery3 = isset($jquery3) ? $jquery3 : false;
$jqueryui = isset($jqueryui) ? $jqueryui : false;
$bootstrap = isset($bootstrap) ? $bootstrap : false;
$fontawesome = isset($fontawesome) ? $fontawesome : false;
$bootstrapdatepicker = isset($bootstrapdatepicker) ? $bootstrapdatepicker : false;
$datatables = isset($datatables) ? $datatables : false;
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

		<?php
			// --------------------------------------------------------------------------------------------------------
			// CSS

			// jQuery UI CSS
			if ($jqueryui === true) _generateCSSsInclude('vendor/components/jqueryui/themes/base/jquery-ui.min.css');
			// bootstrap CSS
			if ($bootstrap === true) _generateCSSsInclude('vendor/components/bootstrap/css/bootstrap.min.css');
			// font awesome CSS
			if ($fontawesome === true) _generateCSSsInclude('vendor/components/font-awesome/css/font-awesome.min.css');
			// bootstrap datepicker CSS
			if ($bootstrapdatepicker === true) _generateCSSsInclude('vendor/eternicode/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css');
			// datatables CSS
			if ($datatables === true)
			{
				_generateCSSsInclude('vendor/datatables/datatables/media/css/dataTables.bootstrap.min.css');
			}
			// Table sorter CSS
			if ($tablesorter === true) _generateCSSsInclude('skin/tablesort.css');

			// Eventually required CSS
			_generateCSSsInclude($customCSSs); // Eventually required CSS

			// --------------------------------------------------------------------------------------------------------
			// Javascripts

			// JQuery V3
			if ($jquery3 === true) _generateJSsInclude('vendor/components/jquery/jquery.min.js');
			// JQuery UI
			if ($jqueryui === true) _generateJSsInclude('vendor/components/jqueryui/jquery-ui.min.js');
			// bootstrap JS
			if ($bootstrap === true) _generateJSsInclude('vendor/components/bootstrap/js/bootstrap.min.js');
			// bootstrap datepicker JS
			if ($bootstrapdatepicker === true){
				_generateJSsInclude('vendor/eternicode/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js');
				_generateJSsInclude('vendor/eternicode/bootstrap-datepicker/dist/locales/bootstrap-datepicker.de.min.js');
			}
			// datatables JS
			if ($datatables === true)
			{
			 	_generateJSsInclude('vendor/datatables/datatables/media/js/jquery.dataTables.js');
				_generateJSsInclude('vendor/datatables/datatables/media/js/dataTables.bootstrap.min.js');
				_generateJSsInclude('vendor/moment/moment/min/moment.min.js');
				_generateJSsInclude('vendor/datatables/plugins/sorting/datetime-moment.js');
			}
			// Table sorter JS
			if ($tablesorter === true) _generateJSsInclude('vendor/christianbach/tablesorter/jquery.tablesorter.min.js');

			// Eventually required JS
			_generateJSsInclude($customJSs);
		?>

	</head>
<!-- Header end -->
