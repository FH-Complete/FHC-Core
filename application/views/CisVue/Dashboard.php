<?php
$includesArray = array(
	'customJSModules' => ['public/js/apps/Dashboard/Fhc.js'],
	'customCSSs' => [
		'public/css/components/dashboard.css'
	],
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	<h2>Dashboard</h2>
	<hr>
	<fhc-dashboard dashboard="CIS"/>
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>

