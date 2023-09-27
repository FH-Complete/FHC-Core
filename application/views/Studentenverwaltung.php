<?php
	$includesArray = array(
		'title' => 'Studentenverwaltung',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'primevue3' => true,
		'filtercomponent' => true,
		'tabulator5' => true,
		'phrases' => [],
		'customCSSs' => [
			'public/css/Studentenverwaltung.css',
			'public/css/components/vue-datepicker.css'
		],
		'customJSModules' => [
			'public/js/apps/Studentenverwaltung.js'
		]
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">
		<router-view></router-view>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

