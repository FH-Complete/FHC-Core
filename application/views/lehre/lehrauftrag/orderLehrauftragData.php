<?php

$STUDIENSEMESTER = $studiensemester_selected;
$STUDIENGANG = (isset($studiengang_selected) && !is_null($studiengang_selected)) ? array($studiengang_selected) : $studiengang;
$AUSBILDUNGSSEMESTER = (isset($ausbildungssemester_selected) && !is_null($ausbildungssemester_selected)) ? $ausbildungssemester_selected : '1,2,3,4,5,6,7,8';

$query = '
SELECT
    /* provide extra row index for tabulator, because no other column has unique ids */
    ROW_NUMBER() OVER () AS "row_index",
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
    stundensatz,
    betrag,
    vertrag_id,
    vertrag_stunden,
    vertrag_betrag,
    vertrag_insertvon,
    vertrag_insertamum,
    vertrag_updatevon,
    vertrag_updateamum,
    mitarbeiter_uid,
    bestellt,
    erteilt,
    akzeptiert
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
                lema.stundensatz,
                TRUNC((lema.semesterstunden * lema.stundensatz), 2) AS "betrag",
                vertrag_id,
                vertragsstunden                                                                     AS "vertrag_stunden",
                vertrag.betrag                                                                      AS "vertrag_betrag",
                vertrag.insertvon                                                                   AS "vertrag_insertvon",
                vertrag.insertamum                                                                  AS "vertrag_insertamum",
                vertrag.updatevon                                                                   AS "vertrag_updatevon",
                vertrag.updateamum                                                                  AS "vertrag_updateamum",
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
              /* filter studiengang */
                lv.studiengang_kz IN ('. implode(',', $STUDIENGANG) . ')
                    /* filter studiensemester */
              AND le.studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\'
                /* filter active lehrveranstaltungen */
              AND lv.aktiv = TRUE
                /* filter active organisationseinheiten */
              AND oe.aktiv = TRUE
                /* filter ausbildungssemester */
              AND lv.semester IN  ('. $AUSBILDUNGSSEMESTER . ')
                /* filter dummies and invalid mitarbeiter */
              AND ma.personalnummer >= 0
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
                    pa.lehreinheit_id,
                    lv.lehrveranstaltung_id,
                    lv.bezeichnung                                                                      AS "lv_bezeichnung",
                    pa.projektarbeit_id                                                                 AS "projektarbeit_id",
                    le.studiensemester_kurzbz,
                    stg.studiengang_kz,
                    upper(stg.typ || stg.kurzbz)                                                        AS "stg_typ_kurzbz",
                    lv.orgform_kurzbz,
                    person.person_id,
                    \'Betreuung\'                                                                         AS "typ",
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
                    (vorname || \' \' || nachname)                                                      AS "lektor",
                    TRUNC(pb.stunden, 1)                                                                AS "stunden",
                    pb.stundensatz,
                    TRUNC((pb.stunden * pb.stundensatz), 2)                                             AS "betrag",
                    vertrag_id,
                    vertragsstunden                                                                     AS "vertrag_stunden",
                    vertrag.betrag                                                                      AS "vertrag_betrag",
                    vertrag.insertvon                                                                   AS "vertrag_insertvon",
                    vertrag.insertamum                                                                  AS "vertrag_insertamum",
                    vertrag.updatevon                                                                   AS "vertrag_updatevon",
                    vertrag.updateamum                                                                  AS "vertrag_updateamum"
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
                    /* filter studiengang */
                    lv.studiengang_kz IN ('. implode(',', $STUDIENGANG) . ')
                    /* filter studiensemester */
                  AND le.studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\'
                    /* filter active lehrveranstaltungen */
                  AND lv.aktiv = TRUE
                    /* filter ausbildungssemester */
                  AND lv.semester IN  ('. $AUSBILDUNGSSEMESTER . ')
                    /* filter active organisationseinheiten */
                  AND oe.aktiv = TRUE
            ) tmp_projektbetreuung
    ) auftraege
