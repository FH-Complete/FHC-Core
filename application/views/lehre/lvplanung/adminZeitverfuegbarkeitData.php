<?php

$STUDIENGANG_KZ_ARR = $studiengang_kz_arr; // stg the user is entitled to administrate

$qry = '
    SELECT * FROM (
        SELECT DISTINCT ON (zeitsperre_id, zsp.mitarbeiter_uid) zeitsperre_id, zsp.mitarbeiter_uid,
            concat_ws(\' \', nachname, vorname) AS "lektor",
            vondatum, vonstunde, bisdatum, bisstunde, zsp.bezeichnung
        FROM public.tbl_person
        JOIN public.tbl_benutzer b USING (person_id)
        JOIN public.tbl_mitarbeiter ma ON (ma.mitarbeiter_uid = b.uid)
        JOIN lehre.tbl_lehreinheitmitarbeiter lema ON (lema.mitarbeiter_uid = b.uid)
        JOIN lehre.tbl_lehreinheit le USING (lehreinheit_id)
        JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
        JOIN public.tbl_studiensemester ss USING (studiensemester_kurzbz)
        JOIN campus.tbl_zeitsperre zsp ON zsp.mitarbeiter_uid = lema.mitarbeiter_uid
        WHERE lv.studiengang_kz IN (' . implode(',', $STUDIENGANG_KZ_ARR) . ')
        AND b.aktiv
		AND zsp.vondatum >= ss.start
        AND zeitsperretyp_kurzbz = \'ZVerfueg\'
        ORDER BY zeitsperre_id, zsp.mitarbeiter_uid
    ) as tmp
    ORDER BY vondatum DESC
';

$filterWidgetArray = array(
    'query' => $qry,
    'tableUniqueId' => 'adminZeitverfuegbarkeit',
    'requiredPermissions' => 'lehre/zeitverfuegbarkeit',
    'datasetRepresentation' => 'tabulator',
    'columnsAliases' => array(
        'ZeitsperreID',
        'UID',
        ucfirst($this->p->t('lehre', 'lektor')),
        ucfirst($this->p->t('ui', 'von')),
        'VonStunde',
        ucfirst($this->p->t('global', 'bis')),
        'BisStunde',
        ucfirst($this->p->t('global', 'notiz'))
    ),
    'datasetRepOptions' => '{
        height: func_height(this),
		layout: "fitColumns",           // fit columns to width of table
		autoResize: false, 				// prevent auto resizing of table (false to allow adapting table size when cols are (de-)activated
        index: "zeitsperre_id",             // assign specific column as unique id (important for row indexing)
        selectable: 1,               // allow row selection
        tableWidgetHeader: false,
        columnDefaults:{
            headerFilterPlaceholder: " ",
        },
       
      
    }', // tabulator properties
    'datasetRepFieldsDefs' => '{
        zeitsperre_id: {visible:false},
        mitarbeiter_uid: {visible: true, headerFilter:"input"},
        lektor: {visible: true, headerFilter:"input"},
        vondatum: {visible: true, headerFilter:"input"},
        vonstunde: {visible: true, headerFilter:"input"},
        bisdatum: {visible: true, headerFilter:"input"},
        bisstunde: {visible: true, headerFilter:"input"},
        bezeichnung: {visible: true, headerFilter:"input"}
    }', // col properties
);
?>
<div class="tabulator-initialfontsize">
<?php
	echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);
?>
</div>
