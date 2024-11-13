<?php
$includesArray = array(
	'title' => 'Dashboard',
	'tabulator5'=>true,
	'primevue3' => true,
	'customJSModules' => ['public/js/apps/Dashboard/Fhc.js'],
	'customCSSs' => [
		'public/css/components/dashboard.css'
	],
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	<h2>Hallo <?= $name?>!</h2>
	<hr>
	<fhc-dashboard dashboard="CIS"/>
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>

