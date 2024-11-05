<?php
$sitesettings = array(
	'title' => 'Antrag auf Änderung des Studierendenstatus',
	'cis' => true,
	'vue3' => true,
	'axios027' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'phrases' => array(
	),
	'customJSModules' => array('public/js/apps/lehre/Antrag.js'),
	'customCSSs' => array(
		'public/css/Fhc.css',
		'public/css/components/primevue.css',
		'vendor/vuejs/vuedatepicker_css/main.css'
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
		<h1 class="h2"><?= $this->p->t('studierendenantrag', 'antrag_header'); ?></h1>
	</div>

	<div class="fhc-container row">
		<div class="col-sm-8 mb-3">
			<studierendenantrag-antrag
				:prestudent-id="<?= $prestudent_id; ?>"
				antrag-type="<?= $antrag_type; ?>"
				:studierendenantrag-id="<?= $studierendenantrag_id ?: 'undefined'; ?>"
				v-model:info-array="infoArray"
				v-model:status-msg="status.msg"
				v-model:status-severity="status.severity"
				>
			</studierendenantrag-antrag>
		</div>
		<div class="col-sm-4 mb-3">
			<studierendenantrag-status :msg="status.msg" :severity="status.severity"></studierendenantrag-status>
			<studierendenantrag-infoblock :infos="infoArray"></studierendenantrag-infoblock>
		</div>
	</div>
</div>

<?php
$this->load->view(
	'templates/FHC-Footer',
	$sitesettings
);