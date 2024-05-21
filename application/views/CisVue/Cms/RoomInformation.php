<?php
$includesArray = array(
	'title' => 'RoomInformation',
	'customJSModules' => ['public/js/apps/Cis/RoomInformation.js'],
	'customCSSs' => ['public/css/components/calendar.css']
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div >
	<h2>Room Information</h2>
	<hr>
    <div id="content"></div>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
