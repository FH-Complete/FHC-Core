<?php
$includesArray = array(
	'title' => ucfirst($this->p->t('ui', 'roomManagerPageTitle')),
	'vue3' => true,
	'axios027' => true,
	'bootstrap5' => true,
	'tabulator5' => true,
	'fontawesome6' => true,
	'primevue3' => true,
	'navigationcomponent' => true,
	'filtercomponent' => true,
	'vuedatepicker11' => true,
    'customJSs' => array(
		'vendor/moment/luxonjs/luxon.min.js'
	),
	'customJSModules' => array(
		'public/js/apps/RoomManagerApp.js'
	),
	'customCSSs' => array(
        'public/css/components/primevue.css',
		'public/css/components/verticalsplit.css',
		'public/extensions/FHC-Core-Developer/css/FhcMain.css',
		'public/css/components/calendar.css',
		'public/css/components/vue-datepicker.css',
		'public/css/roomManagerOverview.css'
	)
);

$this->load->view('templates/FHC-Header', $includesArray);
?>

<div id="main">
	<core-navigation-cmpt></core-navigation-cmpt>
	<router-view
		cis-root="<?= CIS_ROOT; ?>"
		:permissions="<?= htmlspecialchars(json_encode($permissions)); ?>"
	>
	</router-view>
</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>