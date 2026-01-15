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
		'public/css/components/searchbar/searchbar.css',
		'public/css/Fhc.css',
		'public/css/components/dashboard.css',
		//'public/css/components/calendar.css', <= imported in dashboard.css
		'public/css/components/Sprachen.css',
		'public/css/components/MyLv.css',
		'public/css/components/FilterComponent.css',
		'public/css/components/Profil.css',
		'public/css/components/FormUnderline.css',
		'public/css/Cis4/Cms.css',
		'public/css/Cis4/Studium.css',
		'public/css/Cis4/Zeitsperren.css',
	),
	'customJSs' => array(
		'vendor/npm-asset/primevue/accordion/accordion.min.js',
		'vendor/npm-asset/primevue/accordiontab/accordiontab.min.js',
		'vendor/npm-asset/primevue/inputnumber/inputnumber.min.js',
		'vendor/npm-asset/primevue/textarea/textarea.min.js',
		'vendor/npm-asset/primevue/checkbox/checkbox.min.js',
		'vendor/moment/luxonjs/luxon.min.js'
	),
	'customJSModules' => array(
		'public/js/apps/Dashboard/Fhc.js'
	),

);

$this->load->view('templates/CISVUE-Header', $includesArray);
?>
<div id="fhccontent" class="h-100" route=<?php echo $route ?>>
	<router-view 
			:view-data='<?php echo json_encode($viewData) ?>'
	></router-view>
</div>
<?php $this->load->view('templates/CISVUE-Footer', $includesArray); ?>
