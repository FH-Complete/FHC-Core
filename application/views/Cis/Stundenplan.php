<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'primevue3' => true,
	'customJSModules' => ['public/js/apps/Cis/Stundenplan.js'],
	'customCSSs' => ['public/css/components/calendar.css']
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div id="content">
	
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
