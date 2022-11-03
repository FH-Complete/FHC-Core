<?php
	if (! defined('BASEPATH')) exit('No direct script access allowed');

	// Defines the includes variables
	require('FHC-Common.php');

	// All the following variables are used only in this view
	// By default set the parameters to null
	$title = isset($title) ? $title : null;
	$refresh = isset($refresh) ? $refresh : null;
	$customCSSs = isset($customCSSs) ? $customCSSs : null;
?>
<!-- Header start -->

<!DOCTYPE HTML>
<html>
	<head>
		<title><?php printPageTitle($title); ?></title>

		<meta name="viewport" content="width=device-width, initial-scale=1">

		<meta charset="UTF-8">

		<?php printRefreshMeta($refresh); ?>

		<?php
			// --------------------------------------------------------------------------------------------------------
			// CSS

			// --------------------------------------------------------------------------------------------------------
			// From vendor folder

			// Securimage CSS
			if ($captcha3 === true) generateCSSsInclude('vendor/dapphp/securimage/securimage.css');

			// Font Awesome 4 CSS free icons
			if ($fontawesome4 === true) generateCSSsInclude('vendor/fortawesome/font-awesome4/css/font-awesome.min.css');

			// Font Awesome 6 CSS free icons
			if ($fontawesome6 === true)
			{
				generateCSSsInclude('vendor/fortawesome/font-awesome6/css/fontawesome.min.css');
				generateCSSsInclude('vendor/fortawesome/font-awesome6/css/solid.min.css');
				generateCSSsInclude('vendor/fortawesome/font-awesome6/css/brands.min.css');
			}

			// jQuery UI CSS
			if ($jqueryui1 === true) generateCSSsInclude('vendor/components/jqueryui/themes/base/jquery-ui.min.css');

			// jQuery treetable
			if ($jquerytreetable3 === true) generateCSSsInclude('vendor/ludo/jquery-treetable/css/jquery.treetable.css');

			// Bootstrap 3 CSS
			if ($bootstrap3 === true) generateCSSsInclude('vendor/twbs/bootstrap3/dist/css/bootstrap.min.css');

			// Bootstrap 5 CSS
			if ($bootstrap5 === true) generateCSSsInclude('vendor/twbs/bootstrap5/dist/css/bootstrap.min.css');

			// PivotUI CSS
			if ($pivotui2 === true) generateCSSsInclude('vendor/nicolaskruchten/pivottable/dist/pivot.min.css');

			// SB Admin 2 template CSS
			if ($sbadmintemplate3 === true)
			{
				generateCSSsInclude('vendor/blackrockdigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.css');
				generateCSSsInclude('vendor/blackrockdigital/startbootstrap-sb-admin-2/dist/css/sb-admin-2.min.css');
			}

			// Table sorter CSS
			if ($tablesorter2 === true) generateCSSsInclude('vendor/mottie/tablesorter/dist/css/theme.default.min.css');

			// Tabulator 4 CSS
			if ($tabulator4 === true)
			{
				generateCSSsInclude('vendor/olifolkerd/tabulator4/dist/css/bootstrap/tabulator_bootstrap.min.css');
				generateCSSsInclude('public/css/Tabulator.css');
			}

			// Tabulator 5 CSS
			if ($tabulator5 === true) generateCSSsInclude('vendor/olifolkerd/tabulator5/dist/css/tabulator_bootstrap5.min.css');

			// Tinymce 4 CSS
			if ($tinymce4 === true) generateCSSsInclude('public/css/TinyMCE4.css');

			// Tinymce 5 CSS
			if ($tinymce5 === true) generateCSSsInclude('public/css/TinyMCE5.css');

			// PrimeVUE
			if ($primevue3 === true)
			{
				generateCSSsInclude('vendor/npm-asset/primevue/resources/themes/bootstrap4-light-blue/theme.css');
				generateCSSsInclude('vendor/npm-asset/primevue/resources/primevue.min.css');
				// generateCSSsInclude('vendor/npm-asset/primevue/resources/primeflex.min.css');
				generateCSSsInclude('vendor/npm-asset/primeicons/primeicons.css');
			}

			// --------------------------------------------------------------------------------------------------------
			// From public folder

			// AjaxLib CSS
			if ($ajaxlib === true) generateCSSsInclude('public/css/AjaxLib.css');

			// DialogLib CSS
			if ($dialoglib === true) generateCSSsInclude('public/css/DialogLib.css');

			// VUE FilterWidget CSS
			if ($filtercomponent === true) generateCSSsInclude('public/css/components/FilterComponent.css');

			// FilterWidget CSS
			if ($filterwidget === true) generateCSSsInclude('public/css/FilterWidget.css');

			// VUE NavigationWidget CSS
			if ($navigationcomponent === true) generateCSSsInclude('public/css/components/NavigationComponent.css');

			// NavigationWidget CSS
			if ($navigationwidget === true) generateCSSsInclude('public/css/NavigationWidget.css');

			// HTML Widget CSS
			if ($widgets === true) generateCSSsInclude('public/css/Widgets.css');

			// Eventually required CSS
			generateCSSsInclude($customCSSs); // Eventually required CSS
		?>
	</head>
	<body>

<!-- Header end -->

