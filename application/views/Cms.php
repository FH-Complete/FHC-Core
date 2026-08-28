<?php
	$includesArray = array(
		'title'      => 'CMS',
		'axios027'   => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3'       => true,
		'primevue3'  => true,
		'tabulator6' => true,
		'tinymce5'   => true,
		'phrases'    => array('global', 'ui', 'cms'),
		'customCSSs' => [
			'public/css/components/verticalsplit.css',
			'public/css/components/horizontalsplit.css',
			'public/css/Cms.css'
		],
		'customJSs' => [
			'vendor/npm-asset/primevue/tree/tree.min.js',
			'vendor/npm-asset/primevue/checkbox/checkbox.min.js',
			'vendor/npm-asset/primevue/textarea/textarea.min.js',
			'vendor/moment/luxonjs/luxon.min.js'
		],
		'customJSModules' => ['public/js/apps/Cms.js']
	);
	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="main">
		<router-view
			cms-root="<?= site_url('cms'); ?>"
			auth-uid="<?= getAuthUID(); ?>"
			default-language="<?= defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : 'German'; ?>"
		></router-view>
	</div>
<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
