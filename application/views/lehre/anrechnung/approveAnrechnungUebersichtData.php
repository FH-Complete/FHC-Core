<?php
const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';

$STUDIENSEMESTER = $studiensemester_selected;
$STUDIENGAENGE_ENTITLED = implode(', ', $studiengaenge_entitled);						// alle STG mit Lese- und Schreibberechtigung
$ORGANISATIONSEINHEITEN_SCHREIBBERECHTIGT = "'" . implode('\',\'', $oes_schreibberechtigt) . "'";		// alle OE nur mit Schreibberechtigung; singlequote für jeden string notwendig
$LANGUAGE_INDEX = getUserLanguage() == 'German' ? '1' : '2';

$query = '
	WITH anrechnungen AS
	(
		SELECT DISTINCT
			anrechnung.anrechnung_id,
			anrechnung.lehrveranstaltung_id,
			anrechnung.begruendung_id,
			anrechnung.dms_id,
			CASE
				WHEN stg.typ || stg.kurzbz IN (' . $ORGANISATIONSEINHEITEN_SCHREIBBERECHTIGT . ') THEN TRUE
				ELSE FALSE
			END "schreibberechtigt",
			anrechnung.studiensemester_kurzbz,
			stg.studiengang_kz,
			stg.bezeichnung AS stg_bezeichnung,
			lv.orgform_kurzbz,
			(SELECT ausbildungssemester
			FROM public.tbl_prestudentstatus press
			WHERE press.prestudent_id = anrechnung.prestudent_id
			AND press.studiensemester_kurzbz = anrechnung.studiensemester_kurzbz
			AND press.status_kurzbz = \'Student\'
			ORDER BY press.datum DESC
			LIMIT 1
			),
			lv.bezeichnung AS lv_bezeichnung,
			lv.ects::numeric(4,1),
	        get_ects_summe_schulisch(student.student_uid, anrechnung.prestudent_id, stg.studiengang_kz) AS ectsSumSchulisch,
	        get_ects_summe_beruflich(student.student_uid) AS ectsSumBeruflich,
			(person.nachname || \' \' || person.vorname) AS "student",
			begruendung.bezeichnung AS "begruendung",
			dmsversion.name AS "dokument_bezeichnung",
			anrechnung.anmerkung_student,
			(SELECT COALESCE(
				zgvmaster.bezeichnung[' . $LANGUAGE_INDEX . '],
				zgv.bezeichnung[' . $LANGUAGE_INDEX . ']
				) AS zgv
			FROM public.tbl_prestudent
			LEFT JOIN bis.tbl_zgv zgv USING (zgv_code)
			LEFT JOIN bis.tbl_zgvmaster zgvmaster USING (zgvmas_code)
			WHERE prestudent_id = anrechnung.prestudent_id
			) AS zgv,
			anrechnung.insertamum::date AS "antragsdatum",
			empfehlung_anrechnung,
			(SELECT status_kurzbz
			FROM lehre.tbl_anrechnungstatus
			JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
			WHERE anrechnung_id = anrechnung.anrechnung_id
			ORDER BY insertamum DESC
			LIMIT 1
			) AS status_kurzbz,
			student.student_uid,
			anrechnung.prestudent_id
		FROM lehre.tbl_anrechnung AS anrechnung
		JOIN public.tbl_prestudent USING (prestudent_id)
		JOIN public.tbl_person AS person USING (person_id)
		JOIN public.tbl_studiengang AS stg USING (studiengang_kz)
		JOIN lehre.tbl_lehrveranstaltung AS lv USING (lehrveranstaltung_id)
		LEFT JOIN campus.tbl_dms_version AS dmsversion USING (dms_id)
		JOIN lehre.tbl_anrechnung_anrechnungstatus USING (anrechnung_id)
		JOIN lehre.tbl_anrechnung_begruendung AS begruendung USING (begruendung_id)
		JOIN public.tbl_student student USING (prestudent_id)
		WHERE anrechnung.studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
	    AND stg.studiengang_kz IN (' . $STUDIENGAENGE_ENTITLED . ')
	)

	SELECT  anrechnungen.anrechnung_id,
            anrechnungen.lehrveranstaltung_id,
			anrechnungen.begruendung_id,
			anrechnungen.dms_id,
			anrechnungen.schreibberechtigt,
			anrechnungen.studiensemester_kurzbz,
			anrechnungen.studiengang_kz,
			anrechnungen.stg_bezeichnung,
			anrechnungen.orgform_kurzbz,
			anrechnungen.ausbildungssemester,
			anrechnungen.lv_bezeichnung,
			anrechnungen.ects::float4 AS ects,
			NULL AS "ectsSumBisherUndNeu",
	        anrechnungen.ectsSumSchulisch::float4 AS "ectsSumSchulisch",
	        anrechnungen.ectsSumBeruflich::float4 AS "ectsSumBeruflich",
			anrechnungen.begruendung,
			anrechnungen.student,
			anrechnungen.dokument_bezeichnung,
			anrechnungen.anmerkung_student,
			anrechnungen.zgv,
            anrechnungen.antragsdatum,
			anrechnungen.empfehlung_anrechnung,
			anrechnungen.status_kurzbz,
            anrechnungstatus.bezeichnung_mehrsprachig[' . $LANGUAGE_INDEX . '] AS "status_bezeichnung",
            anrechnungen.prestudent_id,
            CASE
                WHEN (anrechnungen.empfehlung_anrechnung IS NULL AND anrechnungen.status_kurzbz = \'' . ANRECHNUNGSTATUS_PROGRESSED_BY_STGL . '\') THEN NULL
                ELSE
                (SELECT insertamum::date
                    FROM lehre.tbl_anrechnungstatus
                    JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
                    WHERE anrechnung_id = anrechnungen.anrechnung_id
                    AND status_kurzbz = \'' . ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR . '\'
                    ORDER BY insertamum DESC
                    LIMIT 1)
            END "empfehlungsanfrageAm",';

if ($configFachbereichsleitung === TRUE) {
	$query .= ' CASE
                WHEN (anrechnungen.empfehlung_anrechnung IS NULL AND anrechnungen.status_kurzbz = \'' . ANRECHNUNGSTATUS_PROGRESSED_BY_STGL . '\') THEN NULL
                ELSE
                (SELECT COALESCE(
                        STRING_AGG(CONCAT_WS(\' \', vorname, nachname), \', \') 
                    ) empfehlungsanfrageAn
                    FROM (
                        SELECT DISTINCT ON (benutzer.uid) bf.uid, vorname, nachname
                        FROM lehre.tbl_lehreinheit
						JOIN lehre.tbl_lehrveranstaltung lv using (lehrveranstaltung_id)
						JOIN public.tbl_organisationseinheit og using (oe_kurzbz)
						JOIN public.tbl_benutzerfunktion bf using (oe_kurzbz)		
                        JOIN public.tbl_benutzer benutzer ON bf.uid = benutzer.uid
                        JOIN public.tbl_person USING (person_id)
                        WHERE studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
						and bf.datum_von <= now()
						and (bf.datum_bis >= now() or bf.datum_bis is null)
						AND bf.funktion_kurzbz = \'Leitung\'
                        AND lehrveranstaltung_id = anrechnungen.lehrveranstaltung_id
                        AND benutzer.aktiv = TRUE
                        AND tbl_person.aktiv = TRUE
                        ORDER BY benutzer.uid, nachname, vorname
                        ) as tmp_empfehlungsanfrageEmpfaenger
                    )
            END "empfehlungsanfrageAn"';
} else {
	$query .= ' CASE
                WHEN (anrechnungen.empfehlung_anrechnung IS NULL AND anrechnungen.status_kurzbz = \'' . ANRECHNUNGSTATUS_PROGRESSED_BY_STGL . '\') THEN NULL
                ELSE
                (SELECT COALESCE(
                        STRING_AGG(CONCAT_WS(\' \', vorname, nachname), \', \') FILTER (WHERE lvleiter = TRUE),
                        STRING_AGG(CONCAT_WS(\' \', vorname, nachname), \', \') FILTER (WHERE lvleiter = FALSE)
                    ) empfehlungsanfrageAn
                    FROM (
                        SELECT DISTINCT ON (benutzer.uid) uid, vorname, nachname,
                        CASE WHEN lehrfunktion_kurzbz = \'LV-Leitung\' THEN TRUE ELSE FALSE END AS lvleiter
                        FROM lehre.tbl_lehreinheit
                        JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
                        JOIN public.tbl_benutzer benutzer ON lema.mitarbeiter_uid = benutzer.uid
                        JOIN public.tbl_person USING (person_id)
                        WHERE studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
                        AND lehrveranstaltung_id = anrechnungen.lehrveranstaltung_id
                        AND lema.mitarbeiter_uid NOT like \'_Dummy%\'
                        AND benutzer.aktiv = TRUE
                        AND tbl_person.aktiv = TRUE
                        ORDER BY benutzer.uid, lvleiter DESC, nachname, vorname
                        ) as tmp_empfehlungsanfrageEmpfaenger
                    )
            END "empfehlungsanfrageAn"';
}

$query .= '	FROM anrechnungen
	JOIN lehre.tbl_anrechnungstatus as anrechnungstatus ON (anrechnungstatus.status_kurzbz = anrechnungen.status_kurzbz)
	WHERE studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
	AND studiengang_kz IN (' . $STUDIENGAENGE_ENTITLED . ')
';

$filterWidgetArray = array(
	'query' => $query,
	'bootstrapVersion' => 5,
	'tableUniqueId' => 'approveAnrechnungUebersicht',
	'requiredPermissions' => 'lehre/anrechnung_genehmigen',
	'datasetRepresentation' => 'tabulator',
	'columnsAliases' => array(
		'anrechnung_id',
		'lehrveranstaltung_id',
		'begruendung_id',
		'dms_id',
		'Schreibberechtigt',
		'studiensemester_kurzbz',
		'studiengang_kz',
		ucfirst($this->p->t('lehre', 'studiengang')),
		ucfirst($this->p->t('lehre', 'organisationsform')),
		'Semester',
		ucfirst($this->p->t('lehre', 'lehrveranstaltung')),
		'ECTS (LV)',
		'ECTS (LV + Bisher)',
		'ECTS (Bisher schulisch)',
		'ECTS (Bisher beruflich',
		ucfirst($this->p->t('global', 'begruendung')),
		ucfirst($this->p->t('person', 'studentIn')),
		ucfirst($this->p->t('anrechnung', 'nachweisdokumente')),
		ucfirst($this->p->t('anrechnung', 'herkunft')),
		ucfirst($this->p->t('global', 'zgv')),
		ucfirst($this->p->t('anrechnung', 'antragdatum')),
		ucfirst($this->p->t('anrechnung', 'empfehlung')),
		'status_kurzbz',
		'Status',
		'PrestudentID',
		ucfirst($this->p->t('anrechnung', 'empfehlungsanfrageAm')),
		ucfirst($this->p->t('anrechnung', 'empfehlungsanfrageAn'))
	),

	'datasetRepOptions' => '{

		height: func_height(this),
		layout: "fitColumns",           // fit columns to width of table
		persistenceID: "approveAnrechnungUebersicht_V1",
		autoResize: false, 				// prevent auto resizing of table (false to allow adapting table size when cols are (de-)activated
		index: "anrechnung_id",             // assign specific column as unique id (important for row indexing)
        selectable: true,               // allow row selection
        selectableRangeMode: "click",   // allow range selection using shift end click on end of range
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        tableWidgetFooter: {
			selectButtons: true  // tableWidgetFooter properties are checked in _renderTabulatorFooterHTML function
		},
		selectableCheck: function(row){
            return func_selectableCheck(row);
        },
		rowFormatter:function(row){
			return func_rowFormatter(row);
		},
		
		columnDefaults:{
			tooltip:func_tooltips,
			headerFilterPlaceholder: " ",
		}
		
	 }', // tabulator properties

	'datasetRepFieldsDefs' => '{
		anrechnung_id: {visible: false, headerFilter:"input"},
		lehrveranstaltung_id: {visible: false, headerFilter:"input"},
		begruendung_id: {visible: false, headerFilter:"input"},
		dms_id: {visible: false, headerFilter:"input"},
		schreibberechtigt: {
			formatter:"tickCross", hozAlign:"center", 
		    headerFilter:"tickCross", headerFilterParams:{tristate: true}, headerFilterFunc: hf_schreibberechtigt
		},
		studiensemester_kurzbz: {visible: false, headerFilter:"input"},
		studiengang_kz: {visible: false, headerFilter:"input"},
		stg_bezeichnung: {headerFilter:"input"},
		orgform_kurzbz: {headerFilter:"input"},
		ausbildungssemester: {headerFilter:"input"},
		lv_bezeichnung: {headerFilter:"input"},
		ects: {headerFilter:"input", hozAlign:"center"},
		ectsSumBisherUndNeu: {formatter: format_ectsSumBisherUndNeu},
		ectsSumSchulisch: {visible: false, headerFilter:"input", hozAlign:"right"},
		ectsSumBeruflich: {visible: false, headerFilter:"input", hozAlign:"right"},
		begruendung: {headerFilter:"input", visible: true},
		
		student: {headerFilter:"input"},
		zgv: {headerFilter:"input"},
		dokument_bezeichnung: {headerFilter:"input", formatter:"link", formatterParams: paramLookup_dokBez},
		anmerkung_student: {headerFilter:"input"},
		antragsdatum: {hozAlign:"center", headerFilter:"input", mutator: mut_formatStringDate},
		empfehlung_anrechnung: {headerFilter:"input", hozAlign:"center", formatter: format_empfehlung_anrechnung, headerFilterFunc: hf_filterTrueFalse},
		status_kurzbz: {visible: false, headerFilter:"input"},
		status_bezeichnung: {headerFilter:"input"},
		prestudent_id: {visible: false, headerFilter:"input"},
		empfehlungsanfrageAm: {visible: false, hozAlign:"center", headerFilter:"input", mutator: mut_formatStringDate},
		empfehlungsanfrageAn: {visible: false, headerFilter:"input"}
	 }', // col properties
);

echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);

?>