<?php

	$APP = 'infocenter';
	$NOTBEFORE = '2018-03-01 18:00:00';
	$filterWidgetArray = array(
		'query' => '
		SELECT
				p.person_id AS "PersonId",
				p.vorname AS "Vorname",
				p.nachname AS "Nachname",
				p.gebdatum AS "Gebdatum",
				p.staatsbuergerschaft AS "Nation",
				(
					SELECT zeitpunkt
					FROM system.tbl_log
					WHERE taetigkeit_kurzbz IN(\'bewerbung\',\'kommunikation\')
					AND logdata->>\'name\' NOT IN (\'Login with code\', \'New application\', \'Interessent rejected\')
					AND person_id = p.person_id
					ORDER BY zeitpunkt DESC
					LIMIT 1
				) AS "LastAction",
				(
					SELECT insertvon
					FROM system.tbl_log
					WHERE taetigkeit_kurzbz IN(\'bewerbung\',\'kommunikation\')
					AND logdata->>\'name\' NOT IN (\'Login with code\', \'New application\', \'Interessent rejected\')
					AND person_id = p.person_id
					ORDER BY zeitpunkt DESC
					LIMIT 1
				) AS "User/Operator",
				(
					SELECT
						pss.studiensemester_kurzbz
					FROM
						public.tbl_prestudentstatus pss
						INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE pss.status_kurzbz = \'Interessent\'
					AND pss.bestaetigtam IS NULL
					AND ps.person_id = p.person_id
					AND tbl_studiengang.typ in(\'b\')
					AND studiensemester_kurzbz IN (
						SELECT studiensemester_kurzbz
						FROM public.tbl_studiensemester
						WHERE ende >= NOW()
					)
					ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
					LIMIT 1
				) AS "Studiensemester",
				(
					SELECT pss.bewerbung_abgeschicktamum
					FROM
						public.tbl_prestudentstatus pss
						INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE pss.status_kurzbz = \'Interessent\'
						AND (pss.bewerbung_abgeschicktamum IS NOT NULL AND pss.bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\')
						AND pss.bestaetigtam IS NULL
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
						AND studiensemester_kurzbz IN (
							SELECT studiensemester_kurzbz
							FROM public.tbl_studiensemester
							WHERE ende >= NOW()
						)
					ORDER BY pss.datum DESC, pss.insertamum DESC, pss.ext_id DESC
					LIMIT 1
				) AS "SendDate",
				(
					SELECT count(*)
					FROM
						public.tbl_prestudentstatus pss
						INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE pss.status_kurzbz = \'Interessent\'
						AND (pss.bewerbung_abgeschicktamum IS NOT NULL AND pss.bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\')
						AND pss.bestaetigtam IS NULL
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
						AND studiensemester_kurzbz IN (
							SELECT studiensemester_kurzbz
							FROM public.tbl_studiensemester
							WHERE ende >= NOW()
						)
						AND not exists (select 1 from tbl_prestudentstatus psss where psss.prestudent_id = pss.prestudent_id and psss.status_kurzbz = \'Abgewiesener\')
					LIMIT 1
				) AS "AnzahlAbgeschickt",
				array_to_string(
					(
					SELECT array_agg(distinct UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) || \':\' || tbl_studienplan.orgform_kurzbz)
					FROM
						public.tbl_prestudentstatus pss
						INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
						JOIN lehre.tbl_studienplan using (studienplan_id)
					WHERE pss.status_kurzbz = \'Interessent\'
						AND (pss.bewerbung_abgeschicktamum IS NOT NULL AND pss.bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\')
						AND pss.bestaetigtam IS NULL
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
						AND studiensemester_kurzbz IN (
							SELECT studiensemester_kurzbz
							FROM public.tbl_studiensemester
							WHERE ende >= NOW()
						)
						AND not exists (select 1 from tbl_prestudentstatus psss where psss.prestudent_id = pss.prestudent_id and psss.status_kurzbz = \'Abgewiesener\')
					LIMIT 1
					),\', \'
				) AS "StgAbgeschickt",
				array_to_string(
					(
					SELECT array_agg(distinct UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) || \':\' || tbl_studienplan.orgform_kurzbz)
					FROM
						public.tbl_prestudentstatus pss
						INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
						JOIN lehre.tbl_studienplan using (studienplan_id)
					WHERE pss.status_kurzbz = \'Interessent\'
						AND (pss.bewerbung_abgeschicktamum IS NULL)
						AND pss.bestaetigtam IS NULL
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
						AND studiensemester_kurzbz IN (
							SELECT studiensemester_kurzbz
							FROM public.tbl_studiensemester
							WHERE ende >= NOW()
						)
					AND not exists (select 1 from tbl_prestudentstatus psss where psss.prestudent_id = pss.prestudent_id and psss.status_kurzbz = \'Abgewiesener\')
					LIMIT 1
					),\', \'
				) AS "StgNichtAbgeschickt",
				array_to_string(
					(
					SELECT array_agg(distinct UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) || \':\' || tbl_studienplan.orgform_kurzbz)
					FROM
						public.tbl_prestudentstatus pss
						INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
						JOIN lehre.tbl_studienplan using (studienplan_id)
					WHERE pss.status_kurzbz in (\'Wartender\', \'Bewerber\', \'Aufgenommener\', \'Student\')
						AND (pss.bewerbung_abgeschicktamum IS NULL)
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
						AND studiensemester_kurzbz IN (
							SELECT studiensemester_kurzbz
							FROM public.tbl_studiensemester
							WHERE start >= NOW()
						)
					AND not exists (select 1 from tbl_prestudentstatus psss where psss.prestudent_id = pss.prestudent_id and psss.status_kurzbz = \'Abgewiesener\')
					LIMIT 1
					),\', \'
				) AS "StgAktiv",
				pl.zeitpunkt AS "LockDate",
				pl.lockuser as "LockUser",
				pd.parkdate AS "ParkDate"
			FROM public.tbl_person p
		LEFT JOIN (SELECT person_id, zeitpunkt, uid as lockuser FROM system.tbl_person_lock WHERE app = \''.$APP.'\') pl USING(person_id)
		LEFT JOIN (
					SELECT person_id, zeitpunkt as parkdate
					FROM system.tbl_log
					WHERE logdata->>\'name\' = \'Parked\'
					AND zeitpunkt > now()
				) pd USING(person_id)	
			WHERE
				EXISTS(
					SELECT 1
					FROM
						public.tbl_prestudent
						JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE
						person_id=p.person_id
						AND tbl_studiengang.typ in(\'b\')
						AND \'Interessent\' = (SELECT status_kurzbz FROM public.tbl_prestudentstatus
												WHERE prestudent_id=tbl_prestudent.prestudent_id
												ORDER BY datum DESC, insertamum DESC, ext_id DESC
												LIMIT 1
												)
						AND EXISTS (
							SELECT
								1
							FROM
								public.tbl_prestudentstatus
							WHERE
								prestudent_id = tbl_prestudent.prestudent_id
								AND status_kurzbz = \'Interessent\'
								AND (bestaetigtam IS NULL AND (bewerbung_abgeschicktamum is null OR bewerbung_abgeschicktamum>=\''.$NOTBEFORE.'\'))
								AND studiensemester_kurzbz IN (
									SELECT studiensemester_kurzbz
									FROM public.tbl_studiensemester
									WHERE ende >= NOW()
							)
					)
				)
			ORDER BY "LastAction" ASC
		',
		'fhc_controller_id' => $fhc_controller_id,
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonID', 
			ucfirst($this->p->t('person','vorname')) , 
			ucfirst($this->p->t('person','nachname')),
			ucfirst($this->p->t('person','geburtsdatum')),
			ucfirst($this->p->t('person','nation')), 
			ucfirst($this->p->t('global','letzteAktion')),
			ucfirst($this->p->t('global','letzterBearbeiter')),
			ucfirst($this->p->t('lehre','studiensemester')),
			ucfirst($this->p->t('global','gesendetAm')),
			ucfirst($this->p->t('global','abgeschickt')) . ' (' . $this->p->t('global','anzahl') . ')',
			ucfirst($this->p->t('lehre','studiengang')) . ' (' . $this->p->t('global','gesendet') . ')',
			ucfirst($this->p->t('lehre','studiengang')) . ' (' . $this->p->t('global','nichtGesendet') . ')',
			ucfirst($this->p->t('lehre','studiengang')) . ' (' . $this->p->t('global','aktiv') . ')',
			ucfirst($this->p->t('global','sperrdatum')),
			ucfirst($this->p->t('global','gesperrtVon')),
			"ParkedDate"
		),
		'formatRaw' => function($datasetRaw) {

			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s%s">Details</a>',
				site_url('system/infocenter/InfoCenter/showDetails/'),
				$datasetRaw->{'PersonId'}
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

			if ($datasetRaw->ParkDate != null)
			{
				//parking has prio over locking
				$mark = "text-info";
			}

			return $mark;
		}
	);
	
	$filterId = isset($_GET[InfoCenter::FILTER_ID]) ? $_GET[InfoCenter::FILTER_ID] : null;

	if (isset($filterId) && is_numeric($filterId))
	{
		$filterWidgetArray[InfoCenter::FILTER_ID] = $filterId;
	}
	else
	{
		$filterWidgetArray['app'] = $APP;
		$filterWidgetArray['datasetName'] = 'PersonActions';
		$filterWidgetArray['filterKurzbz'] = 'InfoCenterNotSentApplicationAll';
	}

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
