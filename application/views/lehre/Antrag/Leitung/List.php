<?php

use \DateTime as DateTime;

$sitesettings = array(
	'title' => 'Anträge auf Änderung des Studierendenstatus',
	'cis' => true,
	'vue3' => true,
	'axios027' => true,
	'bootstrap5' => true,
	'tabulator5' => true,
	'fontawesome6' => true,
	'primevue3' => true,
	'phrases' => array(
		'global',
		'ui',
		'studierendenantrag',
		'lehre',
		'person',
	),
	'customJSModules' => array('public/js/apps/lehre/Antrag/Leitung.js'),
	'customCSSs' => array(
		'public/css/Fhc.css'
	),
	'customJSs' => array(
	)
);

$this->load->view(
	'templates/FHC-Header',
	$sitesettings
);
?>

<div id="wrapper">
	<div class="fhc-header">
		<h1><?= $this->p->t('studierendenantrag', 'antrag_header'); ?></h1>
	</div>

	<div class="fhc-container row">
		<div class="col-xs-8">

			<studierendenantrag-leitung
				:stg-a="<?= htmlspecialchars(json_encode(array_values($stgA))); ?>"
				:stg-l="<?= htmlspecialchars(json_encode(array_values($stgL))); ?>"
				>
			</studierendenantrag-leitung>

		</div>
		<div class="col-xs-4">
		</div>
	</div>
</div>

<?php
$this->load->view(
	'templates/FHC-Footer',
	$sitesettings
);
