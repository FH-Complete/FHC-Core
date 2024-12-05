<?php
$includesArray = array(
	'title' => 'Dashboard',
	'tabulator5'=>true,
	'primevue3' => true,
	'customJSModules' => [
		'public/js/apps/Dashboard/Fhc.js'
	],
	'customJSs' => [
		'vendor/npm-asset/primevue/accordion/accordion.js',
		'vendor/npm-asset/primevue/accordiontab/accordiontab.js'
	],
	'customCSSs' => [
		'public/css/components/dashboard.css'
	],
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	<fhc-dashboard dashboard="CIS" view-data-string='<?php echo json_encode($viewData) ?>' />
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>

