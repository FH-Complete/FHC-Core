<?php

	$filterWidgetArray = array(
		'query' => '
		SELECT *
		FROM (
			SELECT p.person_id AS "PersonId",
					p.vorname AS "Vorname",
					p.nachname AS "Nachname",
					p.gebdatum AS "Gebdatum",
					(SELECT zeitpunkt
					   FROM system.tbl_log
					  WHERE app = \'aufnahme\'
					    AND person_id = p.person_id
				   ORDER BY zeitpunkt DESC
				      LIMIT 1) AS "LastAction",
					(SELECT insertvon
					   FROM system.tbl_log
					  WHERE app = \'aufnahme\'
					    AND person_id = p.person_id
				   ORDER BY zeitpunkt DESC
				      LIMIT 1) AS "User/Operator",
					(SELECT pss.studiensemester_kurzbz
					   FROM public.tbl_prestudentstatus pss
			     INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
					  WHERE pss.status_kurzbz = \'Interessent\'
					    AND pss.bestaetigtam IS NULL
					    AND pss.bestaetigtvon IS NULL
					    AND ps.person_id = p.person_id
				   ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
			  		  LIMIT 1) AS "Studiensemester",
	  				(SELECT pss.bewerbung_abgeschicktamum
	  				   FROM public.tbl_prestudentstatus pss
	  		     INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
	  				  WHERE pss.status_kurzbz = \'Interessent\'
	  				    AND pss.bestaetigtam IS NULL
	  				    AND pss.bestaetigtvon IS NULL
	  				    AND ps.person_id = p.person_id
	  			   ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
	  		  		  LIMIT 1) AS "SendDate"
			  FROM public.tbl_person p
			 WHERE p.aktiv = TRUE
			   AND p.person_id IN (
				   SELECT person_id
				     FROM public.tbl_prestudent
			   INNER JOIN public.tbl_prestudentstatus USING(prestudent_id)
			        WHERE status_kurzbz = \'Interessent\'
					  AND bestaetigtam IS NULL
					  AND bestaetigtvon IS NULL)
		  GROUP BY 1, 2, 4, 5, 6, 7
		  ORDER BY "LastAction" DESC
	  ) tbl_infocenter
		WHERE "Studiensemester" IN (
			SELECT studiensemester_kurzbz
			  FROM public.tbl_studiensemester
			 WHERE (NOW() >= start AND NOW() <= ende)
			 	OR start > NOW()
		)
		',
		'hideHeader' => false,
		'hideSave' => false,
		'additionalColumns' => array('Details'),
		'formatRaw' => function($fieldName, $fieldValue, $datasetRaw) {

			if ($fieldName == 'Details')
			{
				$link = '<a href="%s%s" target="_blank">Details</a>';

				$datasetRaw->{$fieldName} = sprintf(
					$link,
					base_url('index.ci.php/system/infocenter/infocenterDetails/showDetails/'),
					$datasetRaw->PersonId
				);
			}

			if ($fieldName == 'SendDate')
			{
				if ($datasetRaw->{$fieldName} == '1970.01.01 01:00:00')
				{
					$datasetRaw->{$fieldName} = 'Not sent';
				}
			}

			if ($fieldName == 'LastAction')
			{
				if ($datasetRaw->{$fieldName} == '1970.01.01 01:00:00')
				{
					$datasetRaw->{$fieldName} = 'Not logged';
				}
			}

			if ($fieldName == 'User/Operator')
			{
				if ($datasetRaw->{$fieldName} == '')
				{
					$datasetRaw->{$fieldName} = 'NA';
				}
			}

			return $datasetRaw;
		}
	);

	$filterId = isset($_GET['filterId']) ? $_GET['filterId'] : null;

	if (isset($filterId) && is_numeric($filterId))
	{
		$filterWidgetArray['filterId'] = $filterId;
	}
	else
	{
		$filterWidgetArray['app'] = 'aufnahme';
		$filterWidgetArray['datasetName'] = 'PersonActions';
		$filterWidgetArray['filterKurzbz'] = 'InfoCenterNotSentApplicationAll';
	}

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
