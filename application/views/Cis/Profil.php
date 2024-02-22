<?php
$includesArray = array(
	'title' => 'Stundenplan',
	'customJSModules' => ['public/js/apps/Cis/Profil.js'],
	'tabulator5' => true,
	'primevue3' => true,
	'customCSSs' => ['public/css/components/calendar.css', 'public/css/components/FilterComponent.css','public/css/components/Profil.css','public/css/components/FormUnderline.css'],
	
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content" >

</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>
