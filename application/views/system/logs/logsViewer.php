<?php
	$includesArray = array(
		'title' => 'Logs Viewer',
		'jquery3' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'tablesorter2' => true,
		'vue3' => true,
		'ajaxlib' => true,
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

		<!-- NavigationWidget -->
		<navigation-widget :add-side-menu-entries="appSideMenuEntries"></navigation-widget>

		<div id="content">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header">
						Job Logs Viewer
					</h3>
				</div>
			</div>
			<div>
				<!-- FilterWidget -->
				<filter-widget filter-type="LogsViewer" @nw-new-entry="newSideMenuEntryHandler"></filter-widget>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

