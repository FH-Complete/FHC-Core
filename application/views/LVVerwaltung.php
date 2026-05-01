<?php
	$includesArray = array(
		'title' => 'LVVerwaltung',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'primevue3' => true,
		'tabulator6' => true,
		'tinymce5' => true,
		'tags' => true,

		'customCSSs' => [
			'public/css/components/vue-datepicker.css',
			'public/css/components/primevue.css',
			'public/css/Studentenverwaltung.css',
			'public/css/Lvverwaltung.css'

		],
		'customJSModules' => [
			'public/js/apps/LVVerwaltung.js'
		]
	);

	$this->load->view('templates/FHC-Header', $includesArray);

?>
<div id="main">
	<router-view
		default-semester="<?= $variables['semester_aktuell']; ?>"
		lv-root="<?= site_url('LVVerwaltung'); ?>"
		:permissions="<?= htmlspecialchars(json_encode($permissions));?>"
		:config="<?=  htmlspecialchars(json_encode($configs)); ?>"
	>
	</router-view>

</div>
<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

