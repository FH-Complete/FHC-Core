<?php
	$includesArray = array(
		'title' => 'Logs Viewer',
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
		'customJSs' => array('public/js/apps/LogsViewer.js')
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
						Job Logs Viewer
					</h3>
				</div>
			</div>
			<div>
				<!-- Filter component -->
				<core-filter-cmpt filter-type="LogsViewer" @nw-new-entry="newSideMenuEntryHandler"></core-filter-cmpt>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

