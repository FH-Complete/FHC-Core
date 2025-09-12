<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'FH-Complete',
		'bootstrap5' => true,
		'fontawesome6' => true,
		'axios027' => true,
		'restclient' => true,
		'vue3' => true,
		'customJSModules' => ['public/js/apps/Dashboard.js'],
		'customCSSs' => [
			'public/css/components/dashboard.css'
		],
		'navigationcomponent' => true
	)
);
?>

	<div id="main">

		<core-navigation-cmpt :add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>

		<div id="content">
			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
				<h1 class="h2">Dashboard</h1>
			</div>
			<core-dashboard dashboard="CIS" apiurl="<?= site_url('dashboard'); ?>"></core-dashboard>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer'); ?>
