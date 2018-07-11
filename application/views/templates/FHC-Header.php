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
?>

<!-- Header start -->
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php printPageTitle($title); ?></title>

		<meta charset="UTF-8">

		<?php
			// --------------------------------------------------------------------------------------------------------
			// CSS

			// jQuery UI CSS
			if ($jqueryui === true) generateCSSsInclude('vendor/components/jqueryui/themes/base/jquery-ui.min.css');

			// Bootstrap CSS
			if ($bootstrap === true) generateCSSsInclude('vendor/twbs/bootstrap/dist/css/bootstrap.min.css');

			// Font Awesome CSS
			if ($fontawesome === true) generateCSSsInclude('vendor/components/font-awesome/css/font-awesome.min.css');

			// AjaxLib CSS
			if ($ajaxlib === true) generateCSSsInclude('public/css/AjaxLib.css');

			// Table sorter CSS
			if ($tablesorter === true)
			{
				generateCSSsInclude('vendor/mottie/tablesorter/dist/css/theme.default.min.css');
				generateCSSsInclude('vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css');
			}

			// SB Admin 2 template CSS
			if ($sbadmintemplate === true)
			{
				generateCSSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.css');
				generateCSSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/dist/css/sb-admin-2.min.css');
			}

			// FilterWidget CSS
			if ($filterwidget === true) generateCSSsInclude('public/css/FilterWidget.css');

			// NavigationWidget CSS
			if ($navigationwidget === true) generateCSSsInclude('public/css/NavigationWidget.css');

			// Eventually required CSS
			generateCSSsInclude($customCSSs); // Eventually required CSS


			// --------------------------------------------------------------------------------------------------------
			// Javascripts

			// Generates the global object to pass useful parameters to other javascripts
			// NOTE: must be called before any other JS include
			generateJSDataStorageObject($indexPage, $calledPath, $calledMethod);

			// Generates the global object to pass phrases to javascripts
			// NOTE: must be called before including the PhrasesLib.js
			if ($phrases != null) generateJSPhrasesStorageObject($phrases);

			// JQuery V3
			if ($jquery === true) generateJSsInclude('vendor/components/jquery/jquery.min.js');

			// JQuery UI
			if ($jqueryui === true)
			{
				generateJSsInclude('vendor/components/jqueryui/jquery-ui.min.js');
				generateJSsInclude('vendor/components/jqueryui/ui/i18n/datepicker-de.js'); // datepicker german language file
			}

			// Bootstrap JS
			if ($bootstrap === true) generateJSsInclude('vendor/twbs/bootstrap/dist/js/bootstrap.min.js');

			// Table sorter JS
			if ($tablesorter === true)
			{
				generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js');
				generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js');
				generateJSsInclude('vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js');
			}

			// Tinymce JS
			if($tinymce === true) generateJSsInclude('vendor/tinymce/tinymce/tinymce.min.js') ;

			// SB Admin 2 template JS
			if ($sbadmintemplate === true)
			{
				generateJSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.js');
				generateJSsInclude('vendor/BlackrockDigital/startbootstrap-sb-admin-2/dist/js/sb-admin-2.min.js');
				backwardCompatibleJSMsIe();
			}

			// AjaxLib JS
			// NOTE: must be called before including others JS libraries that use it
			if ($ajaxlib === true) generateJSsInclude('public/js/AjaxLib.js');

			// PhrasesLib JS
			if ($phrases != null) generateJSsInclude('public/js/PhrasesLib.js');

			// FilterWidget JS
			if($filterwidget === true) generateJSsInclude('public/js/FilterWidget.js') ;

			// NavigationWidget JS
			if($navigationwidget === true) generateJSsInclude('public/js/NavigationWidget.js') ;

			// Load addon hooks JS
			if ($addons === true) generateAddonsJSsInclude($calledPath.'/'.$calledMethod);

			// Eventually required JS
			generateJSsInclude($customJSs);
		?>

	</head>
<!-- Header end -->
