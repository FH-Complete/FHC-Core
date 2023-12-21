<?php

	$this->config->load('infocenter');
	$APP = '\'infocenter\'';
	$ABGEWIESENEN_STATUS = '\'Abgewiesener\'';
	$STUDIENGANG_TYP = '\''.$this->variablelib->getVar('infocenter_studiensgangtyp').'\'';
	$ADDITIONAL_STG = $this->config->item('infocenter_studiengang_kz');
	$STUDIENSEMESTER = '\''.$this->variablelib->getVar('infocenter_studiensemester').'\'';
	$LOGDATA_NAME = '\'Message sent\'';
	$LOGDATA_VON = '\'online\'';
	$STUDIENGEBUEHR_ANZAHLUNG = '\'StudiengebuehrAnzahlung\'';

$query = '
		SELECT
			p.person_id AS "PersonId",
			ps.prestudent_id AS "PreStudentID",
			p.vorname AS "Vorname",
			p.nachname AS "Nachname",
			so.studiengangkurzbzlang as "Studiengang",
			pss.insertamum AS "AbgewiesenAm",
			pl.zeitpunkt AS "LockDate",
			pl.lockuser AS "LockUser",
			(
				SELECT l.zeitpunkt
				FROM system.tbl_log l
				WHERE l.person_id = p.person_id
				AND '. $LOGDATA_NAME .' = (
					SELECT l.logdata->>\'name\'
					FROM system.tbl_log l
					WHERE l.person_id = p.person_id
					ORDER BY l.log_id DESC
					LIMIT 1
				)
				AND ('. $LOGDATA_VON .' = (
					SELECT l.insertvon
					FROM system.tbl_log l
					WHERE l.person_id = p.person_id
					ORDER BY l.log_id DESC
					LIMIT 1
				)
				OR
					(
						(
						SELECT l.insertvon
						FROM system.tbl_log l
						WHERE l.person_id = p.person_id
						ORDER BY l.log_id DESC
						LIMIT 1
						) IS NULL
					)
				)
				AND l.zeitpunkt >= pss.insertamum
			  ORDER BY l.log_id DESC
				 LIMIT 1
			) AS "Nachricht",
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
		FROM
			public.tbl_prestudentstatus pss
			JOIN public.tbl_prestudent ps USING(prestudent_id)
			JOIN public.tbl_person p USING(person_id)
			JOIN public.tbl_studiengang sg USING(studiengang_kz)
			JOIN lehre.tbl_studienplan sp USING(studienplan_id)
			JOIN lehre.tbl_studienordnung so USING(studienordnung_id)
			LEFT JOIN (
				SELECT tpl.person_id,
					   tpl.zeitpunkt,
					   sp.nachname AS lockuser
				  FROM system.tbl_person_lock tpl
				  JOIN public.tbl_benutzer sb USING (uid)
				  JOIN public.tbl_person sp ON sb.person_id = sp.person_id
				 WHERE tpl.app = '.$APP.'
			 ) pl USING(person_id)
		WHERE pss.status_kurzbz = '. $ABGEWIESENEN_STATUS .'
		AND pss.studiensemester_kurzbz = '. $STUDIENSEMESTER .'
		AND (sg.typ IN ('. $STUDIENGANG_TYP .') 
			OR
			sg.studiengang_kz IN ('. $ADDITIONAL_STG .')
			)
		ORDER BY "AbgewiesenAm" DESC';

	$filterWidgetArray = array(
		'query' => $query,
		'app' => InfoCenter::APP,
		'datasetName' => 'abgewiesen',
		'filter_id' => $this->input->get('filter_id'),
		'requiredPermissions' => 'infocenter',
		'datasetRepresentation' => 'tablesorter',
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonId',
			'PreStudentID',
			ucfirst($this->p->t('person', 'vorname')) ,
			ucfirst($this->p->t('person', 'nachname')),
			ucfirst($this->p->t('lehre', 'studiengang')),
			ucfirst($this->p->t('infocenter', 'abgewiesenam')),
			ucfirst($this->p->t('global', 'sperrdatum')),
			ucfirst($this->p->t('global', 'gesperrtVon')),
			ucfirst($this->p->t('global', 'nachricht')),
			ucfirst($this->p->t('infocenter', 'kaution'))
		),

		'formatRow' => function($datasetRaw) {
			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?person_id=%s&origin_page=%s&fhc_controller_id=%s&prev_filter_id=%s">Details</a>',
				site_url('system/infocenter/InfoCenter/showDetails'),
				$datasetRaw->{'PersonId'},
				'abgewiesen',
				(isset($_GET['fhc_controller_id']) ? $_GET['fhc_controller_id'] : ''),
				(isset($_GET['filter_id']) ? $_GET['filter_id'] : '')
			);

			if ($datasetRaw->{'Nachricht'} === null)
			{
				$datasetRaw->{'Nachricht'} = 'Nein';
			}
			else
			{
				$datasetRaw->{'Nachricht'} = 'Ja';
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
			
			if ($datasetRaw->{'LockDate'} == null)
			{
				$datasetRaw->{'LockDate'} = '-';
			}
			
			if ($datasetRaw->{'LockUser'} == null)
			{
				$datasetRaw->{'LockUser'} = '-';
			}

			$datasetRaw->{'AbgewiesenAm'} = date_format(date_create($datasetRaw->{'AbgewiesenAm'}),'Y-m-d H:i');
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
