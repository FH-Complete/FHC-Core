<?php

	$APP = '\'infocenter\'';
	$REJECTED_STATUS = '\'Abgewiesener\'';
	$INTERESSENT_STATUS = '\'Interessent\'';
	$STUDIENGANG_TYP = '\'b\'';
	$TAETIGKEIT_KURZBZ = '\'bewerbung\', \'kommunikation\'';
	$LOGDATA_NAME = '\'Login with code\', \'New application\', \'Interessent rejected\'';
	$LOGDATA_NAME_PARKED = '\'Parked\'';
	$LOGTYPE_KURZBZ = '\'Processstate\'';
	$STATUS_KURZBZ = '\'Wartender\', \'Bewerber\', \'Aufgenommener\', \'Student\'';

	$filterWidgetArray = array(
		'query' => '
			WITH currentOrNextStudiensemester AS (
				SELECT ss.studiensemester_kurzbz
				  FROM public.tbl_studiensemester ss
				 WHERE ss.ende > NOW()
			  ORDER BY ss.ende
				 LIMIT 3
			)

			SELECT
				p.person_id AS "PersonId",
				p.vorname AS "Vorname",
				p.nachname AS "Nachname",
				p.gebdatum AS "Gebdatum",
				p.staatsbuergerschaft AS "Nation",
				pl.zeitpunkt AS "LockDate",
				pl.lockuser AS "LockUser",
				pd.parkdate AS "ParkDate",
				(
					SELECT l.zeitpunkt
					  FROM system.tbl_log l
					 WHERE l.taetigkeit_kurzbz IN ('.$TAETIGKEIT_KURZBZ.')
					   AND l.logdata->>\'name\' NOT IN ('.$LOGDATA_NAME.')
					   AND l.person_id = p.person_id
				  ORDER BY l.zeitpunkt DESC
					 LIMIT 1
				) AS "LastAction",
				(
					SELECT l.insertvon
					  FROM system.tbl_log l
					 WHERE l.taetigkeit_kurzbz IN ('.$TAETIGKEIT_KURZBZ.')
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
					   AND pss.bestaetigtam IS NULL
					   AND ps.person_id = p.person_id
					   AND sg.typ IN ('.$STUDIENGANG_TYP.')
					   AND pss.studiensemester_kurzbz IN (SELECT cnss.studiensemester_kurzbz FROM currentOrNextStudiensemester cnss)
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
					   AND pss.bestaetigtam IS NULL
					   AND ps.person_id = p.person_id
					   AND sg.typ IN ('.$STUDIENGANG_TYP.')
					   AND pss.studiensemester_kurzbz IN (SELECT cnss.studiensemester_kurzbz FROM currentOrNextStudiensemester cnss)
					   AND NOT EXISTS (
						   SELECT 1
						     FROM tbl_prestudentstatus spss
							WHERE spss.prestudent_id = pss.prestudent_id
							  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
							  AND spss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende > NOW())
						)
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
					   AND pss.bestaetigtam IS NULL
					   AND ps.person_id = p.person_id
					   AND sg.typ IN ('.$STUDIENGANG_TYP.')
					   AND pss.studiensemester_kurzbz IN (SELECT cnss.studiensemester_kurzbz FROM currentOrNextStudiensemester cnss)
					   AND NOT EXISTS (
							SELECT 1
							  FROM tbl_prestudentstatus spss
							 WHERE spss.prestudent_id = pss.prestudent_id
							   AND spss.status_kurzbz = '.$REJECTED_STATUS.'
							   AND spss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende > NOW())
						)
					 LIMIT 1
				) AS "AnzahlAbgeschickt",
				(
					SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(sg.typ || sg.kurzbz) || \':\' || sp.orgform_kurzbz), \', \')
					  FROM public.tbl_prestudentstatus pss
					  JOIN public.tbl_prestudent ps USING(prestudent_id)
					  JOIN public.tbl_studiengang sg USING(studiengang_kz)
					  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
					 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
					   AND pss.bewerbung_abgeschicktamum IS NOT NULL
					   AND pss.bestaetigtam IS NULL
					   AND ps.person_id = p.person_id
					   AND sg.typ IN ('.$STUDIENGANG_TYP.')
					   AND pss.studiensemester_kurzbz IN (SELECT cnss.studiensemester_kurzbz FROM currentOrNextStudiensemester cnss)
					   AND NOT EXISTS (
						   SELECT 1
						     FROM tbl_prestudentstatus spss
							WHERE spss.prestudent_id = pss.prestudent_id
							  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
							  AND spss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende > NOW())
						)
					 LIMIT 1
				) AS "StgAbgeschickt",
				(
					SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(sg.typ || sg.kurzbz) || \':\' || sp.orgform_kurzbz), \', \')
					  FROM public.tbl_prestudentstatus pss
					  JOIN public.tbl_prestudent ps USING(prestudent_id)
					  JOIN public.tbl_studiengang sg USING(studiengang_kz)
					  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
					 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
					   AND pss.bewerbung_abgeschicktamum IS NULL
 					   AND pss.bestaetigtam IS NULL
					   AND ps.person_id = p.person_id
					   AND sg.typ IN ('.$STUDIENGANG_TYP.')
					   AND pss.studiensemester_kurzbz IN (SELECT cnss.studiensemester_kurzbz FROM currentOrNextStudiensemester cnss)
					   AND NOT EXISTS (
						  SELECT 1
						    FROM tbl_prestudentstatus spss
						   WHERE spss.prestudent_id = pss.prestudent_id
						     AND spss.status_kurzbz = '.$REJECTED_STATUS.'
						     AND spss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende > NOW())
						)
					 LIMIT 1
				) AS "StgNichtAbgeschickt",
				(
					SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(sg.typ || sg.kurzbz) || \':\' || sp.orgform_kurzbz), \', \')
					  FROM public.tbl_prestudentstatus pss
					  JOIN public.tbl_prestudent ps USING(prestudent_id)
					  JOIN public.tbl_studiengang sg USING(studiengang_kz)
					  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
					 WHERE pss.status_kurzbz IN ('.$STATUS_KURZBZ.')
					   AND pss.bewerbung_abgeschicktamum IS NULL
					   AND ps.person_id = p.person_id
					   AND sg.typ IN ('.$STUDIENGANG_TYP.')
					   AND pss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.start >= NOW())
					   AND NOT EXISTS (
						   SELECT 1
						     FROM tbl_prestudentstatus spss
							WHERE spss.prestudent_id = pss.prestudent_id
							  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
							  AND spss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende > NOW())
						)
					 LIMIT 1
				) AS "StgAktiv"
			  FROM public.tbl_person p
		 LEFT JOIN (
			 		SELECT tpl.person_id,
						   tpl.zeitpunkt,
						   tpl.uid AS lockuser
					  FROM system.tbl_person_lock tpl
					 WHERE tpl.app = '.$APP.'
				) pl USING(person_id)
		 LEFT JOIN (
					SELECT l.person_id,
						   l.zeitpunkt AS parkdate
					  FROM system.tbl_log l
					 WHERE l.logtype_kurzbz = '.$LOGTYPE_KURZBZ.'
					   AND l.logdata->>\'name\' = '.$LOGDATA_NAME_PARKED.'
					   AND l.zeitpunkt >= NOW()
				) pd USING(person_id)
			 WHERE
				EXISTS (
					SELECT 1
					  FROM public.tbl_prestudent sps
					  JOIN public.tbl_studiengang ssg USING(studiengang_kz)
					 WHERE sps.person_id = p.person_id
					   AND ssg.typ IN ('.$STUDIENGANG_TYP.')
					   AND '.$INTERESSENT_STATUS.' = (
						   	SELECT spss.status_kurzbz
							  FROM public.tbl_prestudentstatus spss
							 WHERE spss.prestudent_id = sps.prestudent_id
						  ORDER BY spss.datum DESC, spss.insertamum DESC, spss.ext_id DESC
							 LIMIT 1
						)
					   AND EXISTS (
							SELECT 1
							  FROM public.tbl_prestudentstatus spss
							 WHERE spss.prestudent_id = sps.prestudent_id
							   AND spss.status_kurzbz = '.$INTERESSENT_STATUS.'
							   AND spss.bestaetigtam IS NULL
							   AND spss.bewerbung_abgeschicktamum IS NOT NULL
							   AND spss.studiensemester_kurzbz IN (SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende > NOW())
						)
				)
		ORDER BY "LastAction" ASC
		',
		'requiredPermissions' => 'infocenter',
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonID',
			ucfirst($this->p->t('person', 'vorname')) ,
			ucfirst($this->p->t('person', 'nachname')),
			ucfirst($this->p->t('person', 'geburtsdatum')),
			ucfirst($this->p->t('person', 'nation')),
			ucfirst($this->p->t('global', 'letzteAktion')),
			ucfirst($this->p->t('global', 'letzterBearbeiter')),
			ucfirst($this->p->t('lehre', 'studiensemester')),
			ucfirst($this->p->t('global', 'gesendetAm')),
			ucfirst($this->p->t('global', 'abgeschickt')).' ('.$this->p->t('global', 'anzahl').')',
			ucfirst($this->p->t('lehre', 'studiengang')).' ('.$this->p->t('global', 'gesendet').')',
			ucfirst($this->p->t('lehre', 'studiengang')).' ('.$this->p->t('global', 'nichtGesendet').')',
			ucfirst($this->p->t('lehre', 'studiengang')).' ('.$this->p->t('global', 'aktiv').')',
			ucfirst($this->p->t('global', 'sperrdatum')),
			ucfirst($this->p->t('global', 'gesperrtVon')),
			ucfirst($this->p->t('global', 'parkdatum'))
		),
		'formatRow' => function($datasetRaw) {

			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?person_id=%s&origin_page=%s&fhc_controller_id=%s">Details</a>',
				site_url('system/infocenter/InfoCenter/showDetails'),
				$datasetRaw->{'PersonId'},
				'index',
				(isset($_GET['fhc_controller_id'])?$_GET['fhc_controller_id']:'')
			);

			if ($datasetRaw->{'SendDate'} == null)
			{
				$datasetRaw->{'SendDate'} = 'Not sent';
			}
			else
			{
				$datasetRaw->{'SendDate'} = date_format(date_create($datasetRaw->{'SendDate'}), 'Y-m-d H:i');
			}

			if ($datasetRaw->{'LastAction'} == null)
			{
				$datasetRaw->{'LastAction'} = '-';
			}
			else
			{
				$datasetRaw->{'LastAction'} = date_format(date_create($datasetRaw->{'LastAction'}), 'Y-m-d H:i');
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

			if ($datasetRaw->{'ParkDate'} == null)
			{
				$datasetRaw->{'ParkDate'} = '-';
			}

			if ($datasetRaw->{'StgAbgeschickt'} == null)
			{
				$datasetRaw->{'StgAbgeschickt'} = '-';
			}

			if ($datasetRaw->{'StgNichtAbgeschickt'} == null)
			{
				$datasetRaw->{'StgNichtAbgeschickt'} = '-';
			}

			if ($datasetRaw->{'StgAktiv'} == null)
			{
				$datasetRaw->{'StgAktiv'} = '-';
			}

			if ($datasetRaw->{'Nation'} == null)
			{
				$datasetRaw->{'Nation'} = '-';
			}

			return $datasetRaw;
		},
		'markRow' => function($datasetRaw) {

			$mark = '';

			if ($datasetRaw->LockDate != null)
			{
				$mark = FilterWidget::DEFAULT_MARK_ROW_CLASS;
			}

			// Parking has priority over locking
			if ($datasetRaw->ParkDate != null)
			{
				$mark = "text-info";
			}

			return $mark;
		}
	);

	$filterWidgetArray['app'] = 'infocenter';
	$filterWidgetArray['datasetName'] = 'overview';
	$filterWidgetArray['filterKurzbz'] = 'InfoCenterSentApplicationAll';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
