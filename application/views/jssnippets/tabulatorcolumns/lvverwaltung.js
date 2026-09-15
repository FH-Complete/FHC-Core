return [
	{
		title: 'kurzbz',
		titlePhrase: 'lehre/kurzbz',
		field: "lv_kurzbz",
		headerFilterFuncParams: { field: 'lv_kurzbz' },
		headerFilter: true,
		formatter(cell, formatterParams) {
			const rowData = cell.getRow().getData();
			const iconKey = (rowData.lehrtyp_kurzbz || '').toLowerCase();
			const lvkurzbz = (cell.getValue()).toUpperCase();

			const parentspan = document.createElement('span');
			const span = document.createElement('span');

			span.classList.add('lv_table_icon', `icon-${iconKey}`);
			span.title = iconKey || 'LV-Teil';

			parentspan.appendChild(span);
			parentspan.appendChild(document.createTextNode(` ${lvkurzbz}`));

			return parentspan;
		},
		cellClick(e, cell) {
			cell.getRow().treeToggle();
		}
	},
	{
		title: 'Tags',
		field: 'tags',
		tooltip: false,
		headerFilter: "input",
		headerFilterFunc: "tagHeaderFilter",
		headerFilterFuncParams: { field: 'tags' },
		formatter: "tagFormatter",
		width: 150,
	},
	{
		title: 'lehrveranstaltung_id',
		titlePhrase: 'lehre/lehrveranstaltung_id',
		field: "lehrveranstaltung_id",
		headerFilterFuncParams: { field: 'lehrveranstaltung_id' },
		headerFilter: true,
		visible: false
	},
	{
		title: 'bezeichnung',
		titlePhrase: 'ui/bezeichnung',
		field: "lv_bezeichnung",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lv_bezeichnung' }
	},
	{
		title: 'bezeichnungeng',
		titlePhrase: 'lehre/bezeichnungeng',
		field: "lv_bezeichnung_english",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lv_bezeichnung_english' },
		visible: false
	},
	{
		title: 'studiengangskennzahlLehre',
		titlePhrase: 'lehre/studiengangskennzahlLehre',
		field: "lv_studiengang_kz",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lv_studiengang_kz' },
		visible: false
	},
	{
		title: 'studiengang',
		titlePhrase: 'lehre/studiengang',
		field: "studiengang",
		headerFilter: true,
		headerFilterFuncParams: { field: 'studiengang' },
		visible: false
	},
	{
		title: 'semester',
		titlePhrase: 'lehre/semester',
		field: "semester",
		headerFilter: true,
		headerFilterFuncParams: { field: 'semester' }
	},
	{
		title: 'sprache',
		titlePhrase: 'global/sprache',
		field: "sprache",
		headerFilter: true,
		headerFilterFuncParams: { field: 'sprache' },
		visible: false
	},
	{
		title: 'ects',
		titlePhrase: 'lehre/ects',
		field: "lv_ects",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lv_ects' },
		visible: false
	},
	{
		title: 'semesterstunden',
		titlePhrase: 'lehre/semesterstunden',
		field: "semesterstunden",
		headerFilter: true,
		headerFilterFuncParams: { field: 'semesterstunden' },
		visible: false
	},
	{
		title: 'anmerkung',
		titlePhrase: 'global/anmerkung',
		field: "anmerkung",
		headerFilter: true,
		headerFilterFuncParams: { field: 'anmerkung' },
		visible: false
	},
	{
		title: 'lehre',
		titlePhrase: 'lehre/lehre',
		field: "lehre",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lehre' },
		formatter: 'ja_nein',
		visible: false
	},
	{
		title: 'aktiv',
		titlePhrase: 'person/aktiv',
		field: "aktiv",
		headerFilter: true,
		headerFilterFuncParams: { field: 'aktiv' },
		formatter: 'ja_nein',
		visible: false
	},
	{
		title: 'organisationsform',
		titlePhrase: 'lehre/organisationsform',
		field: "orgform_kurzbz",
		headerFilter: true,
		headerFilterFuncParams: { field: 'orgform_kurzbz' }
	},
	{
		title: 'studienplan_id',
		titlePhrase: 'ui/studienplan_id',
		field: "studienplan_id",
		headerFilter: true,
		headerFilterFuncParams: { field: 'studienplan_id' },
		visible: false
	},
	{
		title: 'studienplan',
		titlePhrase: 'lehre/studienplan',
		field: "studienplan_bezeichnung",
		headerFilter: true,
		headerFilterFuncParams: { field: 'studienplan_bezeichnung' },
		visible: false
	},
	{
		title: 'lehrtyp',
		titlePhrase: 'lehre/lehrtyp',
		field: "lehrtyp_kurzbz",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lehrtyp_kurzbz' },
		visible: false
	},
	{
		title: 'lehrform',
		titlePhrase: 'lehre/lehrform',
		field: "lehrform_kurzbz",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lehrform_kurzbz'} },
	{
		title: 'leplanstunden',
		titlePhrase: 'lehre/leplanstunden',
		field: "le_planstunden",
		headerFilter: true,
		headerFilterFuncParams: { field: 'le_planstunden' },
		visible: false
	},
	{
		title: 'lehreinheit_id',
		titlePhrase: 'lehre/lehreinheit_id',
		field: "lehreinheit_id",
		headerFilter: true,
		headerFilterFuncParams: { field: 'lehreinheit_id' },
		visible: false
	},
	{
		title: 'studiensemester',
		titlePhrase: 'lehre/studiensemester',
		field: "studiensemester_kurzbz",
		headerFilter: true,
		headerFilterFuncParams: { field: 'studiensemester_kurzbz' },
		visible: false
	},
	{
		title: 'unr',
		titlePhrase: 'lehre/unr',
		field: "unr",
		headerFilter: true,
		headerFilterFuncParams: { field: 'unr' },
		visible: false
	},
	{
		title: 'organisationseinheit',
		titlePhrase: 'lehre/organisationseinheit',
		field: "fachbereich",
		headerFilter: true,
		headerFilterFuncParams: { field: 'fachbereich' },
		visible: false
	},
	{
		title: 'stundenblockung',
		titlePhrase: 'lehre/stundenblockung',
		field: "stundenblockung",
		headerFilter: true,
		headerFilterFuncParams: { field: 'stundenblockung' },
		visible: false
	},
	{
		title: 'wochenrhythmus',
		titlePhrase: 'lehre/wochenrhythmus',
		field: "wochenrythmus",
		headerFilter: true,
		headerFilterFuncParams: { field: 'wochenrythmus' },
		visible: false
	},
	{
		title: 'startkw',
		titlePhrase: 'lehre/startkw',
		field: "start_kw",
		headerFilter: true,
		headerFilterFuncParams: { field: 'startkw' },
		visible: false
	},
	{
		title: 'raumtyp',
		titlePhrase: 'lehre/raumtyp',
		field: "raumtyp",
		headerFilter: true,
		headerFilterFuncParams: { field: 'raumtyp' },
		visible: false
	},
	{
		title: 'raumtypalternativ',
		titlePhrase: 'lehre/raumtypalternativ',
		field: "raumtypalternativ",
		headerFilter: true,
		headerFilterFuncParams: { field: 'raumtypalternativ' },
		visible: false
	},
	{
		title: 'gruppen',
		titlePhrase: 'lehre/gruppen',
		field: "gruppen",
		headerFilter: true,
		headerFilterFuncParams: { field: 'gruppen'} },
	{
		title: 'lehrende',
		titlePhrase: 'lehre/lehrende',
		field: "lektoren",
		headerFilter: true,
		headerFilterFuncParams: { field: ['lektoren', 'vorname', 'nachname'] }
	},
];
