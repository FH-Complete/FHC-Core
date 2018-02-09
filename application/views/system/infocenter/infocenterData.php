<?php
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
				) AS "AnzahlAbgeschickt"
			FROM public.tbl_person p
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
		'hideHeader' => true,
		'hideSave' => true,
		'checkboxes' => array('PersonId'),
		'additionalColumns' => array('Details'),
		'formatRaw' => function($fieldName, $fieldValue, $datasetRaw) {

			if ($fieldName == 'Details')
			{
				$link = '<a href="%s%s">Details</a>';

				$datasetRaw->{$fieldName} = sprintf(
					$link,
					base_url('index.ci.php/system/infocenter/InfoCenter/showDetails/'),
					$datasetRaw->PersonId
				);
			}

			if ($fieldName == 'SendDate')
			{
				if ($datasetRaw->{$fieldName} == '01.01.1970 01:00:00')
				{
					$datasetRaw->{$fieldName} = 'Not sent';
				}
			}

			if ($fieldName == 'LastAction')
			{
				if ($datasetRaw->{$fieldName} == '01.01.1970 01:00:00')
				{
					$datasetRaw->{$fieldName} = 'Not logged';
				}
			}

			if ($fieldName == 'User/Operator')
			{
				if ($datasetRaw->{$fieldName} == '')
				{
					$datasetRaw->{$fieldName} = 'NA';
				}
			}

			return $datasetRaw;
		}
	);

	$filterId = isset($_GET[InfoCenter::FILTER_ID]) ? $_GET[InfoCenter::FILTER_ID] : null;

	if (isset($filterId) && is_numeric($filterId))
	{
		$filterWidgetArray[InfoCenter::FILTER_ID] = $filterId;
	}
	else
	{
		$filterWidgetArray['app'] = 'infocenter';
		$filterWidgetArray['datasetName'] = 'PersonActions';
		$filterWidgetArray['filterKurzbz'] = 'InfoCenterNotSentApplicationAll';
	}

	echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
