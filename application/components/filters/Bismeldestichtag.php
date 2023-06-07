<?php
	$filterCmptArray = array(
		'app' => 'core',
		'datasetName' => 'bismeldestichtag',
		'query' => '
			SELECT
				bmt.meldestichtag_id AS "Id",
				bmt.meldestichtag AS "Meldestichtag",
				bmt.studiensemester_kurzbz AS "Studiensemester"
			 FROM
				bis.tbl_bismeldestichtag bmt
		 ORDER BY
			meldestichtag DESC, meldestichtag_id DESC
		',
		'requiredPermissions' => 'admin'
	);

