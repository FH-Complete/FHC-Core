<?php

$APP = '\'lehrauftrag\'';

// TODO: projektbetreuer noch ergänzen; query result noch im detail testen
$query = '
    SELECT
    (lv.bezeichnung || \' [\' || upper(lv.lehrtyp_kurzbz) || \']\') AS "Lehrveranstaltung",
    CASE
        WHEN oe.organisationseinheittyp_kurzbz = \'Kompetenzfeld\' THEN (\'KF \' || oe.bezeichnung)
        WHEN oe.organisationseinheittyp_kurzbz = \'Department\' THEN (\'DEP \' || oe.bezeichnung)
        ELSE (oe.organisationseinheittyp_kurzbz || \' \' || oe.bezeichnung)
        END                                                     AS "Organisationseinheit",
    (person.vorname || \' \' || person.nachname)                AS "Lektor",
    lema.semesterstunden                                        AS "Stunden",
    (lema.semesterstunden * lema.stundensatz)                   AS "Betrag",
    CASE
        /* existing contracts for given study semester with status bestellt */
        WHEN lema.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz = \'WS2018\' AND
             vvs.vertragsstatus_kurzbz = \'bestellt\' THEN vvs.datum
        END                                                     AS "Bestellt",
    CASE
        /* existing contracts for given study semester with status erteilt */
        WHEN lema.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz = \'WS2018\' AND
             vvs.vertragsstatus_kurzbz = \'erteilt\' THEN vvs.datum
        END                                                     AS "Erteilt",
    CASE
        /* existing contracts for given study semester with status akzeptiert */
        WHEN lema.vertrag_id NOTNULL AND vertrag.vertragsstunden_studiensemester_kurzbz = \'WS2018\' AND
             vvs.vertragsstatus_kurzbz = \'akzeptiert\' THEN vvs.datum
        END                                                     AS "Akzeptiert"
FROM
    lehre.tbl_lehreinheitmitarbeiter               lema
        JOIN lehre.tbl_lehreinheit                 le USING (lehreinheit_id)
        JOIN lehre.tbl_lehrveranstaltung           lv USING (lehrveranstaltung_id)
        JOIN public.tbl_organisationseinheit       oe USING (oe_kurzbz)
        JOIN public.tbl_mitarbeiter                ma USING (mitarbeiter_uid)
        JOIN public.tbl_benutzer                   benutzer ON ma.mitarbeiter_uid = benutzer.uid
        JOIN public.tbl_person                     person USING (person_id)
        LEFT JOIN lehre.tbl_vertrag                vertrag USING (person_id)
        LEFT JOIN lehre.tbl_vertrag_vertragsstatus vvs
                  USING (uid) -- richtig über uid? wenn über vertrags_id: Fehlermeldung, da viele verträge noch nicht existieren (NULL)
        LEFT JOIN lehre.tbl_vertragsstatus         status USING (vertragsstatus_kurzbz)
WHERE
    /* filter studiengang */
    lv.studiengang_kz = 227
    /* filter studiensemester */
  AND le.studiensemester_kurzbz = \'WS2018\'
    /* filter active lehrveranstaltungen */
  AND lv.aktiv = TRUE
';


$filterWidgetArray = array(
    'query' => $query,
    'app' => Lehrauftrag::APP,
    'datasetName' => 'lehrauftragOrder',
    'filterKurzbz' => 'LehrauftragOrder',
    //'filter_id' => $this->input->get('filter_id'),
    'requiredPermissions' => 'infocenter', // TODO: change permission
    'datasetRepresentation' => 'tabulator',
    'reloadDataset' => ($this->input->get('reloadDataset') == 'true' ? true : false), // TODO: needed?
    //'customMenu' => true,
    'hideOptions' => true,
    'hideMenu' => true,
    'columnsAliases' => array(
        ucfirst($this->p->t('person', 'vorname'))
    ),
    'markRow' => function($datasetRaw) {

        $mark = '';

        if ($datasetRaw->LockDate != null)
        {
            $mark = FilterWidget::DEFAULT_MARK_ROW_CLASS;
        }

        // Parking has priority over locking
        if ($datasetRaw->ParkDate != null)
        {
            $mark = "text-info";
        }

        return $mark;
    },
    'datasetRepOptions' => '{height: 300}', // tabulator properties
    'datasetRepFieldsDefs' => '{Vorname: {width: 400}}' // col properties
);

echo $this->widgetlib->widget('FilterWidget', $filterWidgetArray);

?>