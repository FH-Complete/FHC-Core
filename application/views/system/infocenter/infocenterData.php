<?php

	$APP = 'infocenter';

	$filterWidgetArray = array(
		'query' => '
		SELECT
				p.person_id AS "PersonId",
				p.vorname AS "Vorname",
				p.nachname AS "Nachname",
				p.gebdatum AS "Gebdatum",
				(
					SELECT zeitpunkt
					FROM system.tbl_log
					WHERE taetigkeit_kurzbz = \'bewerbung\'
					AND person_id = p.person_id
					ORDER BY zeitpunkt DESC
					LIMIT 1
				) AS "LastAction",
				(
					SELECT insertvon
					FROM system.tbl_log
					WHERE taetigkeit_kurzbz = \'bewerbung\'
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
						AND pss.bewerbung_abgeschicktamum IS NOT NULL
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
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
						AND pss.bewerbung_abgeschicktamum IS NOT NULL
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
					LIMIT 1
				) AS "AnzahlAbgeschickt",
				array_to_string(
					(
					SELECT array_agg(tbl_studiengang.kurzbzlang)
					FROM
						public.tbl_prestudentstatus pss
						INNER JOIN public.tbl_prestudent ps USING(prestudent_id)
						JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE pss.status_kurzbz = \'Interessent\'
						AND pss.bewerbung_abgeschicktamum IS NOT NULL
						AND ps.person_id = p.person_id
						AND tbl_studiengang.typ in(\'b\')
					LIMIT 1
					),\',\'
				) AS "StgAbgeschickt",
				pl.zeitpunkt AS "LockDate"
			FROM public.tbl_person p
		LEFT JOIN (SELECT person_id, zeitpunkt FROM system.tbl_person_lock WHERE app = \''.$APP.'\') pl USING(person_id)
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
								AND bestaetigtam IS NULL
								AND studiensemester_kurzbz IN (
									SELECT studiensemester_kurzbz
									FROM public.tbl_studiensemester
									WHERE ende >= NOW()
							)
					)
				)
			ORDER BY "LastAction" DESC
		',
		'hideHeader' => false,
		'hideSave' => false,
		'checkboxes' => 'PersonId',
		'additionalColumns' => array('Details'),
		'formatRaw' => function($datasetRaw) {

			$datasetRaw->{'Details'} = sprintf(
				'<a href="%s%s">Details</a>',
				base_url('index.ci.php/system/infocenter/InfoCenter/showDetails/'),
				$datasetRaw->{'PersonId'}
			);

			if ($datasetRaw->{'SendDate'} == null)
			{
				$datasetRaw->{'SendDate'} = 'Not sent';
			}

			if ($datasetRaw->{'LastAction'} == null)
			{
				$datasetRaw->{'LastAction'} = 'Not logged';
			}

			if ($datasetRaw->{'User/Operator'} == '')
			{
				$datasetRaw->{'User/Operator'} = 'NA';
			}

			if ($datasetRaw->{'LockDate'} == null)
			{
				$datasetRaw->{'LockDate'} = 'Not locked';
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
