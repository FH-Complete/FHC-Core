<?php
	$includesArray = array(
		'title' => 'Infoterminal',
		'axios027' => true,
		'vue3' => true,
		'primevue3' => true,
		'bootstrap5' => true,
		'tabulator5' => true,
		'vuedatepicker11' => true,
		'fontawesome6' => true,
		'navigationcomponent' => true,
		'phrases' => array(
			'global',
			'ui',
			'lehre'
		),
		'customCSSs' => [
			'public/css/components/calendar.css'
		],
		'customJSs' => [
			'vendor/moment/luxonjs/luxon.min.js',
		],
		'customJSModules' => [
			'public/js/apps/Cis/InfoTerminalApp.js'
		]
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>
	<div id="main">
		<router-view
			:logo-url="<?= htmlspecialchars(json_encode(base_url($this->config->item('theme_logo'))), ENT_QUOTES, 'UTF-8'); ?>"			
			active-addons="<?= defined('ACTIVE_ADDONS') ? ACTIVE_ADDONS : ''; ?>"
			tempus-root="<?= site_url('Tempus'); ?>"
			avatar-url="<?= site_url('Cis/Pub/bild/person/' . getAuthPersonId()); ?>"
			logout-url="<?= site_url('Cis/Auth/logout'); ?>"
			>
		</router-view>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
