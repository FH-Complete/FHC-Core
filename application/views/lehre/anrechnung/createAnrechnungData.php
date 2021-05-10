<?php

$STUDIENSEMESTER = $studiensemester_selected;
$STUDIENGAENGE_ENTITLED = implode(', ', $studiengaenge_entitled);
$LANGUAGE_INDEX = getUserLanguage() == 'German' ? '0' : '1';

$query = '
	SELECT pst.prestudent_id,
		person.person_id,
		pststatus.studienplan_id,
		stg.studiengang_kz,
		stg.bezeichnung AS "stg_bezeichnung",
		ausbildungssemester,
		nachname,
		vorname,
		(SELECT COALESCE(
			array_to_json(zgvmaster.bezeichnung::varchar[])->>' . $LANGUAGE_INDEX . ',
			array_to_json(zgv.bezeichnung::varchar[])->>' . $LANGUAGE_INDEX . '
		) AS zgv
		FROM public.tbl_prestudent
			LEFT JOIN bis.tbl_zgv zgv USING (zgv_code)
			LEFT JOIN bis.tbl_zgvmaster zgvmaster USING (zgvmas_code)
			WHERE prestudent_id = pst.prestudent_id
		) AS zgv
	FROM public.tbl_prestudent pst
	JOIN public.tbl_prestudentstatus pststatus USING (prestudent_id)
	JOIN public.tbl_person person USING (person_id)
	JOIN public.tbl_student student USING (prestudent_id)
	JOIN public.tbl_benutzer benutzer ON benutzer.uid = student.student_uid
	JOIN public.tbl_studiengang stg ON stg.studiengang_kz = pst.studiengang_kz
	WHERE pststatus.studiensemester_kurzbz = \'' . $STUDIENSEMESTER . '\'
	AND pst.studiengang_kz IN (' . $STUDIENGAENGE_ENTITLED . ')
	AND benutzer.aktiv = true
	AND pststatus.status_kurzbz = \'Student\'
	ORDER BY "stg_bezeichnung", ausbildungssemester, nachname
';

$filterWidgetArray = array(
	'query' => $query,
	'tableUniqueId' => 'createAnrechnung',
	'requiredPermissions' => 'lehre/anrechnung_anlegen',
	'datasetRepresentation' => 'tabulator',
	'columnsAliases' => array(
		'prestudent_id',
		'person_id',
		'studienplan_id',
		'studiengang_kz',
		ucfirst($this->p->t('lehre', 'studiengang')),
		'Semester',
		ucfirst($this->p->t('person', 'nachname')),
		ucfirst($this->p->t('person', 'vorname')),
		ucfirst($this->p->t('global', 'zgv'))
	),
	'datasetRepOptions' => '{
		height: 300,
		layout: "fitColumns",           // fit columns to width of table
		persistentLayout:true,
		autoResize: false, 				// prevent auto resizing of table (false to allow adapting table size when cols are (de-)activated
	    headerFilterPlaceholder: " ",
        index: "prestudent_id",         // assign specific column as unique id (important for row indexing)
        selectable: 1,                  // allow row selection
        selectablePersistence:false,    // deselect previously selected rows when table is filtered, sorted or paginated
        rowSelected: function(row) {
	        func_rowSelected(row);
        },
        rowSelectionChanged:function(data, rows){
            func_rowSelectionChanged(data, rows);
        },
		tableWidgetHeader: false
	 }',
	'datasetRepFieldsDefs' => '{
		prestudent_id:          {visible: false, headerFilter:"input"},
		person_id:              {visible: false, headerFilter:"input"},
		studienplan_id:         {visible: false, headerFilter:"input"},
		studiengang_kz:         {visible: false, headerFilter:"input"},
		stg_bezeichnung:        {headerFilter:"input"},
		ausbildungssemester:    {headerFilter:"input"},
		nachname:               {headerFilter:"input"},
		vorname:                {headerFilter:"input"},
		zgv:                    {headerFilter:"input"}
	 }'
);

echo $this->widgetlib->widget('TableWidget', $filterWidgetArray);