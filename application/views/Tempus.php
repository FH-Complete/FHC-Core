<?php
	$includesArray = array(
		'title' => 'Tempus',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'vuedatepicker11' => true,
		'primevue3' => true,
		'tabulator5' => true,
		'phrases' => array(
			'global',
			'ui',
			'notiz',
		),
		'customCSSs' => [
			'public/css/components/vue-datepicker.css',
			'public/css/components/primevue.css',
			'public/css/components/calendar.css',
			'public/css/Tempus.css'
		],
		'customJSs' => [
			#'vendor/npm-asset/primevue/tree/tree.min.js',
			#'vendor/npm-asset/primevue/toast/toast.min.js'
			'vendor/moment/luxonjs/luxon.min.js'
		],
		'customJSModules' => [
			'public/js/apps/Tempus.js'
		]
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="main">
		<router-view
			default-semester="<?= $variables['semester_aktuell']; ?>"
			active-addons="<?= defined('ACTIVE_ADDONS') ? ACTIVE_ADDONS : ''; ?>"
			tempus-root="<?= site_url('Tempus'); ?>"
			cis-root="<?= CIS_ROOT; ?>"
			:permissions="<?= htmlspecialchars(json_encode($permissions)); ?>"
			:config="<?=  htmlspecialchars(json_encode($variables)); ?>"
			>
		</router-view>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
