<?php
	$filterWidgetArray = array(
		'query' => '
		SELECT
			person_id, vorname, nachname, geschlecht, svnr, ersatzkennzeichen, matr_nr,
			staatsbuergerschaft, gebdatum, false AS mitarbeiter,
		    (SELECT count(*) FROM public.tbl_akte WHERE person_id=tbl_person.person_id) AS anzahl_dokumente
		FROM
			public.tbl_person
		WHERE
			matr_nr is not null
			AND bpk is null
			AND EXISTS(SELECT 1 FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) AND
				person_id=tbl_person.person_id AND tbl_benutzer.aktiv=true)
		UNION
		SELECT
			person_id, vorname, nachname, geschlecht, svnr, ersatzkennzeichen, matr_nr,
			staatsbuergerschaft, gebdatum, true AS mitarbeiter,
		    (SELECT count(*) FROM public.tbl_akte WHERE person_id=tbl_person.person_id) AS anzahl_dokumente
		FROM
			public.tbl_person
            JOIN public.tbl_benutzer USING(person_id)
            JOIN public.tbl_mitarbeiter ON (mitarbeiter_uid=uid)
		WHERE
			bpk is null
			AND tbl_benutzer.aktiv=true		
		',
		'requiredPermissions' => 'admin',
		'datasetRepresentation' => 'tablesorter',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonID',
			ucfirst($this->p->t('person', 'vorname')) ,
			ucfirst($this->p->t('person', 'nachname')),
			ucfirst($this->p->t('person', 'geschlecht')),
			ucfirst($this->p->t('person', 'svnr')),
			ucfirst($this->p->t('person', 'ersatzkennzeichen')),
			ucfirst($this->p->t('person', 'matrikelnummer')),
			ucfirst($this->p->t('person', 'staatsbuergerschaft')),
			ucfirst($this->p->t('person', 'geburtsdatum')),
			'Mitarbeiter',
			'Anzahl Dokumente'
		),
		'formatRow' => function($datasetRaw) {

			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?person_id=%s&origin_page=%s&fhc_controller_id=%s">Details</a>',
				site_url('person/BPKWartung/showDetails'),
				$datasetRaw->{'person_id'},
				'index',
				(isset($_GET['fhc_controller_id'])?$_GET['fhc_controller_id']:'')
			);

			if ($datasetRaw->{'ersatzkennzeichen'} == null)
			{
				$datasetRaw->{'ersatzkennzeichen'} = '-';
			}
			if ($datasetRaw->{'svnr'} == null)
			{
				$datasetRaw->{'svnr'} = '-';
			}
			if ($datasetRaw->{'matr_nr'} == null)
			{
				$datasetRaw->{'matr_nr'} = '-';
			}
			$datasetRaw->{'mitarbeiter'} = $datasetRaw->{'mitarbeiter'} == 'true' ? 'ja' : 'nein';

			return $datasetRaw;
		}
	);

	$filterWidgetArray['app'] = 'core';
	$filterWidgetArray['datasetName'] = 'overview';
	$filterWidgetArray['filterKurzbz'] = 'BPKWartung';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);

