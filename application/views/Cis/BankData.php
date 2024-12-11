<?php
	$includesArray = array(
		'title' => 'Bank data',
		'primevue3' => true,
		'customJSModules' => ['public/js/apps/Cis/BankData.js'],
		'customCSSs' => ['public/css/components/FormUnderline.css']
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