ORDER BY "typ" DESC, "auftrag", "lektor", "bestellt"
';
$filterWidgetArray = array(
    'query' => $query,
    'app' => Lehrauftrag::APP,
    'datasetName' => 'lehrauftragOrder',
    'filterKurzbz' => 'LehrauftragOrder',
    'requiredPermissions' => 'lehre', // TODO: change permission
    'datasetRepresentation' => 'tabulator',
    'reloadDataset' => true,    // reload query on page refresh
    'customMenu' => false,
    'hideOptions' => true,
    'hideMenu' => true,
    'columnsAliases' => array(  // TODO: use phrasen
        'Status', // alias for row_index, because row_index is formatted to display the status icons
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
        'Stundensatz',
        'Betrag',
        'Vertrag-ID',
        'Vertrag-Stunden',
        'Vertrag-Betrag',
        'Vertrag-ErstelltVon',
        'Vertrag-ErstelltDatum',
        'Vertrag-UpdateVon',
        'Vertrag-UpdateDatum',
        'UID',
        'Bestellt',
        'Erteilt',
        'Akzeptiert'
    ),
    'datasetRepOptions' => '{
        height: 700,   
        layout:"fitColumns",            // fit columns to width of table
	    responsiveLayout:"hide",        // hide columns that dont fit on the table     
	    movableColumns: true,           // allows changing column     
	    headerFilterPlaceholder: " ",
	    groupBy:"lehrveranstaltung_id",
	    groupToggleElement:"header",    //toggle group on click anywhere in the group header
	    groupHeader: function(value, count, data, group){
	       return func_groupHeader(data);
	    },
        columnCalcs:"both",             // show column calculations at top and bottom of table and in groups
	    index: "row_index",             // assign specific column as unique id (important for row indexing)
        selectable: true,               // allows row selection
        selectableRangeMode: "click",   // allows range selection using shift end click on end of range
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        selectableCheck: function(row){ 
            return func_selectableCheck(row);
        },
        rowUpdated:function(row){
            func_rowUpdated(row); 
        },
        rowFormatter:function(row){
            func_rowFormatter(row);
        },
        renderStarted:function(){
            func_renderStarted(this);
        },
        renderComplete:function(){
            func_renderComplete(this);
        },
        tableBuilt: function(){
            func_tableBuilt(this);
        }
    }', // tabulator properties
    'datasetRepFieldsDefs' => '{
        // column status is built dynamically in funcTableBuilt()
        row_index: {visible: false},  
        lehreinheit_id: {headerFilter:"input", bottomCalc:"count", 
            bottomCalcFormatter:function(cell){return "Anzahl: " + cell.getValue();}},
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
        stunden: {align:"right", 
            headerFilter:"input", headerFilterPlaceholder:">=", headerFilterFunc: hf_compareWithFloat,
            bottomCalc:"sum", bottomCalcParams:{precision:1}}, 
        stundensatz: {visible: false},
        betrag: {align:"right",  
            headerFilter:"input", headerFilterPlaceholder:">=", headerFilterFunc: hf_compareWithFloat,
            bottomCalc:"sum", bottomCalcParams:{precision:2}, bottomCalcFormatter:"money", 
            bottomCalcFormatterParams:{decimal: ",", thousand: ".", symbol:"€"}},
        vertrag_id: {visible: false},
        vertrag_stunden: {visible: false},
        vertrag_betrag: {visible: false},
        vertrag_insertvon: {visible: false},
        vertrag_insertamaum: {visible: false},
        vertrag_updatevon: {visible: false},
        vertrag_updateamum: {visible: false},
        mitarbeiter_uid: {visible: false},
        bestellt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate}, 
        erteilt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate},
        akzeptiert: {align:"center", headerFilter:"input", mutator: mut_formatStringDate}
    }', // col properties
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);

?>

