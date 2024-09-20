<?php
$sitesettings = array(
	'title' => 'Antrag Wiederholung vom Studium',
	'cis' => true,
	'vue3' => true,
	'axios027' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'tabulator5' => true,
	'phrases' => array(
		'ui',
		'lehre',
		'global'
	),
	'customJSModules' => array('public/js/apps/lehre/Antrag/Lvzuweisung.js'),
	'customCSSs' => array(
		'public/css/Fhc.css',
		'public/css/components/primevue.css',
	),
	'customJSs' => array(
	)
);

$this->load->view(
	'templates/FHC-Header',
	$sitesettings
);
?>

<div id="wrapper" class="overflow-hidden">
	<div class="fhc-header" v-if="notinframe">
		<h1 class="h2"><?= $this->p->t('studierendenantrag', 'title_lvzuweisen', ['name' => $antrag->name]);?></h1>
	</div>
	<div class="fhc-container row mt-3">
		<lv-zuweisung :antrag-id="<?= $antrag_id; ?>" initial-status-code="<?= $antrag->status; ?>" initial-status-msg="<?= $antrag->statustyp; ?>"<?= ($antrag->status != Studierendenantragstatus_model::STATUS_CREATED && $antrag->status != Studierendenantragstatus_model::STATUS_LVSASSIGNED) ? ' disabled' : ''; ?>></lv-zuweisung>
	</div>
</div>

<?php
$this->load->view(
	'templates/FHC-Footer',
	$sitesettings
);
