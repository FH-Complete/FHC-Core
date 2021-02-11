<?php
$STUDIENSEMESTER = $studiensemester_selected;
$STUDIENGAENGE_ENTITLED = implode(', ', $studiengaenge_entitled);
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
		JOIN campus.tbl_dms_version AS dmsversion USING (dms_id)
		JOIN lehre.tbl_anrechnung_anrechnungstatus USING (anrechnung_id)
		JOIN lehre.tbl_anrechnung_begruendung AS begruendung USING (begruendung_id)
	)
	
	SELECT anrechnungen.*,
	array_to_json(anrechnungstatus.bezeichnung_mehrsprachig::varchar[])->>' . $LANGUAGE_INDEX . ' AS "status_bezeichnung"
	FROM anrechnungen
	JOIN lehre.tbl_anrechnungstatus as anrechnungstatus ON (anrechnungstatus.status_kurzbz = anrechnungen.status_kurzbz)
	WHERE studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
	AND studiengang_kz IN (' . $STUDIENGAENGE_ENTITLED . ')
';

$filterWidgetArray = array(
	'query' => $query,
	'tableUniqueId' => 'approveAnrechnungUebersicht',
	'requiredPermissions' => 'lehre/anrechnung_genehmigen',
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
		anrechnung_id: {visible: false},
		lehrveranstaltung_id: {visible: false},
		begruendung_id: {visible: false},
		dms_id: {visible: false},
		studiensemester_kurzbz: {visible: false},
		studiengang_kz: {visible: false},
		stg_bezeichnung: {headerFilter:"input"},
		lv_bezeichnung: {headerFilter:"input"},
		ects: {headerFilter:"input", align:"center"},
		student: {headerFilter:"input"},
		begruendung: {headerFilter:"input"},
		dokument_bezeichnung: {headerFilter:"input", formatter:"link", formatterParams:{
		    labelField:"dokument_bezeichnung",
			url:function(cell){return "'. current_url() .'/download?dms_id=" + cell.getData().dms_id},
		    target:"_blank"
		}},
		anmerkung_student: {headerFilter:"input"},
		empfehlung_anrechnung: {headerFilter:"input", align:"center", formatter: format_empfehlung_anrechnung, headerFilterFunc: hf_filterTrueFalse},
		status_kurzbz: {visible: false},
		status_bezeichnung: {headerFilter:"input"}
	 }', // col properties
);

echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);

?>