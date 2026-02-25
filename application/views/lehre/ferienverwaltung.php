<?php
	$includesArray = array(
		'title' => 'Ferienverwaltung',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'filtercomponent' => true,
		'navigationcomponent' => true,
		'tabulator6' => true,
		'primevue3' => true,
		//'vuedatepicker11' => true,
		'customJSModules' => array('public/js/apps/lehre/Ferienverwaltung/Ferienverwaltung.js'),
		'customCSSs' => array('vendor/vuejs/vuedatepicker_css/main.css')
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">

		<div id="content">
			<div>
				<ferienverwaltung></ferienverwaltung>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

