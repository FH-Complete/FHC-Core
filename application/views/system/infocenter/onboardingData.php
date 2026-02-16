<?php

	$APP = '\'infocenter\'';
	$KENNZEICHEN = '\'eobRegistrierungsId\'';

$query = '
		SELECT
			p.person_id AS "PersonId",
			p.vorname AS "Vorname",
			p.nachname AS "Nachname",
			pl.zeitpunkt AS "LockDate",
			pl.lockuser AS "LockUser",
			rueck.datum_bis AS "HoldDate",
			rueck.bezeichnung AS "Rueckstellgrund"
		FROM public.tbl_person p
			JOIN tbl_kennzeichen ON p.person_id = tbl_kennzeichen.person_id AND kennzeichentyp_kurzbz = '. $KENNZEICHEN .'
			LEFT JOIN (
					SELECT tpl.person_id,
						   tpl.zeitpunkt,
						   sp.nachname AS lockuser
					  FROM system.tbl_person_lock tpl
					  JOIN public.tbl_benutzer sb USING (uid)
					  JOIN public.tbl_person sp ON sb.person_id = sp.person_id
					 WHERE tpl.app = '.$APP.'
				 ) pl ON p.person_id = pl.person_id
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
		WHERE p.person_id NOT IN (SELECT person_id FROM public.tbl_prestudent)';

	$filterWidgetArray = array(
		'query' => $query,
		'app' => InfoCenter::APP,
		'datasetName' => 'onboarding',
		'filter_id' => $this->input->get('filter_id'),
		'requiredPermissions' => 'infocenter',
		'datasetRepresentation' => 'tablesorter',
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'columnsAliases' => array(
			'PersonId',
			ucfirst($this->p->t('person', 'vorname')) ,
			ucfirst($this->p->t('person', 'nachname')),
			ucfirst($this->p->t('global', 'sperrdatum')),
			ucfirst($this->p->t('global', 'gesperrtVon')),
			ucfirst($this->p->t('infocenter', 'rueckstelldatum')),
			ucfirst($this->p->t('infocenter', 'rueckstellgrund')),
		),

		'formatRow' => function($datasetRaw) {
			/* NOTE: Dont use $this here for PHP Version compatibility */
			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s?person_id=%s&origin_page=%s&fhc_controller_id=%s&prev_filter_id=%s">Details</a>',
				site_url('system/infocenter/InfoCenter/showDetails'),
				$datasetRaw->{'PersonId'},
				'onboarding',
				(isset($_GET['fhc_controller_id']) ? $_GET['fhc_controller_id'] : ''),
				(isset($_GET['filter_id']) ? $_GET['filter_id'] : '')
			);

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

			if ($datasetRaw->{'Rueckstellgrund'} === null)
			{
				$datasetRaw->{'Rueckstellgrund'} = '-';
			}

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
