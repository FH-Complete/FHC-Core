<?php
$includesArray = array(
	'title' => 'LV Template Übersicht',
	'vue3' => true,
	'axios027' => true,
	'bootstrap5' => true,
	'tabulator5' => true,
	'fontawesome6' => true,
	'primevue3' => true,
	'navigationcomponent' => true,
	'filtercomponent' => true,
	'fhcApps' => array('lehre/lvplanung/LvTemplates'),
	'customCSSs' => array(
	    'public/css/Fhc.css',
	    'public/css/lvTemplateUebersicht.css'
	)
);

$this->load->view('templates/FHC-Header', $includesArray);
?>

<div id="main">
    <!-- Navigation component -->
    <core-navigation-cmpt></core-navigation-cmpt>

    <lv-template-uebersicht></lv-template-uebersicht>
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
