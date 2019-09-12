<?php

$STUDIENSEMESTER = $studiensemester_selected;
$STUDIENGANG = (isset($studiengang_selected) && !is_null($studiengang_selected)) ? array($studiengang_selected) : $studiengang;

$query = '
    SELECT 
    ROW_NUMBER() OVER () AS "id", 
    * 
    FROM
        (
            /* Lehraufträge and -vertragsstati */
            SELECT
            lema.lehreinheit_id                                                                                     AS "LE_ID",
            lv.lehrveranstaltung_id                                                                                 AS "LV_ID",
            NULL                                                                                                    AS "PA_ID",
            le.studiensemester_kurzbz                                                                               AS "Studiensemester",
            stg.studiengang_kz,
            person.person_id                                                                                        AS "Person_ID",
            upper(lv.lehrtyp_kurzbz)                                                                                AS "Typ",
            (lv.bezeichnung || \' [\' || le.lehrform_kurzbz || \' \' || lv.semester || \'.Semester\' || \']\')      AS "Auftrag",
            CASE
                WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
                WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
                ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
                END                                                                                                 AS "Organisationseinheit",
            lv.oe_kurzbz AS "lv_oe_kurzbz",
            CONCAT(stg.kurzbzlang, \'-\', legr.semester, legr.verband, legr.gruppe, \'\n\' || legr.gruppe_kurzbz)   AS "Gruppe",
            (person.vorname || \' \' || person.nachname)                                                            AS "Lektor",
            TRUNC(lema.semesterstunden, 1)                                                                          AS "Stunden",
            TRUNC((lema.semesterstunden * lema.stundensatz), 2)                                                     AS "Betrag",
            CASE
                /* existing contracts for given study semester with status bestellt */
                WHEN lema.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\' AND
                     vvs.vertragsstatus_kurzbz = \'bestellt\' THEN vvs.datum
                END                                                                                  AS "Bestellt",
            CASE
                /* existing contracts for given study semester with status erteilt */
                WHEN lema.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz = \''. $STUDIENSEMESTER. '\' AND
                     vvs.vertragsstatus_kurzbz = \'erteilt\' THEN vvs.datum
                END                                                                                  AS "Erteilt",
            CASE
                /* existing contracts for given study semester with status akzeptiert */
                WHEN lema.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\' AND
                     vvs.vertragsstatus_kurzbz = \'akzeptiert\' THEN vvs.datum
                END                                                                                  AS "Akzeptiert"
        FROM
            lehre.tbl_lehreinheitmitarbeiter               lema
                JOIN lehre.tbl_lehreinheit                 le USING (lehreinheit_id)
                JOIN lehre.tbl_lehrveranstaltung           lv USING (lehrveranstaltung_id)
                JOIN public.tbl_organisationseinheit       oe USING (oe_kurzbz)
                JOIN lehre.tbl_lehreinheitgruppe           legr USING (lehreinheit_id)
                JOIN public.tbl_mitarbeiter                ma USING (mitarbeiter_uid)
                JOIN public.tbl_benutzer                   benutzer ON ma.mitarbeiter_uid = benutzer.uid
                JOIN public.tbl_person                     person USING (person_id)
                LEFT JOIN lehre.tbl_vertrag                vertrag USING (vertrag_id)
                LEFT JOIN lehre.tbl_vertrag_vertragsstatus vvs USING (vertrag_id)
                LEFT JOIN lehre.tbl_vertragsstatus         status USING (vertragsstatus_kurzbz)
                JOIN public.tbl_studiengang                stg ON stg.studiengang_kz = lv.studiengang_kz
        WHERE
            /* filter studiengang */
            lv.studiengang_kz IN ('. implode(',', $STUDIENGANG) . ')
            /* filter studiensemester */
          AND le.studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\'
            /* filter active lehrveranstaltungen */
          AND lv.aktiv = TRUE
            /* filter dummies and invalid mitarbeiter */
          AND ma.personalnummer >= 0
        
        
        UNION
        
        /* Projektbetreuungsaufträge and -vertragsstati */
        SELECT
            pa.lehreinheit_id                                                                                   AS "LE_ID",
            lv.lehrveranstaltung_id                                                                             AS "LV_ID",
            pa.projektarbeit_id                                                                                 AS "PA_ID",
            le.studiensemester_kurzbz                                                                           AS "Studiensemester",
            stg.studiengang_kz,
            person.person_id                                                                                    AS "Person_ID",
            \'Betreuung\'                                                                                       AS "Typ",
            (betreuerart_kurzbz || \' \' ||
             (SELECT
                  vorname || \' \' || nachname
              FROM
                  public.tbl_person
                      JOIN public.tbl_benutzer USING (person_id)
              WHERE
                  uid = pa.student_uid)
                || \' [\' || projekttyp_kurzbz || \'arbeit\' || \' \' || lv.semester || \'.Semester]\')         AS "Auftrag",
            CASE
                WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
                WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
                ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
                END                                                                                             AS "Organisationseinheit",
                lv.oe_kurzbz AS "lv_oe_kurzbz",
            CONCAT(stg.kurzbzlang, \'-\', legr.semester, legr.verband, legr.gruppe, \'\n\' || legr.gruppe_kurzbz) AS "Gruppe",
            (vorname || \' \' || nachname)                                                                      AS "Lektor",
            TRUNC(pb.stunden, 1)                                                                               AS "Stunden",
            TRUNC((pb.stunden * pb.stundensatz), 2)                                                             AS "Betrag",
            CASE
                /* existing contracts for given study semester with status bestellt */
                WHEN pb.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\' AND
                     vvs.vertragsstatus_kurzbz = \'bestellt\' THEN vvs.datum
                END                                             AS "Bestellt",
            CASE
                /* existing contracts for given study semester with status erteilt */
                WHEN pb.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\' AND
                     vvs.vertragsstatus_kurzbz = \'erteilt\' THEN vvs.datum
                END                                             AS "Erteilt",
            CASE
                /* existing contracts for given study semester with status akzeptiert */
                WHEN pb.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\' AND
                     vvs.vertragsstatus_kurzbz = \'akzeptiert\' THEN vvs.datum
                END                                             AS "Akzeptiert"
        FROM
            lehre.tbl_projektbetreuer                      pb
                JOIN lehre.tbl_projektarbeit               pa USING (projektarbeit_id)
                JOIN lehre.tbl_lehreinheit                 le USING (lehreinheit_id)
                JOIN lehre.tbl_lehrveranstaltung           lv USING (lehrveranstaltung_id)
                JOIN public.tbl_organisationseinheit       oe USING (oe_kurzbz)
                JOIN lehre.tbl_lehreinheitgruppe           legr USING (lehreinheit_id)
                JOIN public.tbl_person                     person USING (person_id)
                LEFT JOIN lehre.tbl_vertrag                vertrag USING (vertrag_id)
                LEFT JOIN lehre.tbl_vertrag_vertragsstatus vvs USING (vertrag_id)
                LEFT JOIN lehre.tbl_vertragsstatus         status USING (vertragsstatus_kurzbz)
                JOIN public.tbl_studiengang                stg ON stg.studiengang_kz = lv.studiengang_kz
        WHERE
            /* filter studiengang */
            lv.studiengang_kz IN ('. implode(',', $STUDIENGANG) . ')
            /* filter studiensemester */
          AND le.studiensemester_kurzbz =  \''. $STUDIENSEMESTER. '\'
            /* filter active lehrveranstaltungen */
          AND lv.aktiv = TRUE
        
        ORDER BY "Typ" DESC, "Auftrag", "Lektor"
       ) auftraege
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
        'id',
        'LE-ID',
        'LV-ID',
        'PA-ID',
        'Studiensemester',
        'Studiengang',
        'Person-ID',
        ucfirst($this->p->t('global', 'typ')),
        'Auftrag',
        'Organisationseinheit',
        'lv_oe_kurzbz',
        'Gruppe',
        'Lektor',
        'Stunden',
        'Betrag',
        'Bestellt',
        'Erteilt',
        'Akzeptiert'
    ),
    'formatRow' => function($datasetRaw) {
        if (is_null($datasetRaw->{'Betrag'}))
        {
            $datasetRaw->{'Betrag'} = 'Stundensatz fehlt';
        }
        return $datasetRaw;
    },
    'datasetRepOptions' => '{
        height: 700,   
        layout:"fitColumns",            // fit columns to width of table
	    responsiveLayout:"hide",        // hide columns that dont fit on the table       
        selectable: true,               // allows row selection
        selectableRangeMode: "click",   // allows range selection using shift end click on end of range
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        selectableCheck: function(row){ // only allow selection if is not bestellt (as order already exists)
            return row.getData().Bestellt == null;
        },
        movableColumns: true,           // allows changing column    
	    headerFilterPlaceholder: " "
    }', // tabulator properties
    'datasetRepFieldsDefs' => '{
        id: {visible:false}, // necessary for row indexing
        LE_ID: {headerFilter:"input", bottomCalc:"count"},
        LV_ID: {visible: false},
        PA_ID: {visible: false},
        Studiensemester: {visible: false},
        studiengang_kz: {visible: false},  
        Person_ID: {visible: false},
        Typ: {headerFilter:"input"},
        Auftrag: {headerFilter:"input"},
        Organisationseinheit: {headerFilter:"input"},
        lv_oe_kurzbz: {visible: false},
        Gruppe: {headerFilter:"input"},
        Lektor: {headerFilter:"input"},
        Stunden: {align:"right", headerFilter:"input", bottomCalc:"sum", bottomCalcParams:{precision:1}}, 
        Betrag: {align:"right",  headerFilter:"input", headerFilterPlaceholder:">=", headerFilterFunc: hf_compareWithFloat,
            bottomCalc:"sum", bottomCalcParams:{precision:2}, bottomCalcFormatter:"money", bottomCalcFormatterParams:{decimal: ",", thousand: ".", symbol:"€"}},
        Bestellt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate}, 
        Erteilt: {align:"center", headerFilter:"input", mutator: mut_formatStringDate},
        Akzeptiert: {align:"center", headerFilter:"input", mutator: mut_formatStringDate}
    }', // col properties
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);

?>

