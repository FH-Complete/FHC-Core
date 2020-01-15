<?php

$STUDIENSEMESTER = $studiensemester_selected;
$UID = getAuthUID();

$query = '
    SELECT
           vertrag_id,
           vertragsstunden_studiensemester_kurzbz,
           vertragstyp_kurzbz,
           bezeichnung,
           vertragsstunden,
           betrag,
           datum AS "storniert",
           (
               SELECT
                    vorname || \' \' || nachname
                FROM
                    public.tbl_person
                JOIN public.tbl_benutzer benutzer USING (person_id)
                WHERE benutzer.uid = (
                    SELECT
                        insertvon
                    FROM
                        lehre.tbl_vertrag_vertragsstatus
                    WHERE vertragsstatus_kurzbz = \'storno\'
                      AND vertrag_id = vvs.vertrag_id
                )
           )                                      AS "storniert_von",
           (
               SELECT
                    vertragsstatus_kurzbz
                FROM
                    lehre.tbl_vertrag_vertragsstatus
                WHERE vertrag_id = vvs.vertrag_id
                  AND vertragsstatus_kurzbz != \'storno\'
                ORDER BY datum DESC
                LIMIT 1
            )                              AS "letzterStatus_vorStorniert"
    FROM lehre.tbl_vertrag_vertragsstatus vvs
             JOIN lehre.tbl_vertrag USING (vertrag_id)
    WHERE
      /* filter lector */
        uid = \'' . $UID . '\'
       /* filter studiensemester */
        AND vertragsstunden_studiensemester_kurzbz =  \'' . $STUDIENSEMESTER . '\'
      /* filter cancelled only */
        AND vertragsstatus_kurzbz = \'storno\'
';

$tableWidgetArray = array(
	'query' => $query,
	'tableUniqueId' => 'cancelledLehrauftrag',
	'requiredPermissions' => 'lehre/lehrauftrag_akzeptieren',
	'datasetRepresentation' => 'tabulator',
	'columnsAliases' => array(  // TODO: use phrasen
		'Status',
		'Studiensemester',
		'Typ',
		'LV- / Projektbezeichnung',
		'Stunden',
		'Betrag',
		'Storniert am'
	),
	'datasetRepOptions' => '{
        layout: "fitColumns",           // fit columns to width of table
	    responsiveLayout: "hide",       // hide columns that dont fit on the table
	    movableColumns: true,           // allows changing column
	    placeholder: func_placeholder(),
        rowFormatter:function(row){
            func_rowFormatter(row);
        },
        selectableCheck: function(row){
            return func_selectableCheck(row);
        },
         renderComplete:function(){
            func_renderComplete(this);
        },
        tableBuilt: function(){
            func_tableBuilt(this);
        }
    }', // tabulator properties
	'datasetRepFieldsDefs' => '{
        vertrag_id: {visible: false},
        vertragsstunden_studiensemester_kurzbz: {visible: false},
        vertragstyp_kurzbz: {widthGrow: 2},
        bezeichnung: {widthGrow: 2},
        vertragsstunden: {
            align:"right", formatter: form_formatNulltoStringNumber, formatterParams:{precision:1},
            bottomCalc:"sum", bottomCalcParams:{precision:1}
        },
        betrag: {
            align:"right", formatter: form_formatNulltoStringNumber,
            bottomCalc:"sum", bottomCalcParams:{precision:2}, bottomCalcFormatter:"money", bottomCalcFormatterParams:{decimal: ",", thousand: ".", symbol:"€"}
        },
        storniert: {align:"center", mutator: mut_formatStringDate, tooltip: storniert_tooltip},
        storniert_von: {visible: false},
        letzterStatus_vorStorniert: {visible: false}
    }', // col properties
);

echo $this->widgetlib->widget('TableWidget', $tableWidgetArray);

?>
