<?php
$UID = getAuthUID();
$PERIOD = $period; // filter Pruefungsprotokolle for given period

$query = "
SELECT
	tbl_abschlusspruefung.abschlusspruefung_id,
	tbl_person.nachname || ' ' || tbl_person.vorname as name,
	tbl_studiengangstyp.bezeichnung || ' ' || tbl_studiengang.bezeichnung,
	tbl_abschlusspruefung.datum,
	tbl_abschlusspruefung.freigabedatum
FROM
	lehre.tbl_abschlusspruefung
	JOIN public.tbl_student USING(student_uid)
	JOIN public.tbl_benutzer ON(student_uid=uid)
	JOIN public.tbl_person USING(person_id)
	JOIN public.tbl_studiengang ON(tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz)
	JOIN public.tbl_studiengangstyp USING(typ)
WHERE
	vorsitz='".$UID."'
	AND (
		('". $PERIOD. "' = 'today' AND datum = NOW()::date) OR
		('". $PERIOD. "' = 'lastWeek' AND datum >= (NOW() - interval '1 week')::date AND datum < NOW()::date) OR
		('". $PERIOD. "' = 'upcoming' AND datum > NOW()::date) OR
		('". $PERIOD. "' = 'all' AND datum >= '2020-05-27')
	)
ORDER BY datum, nachname, vorname
";

$filterWidgetArray = array(
    'query' => $query,
    'tableUniqueId' => 'pruefungsprotokoll',
	'requiredPermissions' => 'lehre/pruefungsbeurteilung',
    'datasetRepresentation' => 'tablesorter',
    'columnsAliases' => array(
		ucfirst($this->p->t('global', 'details')),
		ucfirst($this->p->t('global', 'name')),
		ucfirst($this->p->t('lehre', 'studiengang')),
		ucfirst($this->p->t('global', 'datum')),
	    ucfirst($this->p->t('global', 'status')),
    ),
	'formatRow' => function($datasetRaw) {

		/* NOTE: Dont use $this here for PHP Version compatibility */
		$datasetRaw->{'abschlusspruefung_id'} = sprintf(
			'<a href="%s?abschlusspruefung_id=%s" target="_blank">Protokoll ausfüllen</a>',
			site_url('lehre/Pruefungsprotokoll/Protokoll'),
			$datasetRaw->{'abschlusspruefung_id'}
		);

		if ($datasetRaw->{'datum'} == null)
		{
			$datasetRaw->{'datum'} = '-';
		}
		else
		{
			$datasetRaw->{'datum'} = date_format(date_create($datasetRaw->{'datum'}),'d.m.Y');
		}
		if ($datasetRaw->{'freigabedatum'} == null)
		{
			$datasetRaw->{'freigabedatum'} = 'offen';
		}
		else
		{
			$datasetRaw->{'freigabedatum'} = 'Freigegeben am: '.date_format(date_create($datasetRaw->{'freigabedatum'}),'d.m.Y');
		}
		return $datasetRaw;
	},
);

echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);

?>
