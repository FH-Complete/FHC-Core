<?php
	$includesArray = array(
		'title' => 'Bank data',
		'cis' => true,
		'vue3' => true,
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'phrases' => array(),
		'primevue3' => true,
		'customJSModules' => array('public/js/apps/Cis/BankData.js'),
		'customCSSs' => array(
			'public/css/Fhc.css',
			'public/css/components/primevue.css',
			'public/css/components/FormUnderline.css'
		)
	);

	if (defined('CIS4'))
	{
		$this->load->view('templates/CISVUE-Header', $includesArray);
	}
	else
	{
		$this->load->view('templates/FHC-Header', $includesArray);
	}
?>

	<div id="content"></div>

<?php
	if (defined('CIS4'))
	{
		$this->load->view('templates/CISVUE-Footer', $includesArray);
	}
	else
	{
		$this->load->view('templates/FHC-Footer', $includesArray);
	}
?>

