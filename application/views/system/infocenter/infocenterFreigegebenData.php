<?php

	$APP = '\'infocenter\'';
	$INTERESSENT_STATUS = '\'Interessent\'';
	$STUDIENGANG_TYP = '\'b\'';
	$TAETIGKEIT_KURZBZ = '\'bewerbung\', \'kommunikation\'';
	$LOGDATA_NAME = '\'Login with code\', \'New application\'';

	$query = '
		SELECT
			p.person_id AS "PersonId",
			p.vorname AS "Vorname",
			p.nachname AS "Nachname",
			p.gebdatum AS "Gebdatum",
			p.staatsbuergerschaft AS "Nation",
			pl.zeitpunkt AS "LockDate",
			pl.lockuser AS "LockUser",
			(
				SELECT l.zeitpunkt
				  FROM system.tbl_log l
				 WHERE l.taetigkeit_kurzbz IN('.$TAETIGKEIT_KURZBZ.')
				   AND l.logdata->>\'name\' NOT IN ('.$LOGDATA_NAME.')
				   AND l.person_id = p.person_id
			  ORDER BY l.zeitpunkt DESC
				 LIMIT 1
			) AS "LastAction",
			(
				SELECT l.insertvon
				  FROM system.tbl_log l
				 WHERE l.taetigkeit_kurzbz IN('.$TAETIGKEIT_KURZBZ.')
				   AND l.logdata->>\'name\' NOT IN ('.$LOGDATA_NAME.')
				   AND l.person_id = p.person_id
			  ORDER BY l.zeitpunkt DESC
				 LIMIT 1
			) AS "User/Operator",
			(
				SELECT pss.studiensemester_kurzbz
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND ps.person_id = p.person_id
				   AND sg.typ IN ('.$STUDIENGANG_TYP.')
				   AND pss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende >= NOW())
			  ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
				 LIMIT 1
			) AS "Studiensemester",
			(
				SELECT pss.bewerbung_abgeschicktamum
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND pss.bewerbung_abgeschicktamum IS NOT NULL
				   AND ps.person_id = p.person_id
				   AND sg.typ IN ('.$STUDIENGANG_TYP.')
				   AND pss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende >= NOW())
			  ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
				 LIMIT 1
			) AS "SendDate",
			(
				SELECT COUNT(*)
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND pss.bewerbung_abgeschicktamum IS NOT NULL
				   AND ps.person_id = p.person_id
				   AND sg.typ IN ('.$STUDIENGANG_TYP.')
				   AND pss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende >= NOW())
				 LIMIT 1
			) AS "AnzahlAbgeschickt",
			(
				SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(sg.typ || sg.kurzbz)), \', \')
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND pss.bewerbung_abgeschicktamum IS NOT NULL
				   AND ps.person_id = p.person_id
				   AND sg.typ IN ('.$STUDIENGANG_TYP.')
				   AND pss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende >= NOW())
				 LIMIT 1
			) AS "StgAbgeschickt"
		  FROM public.tbl_person p
	 LEFT JOIN (
			SELECT tpl.person_id,
				   tpl.zeitpunkt,
				   tpl.uid AS lockuser
			  FROM system.tbl_person_lock tpl
			 WHERE tpl.app = '.$APP.'
		 ) pl USING(person_id)
		 WHERE
			EXISTS (
				SELECT 1
				  FROM public.tbl_prestudent ps
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				WHERE ps.person_id = p.person_id
				  AND sg.typ IN ('.$STUDIENGANG_TYP.')
				  AND EXISTS (
						SELECT 1
						  FROM public.tbl_prestudentstatus pss
						 WHERE pss.prestudent_id = ps.prestudent_id
							AND pss.status_kurzbz = '.$INTERESSENT_STATUS.'
							AND pss.bestaetigtam IS NOT NULL
							AND pss.bewerbung_abgeschicktamum IS NOT NULL
							AND pss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende >= NOW())
				)
			)
	ORDER BY "LastAction" DESC';

	$filterWidgetArray = array(
		'query' => $query,
		'app' => 'infocenter',
		'datasetName' => 'freigegeben',
		'filterKurzbz' => 'InfoCenterFreigegeben5days',
		'filter_id' => $this->input->get('filter_id'),
		'requiredPermissions' => 'infocenter',
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonID',
			'Vorname',
			'Nachname',
			'GebDatum',
			'Nation',
			'Sperrdatum',
			'GesperrtVon',
			'Letzte Aktion',
			'Letzter Bearbeiter',
			'StSem',
			'GesendetAm',
			'NumAbgeschickt',
			'Studiengänge'
		),
		'formatRow' => function($datasetRaw) {

			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?person_id=%s&origin_page=%s&fhc_controller_id=%s&prev_filter_id=%s">Details</a>',
				site_url('system/infocenter/InfoCenter/showDetails'),
				$datasetRaw->{'PersonId'},
				'freigegeben',
				(isset($_GET['fhc_controller_id']) ? $_GET['fhc_controller_id'] : ''),
				(isset($_GET['filter_id']) ? $_GET['filter_id'] : '')
			);

			if ($datasetRaw->{'SendDate'} == null)
			{
				$datasetRaw->{'SendDate'} = 'Not sent';
			}
			else
			{
				$datasetRaw->{'SendDate'} = date_format(date_create($datasetRaw->{'SendDate'}),'Y-m-d H:i');
			}

			if ($datasetRaw->{'LastAction'} == null)
			{
				$datasetRaw->{'LastAction'} = '-';
			}
			else
			{
				$datasetRaw->{'LastAction'} = date_format(date_create($datasetRaw->{'LastAction'}),'Y-m-d H:i');
			}

			if ($datasetRaw->{'User/Operator'} == '')
			{
				$datasetRaw->{'User/Operator'} = 'NA';
			}

			if ($datasetRaw->{'LockDate'} == null)
			{
				$datasetRaw->{'LockDate'} = '-';
			}

			if ($datasetRaw->{'LockUser'} == null)
			{
				$datasetRaw->{'LockUser'} = '-';
			}

			if ($datasetRaw->{'StgAbgeschickt'} == null)
			{
				$datasetRaw->{'StgAbgeschickt'} = 'N/A';
			}

			if ($datasetRaw->{'Nation'} == null)
			{
				$datasetRaw->{'Nation'} = '-';
			}

			return $datasetRaw;
		},
		'markRow' => function($datasetRaw) {

			if ($datasetRaw->LockDate != null)
			{
				return FilterWidget::DEFAULT_MARK_ROW_CLASS;
			}
		}
	);

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
