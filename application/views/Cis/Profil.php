<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/Profil.js'],
	'tabulator5' => true,
	'customCSSs' => ['public/css/components/calendar.css', 'public/css/components/FilterComponent.css','public/css/components/Profil.css'],
	
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content" >

</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
