<?php

$STUDIENSEMESTER = $studiensemester_selected;
$ORGANISATIONSEINHEIT = (isset($organisationseinheit_selected) && !is_null($organisationseinheit_selected)) ? array($organisationseinheit_selected) : $organisationseinheit;
$AUSBILDUNGSSEMESTER = (isset($ausbildungssemester_selected) && !is_null($ausbildungssemester_selected)) ? $ausbildungssemester_selected : '1,2,3,4,5,6,7,8';

$query = '
SELECT
    /* provide extra row index for tabulator, because no other column has unique ids */
    ROW_NUMBER() OVER () AS "row_index",
    personalnummer,
    lehreinheit_id,
    lehrveranstaltung_id,
    lv_bezeichnung,
    projektarbeit_id,
    studiensemester_kurzbz,
    studiengang_kz,
    stg_typ_kurzbz,
    orgform_kurzbz,
    person_id,
    typ,
    auftrag,
    semester,
    lv_oe_kurzbz,
    gruppe,
    lektor,
    stunden,
    betrag,
    vertrag_id,
    vertrag_betrag,
    mitarbeiter_uid,
    bestellt,
    erteilt,
    akzeptiert,
      (SELECT
         vorname || \' \' || nachname
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
         vorname || \' \' || nachname
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
         vorname || \' \' || nachname
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
             string_agg(concat(stg_typ_kurzbz, \'-\', semester, verband, gruppe,
                               \'\n\' || gruppe_kurzbz), \', \')
         FROM
             lehre.tbl_lehreinheitgruppe
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
                /* lehrauftraege also planned with dummies, therefore personalnummer is needed */
                ma.personalnummer,
                lema.lehreinheit_id,
                lv.lehrveranstaltung_id,
                lv.bezeichnung                                      AS "lv_bezeichnung",
                NULL                                                AS "projektarbeit_id",
                le.studiensemester_kurzbz,
                stg.studiengang_kz,
                upper(stg.typ || stg.kurzbz)                        AS "stg_typ_kurzbz",
                lv.orgform_kurzbz,
                person.person_id,
                upper(lv.lehrtyp_kurzbz)                            AS "typ",
                (lv.bezeichnung || \' [\' || le.lehrform_kurzbz ||
                 \']\')                                             AS "auftrag",
                 lv.semester,
                CASE
                    WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
                    WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
                    ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
                    END                                             AS "lv_oe_kurzbz",
                (person.vorname || \' \' || person.nachname)        AS "lektor",
                TRUNC(lema.semesterstunden, 1)                      AS "stunden",
                TRUNC((lema.semesterstunden * lema.stundensatz), 2) AS "betrag",
                vertrag_id,
                vertrag.betrag                                                                      AS "vertrag_betrag",
                mitarbeiter_uid
            FROM
                lehre.tbl_lehreinheitmitarbeiter         lema
                    JOIN lehre.tbl_lehreinheit           le USING (lehreinheit_id)
                    JOIN lehre.tbl_lehrveranstaltung     lv USING (lehrveranstaltung_id)
                    JOIN PUBLIC.tbl_organisationseinheit oe USING (oe_kurzbz)
                    JOIN PUBLIC.tbl_mitarbeiter          ma USING (mitarbeiter_uid)
                    JOIN PUBLIC.tbl_benutzer             benutzer
                         ON ma.mitarbeiter_uid = benutzer.uid
                    JOIN PUBLIC.tbl_person               person USING (person_id)
                    LEFT JOIN lehre.tbl_vertrag          vertrag USING (vertrag_id)
                    JOIN PUBLIC.tbl_studiengang          stg ON stg.studiengang_kz = lv.studiengang_kz
            WHERE
             /* filter organisationseinheit */
                lv.oe_kurzbz IN (\'' . implode('\',\'', $ORGANISATIONSEINHEIT) . '\')
                /* filter studiensemester */
              AND le.studiensemester_kurzbz =  \'' . $STUDIENSEMESTER . '\'
                /* filter active lehrveranstaltungen */
              AND lv.aktiv = TRUE
                /* filter active organisationseinheiten */
              AND oe.aktiv = TRUE
                /* filter ausbildungssemester */
              AND lv.semester IN  ('. $AUSBILDUNGSSEMESTER . ')
        ) tmp_lehrauftraege

        UNION

	    /* Projektbetreuungsaufträge and -vertragsstati */
        SELECT *,
            /* mitarbeiter uid retrieved by person_id */
            /* NOTE: mitarbeiter MUST come after Select * to ensure correct order with select for tmp_lehrauftraege*/
            (SELECT
                 uid
             FROM
                 public.tbl_benutzer
             WHERE
                 person_id = tmp_projektbetreuung.person_id
               ORDER BY aktiv DESC, updateaktivam DESC      -- accept inactive as some person_ids have no active, but order them last
               LIMIT 1)                                 AS "mitarbeiter_uid",
            /* concatinated and aggregated gruppen */
            (SELECT
                 string_agg(concat(stg_typ_kurzbz, \'-\', semester, verband, gruppe,
                                   \'\n\' || gruppe_kurzbz), \', \')
             FROM
                 lehre.tbl_lehreinheitgruppe
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
                    /* projektbetreuung does not plan with dummies, therefore no need to retrieve personalnummer */
                    NULL                                                                                AS personalnummer,
                    pa.lehreinheit_id,
                    lv.lehrveranstaltung_id,
                    lv.bezeichnung                                                                      AS "lv_bezeichnung",
                    pa.projektarbeit_id                                                                 AS "projektarbeit_id",
                    le.studiensemester_kurzbz,
                    stg.studiengang_kz,
                    upper(stg.typ || stg.kurzbz)                                                        AS "stg_typ_kurzbz",
                    lv.orgform_kurzbz,
                    person.person_id,
                    \'Betreuung\'                                                                       AS "typ",
                    (betreuerart_kurzbz || \' \' ||
                     (SELECT
                          vorname || \' \' || nachname
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
                    (vorname || \' \' || nachname)                                                        AS "lektor",
                    TRUNC(pb.stunden, 1)                                                                AS "stunden",
                    TRUNC((pb.stunden * pb.stundensatz), 2)                                             AS "betrag",
                    vertrag_id,
                    vertrag.betrag                                                                      AS "vertrag_betrag"
                FROM
                    lehre.tbl_projektbetreuer                pb
                        JOIN lehre.tbl_projektarbeit         pa USING (projektarbeit_id)
                        JOIN lehre.tbl_lehreinheit           le USING (lehreinheit_id)
                        JOIN lehre.tbl_lehrveranstaltung     lv USING (lehrveranstaltung_id)
                        JOIN PUBLIC.tbl_organisationseinheit oe USING (oe_kurzbz)
                        JOIN PUBLIC.tbl_person               person USING (person_id)
                        LEFT JOIN lehre.tbl_vertrag          vertrag USING (vertrag_id)
                        JOIN PUBLIC.tbl_studiengang          stg
                             ON stg.studiengang_kz = lv.studiengang_kz
                WHERE
                    /* filter organisationseinheit */
                    lv.oe_kurzbz IN (\'' . implode('\',\'', $ORGANISATIONSEINHEIT) . '\')
                    /* filter studiensemester */
                  AND le.studiensemester_kurzbz =  \'' . $STUDIENSEMESTER . '\'
                    /* filter active lehrveranstaltungen */
                  AND lv.aktiv = TRUE
                    /* filter active organisationseinheiten */
                  AND oe.aktiv = TRUE
                    /* filter ausbildungssemester */
                  AND lv.semester IN  ('. $AUSBILDUNGSSEMESTER . ')
            ) tmp_projektbetreuung
    ) auftraege
ORDER BY "typ" DESC, "auftrag", "personalnummer" DESC, "lektor", "bestellt", "erteilt"
';

$filterWidgetArray = array(
    'query' => $query,
    'app' => LehrauftragErteilen::APP,
    'datasetName' => 'lehrauftragApprove',
    'filterKurzbz' => 'LehrauftragApprove',
    'requiredPermissions' => 'lehre', // TODO: change permission
    'datasetRepresentation' => 'tabulator',
    'reloadDataset' => true,    // reload query on page refresh
    'customMenu' => false,
    'hideOptions' => true,
    'hideMenu' => true,
    'columnsAliases' => array(  // TODO: use phrasen
        'Status', // alias for row_index, because row_index is formatted to display the status icons
        'Personalnummer',
        'LE-ID',
        'LV-ID',
        'LV',
        'PA-ID',
        'Studiensemester',
        'Studiengang-KZ',
        'Studiengang',
        'OrgForm',
        'Person-ID',
        'Typ',
        'Auftrag',
        'Semester',
        'Organisationseinheit',
        'Gruppe',
        'Lektor',
        'Stunden',
        'Betrag',
        'Vertrag-ID',
        'Vertrag-Betrag',
        'UID',
        'Bestellt',
        'Erteilt',
        'Akzeptiert',
        'Bestellt von',
        'Erteilt von',
        'Angenommen von'
    ),
    'datasetRepOptions' => '{
        height: 700,   
        layout: "fitColumns",           // fit columns to width of table
	    responsiveLayout: "hide",       // hide columns that dont fit on the table    
	    movableColumns: true,           // allows changing column 
	    headerFilterPlaceholder: " ",
	    groupBy:"lehrveranstaltung_id",
	    groupToggleElement:"header",    //toggle group on click anywhere in the group header
	    groupHeader: function(value, count, data, group){
	       return func_groupHeader(data);
	    },
        columnCalcs:"both",             // show column calculations at top and bottom of table and in groups
        index: "row_index",             // assign specific column as unique id (important for row indexing)
        selectable: true,               // allow row selection
        selectableRangeMode: "click",   // allow range selection using shift end click on end of range
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        selectableCheck: function(row){ 
            return func_selectableCheck(row);       
        },      
        rowUpdated:function(row){
             func_rowUpdated(row);    
        },
        rowFormatter:function(row)
        {
            func_rowFormatter(row);
        },
        renderStarted:function(){
            func_renderStarted(this);
        },
        tableBuilt: function(){
            func_tableBuilt(this);
        }
    }', // tabulator properties
    'datasetRepFieldsDefs' => '{
        // column status is built dynamically in funcTableBuilt(),
        row_index: {visible:false},     // necessary for row indexing
        personalnummer: {visible: false},
        lehreinheit_id: {headerFilter:"input", bottomCalc:"count", bottomCalcFormatter:function(cell){return "Anzahl: " + cell.getValue();},},
        lehrveranstaltung_id: {headerFilter:"input"},
        lv_bezeichnung: {visible: false},
        projektarbeit_id: {visible: false},
        studiensemester_kurzbz: {headerFilter:"input"},
        studiengang_kz: {visible: false},  
        stg_typ_kurzbz: {visible: false}, 
        orgform_kurzbz: {headerFilter:"input"},
        person_id: {visible: false},
        typ: {headerFilter:"input"},
        auftrag: {headerFilter:"input", width:"20%"},
        semester: {headerFilter:"input"}, 
        lv_oe_kurzbz: {headerFilter:"input"},
        gruppe: {headerFilter:"input"},
        lektor: {headerFilter:"input"},
        stunden: {align:"right", headerFilter:"input", bottomCalc:"sum", bottomCalcParams:{precision:1}}, 
        betrag: {align:"right",  headerFilter:"input", headerFilterPlaceholder:">=", headerFilterFunc: hf_compareWithFloat,
            bottomCalc:"sum", bottomCalcParams:{precision:2}, bottomCalcFormatter:"money", bottomCalcFormatterParams:{decimal: ",", thousand: ".", symbol:"€"}},
        vertrag_id: {visible: false},
        vertrag_betrag: {visible: false},
        mitarbeiter_uid: {visible: false},
        bestellt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate, tooltip: bestellt_tooltip}, 
        erteilt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate, tooltip: erteilt_tooltip},
        akzeptiert: {align:"center", headerFilter:"input", mutator: mut_formatStringDate, tooltip: akzeptiert_tooltip},
        bestellt_von: {visible: false},
        erteilt_von: {visible: false},
        akzeptiert_von: {visible: false},
    }', // col properties
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);

?>

