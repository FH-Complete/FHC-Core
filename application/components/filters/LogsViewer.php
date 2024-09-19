<?php
	$filterCmptArray = array(
		'app' => 'core',
		'datasetName' => 'logs',
		//'filterKurzbz' => 'jobs48hours', // REMOVE ME
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
		'requiredPermissions' => 'admin'
	);

