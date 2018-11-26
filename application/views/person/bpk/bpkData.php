<?php
	$filterWidgetArray = array(
		'query' => '
		SELECT
			person_id, vorname, nachname, geschlecht, svnr, ersatzkennzeichen, matr_nr,
			staatsbuergerschaft, gebdatum
		FROM
			public.tbl_person
		WHERE
			matr_nr is not null
			AND bpk is null
			AND EXISTS(SELECT 1 FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) AND
				person_id=tbl_person.person_id AND tbl_benutzer.aktiv=true)
		',
		'requiredPermissions' => 'admin',
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

			return $datasetRaw;
		}
	);

	$filterWidgetArray['app'] = 'core';
	$filterWidgetArray['datasetName'] = 'overview';
	$filterWidgetArray['filterKurzbz'] = 'BPKWartung';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
