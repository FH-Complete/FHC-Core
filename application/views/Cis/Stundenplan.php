<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/Stundenplan.js'],
	'customCSSs' => ['public/css/components/calendar.css']
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div >
	<h2>Stundenplan</h2>
	<hr>
	<div id="content"></div>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
