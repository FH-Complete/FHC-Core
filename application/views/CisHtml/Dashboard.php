<?php
$includesArray = array(
	'title' => 'Dashboard',
	'customJSModules' => ['public/js/apps/Dashboard/Fhc.js'],
	'customCSSs' => [
		'public/css/components/dashboard.css'
	],
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>Dashboard</h2>
	<hr>
	<fhc-dashboard dashboard="CIS"/>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>

