<?php

	$this->config->load('infocenter');
	$APP = '\'infocenter\'';
	$INTERESSENT_STATUS = '\'Interessent\'';
	$STUDIENGANG_TYP = '\''.$this->variablelib->getVar('infocenter_studiensgangtyp').'\'';
	$TAETIGKEIT_KURZBZ = '\'bewerbung\', \'kommunikation\'';
	$LOGDATA_NAME = '\'Login with code\', \'Login with user\', \'Attempt to register with existing mailadress\', \'Access code sent\', \'Personal data saved\'';
	$REJECTED_STATUS = '\'Abgewiesener\'';
	$ADDITIONAL_STG = $this->config->item('infocenter_studiengang_kz');
	$STATUS_KURZBZ = '\'Wartender\', \'Bewerber\', \'Aufgenommener\', \'Student\'';
	$STUDIENSEMESTER = '\''.$this->variablelib->getVar('infocenter_studiensemester').'\'';
	$ORG_NAME = '\'InfoCenter\'';
	$IDENTITY = '\'identity\'';
	$ONLINE = '\'online\'';

$query = '
		SELECT
			p.person_id AS "PersonId",
			p.vorname AS "Vorname",
			p.nachname AS "Nachname",
			p.gebdatum AS "Gebdatum",
			p.geschlecht AS "Geschlecht",
			p.staatsbuergerschaft AS "Nation",
			pl.zeitpunkt AS "LockDate",
			pl.lockuser AS "LockUser",
			(
				SELECT l.zeitpunkt
				  FROM system.tbl_log l
				 WHERE l.taetigkeit_kurzbz IN('.$TAETIGKEIT_KURZBZ.')
				   AND l.logdata->>\'name\' NOT IN ('.$LOGDATA_NAME.')
				   AND l.person_id = p.person_id
			  ORDER BY l.log_id DESC
				 LIMIT 1
			) AS "LastAction",
			(
				SELECT l.taetigkeit_kurzbz
				  FROM system.tbl_log l
				 WHERE l.taetigkeit_kurzbz IN('.$TAETIGKEIT_KURZBZ.')
				   AND l.logdata->>\'name\' NOT IN ('.$LOGDATA_NAME.')
				   AND l.person_id = p.person_id
			  ORDER BY l.log_id DESC
				 LIMIT 1
			) AS "LastActionType",
			(
				SELECT CASE WHEN student.student_uid IS NULL THEN
					(CASE WHEN sp.nachname IS NULL THEN l.insertvon ELSE sp.nachname END)
					ELSE '. $ONLINE .' END
				  FROM system.tbl_log l
				  LEFT JOIN  public.tbl_benutzer on l.insertvon = tbl_benutzer.uid
				  LEFT JOIN public.tbl_person sp on tbl_benutzer.person_id = sp.person_id
				  LEFT JOIN public.tbl_student student ON tbl_benutzer.uid = student.student_uid
				 WHERE l.taetigkeit_kurzbz IN('.$TAETIGKEIT_KURZBZ.')
				   AND l.logdata->>\'name\' NOT IN ('.$LOGDATA_NAME.')
				   AND l.person_id = p.person_id
			  ORDER BY l.log_id DESC
				 LIMIT 1
			) AS "User/Operator",
			(
				SELECT pss.studiensemester_kurzbz
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND ps.person_id = p.person_id
				   AND (sg.typ IN ('.$STUDIENGANG_TYP.')
					    OR
					    sg.studiengang_kz in('.$ADDITIONAL_STG.')
					    )
				   AND pss.bestaetigtam is not null
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
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
				   AND (sg.typ IN ('.$STUDIENGANG_TYP.')
					    OR
					    sg.studiengang_kz in('.$ADDITIONAL_STG.')
					   )
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
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
				   AND (sg.typ IN ('.$STUDIENGANG_TYP.')
					    OR
					    sg.studiengang_kz in('.$ADDITIONAL_STG.')
					   )
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
				   AND NOT EXISTS (
   					   SELECT 1
   						 FROM tbl_prestudentstatus spss
   						WHERE spss.prestudent_id = ps.prestudent_id
   						  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
   					)
				 LIMIT 1
			) AS "AnzahlAbgeschickt",
			(
				SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(so.studiengangkurzbzlang) || \':\' || sp.orgform_kurzbz), \', \')
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
				  JOIN lehre.tbl_studienordnung so USING(studienordnung_id)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND pss.bewerbung_abgeschicktamum IS NOT NULL
				   AND ps.person_id = p.person_id
				   AND (sg.typ IN ('.$STUDIENGANG_TYP.')
					    OR
					    sg.studiengang_kz in('.$ADDITIONAL_STG.')
					   )
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
				   AND NOT EXISTS (
					   SELECT 1
						 FROM tbl_prestudentstatus spss
						WHERE spss.prestudent_id = ps.prestudent_id
						  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
					)
				 LIMIT 1
			) AS "StgAbgeschickt",
			(
				SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(so.studiengangkurzbzlang) || \':\' || sp.orgform_kurzbz), \', \')
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
				  JOIN lehre.tbl_studienordnung so USING(studienordnung_id)
				 WHERE pss.status_kurzbz IN ('.$STATUS_KURZBZ.')

				   AND ps.person_id = p.person_id
				   AND (sg.typ IN ('.$STUDIENGANG_TYP.')
					OR
					sg.studiengang_kz in('.$ADDITIONAL_STG.')
					)
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
				   AND NOT EXISTS (
					   SELECT 1
						 FROM tbl_prestudentstatus spss
						WHERE spss.prestudent_id = pss.prestudent_id
						  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
						  AND spss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende >
						  (SELECT start FROM public.tbl_studiensemester sss WHERE studiensemester_kurzbz = '.$STUDIENSEMESTER.'))
					)
				 LIMIT 1
			) AS "StgAktiv",
			(
				SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(sg.bezeichnung_mehrsprachig[1])), \', \')
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
			 LEFT JOIN public.tbl_status_grund sg USING(statusgrund_id)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND ps.person_id = p.person_id
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
				 LIMIT 1
			) AS "Statusgrund",
			(
				SELECT CASE WHEN(rtp.teilgenommen IS NULL) THEN FALSE ELSE rtp.teilgenommen END
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
		  	 LEFT JOIN (
					SELECT rtp.person_id,
						   rt.studiensemester_kurzbz,
						   rtp.teilgenommen
					  FROM public.tbl_rt_person rtp
		   			  JOIN tbl_reihungstest rt ON(rtp.rt_id = rt.reihungstest_id)
					 WHERE rt.stufe = 1
				) rtp ON(rtp.person_id = ps.person_id AND rtp.studiensemester_kurzbz = pss.studiensemester_kurzbz)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND ps.person_id = p.person_id
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
			  ORDER BY rtp.teilgenommen NULLS FIRST, pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
				 LIMIT 1
			) AS "ReihungstestAngetreten",
			(
				SELECT CASE WHEN(rtp.person_id IS NULL) THEN FALSE ELSE TRUE END
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
			 LEFT JOIN (
					SELECT rtp.person_id,
						   rt.studiensemester_kurzbz
					  FROM public.tbl_rt_person rtp
		   			  JOIN tbl_reihungstest rt ON(rtp.rt_id = rt.reihungstest_id)
					 WHERE rt.stufe = 1
				) rtp ON(rtp.person_id = ps.person_id AND rtp.studiensemester_kurzbz = pss.studiensemester_kurzbz)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND ps.person_id = p.person_id
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
			  ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
				 LIMIT 1
			) AS "ReihungstestApplied",
			(
				SELECT (ARRAY_TO_STRING(array_agg(DISTINCT(CONCAT(rtp.datum, \' \', to_char(rtp.uhrzeit, \'HH24:MI\'), \' \', studiengang.kurzbzlang))), \', \'))
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
		  	 LEFT JOIN (
					SELECT rtp.person_id,
						   rt.studiensemester_kurzbz,
						   rtp.teilgenommen,
						   rt.datum,
						   rt.uhrzeit,
						   rt.studiengang_kz
					  FROM public.tbl_rt_person rtp
		   			  JOIN tbl_reihungstest rt ON(rtp.rt_id = rt.reihungstest_id)
					 WHERE rt.stufe = 1
				) rtp ON(rtp.person_id = ps.person_id AND rtp.studiensemester_kurzbz = pss.studiensemester_kurzbz)
				JOIN tbl_studiengang studiengang ON rtp.studiengang_kz = studiengang.studiengang_kz
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND ps.person_id = p.person_id
				   AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
				   GROUP BY pss.datum, pss.insertamum, pss.ext_id
			  ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
				 LIMIT 1
			) AS "ReihungstestDate",
			(
				SELECT ps.zgvnation
				FROM public.tbl_prestudent ps
				 WHERE ps.person_id = p.person_id
			  ORDER BY ps.zgvnation DESC NULLS LAST, ps.prestudent_id DESC
				 LIMIT 1
			) AS "ZGVNation",
			(
				SELECT ps.zgvmanation
				FROM public.tbl_prestudent ps
				 WHERE ps.person_id = p.person_id
			  ORDER BY ps.zgvmanation DESC NULLS LAST, ps.prestudent_id DESC
				 LIMIT 1
			) AS "ZGVMNation",
			(
				SELECT tbl_organisationseinheit.bezeichnung
				FROM public.tbl_benutzerfunktion 
				JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
				WHERE (tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von <= now()) 
				AND (tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis >= now())
				AND tbl_organisationseinheit.bezeichnung = '.$ORG_NAME.'
				AND tbl_benutzerfunktion.uid = (
					SELECT l.insertvon
					FROM system.tbl_log l
					WHERE l.taetigkeit_kurzbz IN ('.$TAETIGKEIT_KURZBZ.')
					AND l.logdata->>\'name\' NOT IN ('.$LOGDATA_NAME.')
					AND l.person_id = p.person_id
					ORDER BY l.log_id DESC
					LIMIT 1
				)
				LIMIT 1 
			) AS "InfoCenterMitarbeiter",
			(
				SELECT akte.akte_id
				FROM public.tbl_akte akte
				JOIN public.tbl_dokument USING (dokument_kurzbz)
				WHERE akte.person_id = p.person_id
				AND dokument_kurzbz = '. $IDENTITY .'
				LIMIT 1
			) AS "AktenId"
		  FROM public.tbl_person p
	 LEFT JOIN (
			SELECT tpl.person_id,
				   tpl.zeitpunkt,
				   sp.nachname AS lockuser
			  FROM system.tbl_person_lock tpl
			  JOIN public.tbl_benutzer sb USING (uid)
			  JOIN public.tbl_person sp ON sb.person_id = sp.person_id
			 WHERE tpl.app = '.$APP.'
		 ) pl USING(person_id)
		 WHERE
			EXISTS (
				SELECT 1
				  FROM public.tbl_prestudent ps
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				WHERE ps.person_id = p.person_id
				  AND ps.reihungstestangetreten = FALSE
				  AND (sg.typ IN ('.$STUDIENGANG_TYP.')
					   OR
					   sg.studiengang_kz in('.$ADDITIONAL_STG.')
					  )
				  AND EXISTS (
						SELECT 1
						  FROM public.tbl_prestudentstatus pss
						 WHERE pss.prestudent_id = ps.prestudent_id
							AND pss.status_kurzbz = '.$INTERESSENT_STATUS.'
							AND pss.bestaetigtam IS NOT NULL
							AND pss.bewerbung_abgeschicktamum IS NOT NULL
							AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
				AND NOT EXISTS (
					   SELECT 1
						 FROM tbl_prestudentstatus spss
						WHERE spss.prestudent_id = ps.prestudent_id
						  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
					)
				)
			)
	ORDER BY "LastAction" DESC';

	$filterWidgetArray = array(
		'query' => $query,
		'app' => InfoCenter::APP,
		'datasetName' => 'freigegeben',
		'filter_id' => $this->input->get('filter_id'),
		'requiredPermissions' => 'infocenter',
		'datasetRepresentation' => 'tablesorter',
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonID',
			'Vorname',
			'Nachname',
			'GebDatum',
			'Geschlecht',
			'Nation',
			'Sperrdatum',
			'GesperrtVon',
			'Letzte Aktion',
			'Aktionstyp',
			'Letzter Bearbeiter',
			'StSem',
			'GesendetAm',
			'NumAbgeschickt',
			'Studiengänge',
			'Stg aktiv',
			'Statusgrund',
			'Reihungstest angetreten',
			'Reihungstest angemeldet',
			'Reihungstest date',
			'ZGV Nation BA',
			'ZGV Nation MA',
			'InfoCenter Mitarbeiter',
			'Identitätsnachweis'
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
				$datasetRaw->{'User/Operator'} = 'N/A';
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

			if ($datasetRaw->{'StgAktiv'} == null)
			{
				$datasetRaw->{'StgAktiv'} = '-';
			}

			if ($datasetRaw->{'ReihungstestAngetreten'} == 'true')
			{
				$datasetRaw->{'ReihungstestAngetreten'} = 'Ja';
			}
			else
			{
				$datasetRaw->{'ReihungstestAngetreten'} = 'Nein';
			}

			if ($datasetRaw->{'ReihungstestApplied'} == 'true')
			{
				$datasetRaw->{'ReihungstestApplied'} = 'Ja';
			}
			else
			{
				$datasetRaw->{'ReihungstestApplied'} = 'Nein';
			}

			if ($datasetRaw->{'ReihungstestDate'} == '')
			{
				$datasetRaw->{'ReihungstestDate'} = '-';
			}

			if ($datasetRaw->{'ZGVNation'} == null)
			{
				$datasetRaw->{'ZGVNation'} = '-';
			}

			if ($datasetRaw->{'ZGVMNation'} == null)
			{
				$datasetRaw->{'ZGVMNation'} = '-';
			}

			if ($datasetRaw->{'InfoCenterMitarbeiter'} === null)
			{
				$datasetRaw->{'InfoCenterMitarbeiter'} = 'Nein';
			}
			else
			{
				$datasetRaw->{'InfoCenterMitarbeiter'} = 'Ja';
			}

			if ($datasetRaw->{'AktenId'} !== null)
			{
				$datasetRaw->{'AktenId'} = sprintf(
					'<a href="outputAkteContent/%s">Identitätsnachweis</a>',
					$datasetRaw->{'AktenId'}
				);
			}
			else
			{
				$datasetRaw->{'AktenId'} = '-';
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
