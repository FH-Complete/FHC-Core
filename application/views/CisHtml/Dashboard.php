<?php
$includesArray = array(
	'title' => 'Dashboard',
	'tabulator5'=>true,
	'customJSModules' => ['public/js/apps/Dashboard/Fhc.js'],
	'customCSSs' => [
		'public/css/components/dashboard.css'
	],
);

$this->load->view('templates/CISHTML-Header', $includesArray);
?>

<div id="content">
	<h2>Dashboard</h2>
	<hr>
	<fhc-dashboard active_addons="<?php echo ACTIVE_ADDONS ?>" 
	mail_studierende="<?php echo !defined('CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN ?>" dashboard="CIS"/>
</div>

<?php $this->load->view('templates/CISHTML-Footer', $includesArray); ?>

