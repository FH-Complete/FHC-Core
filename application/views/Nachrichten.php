<?php
$includesArray = array(
	'title' => 'Nachrichten',
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
	],
	'customJSs' => [
		#'vendor/npm-asset/primevue/tree/tree.min.js',
		#'vendor/npm-asset/primevue/toast/toast.min.js'
	],
	'customJSModules' => [
		'public/js/apps/Nachrichten.js'
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
		cis-root="<?= CIS_ROOT; ?>"
		:permissions="<?= htmlspecialchars(json_encode($permissions)); ?>"
		:config="<?=  htmlspecialchars(json_encode($configArray)); ?>"
	>
	</router-view>
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

