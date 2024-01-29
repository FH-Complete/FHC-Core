<?php

	$filterWidgetArray = array(
		'query' => '
			SELECT jq.jobid AS "JobId",
					jq.creationtime AS "CreationTime",
					jq.type AS "Type",
					jq.status AS "Status",
					jq.starttime AS "StartTime",
					jq.endtime AS "EndTime",
					jq.insertvon AS "UserService"
			 FROM system.tbl_jobsqueue jq
		 ORDER BY jq.creationtime DESC, jq.starttime DESC, jq.endtime DESC
		',
		'requiredPermissions' => 'admin',
		'datasetRepresentation' => 'tablesorter',
		'columnsAliases' => array(
			'Job id',
			'Creation time',
			'Type',
			'Status',
			'Start time',
			'End time',
			'User/Service'
		),
		'formatRow' => function($datasetRaw) {

			$datasetRaw->CreationTime = date_format(date_create($datasetRaw->CreationTime), 'd.m.Y H:i:s');
			$datasetRaw->StartTime = date_format(date_create($datasetRaw->StartTime), 'd.m.Y H:i:s');
			$datasetRaw->EndTime = date_format(date_create($datasetRaw->EndTime), 'd.m.Y H:i:s');

			return $datasetRaw;
		},
		'markRow' => function($datasetRaw) {

			$mark = '';

			if ($datasetRaw->Status == JobsQueueLib::STATUS_FAILED)
			{
				$mark = 'text-red';
			}

			if ($datasetRaw->Status == JobsQueueLib::STATUS_DONE)
			{
				$mark = 'text-green';
			}

			if ($datasetRaw->Status == JobsQueueLib::STATUS_RUNNING)
			{
				$mark = 'text-orange';
			}

			if ($datasetRaw->Status == JobsQueueLib::STATUS_NEW)
			{
				$mark = 'text-info';
			}

			return $mark;
		}
	);

	$filterWidgetArray['app'] = 'core';
	$filterWidgetArray['datasetName'] = 'jq';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
