<?php
$includesArray = array(
	'title' => 'RoomInformation',
	'customJSModules' => ['public/js/apps/Cis/RoomInformation.js'],
	'customCSSs' => ['public/css/components/calendar.css']
);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>

<div >
	<h2>Room Information: <?php echo $ort_kurzbz ?></h2>
	<hr>
	<div id="content">
	<room-information ort_kurzbz="<?php echo $ort_kurzbz ?>"></room-information>
	</div>
    
</div>

<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
