<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/Stundenplan.js'],
	'customCSSs' => ['public/css/components/calendar.css']
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>Stundenplan</h2>
	<hr>
	<fhc-calendar :events="events" initial-mode="week" show-weeks></fhc-calendar>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
