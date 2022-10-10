<?php
	$filterWidgetArray = array(
		'query' => '
		SELECT gruppe_kurzbz, grp.bezeichnung AS gruppe_bezeichnung, grp.beschreibung AS gruppe_beschreibung,
		  studiengang_kz, UPPER(stg.typ||stg.kurzbz) AS studiengang_kurzbz, semester, sichtbar, lehre, grp.aktiv, mailgrp, generiert
		FROM public.tbl_gruppe grp
		JOIN public.tbl_studiengang stg USING (studiengang_kz)
		JOIN public.tbl_gruppe_manager grpmgr USING (gruppe_kurzbz)
		WHERE grp.aktiv = TRUE
		AND grpmgr.uid = \''.$uid.'\'',
		'requiredPermissions' => 'lehre/gruppenmanager',
		'datasetRepresentation' => 'tablesorter',
		'additionalColumns' => array('Teilnehmer'),
		'columnsAliases' => array(
			ucfirst($this->p->t('gruppenmanagement', 'kurzbezeichnung')),
			ucfirst($this->p->t('gruppenmanagement', 'bezeichnung')),
			ucfirst($this->p->t('gruppenmanagement', 'beschreibung')),
			ucfirst($this->p->t('lehre', 'studiengangskennzahlLehre')),
			ucfirst($this->p->t('lehre', 'studiengang')),
			ucfirst($this->p->t('lehre', 'semester')),
			'Sichtbar',
			'Lehre',
			'Aktiv',
			'Mailgrp',
			'Generiert'
		),
		'formatRow' => function($datasetRaw) {

			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Teilnehmer'} = sprintf(
				'<a href="%s?gruppe_kurzbz=%s&origin_page=%s&fhc_controller_id=%s">'.$this->p->t('gruppenmanagement', 'zuweisenloeschen').'</a>',
				site_url('person/Gruppenmanagement/showBenutzergruppe'),
				$datasetRaw->{'gruppe_kurzbz'},
				'index',
				(isset($_GET['fhc_controller_id'])?$_GET['fhc_controller_id']:'')
			);

			if ($datasetRaw->{'gruppe_bezeichnung'} == null)
			{
				$datasetRaw->{'gruppe_bezeichnung'} = '-';
			}
			if ($datasetRaw->{'gruppe_beschreibung'} == null)
			{
				$datasetRaw->{'gruppe_beschreibung'} = '-';
			}
			if ($datasetRaw->{'semester'} == null)
			{
				$datasetRaw->{'semester'} = '-';
			}
			$datasetRaw->{'sichtbar'} = $datasetRaw->{'sichtbar'} == 'true' ? 'ja' : 'nein';
			$datasetRaw->{'lehre'} = $datasetRaw->{'lehre'} == 'true' ? 'ja' : 'nein';
			$datasetRaw->{'aktiv'} = $datasetRaw->{'aktiv'} == 'true' ? 'ja' : 'nein';
			$datasetRaw->{'mailgrp'} = $datasetRaw->{'mailgrp'} == 'true' ? 'ja' : 'nein';
			$datasetRaw->{'generiert'} = $datasetRaw->{'generiert'} == 'true' ? 'ja' : 'nein';

			return $datasetRaw;
		}
	);

	$filterWidgetArray['app'] = 'core';
	$filterWidgetArray['datasetName'] = 'gruppenmanagement';
	$filterWidgetArray['filterKurzbz'] = 'gruppenmanagement';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);

