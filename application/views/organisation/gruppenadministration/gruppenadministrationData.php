<?php
	$filterWidgetArray = array(
		'query' => '
		SELECT gruppe_kurzbz, grp.bezeichnung AS gruppe_bezeichnung, grp.beschreibung AS gruppe_beschreibung,
		  studiengang_kz, UPPER(stg.typ||stg.kurzbz) AS studiengang_kurzbz, semester
		FROM public.tbl_gruppe grp
		JOIN public.tbl_studiengang stg USING (studiengang_kz)
		JOIN public.tbl_gruppe_manager grpmgr USING (gruppe_kurzbz)
		WHERE grp.aktiv = TRUE
		AND grpmgr.uid = \''.$uid.'\'',
		'requiredPermissions' => 'admin',
		'datasetRepresentation' => 'tablesorter',
		'additionalColumns' => array('Teilnehmer'),
		'columnsAliases' => array(
			ucfirst($this->p->t('gruppenadministration', 'kurzbezeichnung')) ,
			ucfirst($this->p->t('global', 'bezeichnung')) ,
			ucfirst($this->p->t('global', 'beschreibung')) ,
			ucfirst($this->p->t('lehre', 'studiengangskennzahl')) ,
			ucfirst($this->p->t('lehre', 'studiengang')),
			ucfirst($this->p->t('lehre', 'semester')) ,
		),
		'formatRow' => function($datasetRaw) {

			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Teilnehmer'} = sprintf(
				'<a href="%s?gruppe_kurzbz=%s&origin_page=%s&fhc_controller_id=%s">'.$this->p->t('gruppenadministration', 'zuweisenlöschen').'</a>',
				site_url('organisation/Gruppenadministration/showBenutzergruppe'),
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
			//$datasetRaw->{'lehre'} = $datasetRaw->{'lehre'} == 'true' ? 'ja' : 'nein';

			return $datasetRaw;
		}
	);

	$filterWidgetArray['app'] = 'core';
	$filterWidgetArray['datasetName'] = 'gruppenadministration';
	$filterWidgetArray['filterKurzbz'] = 'gruppenadministration';
	$filterWidgetArray['filter_id'] = $this->input->get('filter_id');

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
