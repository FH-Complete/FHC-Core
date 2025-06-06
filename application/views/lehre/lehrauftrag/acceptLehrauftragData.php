<?php

$STUDIENSEMESTER = $studiensemester_selected;
$UID = getAuthUID();
$PERSON_ID = getAuthPersonId();


$query = '
SELECT
    /* provide extra row index for tabulator, because no other column has unique ids */
    ROW_NUMBER() OVER () AS "row_index",
	auftrag,
	stg_typ_kurzbz,
	gruppe,
	typ,
    lehreinheit_id,
    lehrveranstaltung_id,
    projektarbeit_id,
    studiensemester_kurzbz,
    studiengang_kz,
	semester,
    orgform_kurzbz,
    person_id,
    lv_oe_kurzbz,
    stunden,
    betrag,
    vertrag_id,
    vertrag_stunden,
    vertrag_betrag,
    mitarbeiter_uid,
    bestellt,
    erteilt,
    akzeptiert,
      (SELECT
         nachname || \' \' || vorname
     FROM
         public.tbl_person
             JOIN public.tbl_benutzer benutzer USING (person_id)
     WHERE
             benutzer.uid = (
             SELECT
                 insertvon
             FROM
                 lehre.tbl_vertrag_vertragsstatus vvs
             WHERE
                 vvs.vertragsstatus_kurzbz = \'bestellt\'
               AND vvs.vertrag_id = auftraege.vertrag_id
         )
    )                    AS "bestellt_von",
    (SELECT
         nachname || \' \' || vorname
     FROM
         public.tbl_person
             JOIN public.tbl_benutzer benutzer USING (person_id)
     WHERE
             benutzer.uid = (
             SELECT
                 insertvon
             FROM
                 lehre.tbl_vertrag_vertragsstatus vvs
             WHERE
                 vvs.vertragsstatus_kurzbz = \'erteilt\'
               AND vvs.vertrag_id = auftraege.vertrag_id
         )
    )                    AS "erteilt_von",
    (SELECT
         nachname || \' \' || vorname
     FROM
         public.tbl_person
             JOIN public.tbl_benutzer benutzer USING (person_id)
     WHERE
             benutzer.uid = (
             SELECT
                 insertvon
             FROM
                 lehre.tbl_vertrag_vertragsstatus vvs
             WHERE
                 vvs.vertragsstatus_kurzbz = \'akzeptiert\'
               AND vvs.vertrag_id = auftraege.vertrag_id
         )
    )                    AS "akzeptiert_von"
