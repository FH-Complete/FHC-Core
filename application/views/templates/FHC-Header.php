<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// By default set the parameters to null
$title = isset($title) ? $title : null;
$customCSSs = isset($customCSSs) ? $customCSSs : null;
$customJSs = isset($customJSs) ? $customJSs : null;

// By default set the parameters to false
$jquery = isset($jquery) ? $jquery : false;
$jqueryui = isset($jqueryui) ? $jqueryui : false;
$bootstrap = isset($bootstrap) ? $bootstrap : false;
$fontawesome = isset($fontawesome) ? $fontawesome : false;
$tablesorter = isset($tablesorter) ? $tablesorter : false;
$tinymce = isset($tinymce) ? $tinymce : false;
$sbadmintemplate = isset($sbadmintemplate) ? $sbadmintemplate : false;

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
			if ($bootstrap === true) _generateCSSsInclude('vendor/twbs/bootstrap/dist/css/bootstrap.min.css');
			// font awesome CSS
			if ($fontawesome === true) _generateCSSsInclude('vendor/components/font-awesome/css/font-awesome.min.css');
			// Table sorter CSS
			if ($tablesorter === true)
			{
				_generateCSSsInclude('vendor/mottie/tablesorter/dist/css/theme.default.min.css');
				_generateCSSsInclude('vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css');
			}
			// sb admin template CSS
			if ($sbadmintemplate === true)
			{
				_generateCSSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.css');
				_generateCSSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/dist/css/sb-admin-2.min.css');
			}

			// Eventually required CSS
			_generateCSSsInclude($customCSSs); // Eventually required CSS

			// --------------------------------------------------------------------------------------------------------
			// Javascripts

			// JQuery V3
			if ($jquery === true) _generateJSsInclude('vendor/components/jquery/jquery.min.js');
			// JQuery UI
			if ($jqueryui === true)
			{
				_generateJSsInclude('vendor/components/jqueryui/jquery-ui.min.js');
				//datepicker german language file
				_generateJSsInclude('vendor/components/jqueryui/ui/i18n/datepicker-de.js');
			}
			// bootstrap JS
			if ($bootstrap === true) _generateJSsInclude('vendor/twbs/bootstrap/dist/js/bootstrap.min.js');
			// Table sorter JS
			if ($tablesorter === true)
			{
				_generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js');
				_generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js');
				_generateJSsInclude('vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js');
			}
			//tinymce JS
			if($tinymce === true) _generateJSsInclude('vendor/tinymce/tinymce/tinymce.min.js') ;

			// sb admin template JS
			if ($sbadmintemplate === true)
			{
				_generateJSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.js');
				_generateJSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/dist/js/sb-admin-2.min.js');
			}

			// Eventually required JS
			_generateJSsInclude($customJSs);
		?>

	</head>
<!-- Header end -->
