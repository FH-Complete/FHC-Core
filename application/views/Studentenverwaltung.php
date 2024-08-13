<?php
	$includesArray = array(
		'title' => 'Studentenverwaltung',
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
			'notiz',
		),
		'customCSSs' => [
			'public/css/components/vue-datepicker.css',
			'public/css/components/primevue.css',
			'public/css/Studentenverwaltung.css'
		],
		'customJSs' => [
			#'vendor/npm-asset/primevue/tree/tree.min.js',
			#'vendor/npm-asset/primevue/toast/toast.min.js'
		],
		'customJSModules' => [
			'public/js/apps/Studentenverwaltung.js'
		]
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

<?php
$configArray = [
	'generateAlias' => !defined('GENERATE_ALIAS_STUDENT') ? true : GENERATE_ALIAS_STUDENT,
	'showZgvDoktor' => !defined('ZGV_DOKTOR_ANZEIGEN') ? false : ZGV_DOKTOR_ANZEIGEN,
	'showZgvErfuellt' => !defined('ZGV_ERFUELLT_ANZEIGEN') ? false : ZGV_ERFUELLT_ANZEIGEN
];
?>

	<div id="main">
		<router-view
			default-semester="<?= $variables['semester_aktuell']; ?>"
			active-addons="<?= defined('ACTIVE_ADDONS') ? ACTIVE_ADDONS : ''; ?>"
			stv-root="<?= site_url('Studentenverwaltung'); ?>"
			cis-root="<?= CIS_ROOT; ?>"
			:permissions="<?= htmlspecialchars(json_encode($permissions)); ?>"
			:config="<?=  htmlspecialchars(json_encode($configArray)); ?>"
			>
		</router-view>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

