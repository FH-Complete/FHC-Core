<?php
	$includesArray = array(
		'title' => 'Test VBform',
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'customJSModules' => array('public/js/apps/vbform/vbform.js')
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main"></div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

