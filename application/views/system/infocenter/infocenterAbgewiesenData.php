<?php

	$this->config->load('infocenter');
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
				AND '. $LOGDATA_VON .' = (
					SELECT l.insertvon
					FROM system.tbl_log l
					WHERE l.person_id = p.person_id
					ORDER BY l.log_id DESC
					LIMIT 1
				)
				AND l.zeitpunkt >= pss.insertamum
			  ORDER BY l.log_id DESC
				 LIMIT 1
			) AS "Nachricht",
			(
				SELECT SUM(konto.betrag)
				FROM public.tbl_konto konto
				LEFT JOIN tbl_konto skonto ON (skonto.buchungsnr_verweis = konto.buchungsnr)
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
		'columnsAliases' => array(
			'PersonId',
			'PreStudentID',
			ucfirst($this->p->t('person', 'vorname')) ,
			ucfirst($this->p->t('person', 'nachname')),
			ucfirst($this->p->t('lehre', 'studiengang')),
			ucfirst($this->p->t('infocenter', 'abgewiesenam')),
			ucfirst($this->p->t('global', 'nachricht')),
			ucfirst($this->p->t('infocenter', 'kaution'))
		),

		'formatRow' => function($datasetRaw) {
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

			$datasetRaw->{'AbgewiesenAm'} = date_format(date_create($datasetRaw->{'AbgewiesenAm'}),'Y-m-d H:i');
			return $datasetRaw;
		}
	);

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
