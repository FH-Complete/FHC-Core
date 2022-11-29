<?php
$includesArray = array(
	'customJSModules' => ['public/js/apps/Dashboard/Fhc.js'],
	'customCSSs' => [
		'public/css/components/dashboard.css'
	],
);

$this->load->view('templates/CIS-Header', $includesArray);
?>

<div id="content">
	<fhc-dashboard dashboard="CIS"/>
</div>

<?php $this->load->view('templates/CIS-Footer', $includesArray); ?>