FROM
    (
	/* Lehraufträge and -vertragsstati */
    SELECT *,
        /* concatinated and aggregated gruppen */
        (SELECT
			string_agg(
				CASE WHEN gruppe_kurzbz is null THEN
				   concat(upper(grpstg.typ || grpstg.kurzbz), \'-\', semester, verband, gruppe)
			   ELSE
				   gruppe_kurzbz
			   END
			   , \', \')
         FROM
             lehre.tbl_lehreinheitgruppe
			 JOIN public.tbl_studiengang grpstg USING(studiengang_kz)
         WHERE
             lehreinheit_id = tmp_lehrauftraege.lehreinheit_id
        )                                                 AS "gruppe",
        /* existing contracts with status bestellt */
        (SELECT
             datum
         FROM
             lehre.tbl_vertrag_vertragsstatus
         WHERE
             tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz = \'bestellt\'
           AND vertrag_id = tmp_lehrauftraege.vertrag_id) AS "bestellt",
        /* existing contracts with status erteilt */
        (SELECT
             datum
         FROM
             lehre.tbl_vertrag_vertragsstatus
         WHERE
             tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz = \'erteilt\'
           AND vertrag_id = tmp_lehrauftraege.vertrag_id) AS "erteilt",
        /* existing contracts with status akzeptiert */
        (SELECT
             datum
         FROM
             lehre.tbl_vertrag_vertragsstatus
         WHERE
             tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz = \'akzeptiert\'
           AND vertrag_id = tmp_lehrauftraege.vertrag_id) AS "akzeptiert"
    FROM
        (
             SELECT
                    lema.lehreinheit_id,
                    lv.lehrveranstaltung_id,
                    NULL                                                AS "projektarbeit_id",
                    le.studiensemester_kurzbz,
                    stg.studiengang_kz,
                    upper(stg.typ || stg.kurzbz)                                AS "stg_typ_kurzbz",
                    lv.orgform_kurzbz,
                    person.person_id,
                    upper(lv.lehrtyp_kurzbz)                            AS "typ",
                    (lv.bezeichnung || \' [\' || le.lehrform_kurzbz ||
                     \']\')                                               AS "auftrag",
                     lv.semester,
                    CASE
                        WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
                        WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
                        ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
                        END                                             AS "lv_oe_kurzbz",
                    TRUNC(lema.semesterstunden, 2)                      AS "stunden",
                    TRUNC((lema.semesterstunden * lema.stundensatz), 2) AS "betrag",
                    vertrag_id,
                    vertragsstunden                                     AS "vertrag_stunden",
                    vertrag.betrag                                      AS "vertrag_betrag",
                    mitarbeiter_uid
                FROM
                    lehre.tbl_lehreinheitmitarbeiter               lema
                        JOIN lehre.tbl_lehreinheit                 le USING (lehreinheit_id)
                        JOIN lehre.tbl_lehrveranstaltung           lv USING (lehrveranstaltung_id)
                        JOIN PUBLIC.tbl_organisationseinheit       oe USING (oe_kurzbz)
                        JOIN PUBLIC.tbl_mitarbeiter                ma USING (mitarbeiter_uid)
                        JOIN PUBLIC.tbl_benutzer                   benutzer
                             ON ma.mitarbeiter_uid = benutzer.uid
                        JOIN PUBLIC.tbl_person                     person USING (person_id)
                        LEFT JOIN lehre.tbl_vertrag                vertrag USING (vertrag_id)
                        LEFT JOIN lehre.tbl_vertrag_vertragsstatus vvs USING (vertrag_id)
                        JOIN PUBLIC.tbl_studiengang                stg ON stg.studiengang_kz = lv.studiengang_kz
                WHERE
                    /* filter lector */
                    mitarbeiter_uid =  \'' . $UID . '\'
                    /* filter studiensemester */
                  AND le.studiensemester_kurzbz =  \'' . $STUDIENSEMESTER . '\'
                    /* filter active lehrveranstaltungen */
                  AND lv.aktiv = TRUE
                    /* filter active organisationseinheiten */
                  AND oe.aktiv = TRUE
                    /* filter vertragsstatus to avoid showing before status is bestellt */
                  AND vvs.vertragsstatus_kurzbz IN (\'erteilt\', \'akzeptiert\')
        ) tmp_lehrauftraege

        UNION

	    /* Projektbetreuungsaufträge and -vertragsstati */
        SELECT *,
            /* mitarbeiter uid retrieved by person_id */
            /* NOTE: mitarbeiter MUST come after Select * to ensure correct order with select for tmp_lehrauftraege*/
            (SELECT
                 uid
             FROM
			 public.tbl_benutzer JOIN public.tbl_mitarbeiter ma
                    ON tbl_benutzer.uid = ma.mitarbeiter_uid
             WHERE
                 person_id = tmp_projektbetreuung.person_id
               ORDER BY aktiv DESC, updateaktivam DESC      -- accept inactive as some person_ids have no active, but order them last
               LIMIT 1)                                 AS "mitarbeiter_uid",
            /* concatinated and aggregated gruppen */
            (SELECT
				string_agg(
				   CASE WHEN gruppe_kurzbz is null THEN
					  concat(upper(grpstg.typ || grpstg.kurzbz), \'-\', semester, verband, gruppe)
				  ELSE
					  gruppe_kurzbz
				  END
				  , \', \')
             FROM
                 lehre.tbl_lehreinheitgruppe
				 JOIN public.tbl_studiengang grpstg USING(studiengang_kz)
             WHERE
                     lehreinheit_id = tmp_projektbetreuung.lehreinheit_id
            )                                                    AS "gruppe",
            /* existing contracts with status bestellt */
            (SELECT
                 datum
             FROM
                 lehre.tbl_vertrag_vertragsstatus
             WHERE
                 tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz = \'bestellt\'
               AND vertrag_id = tmp_projektbetreuung.vertrag_id) AS "bestellt",
            /* existing contracts with status erteilt */
            (SELECT
                 datum
             FROM
                 lehre.tbl_vertrag_vertragsstatus
             WHERE
                 tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz = \'erteilt\'
               AND vertrag_id = tmp_projektbetreuung.vertrag_id) AS "erteilt",
            /* existing contracts with status akzeptiert */
            (SELECT
                 datum
             FROM
                 lehre.tbl_vertrag_vertragsstatus
             WHERE
                 tbl_vertrag_vertragsstatus.vertragsstatus_kurzbz = \'akzeptiert\'
               AND vertrag_id = tmp_projektbetreuung.vertrag_id) AS "akzeptiert"
        FROM
            (
                SELECT
                    pa.lehreinheit_id,
                    lv.lehrveranstaltung_id,
                    pa.projektarbeit_id::text                                                                 AS "projektarbeit_id",
                    le.studiensemester_kurzbz,
                    stg.studiengang_kz,
                    upper(stg.typ || stg.kurzbz)                                                        AS "stg_typ_kurzbz",
                    lv.orgform_kurzbz,
                    person.person_id,
                    \'Betreuung\'                                                                       AS "typ",
                    (betreuerart_kurzbz || \' \' ||
                     (SELECT
                          nachname || \' \' || vorname
                      FROM
                          PUBLIC.tbl_person
                              JOIN PUBLIC.tbl_benutzer USING (person_id)
                      WHERE
                          uid = pa.student_uid
                     )
                        || \' [\' || projekttyp_kurzbz || \'arbeit]\') AS "auftrag",
                    lv.semester,
                    CASE
                        WHEN oe.organisationseinheittyp_kurzbz =
                             \'Kompetenzfeld\' THEN (
                            \'KF \' || oe.bezeichnung)
                        WHEN oe.organisationseinheittyp_kurzbz =
                             \'Department\' THEN (
                            \'DEP \' || oe.bezeichnung)
                        ELSE (oe.organisationseinheittyp_kurzbz ||
                              \' \' || oe.bezeichnung)
                        END                                                                             AS "lv_oe_kurzbz",
                    TRUNC(pb.stunden, 1)                                                                AS "stunden",
                    TRUNC((pb.stunden * pb.stundensatz), 2)                                             AS "betrag",
                    vertrag_id,
                    vertragsstunden                                                                     AS "vertrag_stunden",
                    vertrag.betrag                                                                      AS "vertrag_betrag"
                FROM
                    lehre.tbl_projektbetreuer                      pb
                        JOIN lehre.tbl_projektarbeit               pa USING (projektarbeit_id)
                        JOIN lehre.tbl_lehreinheit                 le USING (lehreinheit_id)
                        JOIN lehre.tbl_lehrveranstaltung           lv USING (lehrveranstaltung_id)
                        JOIN PUBLIC.tbl_organisationseinheit       oe USING (oe_kurzbz)
                        JOIN PUBLIC.tbl_person                     person USING (person_id)
                        LEFT JOIN lehre.tbl_vertrag                vertrag USING (vertrag_id)
                        LEFT JOIN lehre.tbl_vertrag_vertragsstatus vvs USING (vertrag_id)
                        JOIN PUBLIC.tbl_studiengang                stg
                             ON stg.studiengang_kz = lv.studiengang_kz
                WHERE
                    /* filter projektbetreuuer */
                    pb.person_id =  \'' . $PERSON_ID . '\'
                    /* filter studiensemester */
                  AND le.studiensemester_kurzbz =  \'' . $STUDIENSEMESTER . '\'
                    /* filter active lehrveranstaltungen */
                  AND lv.aktiv = TRUE
                    /* filter active organisationseinheiten */
                  AND oe.aktiv = TRUE
                    /* filter vertragsstatus to avoid showing before status is erteilt */
                  AND vvs.vertragsstatus_kurzbz IN (\'erteilt\', \'akzeptiert\')
            ) tmp_projektbetreuung
    ) auftraege
ORDER BY "akzeptiert" NULLS FIRST, "erteilt" NULLS LAST, "bestellt"
';

$filterWidgetArray = array(
    'query' => $query,
    'bootstrapVersion' => 5,
    'tableUniqueId' => 'acceptLehrauftrag',
    'requiredPermissions' => 'lehre/lehrauftrag_akzeptieren',
    'datasetRepresentation' => 'tabulator',
    'columnsAliases' => array(
        'Status',   // alias for row_index, because row_index is formatted to display the status icons
	    ucfirst($this->p->t('lehre', 'lehrveranstaltung')). '- / '.
	        ucfirst($this->p->t('ui', 'projekt')). lcfirst($this->p->t('ui', 'bezeichnung')),
	    ucfirst($this->p->t('lehre', 'studiengang')),
	    ucfirst($this->p->t('lehre', 'gruppe')),
	    ucfirst($this->p->t('global', 'typ')),
	    ucfirst($this->p->t('lehre', 'lehreinheit')),
	    ucfirst($this->p->t('lehre', 'lehrveranstaltung')). '-ID',
	    ucfirst($this->p->t('ui', 'projektarbeit')). '-ID',
	    ucfirst($this->p->t('lehre', 'studiensemester')),
        ucfirst($this->p->t('lehre', 'studiengang')). '-'. ucfirst($this->p->t('ui', 'kz')),
		'Semester',
	    ucfirst($this->p->t('lehre', 'organisationsform')),
        'Person-ID',
	    ucfirst($this->p->t('lehre', 'organisationseinheit')),
	    ucfirst($this->p->t('ui', 'stunden')),
	    ucfirst($this->p->t('ui', 'betrag')),
	    ucfirst($this->p->t('ui', 'vertrag')). '-ID',
	    ucfirst($this->p->t('ui', 'vertrag')). '-'. ucfirst($this->p->t('ui', 'stunden')),
	    ucfirst($this->p->t('ui', 'vertrag')). '-'. ucfirst($this->p->t('ui', 'betrag')),
        'UID',
	    ucfirst($this->p->t('ui', 'bestellt')),
	    ucfirst($this->p->t('ui', 'erteilt')),
	    ucfirst($this->p->t('ui', 'angenommen')),
	    ucfirst($this->p->t('ui', 'bestelltVon')),
	    ucfirst($this->p->t('ui', 'erteiltVon')),
	    ucfirst($this->p->t('ui', 'angenommenVon'))
    ),
    'datasetRepOptions' => '{
		height: func_height(this),
		layout: "fitColumns",           // fit columns to width of table
		autoResize: false, 				// prevent auto resizing of table (false to allow adapting table size when cols are (de-)activated
        index: "row_index",             // assign specific column as unique id (important for row indexing)
        selectable: true,               // allow row selection
        selectableRangeMode: "click",   // allow range selection using shift end click on end of range
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        selectableCheck: function(row){
            return func_selectableCheck(row);
        },
        rowFormatter:function(row){
            func_rowFormatter(row);
        },
      
		tableWidgetFooter: {
			selectButtons: true
		},
        columnsDefaults:{
            headerFilterPlaceholder: " ",
        }
    }', // tabulator properties
    'datasetRepFieldsDefs' => '{
        row_index: {visible:false},     // necessary for row indexing
		auftrag: {
			headerFilter:"input", widthGrow: 3,
		 	bottomCalc:"count", bottomCalcFormatter:function(cell){return "'. ucfirst($this->p->t('global', 'anzahl')). ': " + cell.getValue();}
		 },
	 	stg_typ_kurzbz: {headerFilter:"input"},
		gruppe: {headerFilter:"input"},
		typ: {headerFilter:"input"},
        lehreinheit_id: {visible: false, headerFilter:"input"},
        lehrveranstaltung_id: {visible: false, headerFilter:"input"},
        projektarbeit_id: {visible: false, headerFilter:"input"},
        studiensemester_kurzbz: {visible: false, headerFilter:"input"},
        studiengang_kz: {visible: false, headerFilter:"input"},
		semester: {headerFilter:"input"},
        orgform_kurzbz: {visible: false, headerFilter:"input"},
        person_id: {visible: false, headerFilter:"input"},
        lv_oe_kurzbz: {visible: false, headerFilter:"input"},
        stunden: {hozAlign:"right", formatter: form_formatNulltoStringNumber, formatterParams:{precision:2},
            headerFilter:"input", headerFilterFunc: hf_filterStringnumberWithOperator,
            bottomCalc:"sum", bottomCalcParams:{precision:2}
		},
        betrag: {hozAlign:"right", formatter: form_formatNulltoStringNumber,
            headerFilter:"input", headerFilterFunc: hf_filterStringnumberWithOperator,
            bottomCalc:"sum", bottomCalcParams:{precision:2}, bottomCalcFormatter:"money", bottomCalcFormatterParams:{decimal: ",", thousand: ".", symbol:"€"}
		},
        vertrag_id: {visible: false},
        vertrag_stunden: {visible: false},
        vertrag_betrag: {visible: false},
        mitarbeiter_uid: {visible: false, headerFilter:"input"},
        bestellt: {hozAlign:"center", headerFilter:"input", mutator: mut_formatStringDate, tooltip: bestellt_tooltip},
        erteilt: {hozAlign:"center", headerFilter:"input", mutator: mut_formatStringDate, tooltip: erteilt_tooltip},
        akzeptiert: {hozAlign:"center", headerFilter:"input", mutator: mut_formatStringDate, tooltip: akzeptiert_tooltip},
        bestellt_von: {visible: false, headerFilter:"input"},
        erteilt_von: {visible: false, headerFilter:"input"},
        akzeptiert_von: {visible: false, headerFilter:"input"}
    }', // col properties
);

echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);

?>
