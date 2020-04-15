<?php

	$filterWidgetArray = array(
		'query' => '
			SELECT wsl.webservicelog_id AS "LogId",
					wsl.request_id AS "RequestId",
					wsl.execute_time AS "ExecutionTime",
					wsl.execute_user AS "ExecutedBy",
					wsl.beschreibung AS "Description",
					wsl.request_data AS "Data",
					wsl.webservicetyp_kurzbz AS "WebserviceType"
			 FROM system.tbl_webservicelog wsl
		 ORDER BY wsl.execute_time DESC
		',
		'requiredPermissions' => 'admin',
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
	$filterWidgetArray['datasetName'] = 'logs';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
