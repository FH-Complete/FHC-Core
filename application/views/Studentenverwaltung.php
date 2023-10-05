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
		'customJSs' => [
			'vendor/npm-asset/primevue/toast/toast.min.js'
		],
		'customJSModules' => [
			'public/js/apps/Studentenverwaltung.js'
		]
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">
		<router-view
			default-semester="<?= $variables['semester_aktuell']; ?>"
			active-addons="<?= defined('ACTIVE_ADDONS') ? ACTIVE_ADDONS : ''; ?>"
			cis-root="<?= CIS_ROOT; ?>"
			:permissions="<?= htmlspecialchars(json_encode($permissions)); ?>"
			:config="<?= htmlspecialchars(json_encode(['generateAlias' => !defined('GENERATE_ALIAS_STUDENT') ? true : GENERATE_ALIAS_STUDENT])); ?>"
			>
		</router-view>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

