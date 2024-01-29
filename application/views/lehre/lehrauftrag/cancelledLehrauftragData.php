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
                    nachname || \' \' || vorname
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
	'columnsAliases' => array(
		'Status',
		ucfirst($this->p->t('lehre', 'studiensemester')),
		ucfirst($this->p->t('global', 'typ')),
		ucfirst($this->p->t('lehre', 'lehrveranstaltung')). '- / '. ucfirst($this->p->t('ui', 'projekt')). lcfirst($this->p->t('ui', 'bezeichnung')),
		ucfirst($this->p->t('ui', 'stunden')),
		ucfirst($this->p->t('ui', 'betrag')),
		ucfirst($this->p->t('ui', 'storniertAm')),
		ucfirst($this->p->t('ui', 'storniertVon'))
	),
	'datasetRepOptions' => '{
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
        },
    }', // tabulator properties
	'datasetRepFieldsDefs' => '{
        vertrag_id: {visible: false},
        vertragsstunden_studiensemester_kurzbz: {visible: false, widthShrink:1},
        vertragstyp_kurzbz: {minWidth: 200},
        bezeichnung: {minWidth: 200},
        vertragsstunden: {
            align:"right", formatter: form_formatNulltoStringNumber, formatterParams:{precision:1},
            bottomCalc:"sum", bottomCalcParams:{precision:1},
            minWidth: 200
        },
        betrag: {
            align:"right", formatter: form_formatNulltoStringNumber,
            bottomCalc:"sum", bottomCalcParams:{precision:2}, bottomCalcFormatter:"money", bottomCalcFormatterParams:{decimal: ",", thousand: ".", symbol:"€"},
            minWidth: 200
        },
        storniert: {align:"center", mutator: mut_formatStringDate, tooltip: storniert_tooltip, minWidth: 200},
        storniert_von: {visible: false},
        letzterStatus_vorStorniert: {visible: false}
    }', // col properties
);

echo $this->widgetlib->widget('TableWidget', $tableWidgetArray);

?>
