<?php
$includesArray = array(
	'title' => 'Vertragsverwaltung',
	'axios027' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'vue3' => true,
	'primevue3' => true,
	#'filtercomponent' => true,
	'tabulator5' => true,
	'tinymce5' => true,
	'phrases' => array(
		'global',
		'ui',
	),
	'customCSSs' => [
		'public/css/components/vue-datepicker.css',
		'public/css/components/primevue.css',
/*		'public/css/Vertragsverwaltung.css'*/
	],
	'customJSs' => [
		#'vendor/npm-asset/primevue/tree/tree.min.js',
		#'vendor/npm-asset/primevue/toast/toast.min.js'
	],
	'customJSModules' => [
		'public/js/apps/Vertragsverwaltung.js'
	]
);

$this->load->view('templates/FHC-Header', $includesArray);
?>

<div id="main">
	<router-view
		stv-root="<?= site_url('Vertragsverwaltung'); ?>"
		cis-root="<?= CIS_ROOT; ?>"
	>
	</router-view>
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

