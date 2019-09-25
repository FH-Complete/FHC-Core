<?php

$STUDIENSEMESTER = $studiensemester_selected;
$ORGANISATIONSEINHEIT = (isset($organisationseinheit_selected) && !is_null($organisationseinheit_selected)) ? array($organisationseinheit_selected) : $organisationseinheit;

$query = '
SELECT
    /* provide extra row index for tabulator, because no other column has unique ids */
    ROW_NUMBER() OVER () AS "row_index", *
FROM
    (
	/* Lehraufträge and -vertragsstati */
    SELECT *,
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
                person.person_id,
                upper(lv.lehrtyp_kurzbz)                            AS "typ",
                (lv.bezeichnung || \' [\' || le.lehrform_kurzbz || \' \' || lv.semester || \'.Semester\' ||
                 \']\')                                             AS "auftrag",
                CASE
                    WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
                    WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
                    ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
                    END                                             AS "lv_oe_kurzbz",
                CONCAT(stg.kurzbzlang, \'-\', legr.semester, legr.verband, legr.gruppe,
                       \'\n\' || legr.gruppe_kurzbz)                AS "gruppe",
                (person.vorname || \' \' || person.nachname)        AS "lektor",
                TRUNC(lema.semesterstunden, 1)                      AS "stunden",
                TRUNC((lema.semesterstunden * lema.stundensatz), 2) AS "betrag",
                vertrag_id,
                mitarbeiter_uid
            FROM
                lehre.tbl_lehreinheitmitarbeiter         lema
                    JOIN lehre.tbl_lehreinheit           le USING (lehreinheit_id)
                    JOIN lehre.tbl_lehrveranstaltung     lv USING (lehrveranstaltung_id)
                    JOIN PUBLIC.tbl_organisationseinheit oe USING (oe_kurzbz)
                    JOIN lehre.tbl_lehreinheitgruppe     legr USING (lehreinheit_id)
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
                /* filter dummies and invalid mitarbeiter */
              AND ma.personalnummer >= 0
        ) tmp_lehrauftraege

        UNION

	    /* Projektbetreuungsaufträge and -vertragsstati */
        SELECT *,
            (SELECT
                 uid
             FROM
                 public.tbl_benutzer
             WHERE
                 person_id = tmp_projektbetreuung.person_id
               AND aktiv = TRUE)                                 AS "mitarbeiter_uid",
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
                    pa.projektarbeit_id                                                                 AS "projektarbeit_id",
                    le.studiensemester_kurzbz,
                    stg.studiengang_kz,
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
                        || \' [\' || projekttyp_kurzbz || \'arbeit\' || \' \' || lv.semester || \'.Semester]\') AS "auftrag",
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
                    CONCAT(stg.kurzbzlang,
                           \'-\', legr.semester, legr.verband, legr.gruppe,
                           \'\n\' || legr.gruppe_kurzbz)                                                  AS "gruppe",
                    (vorname || \' \' || nachname)                                                        AS "lektor",
                    TRUNC(pb.stunden, 1)                                                                AS "stunden",
                    TRUNC((pb.stunden * pb.stundensatz), 2)                                             AS "betrag",
                    vertrag_id
                FROM
                    lehre.tbl_projektbetreuer                pb
                        JOIN lehre.tbl_projektarbeit         pa USING (projektarbeit_id)
                        JOIN lehre.tbl_lehreinheit           le USING (lehreinheit_id)
                        JOIN lehre.tbl_lehrveranstaltung     lv USING (lehrveranstaltung_id)
                        JOIN PUBLIC.tbl_organisationseinheit oe USING (oe_kurzbz)
                        JOIN lehre.tbl_lehreinheitgruppe     legr USING (lehreinheit_id)
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
            ) tmp_projektbetreuung
    ) auftraege
ORDER BY "typ" DESC, "auftrag", "lektor", "bestellt", "erteilt"
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
        'row_index',
        'LE-ID',
        'LV-ID',
        'PA-ID',
        'Studiensemester',
        'Studiengang',
        'Person-ID',
        'Typ',
        'Auftrag',
        'Organisationseinheit',
        'Gruppe',
        'Lektor',
        'Stunden',
        'Betrag',
        'Vertrag-ID',
        'UID',
        'Bestellt',
        'Erteilt',
        'Akzeptiert'
    ),
    'datasetRepOptions' => '{
        height: 700,   
        layout: "fitColumns",           // fit columns to width of table
	    responsiveLayout: "hide",       // hide columns that dont fit on the table    
	    movableColumns: true,           // allows changing column 
	    groupBy: "lektor",              // collapsable groups   
	    headerFilterPlaceholder: " ",
        index: "row_index",             // assign specific column as unique id (important for row indexing)
        selectable: true,               // allow row selection
        selectableRangeMode: "click",   // allow range selection using shift end click on end of range
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        selectableCheck: function(row)
        { 
            // only allow to select bestellte Lehraufträge         
            return row.getData().bestellt != null && row.getData().erteilt == null;
        },      
        rowUpdated:function(row)
        {
                // deselect and disable new selection of updated rows (approvement done)
                row.deselect();
                row.getElement().off("click");         
        },
        rowFormatter:function(row)
        {
            var data = row.getData();
            
            // default (white): rows to be approved
            // green: rows accepted 
            // grey: all other
            if(row.getData().bestellt != null && row.getData().erteilt == null)
            {
                return;
            }
            else if(row.getData().bestellt != null && row.getData().erteilt != null && row.getData().akzeptiert != null)
            {
                row.getElement().style["background-color"] = "#d1f1d196";   // green
            }
            else
            {
                row.getElement().style["background-color"] = "#f5f5f5";     // grey
            }
          }
    }', // tabulator properties
    'datasetRepFieldsDefs' => '{
        row_index: {visible:false},     // necessary for row indexing
        lehreinheit_id: {headerFilter:"input", bottomCalc:"count", bottomCalcFormatter:function(cell){return "Anzahl: " + cell.getValue();},},
        lehrveranstaltung_id: {headerFilter:"input"},
        projektarbeit_id: {visible: false},
        studiensemester_kurzbz: {headerFilter:"input"},
        studiengang_kz: {visible: false},  
        person_id: {visible: false},
        typ: {headerFilter:"input"},
        auftrag: {headerFilter:"input"},
        lv_oe_kurzbz: {headerFilter:"input"},
        gruppe: {headerFilter:"input"},
        lektor: {headerFilter:"input"},
        stunden: {align:"right", headerFilter:"input", bottomCalc:"sum", bottomCalcParams:{precision:1}}, 
        betrag: {align:"right",  headerFilter:"input", headerFilterPlaceholder:">=", headerFilterFunc: hf_compareWithFloat,
            bottomCalc:"sum", bottomCalcParams:{precision:2}, bottomCalcFormatter:"money", bottomCalcFormatterParams:{decimal: ",", thousand: ".", symbol:"€"}},
        vertrag_id: {visible: false},
        mitarbeiter_uid: {visible: false},
        bestellt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate}, 
        erteilt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate},
        akzeptiert: {align:"center", headerFilter:"input", mutator: mut_formatStringDate}
    }', // col properties
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);

?>

