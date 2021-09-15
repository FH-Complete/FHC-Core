<?php
const ANRECHNUNGSTATUS_PROGRESSED_BY_STGL = 'inProgressDP';
const ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR = 'inProgressLektor';

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
			lv.orgform_kurzbz,
			(SELECT ausbildungssemester
			FROM public.tbl_prestudentstatus press
			WHERE press.prestudent_id = anrechnung.prestudent_id
			AND press.studiensemester_kurzbz = anrechnung.studiensemester_kurzbz
			AND press.status_kurzbz = \'Student\'),
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
	
	SELECT anrechnungen.*,
	array_to_json(anrechnungstatus.bezeichnung_mehrsprachig::varchar[])->>' . $LANGUAGE_INDEX . ' AS "status_bezeichnung",
	CASE
		WHEN (anrechnungen.empfehlung_anrechnung IS NULL AND anrechnungen.status_kurzbz = \'' . ANRECHNUNGSTATUS_PROGRESSED_BY_STGL . '\') THEN NULL
		ELSE
		(SELECT insertamum::date
			FROM lehre.tbl_anrechnungstatus
			JOIN lehre.tbl_anrechnung_anrechnungstatus USING (status_kurzbz)
			WHERE anrechnung_id = anrechnungen.anrechnung_id
			AND status_kurzbz = \'' . ANRECHNUNGSTATUS_PROGRESSED_BY_LEKTOR . '\'
			ORDER BY insertamum DESC
			LIMIT 1)
	END "empfehlungsanfrageAm",
	CASE
		WHEN (anrechnungen.empfehlung_anrechnung IS NULL AND anrechnungen.status_kurzbz = \'' . ANRECHNUNGSTATUS_PROGRESSED_BY_STGL . '\') THEN NULL
		ELSE
		(SELECT COALESCE(
				STRING_AGG(CONCAT_WS(\' \', vorname, nachname), \', \') FILTER (WHERE lvleiter = TRUE),
				STRING_AGG(CONCAT_WS(\' \', vorname, nachname), \', \') FILTER (WHERE lvleiter = FALSE)
			) empfehlungsanfrageAn
			FROM (
				SELECT DISTINCT ON (benutzer.uid) uid, vorname, nachname,
				CASE WHEN lehrfunktion_kurzbz = \'LV-Leitung\' THEN TRUE ELSE FALSE END AS lvleiter
				FROM lehre.tbl_lehreinheit
				JOIN lehre.tbl_lehreinheitmitarbeiter lema USING (lehreinheit_id)
				JOIN public.tbl_benutzer benutzer ON lema.mitarbeiter_uid = benutzer.uid
				JOIN public.tbl_person USING (person_id)
				WHERE studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
				AND lehrveranstaltung_id = anrechnungen.lehrveranstaltung_id
				AND lema.mitarbeiter_uid NOT like \'_Dummy%\'
				AND benutzer.aktiv = TRUE
				AND tbl_person.aktiv = TRUE
				ORDER BY benutzer.uid, lvleiter DESC, nachname, vorname
				) as tmp_lvlektoren
			)
	END "empfehlungsanfrageAn"
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
		ucfirst($this->p->t('lehre', 'organisationsform')),
		'Semester',
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
		'Status',
		ucfirst($this->p->t('anrechnung', 'empfehlungsanfrageAm')),
		ucfirst($this->p->t('anrechnung', 'empfehlungsanfrageAn'))
	),
	'datasetRepOptions' => '{
		height: func_height(this),
		layout: "fitColumns",           // fit columns to width of table
		persistentLayout:true,
		persistentSort:true,
		persistentFilter:true,
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
		orgform_kurzbz: {headerFilter:"input"},
		ausbildungssemester: {headerFilter:"input"},
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
		status_bezeichnung: {headerFilter:"input"},
		empfehlungsanfrageAm: {visible: false, align:"center", headerFilter:"input", mutator: mut_formatStringDate},
		empfehlungsanfrageAn: {visible: false, headerFilter:"input"}
	 }', // col properties
);

echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);

?>