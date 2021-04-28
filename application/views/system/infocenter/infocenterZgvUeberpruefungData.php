<?php

$APP = '\'infocenter\'';
$INTERESSENT_STATUS = '\'Interessent\'';
$TAETIGKEIT_KURZBZ = '\'bewerbung\', \'kommunikation\'';
$LOGDATA_NAME = '\'Login with code\', \'Login with user\', \'New application\'';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
$oeKurz = $rechte->getOEkurzbz('lehre/zgvpruefung');
$oeKurz = '\''. implode('\',\'', $oeKurz) . '\'';

$query = '
		SELECT
			ps.prestudent_id AS "PreStudentID",
			p.vorname AS "Vorname",
			p.nachname AS "Nachname",
			sg.kurzbzlang AS "Studiengang",
		    zgvstatus.status as "Status"
		FROM public.tbl_zgvpruefungstatus_status zgvstatus
		JOIN public.tbl_zgvpruefung zgv USING (zgvpruefung_id)
		JOIN public.tbl_prestudent ps USING (prestudent_id)
		JOIN public.tbl_person p USING(person_id)
		JOIN public.tbl_studiengang sg USING(studiengang_kz)
		WHERE oe_kurzbz IN ('. $oeKurz .')
		AND zgvstatus.datum IN (
		    SELECT MAX(zgvstatus.datum) 
		    FROM public.tbl_zgvpruefungstatus_status zgvstatus GROUP BY zgvstatus.zgvpruefung_id)
	    ORDER BY ps.prestudent_id
	';

$filterWidgetArray = array(
    'query' => $query,
    'app' => 'infocenter',
    'datasetName' => 'zgvUeberpruefung',
    'filter_id' => $this->input->get('filter_id'),
    'requiredPermissions' => 'lehre/zgvpruefung',
    'datasetRepresentation' => 'tablesorter',
    'additionalColumns' => array('Details'),
    'hideOptions' => true,
    'columnsAliases' => array(

    ),
    'formatRow' => function($datasetRaw) {

        /* NOTE: Dont use $this here for PHP Version compatibility */
        $datasetRaw->{'Details'} = sprintf(
            '<a href="%s?prestudent_id=%s&origin_page=%s&fhc_controller_id=%s&prev_filter_id=%s">Details</a>',
            site_url('system/infocenter/InfoCenter/showZGVDetails'),
            $datasetRaw->{'PreStudentID'},
            'ZGVUeberpruefung',
            (isset($_GET['fhc_controller_id']) ? $_GET['fhc_controller_id'] : ''),
            (isset($_GET['filter_id']) ? $_GET['filter_id'] : '')
        );

        switch ($datasetRaw->{'Status'})
        {
            case 'accepted' :
                $datasetRaw->{'Status'} = $this->p->t('infocenter', 'zgvErfuellt');
                break;
            case 'rejected' :
                $datasetRaw->{'Status'} = $this->p->t('infocenter', 'zgvNichtErfuellt');
                break;
            case 'accepted_pruefung' :
                $datasetRaw->{'Status'} = $this->p->t('infocenter', 'zgvErfuelltPruefung');
                break;
        }

        return $datasetRaw;
    },
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);
?>
