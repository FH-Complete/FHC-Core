<!-- Footer start -->
<?php
	if (! defined('BASEPATH')) exit('No direct script access allowed');

	// Defines the includes variables
	require('FHC-Common.php');

	// All the following variables are used only in this view

	// Retrieves the name of the index page, the URL path of the called controller and the called controller
	// NOTE: placed here because it doesn't work inside functions
	$indexPage = $this->config->item('index_page');
	$calledPath = $this->router->directory.$this->router->class;
	$calledMethod = $this->router->method;

	// By default set the parameters to null
	$customJSs = isset($customJSs) ? $customJSs : null;
	$customJSModules = isset($customJSModules) ? $customJSModules : null;

	// --------------------------------------------------------------------------------------------------------
	// Javascripts

	// Generates the global object to pass useful parameters to other javascripts
	// NOTE: must be called before any other JS include
	generateJSDataStorageObject($indexPage, $calledPath, $calledMethod);

	// Generates the global object to pass phrases to javascripts
	// NOTE: must be called before including the PhrasesLib.js
	if ($phrases != null) generateJSPhrasesStorageObject($phrases);

	// --------------------------------------------------------------------------------------------------------
	// From vendor folder

	// Axios V0.27
	if ($axios027 === true) generateJSsInclude('vendor/axios/axios/dist/axios.min.js');

	// Securimage JS
	if ($captcha3 === true) generateJSsInclude('vendor/dapphp/securimage/securimage.js');

	// jQuery V3
	if ($jquery3 === true) generateJSsInclude('vendor/components/jquery/jquery.min.js');

	// jQuery UI
	if ($jqueryui1 === true)
	{
		generateJSsInclude('vendor/components/jqueryui/jquery-ui.min.js');
		generateJSsInclude('vendor/components/jqueryui/ui/i18n/datepicker-de.js'); // datepicker german language file
	}

	// jQuery checkboxes
	// NOTE: keep it after jQuery includes
	if ($jquerycheckboxes1 === true) generateJSsInclude('vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js');

	// jQuery treetable
	// NOTE: keep it after jQuery includes
	if ($jquerytreetable3 === true) generateJSsInclude('vendor/ludo/jquery-treetable/jquery.treetable.js');

	// Bootstrap 3 JS
	// NOTE: to be kept after jQuery!
	if ($bootstrap3 === true) generateJSsInclude('vendor/twbs/bootstrap3/dist/js/bootstrap.min.js');

	// Bootstrap 5 JS
	if ($bootstrap5 === true) generateJSsInclude('vendor/twbs/bootstrap5/dist/js/bootstrap.min.js');

	// Moment JS
	if ($momentjs2 === true)
	{
		generateJSsInclude('vendor/moment/momentjs/min/moment.min.js');
		generateJSsInclude('vendor/moment/momentjs/locale/de-at.js');
		generateJSsInclude('vendor/moment/momentjs/locale/en-ie.js');
	}

	// PivotUI JS
	if ($pivotui2 === true) generateJSsInclude('vendor/nicolaskruchten/pivottable/dist/pivot.min.js');

	// SB Admin 2 template JS
	if ($sbadmintemplate3 === true)
	{
		generateJSsInclude('vendor/blackrockdigital/startbootstrap-sb-admin-2/vendor/metisMenu/metisMenu.min.js');
		generateJSsInclude('vendor/blackrockdigital/startbootstrap-sb-admin-2/dist/js/sb-admin-2.min.js');
		generateBackwardCompatibleJSMsIe('vendor/afarkas/html5shiv/dist/html5shiv.min.js');
		generateBackwardCompatibleJSMsIe('vendor/scottjehl/respond/dest/respond.min.js');
	}

	// Table sorter JS
	if ($tablesorter2 === true)
	{
		generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js');
		generateJSsInclude('vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js');
		generateJSsInclude('vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js');
	}

	// Tabulator 4 JS
	if ($tabulator4 === true)
	{
		generateJSsInclude('vendor/olifolkerd/tabulator4/dist/js/tabulator.min.js');
		generateJSsInclude('vendor/olifolkerd/tabulator4/dist/js/jquery_wrapper.min.js');
	}

	// Tabulator 5 JS
	if ($tabulator5 === true) generateJSsInclude('vendor/olifolkerd/tabulator5/dist/js/tabulator.min.js');

	// Tinymce 4 JS
	if ($tinymce4 === true) generateJSsInclude('vendor/tinymce/tinymce4/tinymce.min.js');

	// Tinymce 5 JS
	if ($tinymce5 === true) generateJSsInclude('vendor/tinymce/tinymce5/tinymce.min.js');

	// Vue 3 JS
	if ($vue3 === true) 
	{
		generateJSsInclude('vendor/vuejs/vuejs3/vue.global.prod.js');
		generateJSsInclude('vendor/vuejs/vuerouter4/vue-router.global.js');
	}

	// PrimeVue
	if ($primevue3)
	{
		generateJSsInclude('vendor/npm-asset/primevue/core/core.min.js');
		generateJSsInclude('vendor/npm-asset/primevue/organizationchart/organizationchart.min.js');
		generateJSsInclude('vendor/npm-asset/primevue/treetable/treetable.min.js');
		generateJSsInclude('vendor/npm-asset/primevue/column/column.min.js');
		generateJSsInclude('vendor/npm-asset/primevue/calendar/calendar.min.js');
		generateJSsInclude('vendor/npm-asset/primevue/skeleton/skeleton.min.js');
	}

	// --------------------------------------------------------------------------------------------------------
	// From public folder

	// DialogLib JS
	// NOTE: must be called before including others JS libraries that use it
	if ($dialoglib === true) generateJSsInclude('public/js/DialogLib.js');

	// AjaxLib JS
	// NOTE: must be called before including others JS libraries that use it
	if ($ajaxlib === true) generateJSsInclude('public/js/AjaxLib.js');

	// Bootstrapper include
	// NOTE: to be used only if you know what you are doing!
	if ($bootstrapper === true) generateJSsInclude('public/js/bootstrapper.js');

	// NavigationWidget JS
	if ($navigationwidget === true) generateJSsInclude('public/js/NavigationWidget.js');

	// FilterWidget JS
	if ($filterwidget === true) generateJSsInclude('public/js/FilterWidget.js');

	// PhrasesLib JS
	if ($phrases != null) generateJSsInclude('public/js/PhrasesLib.js');

	// TableWidget JS
	if ($tablewidget === true) generateJSsInclude('public/js/TableWidget.js');

	// User Defined Fields
	if ($udfs === true) generateJSsInclude('public/js/UDFWidget.js');

	// Load addon hooks JS
	// NOTE: keep it as the last but one
	if ($addons === true) generateAddonsJSsInclude($calledPath.'/'.$calledMethod);

	// Eventually required JS
	// NOTE: keep it as the latest
	generateJSsInclude($customJSs);
	generateJSModulesInclude($customJSModules);
?>
	</body>
</html>

<!-- Footer end -->

