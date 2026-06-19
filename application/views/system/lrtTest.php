<?php
	$includesArray = array(
		'title' => 'LRT Test Page',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'navigationcomponent' => true,
		'phrases' => array(
			'global',
			'ui'
		),
		'customJSModules' => array('public/js/apps/LRTTest.js'),
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">

		<!-- Navigation component -->
		<core-navigation-cmpt></core-navigation-cmpt>

		<div id="content"></div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

