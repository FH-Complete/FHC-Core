<?php
	// All the following global variables are used in the FHC-Header and the FHC-Footer views

	// By default set the parameters to null
	$phrases = isset($phrases) ? $phrases : null;
	
	// By default set the parameters to false

	// External resources
	$axios027 = isset($axios027) ? $axios027 : false;
	$bootstrap3 = isset($bootstrap3) ? $bootstrap3 : false;
	$bootstrap5 = isset($bootstrap5) ? $bootstrap5 : false;
	$captcha3 = isset($captcha3) ? $captcha3 : false;
	$fontawesome4 = isset($fontawesome4) ? $fontawesome4 : false;
	$fontawesome6 = isset($fontawesome6) ? $fontawesome6 : false;
	$jquery3 = isset($jquery3) ? $jquery3 : false;
	$jqueryui1 = isset($jqueryui1) ? $jqueryui1 : false;
	$jquerycheckboxes1 = isset($jquerycheckboxes1) ? $jquerycheckboxes1 : false;
	$jquerytreetable3 = isset($jquerytreetable3) ? $jquerytreetable3 : false;
	$momentjs2 = isset($momentjs2) ? $momentjs2 : false;
	$pivotui2 = isset($pivotui2) ? $pivotui2 : false;
	$sbadmintemplate3 = isset($sbadmintemplate3) ? $sbadmintemplate3 : false;
	$tablesorter2 = isset($tablesorter2) ? $tablesorter2 : false;
	$tabulator4 = isset($tabulator4) ? $tabulator4 : false;
	$tabulator5 = isset($tabulator5) ? $tabulator5 : false;
	$tabulator6 = isset($tabulator6) ? $tabulator6 : false;
	$tabulator5JQuery = isset($tabulator5JQuery) ? $tabulator5JQuery : false;
	$tinymce3 = isset($tinymce3) ? $tinymce3 : false;
	$tinymce5 = isset($tinymce5) ? $tinymce5 : false;
	$vue3 = isset($vue3) ? $vue3 : false;
	$primevue3 = isset($primevue3) ? $primevue3 : false;
	$vuedatepicker11 = isset($vuedatepicker11) ? $vuedatepicker11 : false;

	// Hooks
	$addons = isset($addons) ? $addons : false;

	// Internal resources
	$ajaxlib = isset($ajaxlib) ? $ajaxlib : false;
	$bootstrapper = isset($bootstrapper) ? $bootstrapper : false;
	$cis = isset($cis) ? $cis : false;
	$dialoglib = isset($dialoglib) ? $dialoglib : false;
	$filtercomponent = isset($filtercomponent) ? $filtercomponent : false;
	$filterwidget = isset($filterwidget) ? $filterwidget : false;
	$navigationcomponent = isset($navigationcomponent) ? $navigationcomponent : false;
	$navigationwidget = isset($navigationwidget) ? $navigationwidget : false;
	$tablecomponent = isset($tablecomponent) ? $tablecomponent : false;
	$tablewidget = isset($tablewidget) ? $tablewidget : false;
	$udfs = isset($udfs) ? $udfs : false;
	$widgets = isset($widgets) ? $widgets : false;
	$tags = isset($tags) ? $tags : false;

// VueJs App magic
if (isset($fhcApps)) {
	if (!isset($customJSs))
		$customJSs = ['public/js/FhcApps.js'];
	elseif (!is_array($customJSs))
		$customJSs = [$customJSs, 'public/js/FhcApps.js'];
	else
		array_push($customJSs, 'public/js/FhcApps.js');

	if (!isset($customJSModules))
		$customJSModules = [];
	elseif (!is_array($customJSModules))
		$customJSModules = [$customJSModules];

	if (!isset($customCSSs))
		$customCSSs = [];
	elseif (!is_array($customCSSs))
		$customCSSs = [$customCSSs];

	$ext_path = 'public/extensions/';
	$ext_realpath = str_replace('/', DIRECTORY_SEPARATOR, $ext_path);

	foreach ($fhcApps as $app) {
		if (!strstr($app, ':')) {
			$app_js_path = 'public/js/apps/' . $app . '.js';
			$app_css_path = 'public/css/apps/' . $app . '.css';
			$app_js_ext_path = '/js/extend_app/' . $app . '.js';
			$app_css_ext_path = '/css/extend_app/' . $app . '.css';
		} else {
			list($ext_name, $app_path) = explode(':', $app);
			$app_js_path = 'public/extensions/' . $ext_name . '/js/apps/' . $app_path . '.js';
			$app_css_path = 'public/extensions/' . $ext_name . '/css/apps/' . $app_path . '.css';
			$app_js_ext_path = '/js/extend_app/extensions/' . $ext_name . '/' . $app_path . '.js';
			$app_css_ext_path = '/css/extend_app/extensions/' . $ext_name . '/' . $app_path . '.css';
		}

		if (file_exists(FHCPATH . str_replace('/', DIRECTORY_SEPARATOR, $app_css_path)))
			array_push($customCSSs, $app_css_path);

		foreach (scandir(FHCPATH . $ext_realpath) as $extension_name) {
			if ($extension_name[0] == '.')
				continue;
			
			$app_js_ext_realpath = str_replace('/', DIRECTORY_SEPARATOR, $app_js_ext_path);
			if (file_exists(FHCPATH . $ext_realpath . $extension_name . $app_js_ext_realpath)) {
				array_push($customJSModules, $ext_path . $extension_name . $app_js_ext_path);
			}
			$app_css_ext_realpath = str_replace('/', DIRECTORY_SEPARATOR, $app_css_ext_path);
			if (file_exists(FHCPATH . $ext_realpath . $extension_name . $app_css_ext_realpath)) {
				array_push($customCSSs, $ext_path . $extension_name . $app_css_ext_path);
			}
		}
		
		array_push($customJSModules, $app_js_path);
	}
}
