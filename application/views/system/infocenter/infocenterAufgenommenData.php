<?php

	$this->config->load('infocenter');
	$AUFGENOMMENER_STATUS = '\'Aufgenommener\'';
	$REJECTED_STATUS = '\'Abgewiesener\'';
	$STUDIENGANG_TYP = '\'l\'';
	$STUDIENSEMESTER = '\''.$this->variablelib->getVar('infocenter_studiensemester').'\'';
	$LOGDATA_NAME = '\'Message sent\'';
	$LOGDATA_VON = '\'online\'';

$query = '
		SELECT
			p.person_id AS "PersonId",
			p.vorname AS "Vorname",
			p.nachname AS "Nachname",
			(
				SELECT ARRAY_TO_STRING(ARRAY_AGG(DISTINCT UPPER(sg.typ) || UPPER(sg.kurzbz)), \', \')
				FROM public.tbl_prestudentstatus pss
					JOIN public.tbl_prestudent ps USING(prestudent_id)
					JOIN public.tbl_studiengang sg USING(studiengang_kz)
				WHERE
					pss.status_kurzbz = '. $AUFGENOMMENER_STATUS .'
					AND ps.person_id = p.person_id
					AND sg.typ IN ('.$STUDIENGANG_TYP.')
					AND pss.studiensemester_kurzbz = '.$STUDIENSEMESTER.'
					AND NOT EXISTS (
						SELECT 1
						FROM tbl_prestudentstatus spss
						WHERE spss.prestudent_id = pss.prestudent_id
							AND spss.status_kurzbz = '. $REJECTED_STATUS .'
							AND spss.studiensemester_kurzbz IN (
							SELECT ss.studiensemester_kurzbz FROM public.tbl_studiensemester ss WHERE ss.ende >
							(SELECT start FROM public.tbl_studiensemester sss WHERE studiensemester_kurzbz = '. $STUDIENSEMESTER .'))
					)
				 LIMIT 1
			) AS "Studiengang"
		FROM
			public.tbl_person p
		WHERE
			EXISTS (
				SELECT 1
				  FROM public.tbl_prestudent sps
				  JOIN public.tbl_studiengang ssg USING(studiengang_kz)
				WHERE sps.person_id = p.person_id
				  AND ssg.typ IN (' . $STUDIENGANG_TYP . ')
				  AND ' . $AUFGENOMMENER_STATUS . ' = (
						SELECT spss.status_kurzbz
						FROM public.tbl_prestudentstatus spss
						WHERE spss.prestudent_id = sps.prestudent_id
						ORDER BY spss.datum DESC, spss.insertamum DESC, spss.ext_id DESC
						LIMIT 1
					)
				AND EXISTS (
						SELECT 1
						FROM tbl_prestudentstatus spss
						WHERE spss.prestudent_id = sps.prestudent_id
						AND spss.status_kurzbz = ' . $AUFGENOMMENER_STATUS . '
						AND spss.studiensemester_kurzbz = ' . $STUDIENSEMESTER . '
				)
			)
			
		';

	$filterWidgetArray = array(
		'query' => $query,
		'app' => InfoCenter::APP,
		'datasetName' => 'aufgenommen',
		'filter_id' => $this->input->get('filter_id'),
		'requiredPermissions' => 'infocenter',
		'datasetRepresentation' => 'tablesorter',
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonId',
			ucfirst($this->p->t('person', 'vorname')) ,
			ucfirst($this->p->t('person', 'nachname')),
			ucfirst($this->p->t('lehre', 'studiengang'))
		),

		'formatRow' => function($datasetRaw)
		{
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?person_id=%s&origin_page=%s&fhc_controller_id=%s&prev_filter_id=%s">Details</a>',
				site_url('system/infocenter/InfoCenter/showDetails'),
				$datasetRaw->{'PersonId'},
				'aufgenommen',
				(isset($_GET['fhc_controller_id']) ? $_GET['fhc_controller_id'] : ''),
				(isset($_GET['filter_id']) ? $_GET['filter_id'] : '')
			);
			return $datasetRaw;
		}
	);

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
