<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

// Retrives the name of the index page, the URL path of the called controller and the called controller
// NOTE: placed here because it doesn't work inside functions
$indexPage = $this->config->item('index_page');
$calledPath = $this->router->directory.$this->router->class;
$calledMethod = $this->router->method;

// By default set the parameters to null
$title = isset($title) ? $title : null;
$customCSSs = isset($customCSSs) ? $customCSSs : null;
$customJSs = isset($customJSs) ? $customJSs : null;
$phrases = isset($phrases) ? $phrases : null;

// By default set the parameters to false
$jquery = isset($jquery) ? $jquery : false;
$jqueryui = isset($jqueryui) ? $jqueryui : false;
$ajaxlib = isset($ajaxlib) ? $ajaxlib : false;
$bootstrap = isset($bootstrap) ? $bootstrap : false;
$fontawesome = isset($fontawesome) ? $fontawesome : false;
$tablesorter = isset($tablesorter) ? $tablesorter : false;
$tinymce = isset($tinymce) ? $tinymce : false;
$sbadmintemplate = isset($sbadmintemplate) ? $sbadmintemplate : false;
$addons = isset($addons) ? $addons : false;
$filterwidget = isset($filterwidget) ? $filterwidget : false;
$navigationwidget = isset($navigationwidget) ? $navigationwidget : false;

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
 * Generates global JS-Object to pass parms to other javascripts
 */
function _generateJSDataStorageObject($indexPage, $calledPath, $calledMethod)
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
function _generateJSPhrasesStorageObject($phrases)
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

/**
 * Generates all the includes needed by the Addons
 */
function _generateAddonsJSsInclude($calledFrom)
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
					_generateJSsInclude('addons/'.$addon.'/'.$js_file);
			}
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

			// Bootstrap CSS
			if ($bootstrap === true) _generateCSSsInclude('vendor/twbs/bootstrap/dist/css/bootstrap.min.css');

			// Font Awesome CSS
			if ($fontawesome === true) _generateCSSsInclude('vendor/components/font-awesome/css/font-awesome.min.css');

			// AjaxLib CSS
			if ($ajaxlib === true) _generateCSSsInclude('public/css/AjaxLib.css');

			// Table sorter CSS
			if ($tablesorter === true)
			{
				_generateCSSsInclude('vendor/mottie/tablesorter/dist/css/theme.default.min.css');
				_generateCSSsInclude('vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css');
			}

			// SB Admin 2 template CSS
			if ($sbadmintemplate === true)
			{
				_generateCSSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.css');
				_generateCSSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/dist/css/sb-admin-2.min.css');
			}

			// FilterWidget CSS
			if ($filterwidget === true) _generateCSSsInclude('public/css/FilterWidget.css');

			// NavigationWidget CSS
			if ($navigationwidget === true) _generateCSSsInclude('public/css/NavigationWidget.css');

			// Eventually required CSS
			_generateCSSsInclude($customCSSs); // Eventually required CSS


			// --------------------------------------------------------------------------------------------------------
			// Javascripts

			// Generates the global object to pass useful parameters to other javascripts
			// NOTE: must be called before any other JS include
			_generateJSDataStorageObject($indexPage, $calledPath, $calledMethod);

			// Generates the global object to pass phrases to javascripts
			// NOTE: must be called before including the PhrasesLib.js
			_generateJSPhrasesStorageObject($phrases);

			// JQuery V3
			if ($jquery === true) _generateJSsInclude('vendor/components/jquery/jquery.min.js');

			// JQuery UI
			if ($jqueryui === true)
			{
				_generateJSsInclude('vendor/components/jqueryui/jquery-ui.min.js');
				_generateJSsInclude('vendor/components/jqueryui/ui/i18n/datepicker-de.js'); // datepicker german language file
			}

			// Bootstrap JS
			if ($bootstrap === true) _generateJSsInclude('vendor/twbs/bootstrap/dist/js/bootstrap.min.js');

			// Table sorter JS
			if ($tablesorter === true)
			{
				_generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js');
				_generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js');
				_generateJSsInclude('vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js');
			}

			// Tinymce JS
			if($tinymce === true) _generateJSsInclude('vendor/tinymce/tinymce/tinymce.min.js') ;

			// SB Admin 2 template JS
			if ($sbadmintemplate === true)
			{
				_generateJSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.js');
				_generateJSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/dist/js/sb-admin-2.min.js');
			}

			// AjaxLib JS
			// NOTE: must be called before including others JS libraries that use it
			if ($ajaxlib === true) _generateJSsInclude('public/js/AjaxLib.js');

			// PhrasesLib JS
			if ($phrases != null) _generateJSsInclude('public/js/PhrasesLib.js');

			// FilterWidget JS
			if($filterwidget === true) _generateJSsInclude('public/js/FilterWidget.js') ;

			// NavigationWidget JS
			if($navigationwidget === true) _generateJSsInclude('public/js/NavigationWidget.js') ;

			// Load addon hooks JS
			if ($addons === true) _generateAddonsJSsInclude($calledPath.'/'.$calledMethod);

			// Eventually required JS
			_generateJSsInclude($customJSs);
		?>

	</head>
<!-- Header end -->
