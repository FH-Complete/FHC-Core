<?php

$includesArray = array(
	'title' => 'Cis4',
	'axios027' => true,
	'bootstrap5' => true,
	'fontawesome6' => true,
	'tabulator5' => true, // TODO: upgrade to 6 when available
	'vue3' => true,
	'primevue3' => true,
	'skipID' => '#fhccontent',
	'vuedatepicker11' => true,
	'customCSSs' => array(
		'public/css/components/verticalsplit.css',
		'public/css/components/FilterComponent.css',
		'public/css/components/FormUnderline.css',
		'public/css/theme/default.css',
		'public/css/components/abgabetool/abgabe.css'
	),
	'customJSs' => array(
		'vendor/npm-asset/primevue/accordion/accordion.min.js',
		'vendor/npm-asset/primevue/accordiontab/accordiontab.min.js',
		'vendor/npm-asset/primevue/checkbox/checkbox.min.js',
		'vendor/npm-asset/primevue/inputnumber/inputnumber.min.js',
		'vendor/npm-asset/primevue/speeddial/speeddial.min.js',
		'vendor/npm-asset/primevue/textarea/textarea.min.js',
		'vendor/npm-asset/primevue/timeline/timeline.min.js',
		'vendor/moment/luxonjs/luxon.min.js'
	),
	'customJSModules' => array(
		'public/js/apps/Abgabetool/Abgabetool.js',
	),

);

$this->load->view('templates/FHC-Header', $includesArray);
?>
<div id="abgabetoolroot" class="h-100" route=<?php echo json_encode($route) ?> uid=<?php echo $uid ?> student_uid_prop=<?php echo $student_uid_prop ?? '' ?>>

</div>
<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>
