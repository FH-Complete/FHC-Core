<?php
	$includesArray = array(
		'title' => 'Test Search',
		'jquery3' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'tablesorter2' => true,
		'vue3' => true,
		'ajaxlib' => true,
		'jqueryui1' => true,
		'filtercomponent' => true,
		'navigationcomponent' => true,
		'phrases' => array(
			'global' => array('mailAnXversandt'),
			'ui' => array('bitteEintragWaehlen')
		),
		'customCSSs' => array(
			'public/css/components/verticalsplit.css',
			'public/css/components/searchbar.css',
		),
		'customJSs' => array('vendor/axios/axios/axios.min.js'),
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

				<searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunction"></searchbar>
				
				<verticalsplit>
					<template #top>
						<searchbar :searchoptions="searchbaroptions" :searchfunction="searchfunctiondummy"></searchbar>
					</template>
					<template #bottom>
						<!-- Filter component -->
						<core-filter-cmpt filter-type="LogsViewer" @nw-new-entry="newSideMenuEntryHandler"></core-filter-cmpt>
					</template>
				</verticalsplit>				

			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

