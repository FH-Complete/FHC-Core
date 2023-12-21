<?php

	$this->config->load('infocenter');
	$APP = '\'infocenter\'';
	$REJECTED_STATUS = '\'Abgewiesener\'';
	$INTERESSENT_STATUS = '\'Interessent\'';
	$STUDIENGANG_TYP = '\''.$this->variablelib->getVar('infocenter_studiensgangtyp').'\'';
	$TAETIGKEIT_KURZBZ = '\'bewerbung\', \'kommunikation\'';
	$LOGDATA_NAME = '\'Login with code\', \'Login with user\', \'Interessent rejected\', \'Attempt to register with existing mailadress\', \'Access code sent\', \'Personal data saved\'';
	$POSTPONE_STATUS_PARKED = '\'parked\'';
	$STATUS_KURZBZ = '\'Wartender\', \'Bewerber\', \'Aufgenommener\', \'Student\'';
	$ADDITIONAL_STG = $this->config->item('infocenter_studiengang_kz');
	$AKTE_TYP = '\'identity\', \'zgv_bakk\'';
	$STUDIENSEMESTER = '\''.$this->variablelib->getVar('infocenter_studiensemester').'\'';
	$STUDIENGEBUEHR_ANZAHLUNG = '\'StudiengebuehrAnzahlung\'';
	$ORG_NAME = '\'InfoCenter\'';
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
				 WHERE l.taetigkeit_kurzbz IN ('.$TAETIGKEIT_KURZBZ.')
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
				SELECT count(akte_id)
				  FROM public.tbl_akte a
				 WHERE
				   a.person_id = p.person_id
				 AND
				  a.dokument_kurzbz in ('.$AKTE_TYP.')
			) AS "AnzahlAkte",
			(
				SELECT CASE WHEN student.student_uid IS NULL THEN
					(CASE WHEN sp.nachname IS NULL THEN l.insertvon ELSE sp.nachname END)
					ELSE '. $ONLINE .' END
				  FROM system.tbl_log l
				  LEFT JOIN  public.tbl_benutzer on l.insertvon = tbl_benutzer.uid
				  LEFT JOIN public.tbl_person sp on tbl_benutzer.person_id = sp.person_id
				  LEFT JOIN public.tbl_student student ON tbl_benutzer.uid = student.student_uid
				 WHERE l.taetigkeit_kurzbz IN ('.$TAETIGKEIT_KURZBZ.')
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
				   AND pss.bestaetigtam IS NULL
				   AND ps.person_id = p.person_id
				   AND (sg.typ IN ('.$STUDIENGANG_TYP.')
				   		OR
						sg.studiengang_kz in('.$ADDITIONAL_STG.')
					   )
				   AND NOT EXISTS (
					   SELECT 1
						 FROM tbl_prestudentstatus spss
						WHERE spss.prestudent_id = ps.prestudent_id
						  AND spss.status_kurzbz = '.$REJECTED_STATUS.'
					)
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
				   AND (
					   sg.typ IN ('.$STUDIENGANG_TYP.')
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
				 -- AND pss.bestaetigtam IS NULL
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
			) AS "StgAbgeschickt",
			(
				SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(so.studiengangkurzbzlang) || \':\' || sp.orgform_kurzbz), \', \')
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
				  JOIN lehre.tbl_studienordnung so USING(studienordnung_id)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND pss.bewerbung_abgeschicktamum IS NULL
				   AND pss.bestaetigtam IS NULL
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
			) AS "StgNichtAbgeschickt",
			(
				SELECT COUNT(*)
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
				 WHERE pss.status_kurzbz = '.$INTERESSENT_STATUS.'
				   AND pss.bewerbung_abgeschicktamum IS NULL
				   AND pss.bestaetigtam IS NULL
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
			) AS "AnzahlStgNichtAbgeschickt",
			(
				SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(so.studiengangkurzbzlang) || \':\' || sp.orgform_kurzbz), \', \')
				  FROM public.tbl_prestudentstatus pss
				  JOIN public.tbl_prestudent ps USING(prestudent_id)
				  JOIN public.tbl_studiengang sg USING(studiengang_kz)
				  JOIN lehre.tbl_studienplan sp USING(studienplan_id)
				  JOIN lehre.tbl_studienordnung so USING(studienordnung_id)
				 WHERE pss.status_kurzbz IN ('.$STATUS_KURZBZ.')
				   AND pss.bewerbung_abgeschicktamum IS NULL
				   AND ps.person_id = p.person_id
				   AND (sg.typ IN ('.$STUDIENGANG_TYP.')
					   OR
					   sg.studiengang_kz in('.$ADDITIONAL_STG.')
					   )
				   AND pss.studiensemester_kurzbz  = '.$STUDIENSEMESTER.'
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
				SELECT upper(tbl_nation.nationengruppe_kurzbz)
				FROM public.tbl_prestudent ps
				JOIN bis.tbl_nation ON ps.zgvnation = tbl_nation.nation_code
				WHERE ps.person_id = p.person_id
				ORDER BY ps.zgvnation DESC NULLS LAST, ps.prestudent_id DESC
				LIMIT 1
			) AS "ZGVNationGruppe",
			(
				SELECT upper(tbl_nation.nationengruppe_kurzbz)
				FROM public.tbl_prestudent ps
				JOIN bis.tbl_nation ON ps.zgvmanation = tbl_nation.nation_code
				WHERE ps.person_id = p.person_id
				ORDER BY ps.zgvmanation DESC NULLS LAST, ps.prestudent_id DESC
				LIMIT 1
			) AS "ZGVMNationGruppe",
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
			rueck.datum_bis AS "HoldDate",
			rueck.bezeichnung AS "Rueckstellgrund",
			(
				SELECT
					CASE
						WHEN COUNT(CASE WHEN konto.betrag != 0 THEN 1 END) = 0 THEN null
						ELSE SUM(konto.betrag)
					END AS "Kaution"
				FROM public.tbl_konto konto
				WHERE konto.person_id = p.person_id
					AND konto.studiensemester_kurzbz = '. $STUDIENSEMESTER .'
					AND konto.buchungstyp_kurzbz = '. $STUDIENGEBUEHR_ANZAHLUNG .'
			) AS "Kaution"
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
	LEFT JOIN (
				SELECT
					tbl_rueckstellung.person_id,
					tbl_rueckstellung.datum_bis,
					tbl_rueckstellung.status_kurzbz,
					array_to_json(bezeichnung_mehrsprachig::varchar[])->>0 as bezeichnung
				FROM public.tbl_rueckstellung
				JOIN public.tbl_rueckstellung_status USING(status_kurzbz)
				JOIN public.tbl_person sp ON tbl_rueckstellung.person_id = sp.person_id
				WHERE tbl_rueckstellung.rueckstellung_id =
				(
					SELECT srueck.rueckstellung_id
					FROM public.tbl_rueckstellung srueck
					WHERE srueck.person_id = tbl_rueckstellung.person_id
						AND datum_bis >= NOW()
					ORDER BY srueck.datum_bis DESC LIMIT 1
				)
			) rueck ON rueck.person_id = p.person_id
		 WHERE
			EXISTS (
				SELECT 1
				  FROM public.tbl_prestudent sps
				  JOIN public.tbl_studiengang ssg USING(studiengang_kz)
				 WHERE sps.person_id = p.person_id
				   AND (ssg.typ IN ('.$STUDIENGANG_TYP.')
					   OR
					   ssg.studiengang_kz in('.$ADDITIONAL_STG.')
					   )
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
						   AND spss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
					)
			)
	ORDER BY CASE
		WHEN rueck.status_kurzbz IS NULL THEN 1
		WHEN rueck.status_kurzbz = ' .$POSTPONE_STATUS_PARKED .' THEN 2
		WHEN rueck.status_kurzbz != '. $POSTPONE_STATUS_PARKED .' THEN 3
	END,
	rueck.datum_bis NULLS LAST, "LastAction" ASC';

	$filterWidgetArray = array(
		'query' => $query,
		'app' => InfoCenter::APP,
		'datasetName' => 'overview',
		'filterKurzbz' => 'InfoCenterSentApplicationAll',
		'filter_id' => $this->input->get('filter_id'),
		'requiredPermissions' => 'infocenter',
		'datasetRepresentation' => 'tablesorter',
		'customMenu' => true,
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonID',
			ucfirst($this->p->t('person', 'vorname')) ,
			ucfirst($this->p->t('person', 'nachname')),
			ucfirst($this->p->t('person', 'geburtsdatum')),
			ucfirst($this->p->t('person', 'geschlecht')),
			ucfirst($this->p->t('person', 'nation')),
			ucfirst($this->p->t('global', 'sperrdatum')),
			ucfirst($this->p->t('global', 'gesperrtVon')),
			ucfirst($this->p->t('global', 'letzteAktion')),
			'Aktionstyp',
			'AnzahlAktePflicht',
			ucfirst($this->p->t('global', 'letzterBearbeiter')),
			ucfirst($this->p->t('lehre', 'studiensemester')),
			ucfirst($this->p->t('global', 'gesendetAm')),
			ucfirst($this->p->t('global', 'abgeschickt')).' ('.$this->p->t('global', 'anzahl').')',
			ucfirst($this->p->t('lehre', 'studiengang')).' ('.$this->p->t('global', 'gesendet').')',
			ucfirst($this->p->t('lehre', 'studiengang')).' ('.$this->p->t('global', 'nichtGesendet').')',
			ucfirst($this->p->t('lehre', 'studiengang')).' ('.$this->p->t('global', 'anzahlNichtGesendet').')',
			ucfirst($this->p->t('lehre', 'studiengang')).' ('.$this->p->t('global', 'aktiv').')',
			'ZGV Nation BA',
			'ZGV Nation MA',
			'ZGV Gruppe BA',
			'ZGV Gruppe MA',
			'InfoCenter Mitarbeiter',
			ucfirst($this->p->t('infocenter', 'rueckstelldatum')),
			ucfirst($this->p->t('infocenter', 'rueckstellgrund')),
			ucfirst($this->p->t('infocenter', 'kaution'))
		),
		'formatRow' => function($datasetRaw) {

			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?person_id=%s&origin_page=%s&fhc_controller_id=%s&prev_filter_id=%s">Details</a>',
				site_url('system/infocenter/InfoCenter/showDetails'),
				$datasetRaw->{'PersonId'},
				'index',
				(isset($_GET['fhc_controller_id']) ? $_GET['fhc_controller_id'] : ''),
				(isset($_GET['filter_id']) ? $_GET['filter_id'] : '')
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

			if ($datasetRaw->{'HoldDate'} == null)
			{
				$datasetRaw->{'HoldDate'} = '-';
			}
			else
			{
				$datasetRaw->{'HoldDate'} = date_format(date_create($datasetRaw->{'HoldDate'}), 'Y-m-d H:i');
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

			if ($datasetRaw->{'ZGVNation'} == null)
			{
				$datasetRaw->{'ZGVNation'} = '-';
			}

			if ($datasetRaw->{'ZGVMNation'} == null)
			{
				$datasetRaw->{'ZGVMNation'} = '-';
			}

			if ($datasetRaw->{'ZGVNationGruppe'} == null)
			{
				$datasetRaw->{'ZGVNationGruppe'} = '-';
			}

			if ($datasetRaw->{'ZGVMNationGruppe'} == null)
			{
				$datasetRaw->{'ZGVMNationGruppe'} = '-';
			}

			if ($datasetRaw->{'InfoCenterMitarbeiter'} === null)
			{
				$datasetRaw->{'InfoCenterMitarbeiter'} = 'Nein';
			}
			else
			{
				$datasetRaw->{'InfoCenterMitarbeiter'} = 'Ja';
			}

			if ($datasetRaw->{'Rueckstellgrund'} === null)
			{
				$datasetRaw->{'Rueckstellgrund'} = '-';
			}

			if ($datasetRaw->{'Kaution'} === null)
			{
				$datasetRaw->{'Kaution'} = '-';
			}
			else if ($datasetRaw->{'Kaution'} === '0.00')
			{
				$datasetRaw->{'Kaution'} = 'Bezahlt';
			}
			else
			{
				$datasetRaw->{'Kaution'} = 'Offen';
			}

			return $datasetRaw;
		},
		'markRow' => function($datasetRaw) {

			$mark = '';

			if ($datasetRaw->LockDate != null)
			{
				$mark = FilterWidget::DEFAULT_MARK_ROW_CLASS;
			}

			if ($datasetRaw->Rueckstellgrund != null && $datasetRaw->Rueckstellgrund !== 'Parken')
				$mark = "onhold";

			// Parking has priority over locking
			if ($datasetRaw->Rueckstellgrund === 'Parken')
				$mark = "text-info";

			return $mark;
		}
	);

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
