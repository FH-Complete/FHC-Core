return [
	{
		title: "UID",
		titlePhrase: 'person/uid',
		field: "uid",
		headerFilter: true
	},
	{
		title: "TitelPre",
		titlePhrase: 'person/titelpre',
		field: "titelpre",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Nachname",
		titlePhrase: 'person/nachname',
		field: "nachname",
		headerFilter: true
	},
	{
		title: "Vorname",
		titlePhrase: 'person/vorname',
		field: "vorname",
		headerFilter: true
	},
	{
		title: "Wahlname",
		titlePhrase: 'person/wahlname',
		field: "wahlname",
		visible: false,
		headerFilter: true
	},
	{
		title: "Vornamen",
		titlePhrase: 'person/vornamen',
		field: "vornamen",
		visible: false,
		headerFilter: true
	},
	{
		title: "TitelPost",
		titlePhrase: 'person/titelpost',
		field: "titelpost",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Ersatzkennzeichen",
		titlePhrase: 'person/ersatzkennzeichen',
		field: "ersatzkennzeichen",
		headerFilter: true
	},
	{
		title: "Geburtsdatum",
		titlePhrase: 'person/geburtsdatum',
		field: "gebdatum",
		formatter: 'datetime',
		formatterParams: {
			inputFormat: "iso",
			outputFormat: "dd.MM.yyyy",
			invalidPlaceholder: "&nbsp;",
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone
		}, 
		headerFilter: 'input',
		headerFilterFunc: 'dateinput'
	},
	{
		title: "Geschlecht",
		titlePhrase: 'person/geschlecht',
		field: "geschlecht",
		headerFilter: "list",
		headerFilterParams: {
			values: {
				'm': 'männlich',
				'w': 'weiblich',
				'x': 'divers',
				'u': 'unbekannt'
			},
			listOnEmpty: true,
			autocomplete: true
		}
	},
	{
		title: "Sem.",
		titlePhrase: 'lehre/sem',
		field: "semester_berechnet",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Verb.",
		titlePhrase: 'lehre/verb',
		field: "verband",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Grp.",
		titlePhrase: 'lehre/grp',
		field: "gruppe",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Studiengang",
		titlePhrase: 'lehre/studiengang',
		field: "studiengang",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Studiengang_kz",
		titlePhrase: 'lehre/studiengang_kz',
		field: "studiengang_kz",
		visible: false,
		headerFilter: true
	},
	{
		title: "Personenkennzeichen",
		titlePhrase: 'person/personenkennzeichen',
		field: "matrikelnr",
		headerFilter: true
	},
	{
		title: "PersonID",
		titlePhrase: 'person/person_id',
		field: "person_id",
		headerFilter: true
	},
	{
		title: "Status",
		titlePhrase: 'global/status',
		field: "status",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Status Datum",
		titlePhrase: 'profilUpdate/statusDate',
		field: "status_datum",
		visible: false,
		formatter: 'datetime',
		formatterParams: {
			inputFormat: "iso",
			outputFormat: "dd.MM.yyyy",
			invalidPlaceholder: "&nbsp;",
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone
		}
	},
	{
		title: "Status Bestaetigung",
		titlePhrase: 'global/status_bestaetigung',
		field: "status_bestaetigung",
		visible: false,
		formatter: 'datetime',
		formatterParams: {
			inputFormat: "iso",
			outputFormat: "dd.MM.yyyy",
			invalidPlaceholder: "&nbsp;",
			timezone: FHC_JS_DATA_STORAGE_OBJECT.timezone
		},
		headerFilter: 'input',
		headerFilterFunc: 'dateinput'
	},
	{
		title: "EMail (Privat)",
		titlePhrase: 'person/email_private',
		field: "mail_privat",
		visible: false,
		headerFilter: true
	},
	{
		title: "EMail (Intern)",
		titlePhrase: 'person/email_intern',
		field: "mail_intern",
		visible: false,
		headerFilter: true
	},
	{
		title: "Anmerkungen",
		titlePhrase: 'stv/notes_person',
		field: "anmerkungen",
		visible: false,
		headerFilter: true
	},
	{
		title: "AnmerkungPre",
		titlePhrase: 'stv/notes_prestudent',
		field: "anmerkung",
		visible: false,
		headerFilter: true
	},
	{
		title: "OrgForm",
		titlePhrase: 'lehre/orgform',
		field: "orgform_kurzbz",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "Aufmerksamdurch",
		titlePhrase: 'person/aufmerksamDurch',
		field: "aufmerksamdurch_kurzbz",
		visible: false
	},
	{
		title: "Gesamtpunkte",
		titlePhrase: 'admission/gesamtpunkte',
		field: "punkte",
		visible: false
	},
	{
		title: "Aufnahmegruppe",
		titlePhrase: 'stv/aufnahmegruppe_kurzbz',
		field: "aufnahmegruppe_kurzbz",
		visible: false
	},
	{
		title: "Dual",
		titlePhrase: 'lehre/dual_short',
		field: "dual",
		visible: false,
		formatter: 'tickCross',
		formatterParams: {
			tickElement: '<i class="fas fa-check text-success"></i>',
			crossElement: '<i class="fas fa-times text-danger"></i>'
		},						
		headerFilter: "tickCross",
		headerFilterParams: {
			tristate: true,
			elementAttributes: {
				value: "true"
			}
		},
		headerFilterEmptyCheck(value) {
			return value === null
		}
	},
	{
		title: "Matrikelnummer",
		titlePhrase: 'person/matrikelnummer',
		field: "matr_nr",
		visible: false,
		headerFilter: true
	},
	{
		title: "Studienplan",
		titlePhrase: 'lehre/studienplan',
		field: "studienplan_bezeichnung",
		headerFilter: "list",
		headerFilterParams: {
			valuesLookup: true,
			listOnEmpty: true,
			autocomplete: true,
			sort: "asc"
		}
	},
	{
		title: "PreStudentInnenID",
		titlePhrase: 'ui/prestudent_id',
		field: "prestudent_id",
		headerFilter: true
	},
	{
		title: "Priorität",
		titlePhrase: 'lehre/prioritaet',
		field: "priorisierung_relativ"
	},
	{
		title: "Mentor",
		titlePhrase: 'stv/mentor',
		field: "mentor",
		visible: false
	},
	{
		title: "Aktiv",
		titlePhrase: 'person/aktiv',
		field: "bnaktiv",
		visible: false,
		formatter: 'tickCross',
		formatterParams: {
			allowEmpty: true,
			tickElement: '<i class="fas fa-check text-success"></i>',
			crossElement: '<i class="fas fa-times text-danger"></i>'
		},						
		headerFilter: "tickCross",
		headerFilterParams: {
			tristate: true,
			elementAttributes: {
				value: "true"
			}
		},
		headerFilterEmptyCheck(value) {
			return value === null
		}
	},
	{
		title: "Unruly",
		field: "unruly",
		visible: false
	}
];
