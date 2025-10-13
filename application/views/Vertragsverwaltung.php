<?php
$includesArray = array(
	'title' => 'Vertragsverwaltung',
	'axios027' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'vue3' => true,
	'primevue3' => true,
	'filtercomponent' => true,
	'navigationcomponent' => true,
	'tabulator5' => true,
	'tinymce5' => true,
	'phrases' => array(
		'global',
		'ui',
	),
	'customCSSs' => [
		'public/css/components/vue-datepicker.css',
		'public/css/components/primevue.css',
		'public/css/Vertragsverwaltung.css',
		'public/css/components/Detailheader.css'
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

<?php
$configArray = [
	'domain' => !defined('DOMAIN') ? 'notDefined' : DOMAIN,
];
?>

<div id="main">
	<router-view
		:permissions="<?= htmlspecialchars(json_encode($permissions)); ?>"
		:config="<?=  htmlspecialchars(json_encode($configArray)); ?>"
	>
	</router-view>
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

