<?php

	$filterWidgetArray = array(
		'query' => '
			SELECT wsl.webservicelog_id AS "LogId",
					wsl.request_id AS "RequestId",
					wsl.execute_time AS "ExecutionTime",
					wsl.execute_user AS "ExecutedBy",
					wsl.beschreibung AS "Description",
					wsl.request_data AS "Data"
			 FROM system.tbl_webservicelog wsl
			WHERE wsl.webservicetyp_kurzbz = \'job\'
		 ORDER BY wsl.execute_time DESC
		',
		'requiredPermissions' => 'admin',
		'datasetRepresentation' => 'tablesorter',
		'reloadDataset' => ($this->input->get('reloadDataset') == 'true' ? true : false),
		'columnsAliases' => array(
			'Log id',
			'Request id',
			'Execution time',
			'Executed by',
			'Producer',
			'Data'
		),
		'formatRow' => function($datasetRaw) {

			$datasetRaw->ExecutionTime = date_format(date_create($datasetRaw->ExecutionTime), 'd.m.Y H:i:s');

			return $datasetRaw;
		},
		'markRow' => function($datasetRaw) {

			$mark = '';

			if ($datasetRaw->RequestId == 'Cronjob error')
			{
				$mark = 'text-red';
			}

			if ($datasetRaw->RequestId == 'Cronjob info')
			{
				$mark = 'text-green';
			}

			if ($datasetRaw->RequestId == 'Cronjob warning')
			{
				$mark = 'text-orange';
			}

			if ($datasetRaw->RequestId == 'Cronjob debug')
			{
				$mark = 'text-info';
			}

			return $mark;
		}
	);

	$filterWidgetArray['app'] = 'core';
	$filterWidgetArray['datasetName'] = 'jobslogs';
	$filterWidgetArray['filterKurzbz'] = 'all';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
