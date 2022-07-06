<?php
	$includesArray = array(
		'title' => 'Logs Viewer',
		'axios027' => true,
		'bootstrap5' => true,
		'fontawesome6' => true,
		'jquery3' => true,
		'tablesorter2' => true,
		'vue3' => true,
		'filtercomponent' => true,
		'navigationcomponent' => true,
		'phrases' => array(
			'global' => array('mailAnXversandt'),
			'ui' => array('bitteEintragWaehlen')
		),
		'customJSModules' => array('public/js/apps/LogsViewer.js')
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
				<core-filter-cmpt filter-type="LogsViewer" @nw-new-entry="newSideMenuEntryHandler"></core-filter-cmpt>
				<!--
					'datasetRepresentation' => 'tablesorter',
               				 'columnsAliases' => array(
               				         'Log id',
               				         'Request id',
               				         'Execution time',
               				         'Executed by',
               				         'Producer',
               				         'Data',
               				         'Webservice type'
               				 ),
               				 'formatRow' => function($datasetRaw) {

               				         $datasetRaw->ExecutionTime = date_format(date_create($datasetRaw->ExecutionTime), 'd.m.Y H:i:s:u');

               				         return $datasetRaw;
               				 },
               				 'markRow' => function($datasetRaw) {

               				         $mark = '';

               				         if (strpos($datasetRaw->RequestId, 'error') != false)
               				         {
               				                 $mark = 'text-red';
               				         }

               				         if (strpos($datasetRaw->RequestId, 'info') != false)
               				         {
               				                 $mark = 'text-green';
               				         }

               				         if (strpos($datasetRaw->RequestId, 'warning') != false)
               				         {
               				                 $mark = 'text-orange';
               				         }

               				         if (strpos($datasetRaw->RequestId, 'debug') != false)
               				         {
               				                 $mark = 'text-info';
               				         }

               				         return $mark;
               				 }
				-->
			</div>
		</div>
	</div>

<?php $this->load->view('templates/FHC-Footer', $includesArray); ?>

