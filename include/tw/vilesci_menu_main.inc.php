<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Enthaelt das Array fuer die Menuepunkt der Vilesci-Seite
 */

$menu=array
(
	//'Admin'=> 		array('name'=>'Admin', 'link'=>'admin/menu.html', 'target'=>'main'),
	'LVPlan'=> 		array
	(
		'name'=>'LV-Plan', 'opener'=>'true', 'hide'=>'false', 'permissions'=>array('admin','lv-plan'), 'image'=>'vilesci_lvplan.png',
		'link'=>'left.php?categorie=LVPlan', 'target'=>'nav',
		'Gruppenverwaltung'=>array('name'=>'Gruppenverwaltung', 'permissions'=>array('admin','lv-plan','support'), 'link'=>'stammdaten/lvbgruppenverwaltung.php', 'target'=>'main'),
		'Lehrveranstaltung'=>array('name'=>'Lehrveranstaltung', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
		'Lehrfach'=>array('name'=>'Lehrfach', 'link'=>'lehre/lehrfach.php', 'target'=>'main'),
		/* Lerrzeile */'Verplanungsuebersicht1'=>array('name'=>'', 'link'=>'lehre/check/verplanungsuebersicht.php', 'target'=>'main'),
		'Verplanungsuebersicht'=>array('name'=>'Verplanungsübersicht', 'link'=>'lehre/check/verplanungsuebersicht.php', 'target'=>'main'),
		'Reihungstest'=>array('name'=>'Reihungstest', 'link'=>'stammdaten/reihungstestverwaltung.php', 'target'=>'main','permissions'=>array('admin','assistenz')),
		'LV-Planung'=>array
		(
			'name'=>'LV-Planung', 'permissions'=>array('admin','lv-plan','support'),
			'Wartung'=>array('name'=>'Wartung', 'link'=>'lehre/lvplanwartung.php', 'target'=>'main'),
			'Check'=>array('name'=>'Checken', 'link'=>'lehre/check/index.html', 'target'=>'main'),
			'Kollision'=>array('name'=>'Kollision Student', 'link'=>'lehre/stpl_benutzer_kollision_frameset.html', 'target'=>'main'),
			'Stundenplan'=>array('name'=>'Stundenplan', 'link'=>'../cis/private/lvplan/index.html', 'target'=>'main'),
			//'Zeitwuensche'=>array('name'=>'Zeitwünsche', 'link'=>'lehre/zeitwuensche.php', 'target'=>'main'),
			'LVPlanSync'=>array('name'=>'Sync', 'link'=>'lehre/lvplan_custom_sync.php', 'target'=>'main'),
			'Ueberbuchungen'=>array('name'=>'Überbuchungen', 'link'=>'lehre/check/ueberbuchung.php', 'target'=>'main'),
			'Reservierungen einfuegen'=>array('name'=>'Reserv. einfügen', 'link'=>'lehre/reservierung_insert.php', 'target'=>'main'),
			'Incoming loeschen'=>array('name'=>'Incoming löschen', 'link'=>'lehre/incoming_delete.php', 'target'=>'main'),
		),
		'Raummitteilung'=>array('name'=>'Raummitteilung', 'link'=>'lehre/raummitteilung.php', 'target'=>'main'),
		
		'Mitarbeiter'=>array
		(
			'name'=>'Mitarbeiter','permissions'=>array('admin','lv-plan','support'),
			'Übersicht'=>array('name'=>'Zeitwünsche', 'link'=>'personen/lektor_uebersicht.php', 'target'=>'main'),
			'Zeitsperren'=>array('name'=>'Zeitsperren', 'link'=>'personen/urlaubsverwaltung.php', 'target'=>'main'),
		),
		
		'Vorrueckung'=>	array
		(
			'name'=>'Vorrueckung', 'permissions'=>array('admin','lv-plan','support'),
			'Lehreinheiten'=>array('name'=>'Lehreinheiten', 'link'=>'lehre/lehreinheiten_vorrueckung.php', 'target'=>'main'),
			'Studenten'=>array('name'=>'Studenten', 'link'=>'personen/student_vorrueckung.php', 'target'=>'main')
		),
		
	),
	'Lehre'=> 		array
	(
		'name'=>'Lehre', 'opener'=>'true', 'hide'=>'false', 'permissions'=>array('admin','lv-plan','support', 'lehre'), 'image'=>'vilesci_lehre.png',
		'link'=>'left.php?categorie=Lehre', 'target'=>'nav',
		'Gruppenverwaltung'=>array('name'=>'Gruppen', 'permissions'=>array('admin','lv-plan','support'), 'link'=>'stammdaten/lvbgruppenverwaltung.php', 'target'=>'main'),
		'Lehrveranstaltung'=>array('name'=>'Lehrveranstaltung', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
		'Lehrfach'=>array('name'=>'Lehrfach', 'link'=>'lehre/lehrfach.php', 'target'=>'main'),
		'Studienordnung'=>array('name'=>'Studienordnung', 'link'=>'lehre/studienordnung.php', 'target'=>'_blank','permissions'=>array('lehre/studienordnung')),
		
		'Moodle'=>array
		(
			'name'=>'Moodle', 'permissions'=>array('admin','lv-plan','support','basis/moodle'),
			'Kursverwaltung'=>array('name'=>'Kurs entfernen', 'link'=>'moodle/kurs_verwaltung.php', 'target'=>'main'),
			'Account'=>array('name'=>'Account', 'link'=>'moodle/account_verwaltung.php', 'target'=>'main'),
			'Zuteilung Verwalten'=>array('name'=>'Zuteilung Verwalten', 'link'=>'moodle/zuteilung_verwaltung.php', 'target'=>'main'),
			'Account24'=>array('name'=>'Account Moodle 2.4', 'link'=>'moodle/account_verwaltung24.php', 'target'=>'main'),
            'Kursverwaltung24'=>array('name'=>'Kurs entfernen 2.4', 'link'=>'moodle/kurs_verwaltung24.php', 'target'=>'main'),
            'Rollenzuteilung24'=>array('name'=>'Rollenzuteilung 2.4', 'link'=>'moodle/rollenzuteilung24.php', 'target'=>'main'),
		),
				
		
		
		'Freifach'=>array
		(
			'name'=>'Freifach', 'permissions'=>array('lehre/freifach'),
			'Studenten'=>array('name'=>'Studenten', 'link'=>'lehre/freifach.php', 'target'=>'main'),
			'Lektoren'=>array('name'=>'Lektoren', 'link'=>'lehre/freifach_lektoren.php', 'target'=>'main'),
			'Studenten Vorrücken'=>array('name'=>'Studenten Vorrücken', 'link'=>'lehre/freifach_studentenvorrueckung.php', 'target'=>'main')
		),
		'LV-Planung'=>array
		(
			'name'=>'LV-Planung', 'permissions'=>array('admin','lv-plan','support'),
			'Wartung'=>array('name'=>'Wartung', 'link'=>'lehre/lvplanwartung.php', 'target'=>'main'),
			'Check'=>array('name'=>'Checken', 'link'=>'lehre/check/index.html', 'target'=>'main'),
			'Kollision'=>array('name'=>'Kollision Student', 'link'=>'lehre/stpl_benutzer_kollision_frameset.html', 'target'=>'main'),
			'Stundenplan'=>array('name'=>'Stundenplan', 'link'=>'../cis/private/lvplan/index.html', 'target'=>'main'),
			'Zeitwuensche'=>array('name'=>'Zeitwünsche', 'link'=>'lehre/zeitwuensche.php', 'target'=>'main'),
			'LVPlanSync'=>array('name'=>'Sync', 'link'=>'lehre/lvplan_custom_sync.php', 'target'=>'main'),
			'Ueberbuchungen'=>array('name'=>'Überbuchungen', 'link'=>'lehre/check/ueberbuchung.php', 'target'=>'main'),
			//'Studenten'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
			//'Insert'=>array('name'=>'Insert', 'link'=>'lehre/stdplan_insert.php', 'target'=>'main'),
			//'Delete'=>array('name'=>'Delete', 'link'=>'lehre/stdplan_delete.php', 'target'=>'main'),
			//'Import'=>array('name'=>'Import', 'link'=>'lehre/import/index.hml', 'target'=>'main'),
			//'Export'=>array('name'=>'Export', 'link'=>'lehre/export/index.html', 'target'=>'main')
		),
		'Raummitteilung'=>array('name'=>'Raummitteilung', 'link'=>'lehre/raummitteilung.php', 'target'=>'main'),
	),
	'Personen'=> 	array
	(
		'name'=>'Personen', 'opener'=>'true', 'hide'=>'true', 'image'=>'vilesci_personen.png', 'permissions'=>array('admin','lv-plan','support','mitarbeiter','basis/person'),
		'link'=>'left.php?categorie=Personen', 'target'=>'nav',
		'Suche'=>array('name'=>'Suche', 'link'=>'personen/suche.php', 'target'=>'main','permissions'=>array('admin','lv-plan','support','basis/person')),
		'Zusammenlegen'=>array('name'=>'Zusammenlegen', 'link'=>'stammdaten/personen_wartung.php', 'target'=>'main', 'permissions'=>array('admin','lv-plan','support')),
		'Wiederholer'=>array('name'=>'Stg-Wiederholer', 'link'=>'personen/wiederholer.php', 'target'=>'main', 'permissions'=>array('basis/person')),
		'Gruppen'=>array
		(
			'name'=>'Gruppen', 'permissions'=>array('admin','lv-plan','support'),
			'Übersicht'=>array('name'=>'Übersicht', 'link'=>'lehre/einheit_menu.php', 'target'=>'main'),
			'Neu'=>array('name'=>'Neu', 'link'=>'lehre/einheit_menu.php?newFrm=true', 'target'=>'main')
		),
		'Benutzer'=>array
		(
			'name'=>'Benutzer','permissions'=>array('admin','lv-plan','support'),
			'Funktionen'=>array('name'=>'Funktionen', 'link'=>'personen/funktion.php', 'target'=>'main'),
			'Berechtigungen'=>array('name'=>'Berechtigungen', 'link'=>'stammdaten/benutzerberechtigung_frameset.html', 'target'=>'main','permissions'=>array('basis/berechtigung')),
			'Variablen'=>array('name'=>'Variablen', 'link'=>'stammdaten/variablen_frameset.html', 'target'=>'main', 'target'=>'main','permissions'=>array('basis/variable')),
		),
		'Mitarbeiter'=>array
		(
			'name'=>'Mitarbeiter','permissions'=>array('admin','mitarbeiter','support'),
			'Übersicht'=>array('name'=>'Übersicht', 'link'=>'personen/lektor_uebersicht.php', 'target'=>'main'),
			'Zeitsperren'=>array('name'=>'Zeitsperren', 'link'=>'personen/urlaubsverwaltung.php', 'target'=>'main'),
			'Resturlaub'=>array('name'=>'Urlaub', 'link'=>'personen/resturlaub_frameset.html', 'target'=>'main')
		),
		/*'Studenten'=>array
		(
			'name'=>'Studenten','permissions'=>array('admin','lv-plan','support'),
			'Übersicht'=>array('name'=>'Übersicht', 'link'=>'personen/studenten_uebersicht.php', 'target'=>'main'),
			//'Neu'=>array('name'=>'Neu', 'link'=>'personen/student_edit.php?new=1', 'target'=>'main'),
			//'Vorrückung'=>array('name'=>'Vorrückung', 'link'=>'personen/student_vorrueckung.php', 'target'=>'main'),
		),*/
		'Betriebsmittel'=>array('name'=>'Betriebsmittel', 'link'=>'stammdaten/betriebsmittel_frameset.php', 'target'=>'main','permissions'=>array('admin','lv-plan','support','basis/betriebsmittel')),
		'Preinteressenten'=>array('name'=>'Preinteressenten', 'link'=>'personen/preinteressent_frameset.html', 'target'=>'_blank','permissions'=>array('admin','lv-plan','support','preinteressent')),
		'Incoming'=>array('name'=>'Incoming', 'link'=>'personen/incoming_frameset.php', 'target'=>'_blank','permissions'=>array('inout/incoming')),
        'Outgoing'=>array('name'=>'Outgoing', 'link'=>'personen/outgoing_frameset.php', 'target'=>'_blank','permissions'=>array('inout/outgoing'))
	),
	'Stammdaten'=>	array
	(
		'name'=>'Stammdaten', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support','basis/berechtigung','basis/variable','basis/studiengang','basis/ort','basis/firma','basis/fhausweis'), 'image'=>'vilesci_stammdaten.png',
		'link'=>'left.php?categorie=Stammdaten', 'target'=>'nav',
		'Betriebsmittel'=>array('name'=>'Betriebsmittel', 'link'=>'stammdaten/betriebsmittel_frameset.php', 'target'=>'main','permissions'=>array('basis/betriebsmittel')),
		'Reihungstest'=>array('name'=>'Reihungstest', 'link'=>'stammdaten/reihungstestverwaltung.php', 'target'=>'main','permissions'=>array('admin','assistenz')),
		
		'User'=>array
		(
			'name'=>'User', 'permissions'=>array('basis/variable','basis/berechtigung'),
			'Berechtigungen'=>array('name'=>'Berechtigungen', 'link'=>'stammdaten/benutzerberechtigung_frameset.html', 'target'=>'main','permissions'=>array('basis/berechtigung')),
			'Variablen'=>array('name'=>'Variablen', 'link'=>'stammdaten/variablen_frameset.html', 'target'=>'main', 'target'=>'main','permissions'=>array('basis/variable')),
		),
		/*'Kommunikation'=>array
		(
			'name'=>'Kommunikation',
			'Kontakte'=>array('name'=>'Kontakte', 'link'=>'kommunikation/kontakt.php', 'target'=>'main'),
			'Mail-Verteiler'=>array('name'=>'Mail-Verteiler', 'link'=>'kommunikation/index.html', 'target'=>'main'),
		),*/
		'Studiengang'=>array('name'=>'Studiengang', 'link'=>'stammdaten/studiengang_frameset.html', 'target'=>'main','permissions'=>array('basis/studiengang')),
		'Ort'=>array('name'=>'Ort (Raum)', 'link'=>'stammdaten/raum_frameset.html', 'target'=>'main','permissions'=>array('basis/ort')),
		'Firmen'=>array('name'=>'Firmen', 'link'=>'stammdaten/firma_frameset.html', 'target'=>'main','permissions'=>array('basis/firma')),
		'Organisationseinheiten'=>array('name'=>'Organisationseinheiten', 'link'=>'stammdaten/organisationseinheiten.php', 'target'=>'main','permissions'=>array('basis/organisationseinheit')),
		'Statistik'=>array('name'=>'Statistik', 'link'=>'stammdaten/statistik_frameset.html', 'target'=>'main','permissions'=>array('basis/statistik')),
		'Ampel'=>array('name'=>'Ampel', 'link'=>'stammdaten/ampel_frameset.html', 'target'=>'main','permissions'=>array('basis/ampel')),
		'Infoscreen'=>array('name'=>'Infoscreen', 'link'=>'stammdaten/infoscreen_frameset.html', 'target'=>'main','permissions'=>array('basis/infoscreen')),
		'Ferien'=>array('name'=>'Ferien', 'link'=>'lehre/ferienverwaltung.php', 'target'=>'main','permissions'=>array('admin')),
		'Service'=>array('name'=>'Service', 'link'=>'stammdaten/service_frameset.html', 'target'=>'main','permissions'=>array('basis/service')),
		'FH Ausweis'=>array
		(
			'name'=>'FH Ausweis','permissions'=>array('basis/fhausweis'),
			'Profilfotocheck'=>array('name'=>'Profilfoto Check','link'=>'fhausweis/bildpruefung.php','target'=>'main'),
			'Kartenverwaltung'=>array('name'=>'Kartenverwaltung','link'=>'fhausweis/kartenverwaltung.php','target'=>'main'),
			'KarteZuweisen'=>array('name'=>'Karte zuweisen','link'=>'fhausweis/kartezuweisen.php','target'=>'main'),
			'Kartentausch'=>array('name'=>'Kartentausch','link'=>'fhausweis/kartentausch.php','target'=>'main'),
			'Verlaengerung'=>array('name'=>'Verlängerung','link'=>'fhausweis/verlaengerung.php','target'=>'main'),
			'Suche'=>array('name'=>'Suche','link'=>'fhausweis/search.php','target'=>'main'),
			'Syncronisation'=>array('name'=>'Syncronisation', 'link'=>'stammdaten/imexport/zutrittskarten/index.html', 'target'=>'main'),
			'Korrektur'=>array('name'=>'Kartenkorrektur','link'=>'fhausweis/kartenkorrektur.php','target'=>'main')
		)
	),
	'Wartung'=>	array
	(
		'name'=>'Wartung', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support','basis/firma'), 'image'=>'vilesci_wartung.png',
		'link'=>'left.php?categorie=Wartung', 'target'=>'nav',
		'Vorrueckung'=>	array
		(
			'name'=>'Vorrueckung', 'permissions'=>array('admin','lv-plan','support'),
			'Lehreinheiten'=>array('name'=>'Lehreinheiten', 'link'=>'lehre/lehreinheiten_vorrueckung.php', 'target'=>'main'),
			'Studenten'=>array('name'=>'Studenten', 'link'=>'personen/student_vorrueckung.php', 'target'=>'main')
		),
		'LVWartung'=>array('name'=>'LVwartung', 'link'=>'stammdaten/lv_wartung.php', 'target'=>'main'),
		'LehrfachWartung'=>array('name'=>'Lehrfachwartung', 'link'=>'lehre/lehrfach/wartung.php', 'target'=>'main'),
		'Lehrfachpflege'=>array('name'=>'Lehrfachpflege', 'link'=>'lehre/lehrfach/lehrfachpflege.php', 'target'=>'main'),
		'LehreinheitWartung'=>array('name'=>'Lehreinheitwartung', 'link'=>'stammdaten/le_wartung.php', 'target'=>'main'),
		'lvverteilung'=>array('name'=>'LVVerteilung', 'link'=>'lehre/lv_verteilung/lv_verteilung.php', 'target'=>'main'),
		'Kreuzerllistekopieren'=>array('name'=>'Kreuzerllisten kopieren', 'link'=>'https://cis.technikum-wien.at/cis/private/lehre/benotungstool/copy_uebung.php', 'target'=>'_blank'),
		'LDAPCheck'=>array('name'=>'LDAPCheck', 'link'=>'personen/ldap_check.php', 'target'=>'main'),
		'Firmenwartung'=>array('name'=>'Firmenwartung', 'link'=>'stammdaten/firma_zusammen_uebersicht.php', 'target'=>'main'),
		'checkStudenten'=>array('name'=>'CheckStudenten', 'link'=>'../system/checkStudenten.php', 'target'=>'main'),

	),
	'Auswertung'=>	array
	(
		'name'=>'Auswertung', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support','assistenz','wawi/inventar','basis/statistik'), 'image'=>'vilesci_statistik.png',
		'link'=>'left.php?categorie=Auswertung', 'target'=>'nav',
	/*
		'Raumauslastung'=>array('name'=>'Raumauslastung...', 'link'=>'lehre/raumauslastung.php', 'target'=>'main'),
		'Verplanungsuebersicht'=>array('name'=>'Verplanungsübersicht...', 'link'=>'lehre/check/verplanungsuebersicht.php', 'target'=>'main'),
		'Zeitwünsche'=>array('name'=>'Zeitwünsche', 'link'=>'lehre/zeitwuensche.php', 'target'=>'main'),
		'Institute'=>array('name'=>'Institute (+)', 'link'=>'personen/institutsliste.php', 'target'=>'main'),
		'Student/Semester'=>array('name'=>'Student/Semester', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/studentenprosemester.php', 'target'=>'main'),
		'ALVS-Statistik'=>array('name'=>'ALVS-Statistik', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/alvsstatistik.php', 'target'=>'main'),
		'LV-Planung Gesamt'=>array('name'=>'LV-Planung Gesamt (+)', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/lvplanunggesamtsj.php', 'target'=>'main'),
		'Bewerberstatistik'=>array('name'=>'Bewerberstatistik (+)', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/bewerberstatistik.php', 'target'=>'main'),
		'Abgängerstatistik'=>array('name'=>'Abgängerstatistik... (~)', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/abgaengerstatistik.php', 'target'=>'main'),
		'Absolventenstatistik'=>array('name'=>'Absolventenstatistik(~)', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/absolventenstatistik.php', 'target'=>'main'),
		'Absolventenzahlen'=>array('name'=>'Absolventenzahlen', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/absolventenzahlen.php', 'target'=>'main'),
		'Studentenstatistik'=>array('name'=>'Studentenstatistik (+)', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/studentenstatistik.php', 'target'=>'main'),
		'Lektorenstatistik'=>array('name'=>'Lektorenstatistik', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/lektorenstatistik.php', 'target'=>'main'),
		'Mitarbeiterstatistik'=>array('name'=>'Mitarbeiterstatistik', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/mitarbeiterstatistik.php', 'target'=>'main'),
		'Stromanalyse'=>array('name'=>'Stromanalyse...', 'link'=>'https://vilesci.technikum-wien.at/content/statistik/bama_stromanalyse.php', 'target'=>'main'),
	*/		
	),
	'Inventar'=>	array
	(
		'name'=>'Inventar', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','wawi','support','wawi/inventar'), 'image'=>'vilesci_inventar.png',
		'link'=>'left.php?categorie=Inventar', 'target'=>'nav',
		'Inventar'=>	array
		(
			'name'=>'Inventar', 'permissions'=>array('admin','wawi','support','wawi/inventar'),		
			'Neu'=>array('name'=>'Neu', 'link'=>'inventar/inventar_pflege.php?vorlage=false', 'target'=>'main'),
			'Suche'=>array('name'=>'Suche', 'link'=>'inventar/inventar.php', 'target'=>'main'),
			'AfA'=>array('name'=>'AfA', 'link'=>'inventar/inventar_afa.php', 'target'=>'main'),
			'Inventur'=>array('name'=>'Inventur', 'link'=>'inventar/inventar_inventur.php', 'target'=>'main'),
			'Etiketten'=>array('name'=>'Etiketten', 'link'=>'inventar/etiketten.php', 'target'=>'main'),
		)
	),	
	'Admin'=>	array
	(
		'name'=>'Admin', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support'), 'image'=>'vilesci_admin.png',
		'link'=>'left.php?categorie=Admin', 'target'=>'nav',
		'Tools'=>	array
		(
			'name'=>'Tools', 'permissions'=>array('admin'),
			'phpPgAdmin'=>array('name'=>'phpPgAdmin', 'link'=>'https://vilesci.technikum-wien.at/phppgadmin/index.php', 'target'=>'_blank'),
			'phpMyAdmin'=>array('name'=>'phpMyAdmin', 'link'=>'https://vilesci.technikum-wien.at/phpmyadmin/index.php', 'target'=>'_blank'),
			'SiPassDB'=>array('name'=>'SiPass Datenbank', 'link'=>'admin/sipassdb.php', 'target'=>'main'),
			'ServerTests'=>array('name'=>'Server-Tests', 'link'=>'admin/test/index.php', 'target'=>'main'),
			'htaccessGenerator'=>array('name'=>'.htaccess-Generator', 'link'=>'admin/htaccess/access.php', 'target'=>'main'),
		),
		'Hilfe'=>	array
		(
			'name'=>'Hilfe',
			'FAS-Installation'=>array('name'=>'FAS-Installation', 'link'=>'admin/fasinstall.html', 'target'=>'main'),
			'ViReferenz'=>array('name'=>'VI-Kurzreferenz', 'link'=>'admin/VI-Kurzreferenz.html', 'target'=>'main'),
		),
		'Cronjobs'=>array('name'=>'Cronjobs', 'link'=>'stammdaten/cronjobverwaltung.php', 'target'=>'main','permissions'=>array('basis/cronjob')),
	),
	'SD-Tools'=>	array
	(
		'name'=>'SD-Tools', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support'), 'image'=>'vilesci_sdtools.png',
		'link'=>'https://sdtools.technikum-wien.at', 'target'=>'_blank',
	)
	
);

require_once(dirname(__FILE__).'/../statistik.class.php');
$statistik = new statistik();
$statistik = $statistik->getMenueArray(1);
$menu['Auswertung']=array_merge($menu['Auswertung'],$statistik);
//var_dump($menu['Auswertung']);

require_once(dirname(__FILE__).'/../addon.class.php');
$addon_obj = new addon();
if($addon_obj->loadAddons())
{
	if(count($addon_obj->result)>0)
	{
		$menu['Addons']=array
		(
			'name'=>'Addons', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('basis/addon'), 'image'=>'vilesci_addons.png',
			'link'=>'left.php?categorie=Addons', 'target'=>'nav'
		);

		foreach($addon_obj->result as $row)
		{	
			$menu['Addons'][$row->kurzbz]=array('name'=>$row->addon_name, 'link'=>'../addons/'.$row->kurzbz.'/vilesci/index.php', 'target'=>'main');
		}
	}
}
?>
