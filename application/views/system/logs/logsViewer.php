<?php
	$includesArray = array(
		'title' => 'Logs Viewer',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'vue3' => true,
		'filtercomponent' => true,
		'navigationcomponent' => true,
		'tabulator5' => true,
		'phrases' => array(
			'global' => array('mailAnXversandt'),
			'ui' => array('bitteEintragWaehlen')
		),
		'customJSModules' => array('public/js/apps/LogsViewer/LogsViewer.js')
	);

	$this->load->view('templates/FHC-Header', $includesArray);
?>

	<div id="main">

		<!-- Navigation component -->
		<core-navigation-cmpt v-bind:add-side-menu-entries="appSideMenuEntries"></core-navigation-cmpt>

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
				<core-filter-cmpt
					filter-type="LogsViewer"
					:tabulator-options="logsViewerTabulatorOptions"
					:tabulator-events="logsViewerTabulatorEventHandlers"
					@nw-new-entry="newSideMenuEntryHandler">
				</core-filter-cmpt>
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

