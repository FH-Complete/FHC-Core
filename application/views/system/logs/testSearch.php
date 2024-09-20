<?php
	$includesArray = array(
		'title' => 'Test Search',
		'bootstrap5' => true,
		'fontawesome6' => true,
		'tabulator5' => true,
		'primevue3' => true,
		'axios027' => true,
		'vue3' => true,
		'filtercomponent' => true,
		'navigationcomponent' => true,
		'phrases' => array(
			'global' => array('mailAnXversandt'),
			'ui' => array('bitteEintragWaehlen')
		),
		'customCSSs' => array(
			'public/css/components/verticalsplit.css',
			'public/css/components/searchbar.css',
			'public/css/components/primevue.css',
		),
		'customJSModules' => array('public/js/apps/TestSearch.js')
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">

		<!-- Navigation component -->
		<core-navigation-cmpt :add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>

		<div id="content">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">
						Test Search
					</h3>
				</div>
			</div>
			<div>

				<core-searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunction"></core-searchbar>
				
				<core-verticalsplit>
					<template #top>
						<core-searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunctiondummy"></core-searchbar>
					</template>
					<template #bottom>
						<!-- Filter component -->
						<core-filter-cmpt filter-type="LogsViewer" @nw-new-entry="newSideMenuEntryHandler"></core-filter-cmpt>
					</template>
				</core-verticalsplit>				

			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

