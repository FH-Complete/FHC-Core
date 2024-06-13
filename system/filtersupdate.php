<?php
/**
 * Copyright (C) 2013 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 */

// Array of filters to be added or updated in the database
$filters = array(
	array(
		'app' => 'infocenter',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'InfoCenterSentApplicationAll',
		'description' => '{Alle}',
		'sort' => 1,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Abgeschickt - Alle",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "ZGVNation"},
					{"name": "ZGVMNation"},
					{"name": "StgAbgeschickt"},
					{"name": "LastAction"},
					{"name": "LastActionType"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"},
					{"name": "HoldDate"},
					{"name": "Rueckstellgrund"},
					{"name": "Kaution"}
				],
				"filters": [
					{
						"name": "AnzahlAbgeschickt",
						"option": "",
						"condition": "0",
						"operation": "gt"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'InfoCenterSentApplication3days',
		'description' => '{"3 Tage keine Aktion"}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Abgeschickt - 3 Tage keine Aktion",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "ZGVNation"},
					{"name": "ZGVMNation"},
					{"name": "StgAbgeschickt"},
					{"name": "Studiensemester"},
					{"name": "LastAction"},
					{"name": "LastActionType"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"}
				],
				"filters": [
					{
						"name": "LastAction",
						"option": "days",
						"condition": "3",
						"operation": "gt"
					},
					{
						"name": "AnzahlAbgeschickt",
						"option": "",
						"condition": "0",
						"operation": "gt"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'InfoCenterNotSentApplicationAll',
		'description' => '{Alle}',
		'sort' => 1,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Nicht abgeschickt - Alle",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "ZGVNation"},
					{"name": "ZGVMNation"},
					{"name": "LastAction"},
					{"name": "LastActionType"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"},
					{"name": "StgNichtAbgeschickt"},
					{"name": "StgAbgeschickt"},
					{"name": "StgAktiv"},
					{"name": "Studiensemester"}
				],
				"filters": [
					{
						"name": "AnzahlStgNichtAbgeschickt",
						"option": "",
						"condition": "0",
						"operation": "gt"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'InfoCenterNotSentApplication14Days',
		'description' => '{"14 Tage keine Aktion"}',
		'sort' => 3,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Nicht abgeschickt - 14 Tage keine Aktion",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "ZGVNation"},
					{"name": "ZGVMNation"},
					{"name": "LastAction"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"},
					{"name": "StgNichtAbgeschickt"},
					{"name": "StgAbgeschickt"},
					{"name": "StgAktiv"},
					{"name": "Studiensemester"}
				],
				"filters": [
					{
						"name": "LastAction",
						"option": "days",
						"condition": "14",
						"operation": "gt"
					},
					{
						"name": "SendDate",
						"option": "",
						"condition": "",
						"operation": "nset"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'InfoCenterSentApplicationLt3days',
		'description' => '{"< 3 Tage"}',
		'sort' => 3,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Abgeschickt - Aktion innert der letzten 3 Tage",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "ZGVNation"},
					{"name": "ZGVMNation"},
					{"name": "StgAbgeschickt"},
					{"name": "Studiensemester"},
					{"name": "LastAction"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"}
				],
				"filters": [
					{
						"name": "LastAction",
						"option": "days",
						"condition": "3",
						"operation": "lt"
					},
					{
						"name": "AnzahlAbgeschickt",
						"option": "",
						"condition": "0",
						"operation": "gt"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'InfoCenterNotSentApplication5DaysOnline',
		'description' => '{"5 Tage keine BewAktion"}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Nicht abgeschickt - 5 Tage keine Aktion durch BewerberIn",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "ZGVNation"},
					{"name": "ZGVMNation"},
					{"name": "LastAction"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"},
					{"name": "StgNichtAbgeschickt"},
					{"name": "StgAbgeschickt"},
					{"name": "StgAktiv"},
					{"name": "Studiensemester"}
				],
				"filters": [
					{
						"name": "SendDate",
						"option": "",
						"condition": "",
						"operation": "nset"
					},
					{
						"name": "LastAction",
						"option": "days",
						"condition": "5",
						"operation": "gt"
					},
					{
						"name": "User/Operator",
						"option": "",
						"condition": "online",
						"operation": "contains"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'freigegeben',
		'filter_kurzbz' => 'InfoCenterFreigegeben5days',
		'description' => '{"5 Tage Letzte Aktion"}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Freigegeben - 5 Tage Letzte Aktion",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "StgAbgeschickt"},
					{"name": "LastAction"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"},
					{"name": "Statusgrund"}
				],
				"filters": [
					{
						"name": "LastAction",
						"option": "days",
						"condition": "5",
						"operation": "lt"
					},
					{
						"name": "ReihungstestAngetreten",
						"operation": "false"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'freigegeben',
		'filter_kurzbz' => 'InfoCenterFreigegebenAlle',
		'description' => '{Alle}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Freigegeben - Alle",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "AktenId"},
					{"name": "StgAbgeschickt"},
					{"name": "LastAction"},
					{"name": "LastActionType"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"},
					{"name": "Statusgrund"},
					{"name": "Studiensemester"},
					{"name": "ReihungstestApplied"},
					{"name": "ReihungstestDate"}
				],
				"filters": [
					{
						"name": "ReihungstestAngetreten",
						"operation": "false"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'BPKWartung',
		'description' => '{bPK Uebersicht}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Fehlende bPK",
				"columns": [
					{"name": "person_id"},
					{"name": "vorname"},
					{"name": "nachname"},
					{"name": "svnr"},
					{"name": "ersatzkennzeichen"},
					{"name": "mitarbeiter"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'leistungsstipendium',
		'filter_kurzbz' => 'LeistungsstipendiumAlle',
		'description' => '{Alle}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Leistungsstipendium - Alle",
				"columns": [
					{"name" : "Vorname"},
					{"name" : "Nachname"},
					{"name" : "Buchungsdatum"},
					{"name" : "Betrag"},
					{"name" : "Buchungstyp"},
					{"name" : "VorgangsId"},
					{"name" : "FoerderfallId"},
					{"name" : "LeistungsdatenId"},
					{"name" : "startjahr"},
					{"name" : "endjahr"},
					{"name" : "Uebermittelt"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'reihungstest',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'Reihungstest',
		'description' => '{Reihungstest Übersicht}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Reihungstest",
				"columns": [
					{"name": "fakultaet"},
					{"name": "datum"},
					{"name": "uhrzeit"},
					{"name": "anmeldefrist"},
					{"name": "oeffentlich"},
					{"name": "studiengaenge"},
					{"name": "freie_plaetze"},
					{"name": "anzahl_angemeldet"},
					{"name": "rt_studiengang"},
					{"name": "reihungstest_id"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'reihungstestAbsolviert',
		'filter_kurzbz' => 'InfoCenterReihungstestAbsolviert5days',
		'description' => '{"Letzten 5 Tage"}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Reihungstest absolviert - Letzten 5 Tage",
				"columns": [
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "StgAbgeschickt"},
					{"name": "LastAction"},
					{"name": "User/Operator"},
					{"name": "InfoCenterMitarbeiter"},
					{"name": "LockUser"}
				],
				"filters": [
					{
						"name": "ReihungstestDatum",
						"option": "days",
						"condition": "5",
						"operation": "lt"
					},
					{
						"name": "ReihungstestAngetreten",
						"operation": "true"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'zgvUeberpruefung',
		'filter_kurzbz' => 'zgvOffen',
		'description' => '{ZGV Überprüfung}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Zgv Überprüfung",
				"columns": [
					{"name": "PreStudentID"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "Studiengang"}
				],
				"filters": [
					{
						"name": "Status",
						"condition": "stg",
						"operation": "contains"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'zgvUeberpruefung',
		'filter_kurzbz' => 'zgvRest',
		'description' => '{ZGV abgeschlossen}',
		'sort' => 2,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Zgv abgeschlossen",
				"columns": [
					{"name": "PreStudentID"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "Studiengang"},
					{"name": "Status"}
				],
				"filters": [
					{
						"name": "Status",
						"condition": "stg",
						"operation": "ncontains"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'abgewiesen',
		'filter_kurzbz' => 'InfoCenterAbgewiesenAlle',
		'description' => '{Alle}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Abgewiesen - Alle",
				"columns": [
					{"name": "PersonId"},
					{"name": "PreStudentID"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "Studiengang"},
					{"name": "AbgewiesenAm"},
					{"name": "Nachricht"},
					{"name": "Kaution"},
					{"name": "LockUser"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'infocenter',
		'dataset_name' => 'aufgenommen',
		'filter_kurzbz' => 'InfoCenterAufgenommenAlle',
		'description' => '{Alle}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Aufgenommen - Lehrgänge",
				"columns": [
					{"name": "PersonId"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "Studiengang"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'budget',
		'dataset_name' => 'budgetoverview',
		'filter_kurzbz' => 'BudgetUebersicht',
		'description' => '{Budgetanträge Übersicht}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Budgetanträge",
				"columns": [
					{"name": "Budgetantrag"},
					{"name": "Kostenstelle"},
					{"name": "Organisationseinheit"},
					{"name": "Geschäftsjahr"},
					{"name": "Budgetstatus"},
					{"name": "Betrag"}
				],
				"filters": [
					{
						"name": "Budgetstatus",
						"condition": "Freigegeben",
						"operation": "ncontains"
					},
					{
						"name": "Geschäftsjahr",
						"condition": "GJ2019-2020",
						"operation": "contains"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'last1min',
		'description' => '{Last minute logs}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "All logs from the last minute",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "1",
						"option": "minutes"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'jobs24hours',
		'description' => '{Last 24 hours jobs logs}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "All jobs logs from the last 24 hours",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "WebserviceType",
						"operation": "contains",
						"condition": "job"
					},
					{
						"name": "RequestId",
						"operation": "contains",
						"condition": "JOB"
					},
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "24",
						"option": "hours"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'jobs48hours',
		'description' => '{Last 48 hours jobs logs}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "All jobs logs from the last 48 hours",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "WebserviceType",
						"operation": "contains",
						"condition": "job"
					},
					{
						"name": "RequestId",
						"operation": "contains",
						"condition": "JOB"
					},
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "48",
						"option": "hours"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'jqws24hours',
		'description' => '{Last 24 hours JQWs logs}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "All Job Queue Workers logs from the last 24 hours",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "WebserviceType",
						"operation": "contains",
						"condition": "job"
					},
					{
						"name": "RequestId",
						"operation": "contains",
						"condition": "JQW"
					},
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "24",
						"option": "hours"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'jqws48hours',
		'description' => '{Last 48 hours JQWs logs}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "All Job Queue Workers logs from the last 48 hours",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "WebserviceType",
						"operation": "contains",
						"condition": "job"
					},
					{
						"name": "RequestId",
						"operation": "contains",
						"condition": "JQW"
					},
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "48",
						"option": "hours"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'repots14days',
		'description' => '{Last 14 days reports logs}',
		'sort' => 3,
		'default_filter' => false,
		'filter' => '
			{
				"name": "All reports logs from the last 14 days",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "WebserviceType",
						"operation": "contains",
						"condition": "reports"
					},
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "14",
						"option": "days"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'content3minutes',
		'description' => '{Last 3 minutes content logs}',
		'sort' => 4,
		'default_filter' => false,
		'filter' => '
			{
				"name": "All content logs from the last 3 minutes",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "WebserviceType",
						"operation": "contains",
						"condition": "content"
					},
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "3",
						"option": "minutes"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'logs',
		'filter_kurzbz' => 'wienerlinien24hours',
		'description' => '{Last 24 hours Wiener Linien logs}',
		'sort' => 5,
		'default_filter' => false,
		'filter' => '
			{
				"name": "All Wiener Linien logs from the last 24 hours",
				"columns": [
					{"name": "RequestId"},
					{"name": "ExecutionTime"},
					{"name": "ExecutedBy"},
					{"name": "Description"},
					{"name": "Data"}
				],
				"filters": [
					{
						"name": "WebserviceType",
						"operation": "contains",
						"condition": "wienerlinien"
					},
					{
						"name": "ExecutionTime",
						"operation": "lt",
						"condition": "24",
						"option": "hours"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
        'app' => 'budget',
        'dataset_name' => 'budgetoverview',
        'filter_kurzbz' => 'BudgetUebersicht',
        'description' => '{Budgetanträge Übersicht}',
        'sort' => 1,
        'default_filter' => true,
        'filter' => '
			{
				"name": "Budgetanträge",
				"columns": [
					{"name": "Budgetantrag"},
					{"name": "Kostenstelle"},
					{"name": "Organisationseinheit"},
					{"name": "Geschäftsjahr"},
					{"name": "Budgetstatus"},
					{"name": "Betrag"}
				],
				"filters": [
					{
						"name": "Budgetstatus",
						"condition": "Freigegeben",
						"operation": "ncontains"
					},
					{
						"name": "Geschäftsjahr",
						"condition": "GJ2019-2020",
						"operation": "contains"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'jq',
		'filter_kurzbz' => 'lastHour',
		'description' => '{Last hour queued jobs}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "All jobs queued in the last hour",
				"columns": [
					{"name": "JobId"},
					{"name": "CreationTime"},
					{"name": "Type"},
					{"name": "Status"},
					{"name": "StartTime"},
					{"name": "EndTime"},
					{"name": "UserService"}
				],
				"filters": [
					{
						"name": "CreationTime",
						"operation": "lt",
						"condition": "1",
						"option": "hours"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'issues',
		'filter_kurzbz' => 'offeneFehler',
		'description' => '{Alle offenen}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Alle offenen Fehler, hauptzuständig",
				"columns": [
					{"name": "Datum"},
					{"name": "Inhalt"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "PersonId"},
					{"name": "Fehlerstatus"},
					{"name": "Zugehörigkeit"},
					{"name": "Person Zuständigkeiten"},
					{"name": "Organisationseinheit Zuständigkeiten"}
				],
				"filters": [
					{
						"name": "Statuscode",
						"operation": "ncontains",
						"condition": "resolved"
					},
					{
						"name": "Hauptzuständig",
						"operation": "contains",
						"condition": "Ja"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'issues',
		'filter_kurzbz' => 'FehlerLetze7Tage',
		'description' => '{Letzten 7 Tage}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Alle in den letzten 7 Tagen aufgetretenen Fehler",
				"columns": [
					{"name": "Datum"},
					{"name": "Inhalt"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "PersonId"},
					{"name": "Fehlerstatus"},
					{"name": "Zugehörigkeit"},
					{"name": "Person Zuständigkeiten"},
					{"name": "Organisationseinheit Zuständigkeiten"}
				],
				"filters": [
					{
						"name": "Datum",
						"operation": "lt",
						"condition": "7",
						"option": "days"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'issues',
		'filter_kurzbz' => 'FehlerLetzte7TageBearbeitet',
		'description' => '{Letzten 7 Tage bearbeitet}',
		'sort' => 3,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Alle in den letzten 7 Tagen bearbeiteten Fehler",
				"columns": [
					{"name": "Datum"},
					{"name": "Inhalt"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "PersonId"},
					{"name": "Fehlerstatus"},
					{"name": "Zugehörigkeit"},
					{"name": "Verarbeitet von"},
					{"name": "Verarbeitet am"}
				],
				"filters": [
					{
						"name": "Verarbeitet am",
						"operation": "lt",
						"condition": "7",
						"option": "days"
					},
					{
						"name": "Statuscode",
						"operation": "contains",
						"condition": "resolved"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'projektarbeitsbeurteilung',
		'dataset_name' => 'projektuebersicht',
		'filter_kurzbz' => 'alleProjekte',
		'description' => '{Projektübersicht}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Projektübersicht",
				"columns": [
					{"name": "Studiengang"},
					{"name": "StudentNachname"},
					{"name": "Abgabedatum"},
					{"name": "Note"},
					{"name": "ErstNachname"},
					{"name": "ErstAbgeschickt"},
					{"name": "ZweitNachname"},
					{"name": "ZweitAbgeschickt"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'dvuh',
		'dataset_name' => 'storno',
		'filter_kurzbz' => 'DVUHStorno',
		'description' => '{DVUH Storno Übersicht}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "DVUHStorno",
				"columns": [
					{"name": "vorname"},
					{"name": "nachname"},
					{"name": "matrikelnummer"},
					{"name": "studiengang"},
					{"name": "studiensemester"},
					{"name": "last_status"},
					{"name": "bismelden"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'dvuh',
		'dataset_name' => 'overview',
		'filter_kurzbz' => 'BPKWartungDVUH',
		'description' => '{bPK Uebersicht}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Fehlende bPK",
				"columns": [
					{"name": "person_id"},
					{"name": "vorname"},
					{"name": "nachname"},
					{"name": "svnr"},
					{"name": "ersatzkennzeichen"},
					{"name": "mitarbeiter"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'employees',
		'filter_kurzbz' => 'mitarbeiter',
		'description' => '{MA Uebersicht}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Mitarbeiter",
				"columns": [
					{"name": "PersonId"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "Alias"},
					{"name": "Aktiv"},
					{"name": "Raum"},
					{"name": "Geschlecht"},
					{"name": "Standardkostenstelle"}
				],
				"filters": [
					{
					"name": "Nachname",
					"option": "",
					"condition": "",
					"operation": ""
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'personalverwaltung',
		'dataset_name' => 'personalIssueViewer',
		'filter_kurzbz' => 'offeneFehlerPersonal',
		'description' => '{Alle offenen Fehler}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Alle offenen Fehler",
				"columns": [
					{"name": "Datum"},
					{"name": "Inhalt"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "PersonId"},
					{"name": "Statuscode"}
				],
				"filters": [
					{
						"name": "Statuscode",
						"operation": "ncontains",
						"condition": "resolved"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'personalverwaltung',
		'dataset_name' => 'personalIssueViewer',
		'filter_kurzbz' => 'FehlerLetzte7TageBearbeitetPersonal',
		'description' => '{Letzten 7 Tage bearbeitet}',
		'sort' => 2,
		'default_filter' => false,
		'filter' => '
			{
				"name": "Alle in den letzten 7 Tagen bearbeiteten Fehler",
				"columns": [
					{"name": "Datum"},
					{"name": "Inhalt"},
					{"name": "Vorname"},
					{"name": "Nachname"},
					{"name": "PersonId"},
					{"name": "Statuscode"},
					{"name": "Verarbeitet von"},
					{"name": "Verarbeitet am"}
				],
				"filters": [
					{
						"name": "Verarbeitet am",
						"operation": "lt",
						"condition": "7",
						"option": "days"
					},
					{
						"name": "Statuscode",
						"operation": "contains",
						"condition": "resolved"
					}
				]
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'fehlerZustaendigkeiten',
		'filter_kurzbz' => 'fehlerZustaendigkeiten',
		'description' => '{Fehler Zustaendigkeiten}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Fehler Zuständigkeiten",
				"columns": [
					{"name": "fehlercode"},
					{"name": "person_id"},
					{"name": "vorname"},
					{"name": "nachname"},
					{"name": "oe_bezeichnung"},
					{"name": "funktion_beschreibung"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null
	),
	array(
		'app' => 'core',
		'dataset_name' => 'fehlerKonfiguration',
		'filter_kurzbz' => 'fehlerKonfiguration',
		'description' => '{Fehler Konfiguration}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Fehler Konfiguration",
				"columns": [
					{"name": "konfigurationstyp_kurzbz"},
					{"name": "fehlercode"},
					{"name": "fehler_kurzbz"},
					{"name": "konfiguration"},
					{"name": "app"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null
	),
	array(
		'app' => 'core',
		'dataset_name' => 'gruppenmanagement',
		'filter_kurzbz' => 'gruppenmanagement',
		'description' => '{Meine Gruppen}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "gruppenmanagement",
				"columns": [
					{"name": "gruppe_kurzbz"},
					{"name": "gruppe_bezeichnung"},
					{"name": "gruppe_beschreibung"},
					{"name": "studiengang_kurzbz"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'softwareManagement',
		'filter_kurzbz' => 'SoftwareManagement',
		'description' => '{Software Verwaltung}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "SoftwareManagement",
				"columns": [
					{"name": "software_kurzbz"},
					{"name": "version"},
					{"name": "beschreibung"},
					{"name": "hersteller"},
					{"name": "os"},
					{"name": "lizenzserver_kurzbz"},
					{"name": "lizenzserver_port"},
					{"name": "softwarestatus_kurzbz"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'imageVerwaltung',
		'filter_kurzbz' => 'ImageVerwaltung',
		'description' => '{Image Verwaltung}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "ImageVerwaltung",
				"columns": [
					{"name": "bezeichnung"},
					{"name": "betriebssystem"},
					{"name": "verfuegbarkeit_start"},
					{"name": "verfuegbarkeit_ende"},
					{"name": "anmerkung"},
					{"name": "ort_count"},
					{"name": "software_count"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'core',
		'dataset_name' => 'lizenzserverVerwaltung',
		'filter_kurzbz' => 'LizenzserverVerwaltung',
		'description' => '{Lizenzserver Verwaltung}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "LizenzserverVerwaltung",
				"columns": [
					{"name": "lizenzserver_kurzbz"},
					{"name": "bezeichnung"},
					{"name": "macadresse"},
					{"name": "ipadresse"},
					{"name": "ansprechpartner"},
					{"name": "anmerkung"},
					{"name": "location"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null,
	),
	array(
		'app' => 'fhctemplate',
		'dataset_name' => 'exampledata',
		'filter_kurzbz' => 'exampledata',
		'description' => '{Beispieldaten Filter}',
		'sort' => 1,
		'default_filter' => true,
		'filter' => '
			{
				"name": "Alle Beispieldaten",
				"columns": [
					{"name": "uid"},
					{"name": "stringval"},
					{"name": "integerval"},
					{"name": "dateval"},
					{"name": "booleanval"},
					{"name": "moneyval"},
					{"name": "dokument_bezeichnung"},
					{"name": "textval"},
					{"name": "examplestatus_kurzbz"}
				],
				"filters": []
			}
		',
		'oe_kurzbz' => null
	)
);

// Loop through the filters array
for ($filtersCounter = 0; $filtersCounter < count($filters); $filtersCounter++)
{
	$filter = $filters[$filtersCounter]; // single filter definition

	// If it's an array and contains the required number of elements
	// and contains the required fields
	if (is_array($filter) && count($filter) == 8
		&& isset($filter['app']) && isset($filter['dataset_name'])
		&& isset($filter['filter_kurzbz']) && isset($filter['description'])
		&& isset($filter['filter']))
	{
		$selectFilterQuery = 'SELECT filter_id
								FROM system.tbl_filters
							   WHERE app = '.$db->db_add_param($filter['app']).
							   ' AND dataset_name = '.$db->db_add_param($filter['dataset_name']).
							   ' AND filter_kurzbz = '.$db->db_add_param($filter['filter_kurzbz']);

		// If no error occurred while loading a filter from the DB
	   	if ($dbFilterDefinition = @$db->db_query($selectFilterQuery))
	   	{
			// If NO filters were loaded: insert
			if ($db->db_num_rows($dbFilterDefinition) == 0)
			{
				$insertFilterQuery = 'INSERT INTO system.tbl_filters (
											app,
											dataset_name,
											filter_kurzbz,
											person_id,
											description,
											sort,
											default_filter,
											filter,
											oe_kurzbz
										) VALUES (
											'.$db->db_add_param($filter['app']).',
											'.$db->db_add_param($filter['dataset_name']).',
											'.$db->db_add_param($filter['filter_kurzbz']).',
											null,
											'.$db->db_add_param($filter['description']).',
											'.$db->db_add_param($filter['sort']).',
											'.$db->db_add_param($filter['default_filter']).',
											'.$db->db_add_param($filter['filter']).',
											'.$db->db_add_param($filter['oe_kurzbz']).'
										)';

				if (!@$db->db_query($insertFilterQuery)) // checks query execution
				{
					echo '<strong>An error occurred while inserting filters: '.$db->db_last_error().'</strong><br>';
				}
				else
				{
					echo 'Filter added: '.$filter['app'].' - '.$filter['dataset_name'].' - '.$filter['filter_kurzbz'].'<br>';
				}
			}
			else // otherwise if the filter is already present in the DB: update
			{
				if ($filterDb = $db->db_fetch_object($dbFilterDefinition))
				{
					$updateFilterQuery = 'UPDATE system.tbl_filters SET
												app = '.$db->db_add_param($filter['app']).',
												dataset_name = '.$db->db_add_param($filter['dataset_name']).',
												filter_kurzbz = '.$db->db_add_param($filter['filter_kurzbz']).',
												person_id = null,
												description = '.$db->db_add_param($filter['description']).',
												sort = '.$db->db_add_param($filter['sort']).',
												default_filter = '.$db->db_add_param($filter['default_filter']).',
												filter = '.$db->db_add_param($filter['filter']).',
												oe_kurzbz = '.$db->db_add_param($filter['oe_kurzbz']).'
										   WHERE filter_id = '.$db->db_add_param($filterDb->filter_id);

					if (!@$db->db_query($updateFilterQuery)) // checks query execution
					{
						echo '<strong>An error occurred while inserting filters: '.$db->db_last_error().'</strong><br>';
					}
					else
					{
						echo 'Filter updated: '.$filter['app'].' - '.$filter['dataset_name'].' - '.$filter['filter_kurzbz'].'<br>';
					}
				}
			}
		}
		else // otherwise if errors occurred
		{
			echo '<strong>An error occurred while inserting filters: '.$db->db_last_error().'</strong><br>';
		}
	}
}
