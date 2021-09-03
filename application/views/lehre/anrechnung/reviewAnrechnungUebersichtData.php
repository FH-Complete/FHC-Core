<?php
$STUDIENSEMESTER = $studiensemester_selected;
$LEKTOR_UID = getAuthUID();
$LANGUAGE_INDEX = getUserLanguage() == 'German' ? '0' : '1';

$query = '
	WITH anrechnungen AS
	(
		SELECT DISTINCT
			anrechnung.anrechnung_id,
			anrechnung.lehrveranstaltung_id,
			anrechnung.begruendung_id,
			anrechnung.dms_id,
			anrechnung.studiensemester_kurzbz,
			stg.studiengang_kz,
			stg.bezeichnung AS "stg_bezeichnung",
			lv.bezeichnung AS "lv_bezeichnung",
			lv.ects,
			(person.nachname || \' \' || person.vorname) AS "student",
			begruendung.bezeichnung AS "begruendung",
			dmsversion.name AS "dokument_bezeichnung",
			anrechnung.anmerkung_student,
			(SELECT COALESCE(
				array_to_json(zgvmaster.bezeichnung::varchar[])->>' . $LANGUAGE_INDEX . ',
				array_to_json(zgv.bezeichnung::varchar[])->>' . $LANGUAGE_INDEX . '
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
			) AS status_kurzbz
		FROM lehre.tbl_anrechnung AS anrechnung
		JOIN public.tbl_prestudent USING (prestudent_id)
		JOIN public.tbl_person AS person USING (person_id)
		JOIN public.tbl_studiengang AS stg USING (studiengang_kz)
		JOIN lehre.tbl_lehrveranstaltung AS lv USING (lehrveranstaltung_id)
		LEFT JOIN campus.tbl_dms_version AS dmsversion USING (dms_id)
		JOIN lehre.tbl_anrechnung_anrechnungstatus USING (anrechnung_id)
		JOIN lehre.tbl_anrechnung_begruendung AS begruendung USING (begruendung_id)
	)
	
	SELECT DISTINCT ON (anrechnungen.*, lema.mitarbeiter_uid) anrechnungen.*,
	array_to_json(anrechnungstatus.bezeichnung_mehrsprachig::varchar[])->>' . $LANGUAGE_INDEX . ' AS "status_bezeichnung"
	FROM anrechnungen
	JOIN lehre.tbl_anrechnungstatus as anrechnungstatus ON (anrechnungstatus.status_kurzbz = anrechnungen.status_kurzbz)
	JOIN lehre.tbl_lehreinheit le USING (lehrveranstaltung_id)
	JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
	WHERE anrechnungen.studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
	AND le.studiensemester_kurzbz = anrechnungen.studiensemester_kurzbz
	AND lema.mitarbeiter_uid = \'' . $LEKTOR_UID . '\'
	AND le.lehre = TRUE
	AND EXISTS (
		SELECT 1
		FROM lehre.tbl_anrechnung_anrechnungstatus
		WHERE anrechnung_id = anrechnungen.anrechnung_id
		AND status_kurzbz=\'inProgressLektor\'
	)
';

$filterWidgetArray = array(
	'query' => $query,
	'tableUniqueId' => 'approveAnrechnungUebersicht',
	'requiredPermissions' => 'lehre/anrechnung_empfehlen',
	'datasetRepresentation' => 'tabulator',
	'columnsAliases' => array(
		'anrechnung_id',
		'lehrveranstaltung_id',
		'begruendung_id',
		'dms_id',
		'studiensemester_kurzbz',
		'studiengang_kz',
		ucfirst($this->p->t('lehre', 'studiengang')),
		ucfirst($this->p->t('lehre', 'lehrveranstaltung')),
		'ECTS',
		ucfirst($this->p->t('person', 'studentIn')),
		ucfirst($this->p->t('global', 'begruendung')),
		ucfirst($this->p->t('anrechnung', 'nachweisdokumente')),
		ucfirst($this->p->t('anrechnung', 'herkunft')),
		ucfirst($this->p->t('global', 'zgv')),
		ucfirst($this->p->t('anrechnung', 'antragdatum')),
		ucfirst($this->p->t('anrechnung', 'empfehlung')),
		'status_kurzbz',
		'Status'
	),
	'datasetRepOptions' => '{
		height: func_height(this),
		layout: "fitColumns",           // fit columns to width of table
		persistentLayout:true,
		persistentSort:true,
		autoResize: false, 				// prevent auto resizing of table (false to allow adapting table size when cols are (de-)activated
	    headerFilterPlaceholder: " ",
        index: "anrechnung_id",             // assign specific column as unique id (important for row indexing)
        selectable: true,               // allow row selection
        selectableRangeMode: "click",   // allow range selection using shift end click on end of range
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        tableBuilt: function(){
            func_tableBuilt(this);
        },
        tableWidgetFooter: {
			selectButtons: true
		},
		selectableCheck: function(row){
            return func_selectableCheck(row);
        },
        rowFormatter:function(row){
            func_rowFormatter(row);
        },
         rowUpdated:function(row){
            func_rowUpdated(row);
        },
        tooltips: function(cell){
            return func_tooltips(cell);
        }
	 }', // tabulator properties
	'datasetRepFieldsDefs' => '{
		anrechnung_id: {visible: false, headerFilter:"input"},
		lehrveranstaltung_id: {visible: false, headerFilter:"input"},
		begruendung_id: {visible: false, headerFilter:"input"},
		dms_id: {visible: false, headerFilter:"input"},
		studiensemester_kurzbz: {visible: false, headerFilter:"input"},
		studiengang_kz: {visible: false, headerFilter:"input"},
		stg_bezeichnung: {headerFilter:"input"},
		lv_bezeichnung: {headerFilter:"input"},
		ects: {headerFilter:"input", align:"center"},
		student: {headerFilter:"input"},
		begruendung: {headerFilter:"input"},
		zgv: {visible: false, headerFilter:"input"},
		dokument_bezeichnung: {headerFilter:"input", formatter:"link", formatterParams:{
		    labelField:"dokument_bezeichnung",
			url:function(cell){return "'. current_url() .'/download?dms_id=" + cell.getData().dms_id},
		    target:"_blank"
		}},
		anmerkung_student: {headerFilter:"input"},
		antragsdatum: {align:"center", headerFilter:"input", mutator: mut_formatStringDate},
		empfehlung_anrechnung: {headerFilter:"input", align:"center", formatter: format_empfehlung_anrechnung, headerFilterFunc: hf_filterTrueFalse},
		status_kurzbz: {visible: false, headerFilter:"input"},
		status_bezeichnung: {headerFilter:"input"}
	 }', // col properties
);

echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);

?>