<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'FH-Complete',
		'bootstrap5' => true,
		'fontawesome6' => true,
		'axios027' => true,
		'restclient' => true,
		'vue3' => true,
		'tabulator6' => true,
		'primevue3' => true,
		'vuedatepicker11' => true,
		'customJSs' => [
			'vendor/moment/luxonjs/luxon.min.js'
		],
		'customJSModules' => ['public/js/apps/Dashboard/WidgetAdmin.js'],
		'customCSSs' => [
			'public/css/Fhc.css',
			'public/css/components/verticalsplit.css',
			'public/css/components/dashboard.css',
			'public/css/components/primevue.css',
			'public/css/components/vue-datepicker.css',
		],
		'navigationcomponent' => true
	)
);
?>

	<div id="main"></div>

<?php $this->load->view('templates/FHC-Footer'); ?>
