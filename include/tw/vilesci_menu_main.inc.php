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
	'LVPlan'=> 		array
	(
		'name'=>'LV-Plan', 'opener'=>'true', 'hide'=>'false', 'permissions'=>array('admin','lv-plan'), 'image'=>'vilesci_lvplan.png',
		'link'=>'left.php?categorie=LVPlan', 'target'=>'nav',
		'Gruppenverwaltung'=>array('name'=>'Gruppenverwaltung', 'permissions'=>array('admin','lv-plan','support', 'lehre/gruppe'), 'link'=>'stammdaten/lvbgruppenverwaltung.php', 'target'=>'main'),
		'Lehrveranstaltung'=>array('name'=>'Lehrveranstaltung', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
		/* Leerzeile */'emptyrow1'=>array('name'=>'', 'link'=>'#empty', 'target'=>'main'),
		'Verplanungsuebersicht'=>array('name'=>'Verplanungsübersicht', 'link'=>'lehre/check/verplanungsuebersicht.php', 'target'=>'main'),
		'Reihungstest'=>array('name'=>'Reihungstest', 'link'=>'stammdaten/reihungstestverwaltung.php', 'target'=>'main','permissions'=>array('lehre/reihungstest')),
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
			'Übersicht'=>array('name'=>'Zeitwünsche', 'link'=>'personen/lektor_uebersicht.php', 'target'=>'main','permissions'=>array('mitarbeiter')),
			'Zeitsperren'=>array('name'=>'Zeitsperren', 'link'=>'personen/urlaubsverwaltung.php', 'target'=>'main','permissions'=>array('mitarbeiter/zeitsperre:begrenzt')),
		),

		'Vorrueckung'=>	array
		(
			'name'=>'Vorrueckung', 'permissions'=>array('lehre/vorrueckung','student/vorrueckung'),
			'Lehreinheiten'=>array('name'=>'Lehreinheiten', 'link'=>'lehre/lehreinheiten_vorrueckung.php', 'target'=>'main', 'permissions'=>array('lehre/vorrueckung'),),
			'Studenten'=>array('name'=>'Studenten', 'link'=>'personen/student_vorrueckung.php', 'target'=>'main','permissions'=>array('student/vorrueckung'),)
		),

	),
	'Lehre'=> 		array
	(
		'name'=>'Lehre', 'opener'=>'true', 'hide'=>'false', 'permissions'=>array('admin','lv-plan','support', 'lehre'), 'image'=>'vilesci_lehre.png',
		'link'=>'left.php?categorie=Lehre', 'target'=>'nav',
		'Gruppenverwaltung'=>array('name'=>'Gruppen', 'permissions'=>array('admin','lv-plan','support','lehre/gruppe'), 'link'=>'stammdaten/lvbgruppenverwaltung.php', 'target'=>'main'),
		'Lehrveranstaltung'=>array('name'=>'Lehrveranstaltung', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
		'lvTemplateUebersicht'=>array('name'=>'LV-Template Übersicht', 'link'=>'../index.ci.php/lehre/lvplanung/LvTemplateUebersicht', 'target'=>'_blank'),
		'Studienordnung'=>array('name'=>'Studienordnung', 'link'=>'lehre/studienordnung.php', 'target'=>'_blank','permissions'=>array('lehre/studienordnung')),
		'StudienplanGueltigkeit'=>array('name'=>'Studienplan Gültigkeit', 'link'=>'lehre/studienplan_gueltigkeit.php', 'target'=>'main','permissions'=>array('lehre/studienordnung')),
		'StudienplanVorruecken'=>array('name'=>'Studienplan vorrücken', 'link'=>'lehre/studienplan_vorrueckung.php', 'target'=>'main','permissions'=>array('lehre/studienplan')),

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
		),
		'Raummitteilung'=>array('name'=>'Raummitteilung', 'link'=>'lehre/raummitteilung.php', 'target'=>'main'),
	),
	'Personen'=> 	array
	(
		'name'=>'Personen', 'opener'=>'true', 'hide'=>'true', 'image'=>'vilesci_personen.png', 'permissions'=>array('admin','lv-plan','support','mitarbeiter','basis/person'),
		'link'=>'left.php?categorie=Personen', 'target'=>'nav',
		'Suche'=>array('name'=>'Suche', 'link'=>'personen/suche.php', 'target'=>'main','permissions'=>array('admin','lv-plan','support','basis/person')),
		'Zusammenlegen'=>array('name'=>'Zusammenlegen', 'link'=>'stammdaten/personen_wartung.php', 'target'=>'main', 'permissions'=>array('basis/person')),
		'Wiederholer'=>array('name'=>'Stg-Wiederholer', 'link'=>'personen/wiederholer.php', 'target'=>'main', 'permissions'=>array('basis/person')),
		'Gruppen'=>array
		(
			'name'=>'Gruppen', 'permissions'=>array('lehre/gruppe'),
			'Übersicht'=>array('name'=>'Übersicht', 'link'=>'lehre/einheit_menu.php', 'target'=>'main','permissions'=>array('lehre/gruppe')),
			'Neu'=>array('name'=>'Neu', 'link'=>'lehre/einheit_menu.php?newFrm=true', 'target'=>'main','permissions'=>array('lehre/gruppe'))
		),
		'Benutzer'=>array
		(
			'name'=>'Benutzer','permissions'=>array('admin','lv-plan','support','mitarbeiter'),
			'Funktionen'=>array('name'=>'Funktionen', 'link'=>'personen/funktion.php', 'target'=>'main', 'permissions'=>array('mitarbeiter')),
			'Berechtigungen'=>array('name'=>'Berechtigungen', 'link'=>'stammdaten/benutzerberechtigung_frameset.html', 'target'=>'main','permissions'=>array('basis/berechtigung')),
			'Rollen'=>array('name'=>'Rollen', 'link'=>'stammdaten/berechtigungrolle.php', 'target'=>'main','permissions'=>array('basis/berechtigung')),
			'Variablen'=>array('name'=>'Variablen', 'link'=>'stammdaten/variablen_frameset.html', 'target'=>'main', 'target'=>'main','permissions'=>array('basis/variable')),
		),
		'Mitarbeiter'=>array
		(
			'name'=>'Mitarbeiter','permissions'=>array('admin','mitarbeiter','support'),
			'Übersicht'=>array('name'=>'Übersicht', 'link'=>'personen/lektor_uebersicht.php', 'target'=>'main'),
			'Zeitsperren'=>array('name'=>'Zeitsperren/Urlaub', 'link'=>'personen/urlaubsverwaltung.php', 'target'=>'main','permissions'=>array('mitarbeiter/zeitsperre:begrenzt')),
			'Projektexport'=>array('name'=>'Projektexport', 'link'=>'personen/projektexport.php', 'target'=>'main','permissions'=>array('mitarbeiter/zeitsperre')),
		),
		'Betriebsmittel'=>array('name'=>'Betriebsmittel', 'link'=>'stammdaten/betriebsmittel_frameset.php', 'target'=>'main','permissions'=>array('basis/betriebsmittel')),
		'AnwesenheitslistenBarcode'=>array('name'=>'Anwesenheitslisten mit Barcodes', 'link'=>'personen/anwesenheitslisten_barcode.php', 'target'=>'main','permissions'=>array('basis/person')),
		'Preinteressenten'=>array('name'=>'Preinteressenten', 'link'=>'personen/preinteressent_frameset.html', 'target'=>'_blank','permissions'=>array('admin','preinteressent')),
		'Incoming'=>array('name'=>'Incoming', 'link'=>'personen/incoming_frameset.php', 'target'=>'_blank','permissions'=>array('inout/incoming')),
		'Outgoing'=>array('name'=>'Outgoing', 'link'=>'personen/outgoing_frameset.php', 'target'=>'_blank','permissions'=>array('inout/outgoing'))
	),
	'Stammdaten'=>	array
	(
		'name'=>'Stammdaten', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support','basis/berechtigung','basis/variable','basis/studiengang','basis/ort','basis/firma','basis/fhausweis','basis/infoscreen','basis/organisationseinheit'), 'image'=>'vilesci_stammdaten.png',
		'link'=>'left.php?categorie=Stammdaten', 'target'=>'nav',
		'Betriebsmittel'=>array('name'=>'Betriebsmittel', 'link'=>'stammdaten/betriebsmittel_frameset.php', 'target'=>'main','permissions'=>array('basis/betriebsmittel')),
		'Reihungstest'=>array('name'=>'Reihungstest', 'link'=>'stammdaten/reihungstestverwaltung.php', 'target'=>'main','permissions'=>array('lehre/reihungstest')),

		'Rechtemanagement'=>array
		(
			'name'=>'Rechtemanagement', 'permissions'=>array('basis/berechtigung','mitarbeiter'),
			'Berechtigungen'=>array('name'=>'Berechtigungen', 'link'=>'stammdaten/benutzerberechtigung_frameset.html', 'target'=>'main','permissions'=>array('basis/berechtigung')),
			'Rechte'=>array('name'=>'Rechte', 'link'=>'stammdaten/berechtigungen.php', 'target'=>'main','permissions'=>array('basis/berechtigung')),
			'Rollen'=>array('name'=>'Rollen', 'link'=>'stammdaten/berechtigungrolle.php', 'target'=>'main','permissions'=>array('basis/berechtigung')),
			'Funktionen'=>array('name'=>'Funktionen', 'link'=>'personen/funktion.php', 'target'=>'main', 'permissions'=>array('mitarbeiter')),
		),
		'Personal'=>array
		(
			'name'=>'Personal', 'permissions'=>array('buchung/typen','vertrag/typen'),
			'Buchungstypen'=>array('name'=>'Buchungstypen', 'link'=>'stammdaten/buchung_typ.php', 'target'=>'main','permissions'=>array('buchung/typen')),
			'Vertragstypen'=>array('name'=>'Vertragstypen', 'link'=>'stammdaten/vertrag_typ.php', 'target'=>'main','permissions'=>array('vertrag/typen'))
		),
		'Studiengang'=>array('name'=>'Studiengang', 'link'=>'stammdaten/studiengang_frameset.html', 'target'=>'main','permissions'=>array('basis/studiengang')),
		'Ort'=>array('name'=>'Ort (Raum)', 'link'=>'stammdaten/raum_frameset.html', 'target'=>'main','permissions'=>array('basis/ort')),
		'Firmen'=>array('name'=>'Firmen', 'link'=>'stammdaten/firma_frameset.html', 'target'=>'main','permissions'=>array('basis/firma')),
		'Organisationseinheiten'=>array('name'=>'Organisationseinheiten', 'link'=>'stammdaten/organisationseinheiten.php', 'target'=>'main','permissions'=>array('basis/organisationseinheit')),
		'Status'=>array('name'=>'Status', 'link'=>'../index.ci.php/crm/statusgrund', 'target'=>'main','permissions'=>array('admin')),
		'Statistik'=>array('name'=>'Statistik', 'link'=>'stammdaten/statistik_frameset.html', 'target'=>'main','permissions'=>array('basis/statistik')),
		'Ampel'=>array('name'=>'Ampel', 'link'=>'stammdaten/ampel_frameset.html', 'target'=>'main','permissions'=>array('basis/ampel')),
		'Infoscreen'=>array('name'=>'Infoscreen', 'link'=>'stammdaten/infoscreen_frameset.html', 'target'=>'main','permissions'=>array('basis/infoscreen')),
			'Ferien'=>array('name'=>'Ferien', 'link'=>'lehre/ferienverwaltung.php', 'target'=>'main','permissions'=>array('basis/ferien')),
		'Service'=>array('name'=>'Service', 'link'=>'stammdaten/service_frameset.html', 'target'=>'main','permissions'=>array('basis/service')),
		'Dokumentvorlagen'=>array('name'=>'Dokumentvorlagen', 'link'=>'stammdaten/dokumentvorlagen_verwaltung.php', 'target'=>'main','permissions'=>array('basis/dokumente')),
		'GSProgramm'=>array('name'=>'GSProgramm', 'link'=>'stammdaten/gsprogramm.php', 'target'=>'main','permissions'=>array('inout/uebersicht')),
		'Studiensemester'=>array('name'=>'Studiensemester', 'link'=>'../index.ci.php/organisation/studiensemester/listStudiensemester', 'target'=>'main','permissions'=>array('admin')),
		'Studienjahr'=>array('name'=>'Studienjahr', 'link'=>'../index.ci.php/organisation/studienjahr/listStudienjahr', 'target'=>'main','permissions'=>array('admin')),
		'FH Ausweis'=>array
		(
			'name'=>'FH Ausweis','permissions'=>array('basis/fhausweis'),
			'Profilfotocheck'=>array('name'=>'Profilfoto Check','link'=>'fhausweis/bildpruefung.php','target'=>'main'),
			'Kartenverwaltung'=>array('name'=>'Kartenverwaltung','link'=>'fhausweis/kartenverwaltung.php','target'=>'main'),
			'KarteZuweisen'=>array('name'=>'Karte zuweisen','link'=>'fhausweis/kartezuweisen.php','target'=>'main'),
			'Kartentausch'=>array('name'=>'Kartentausch','link'=>'fhausweis/kartentausch.php','target'=>'main'),
			'Kartenausgabe'=>array('name'=>'Kartenausgabe','link'=>'fhausweis/kartenausgabe.php','target'=>'main'),
			'Verlaengerung'=>array('name'=>'Verlängerung','link'=>'fhausweis/verlaengerung.php','target'=>'main'),
			'Suche'=>array('name'=>'Suche','link'=>'fhausweis/search.php','target'=>'main'),
			'Kartenruecknahme'=>array('name'=>'Kartenruecknahme','link'=>'fhausweis/kartenruecknahme.php','target'=>'main'),
		)
	),
	'Wartung'=>	array
	(
		'name'=>'Wartung', 'opener'=>'true', 'hide'=>'true', 'image'=>'vilesci_wartung.png', 'link'=>'left.php?categorie=Wartung', 'target'=>'nav',
		'permissions'=>array('basis/studiengang','lehre/vorrueckung','student/vorrueckung','admin','basis/firma','student/stammdaten','assistenz','lv-plan'),
		'DokumenteZuteilung'=>array('name'=>'Dokumente Zuteilung', 'link'=>'stammdaten/studiengang_dokumente.php', 'target'=>'main', 'permissions'=>array('basis/studiengang')),
		'Vorrueckung'=>	array
		(
			'name'=>'Vorrueckung', 'permissions'=>array('lehre/vorrueckung','student/vorrueckung'),
			'Lehreinheiten'=>array('name'=>'Lehreinheiten', 'link'=>'lehre/lehreinheiten_vorrueckung.php', 'target'=>'main','permissions'=>array('lehre/vorrueckung'),),
			'Studenten'=>array('name'=>'Studenten', 'link'=>'personen/student_vorrueckung.php', 'target'=>'main','permissions'=>array('student/vorrueckung'),)
		),
		'Firmenwartung'=>array('name'=>'Firmenwartung', 'link'=>'stammdaten/firma_zusammen_uebersicht.php', 'target'=>'main','permissions'=>array('basis/firma')),
		'checkStudenten'=>array('name'=>'CheckStudenten', 'link'=>'../system/checkStudenten.php', 'target'=>'main', 'permissions'=>array('student/stammdaten')),
		'StudienplanZuteilung'=>array('name'=>'Studienplan Zuteilung', 'link'=>'lehre/studienplan_zuteilung.php', 'target'=>'main','permissions'=>array('assistenz')),
		'lv_merge'=>array('name'=>'LVs zusammenlegen', 'link'=>'lehre/lv_merge.php', 'target'=>'main','permissions'=>array('lehre/lehrveranstaltung')),
		'akteupdate'=>array('name'=>'Akten überschreiben', 'link'=>'personen/akteupdate.php', 'target'=>'main','permissions'=>array('admin')),
		'gemeindeupdate'=>array('name'=>'Gemeinden aktualisieren', 'link'=>'stammdaten/set_gemeinde.php', 'target'=>'main','permissions'=>array('basis/gemeinde')),
	),
	'Auswertung'=>	array
	(
		'name'=>'Auswertung', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support','assistenz','wawi/inventar','basis/statistik'), 'image'=>'vilesci_statistik.png',
		'link'=>'left.php?categorie=Auswertung', 'target'=>'nav',
		'Administration'=>	array
		(
			'name'=>'Administration', 'permissions'=>array('basis/statistik'),
			'Statistik'=>array('name'=>'Statistik', 'link'=>'stammdaten/statistik_frameset.html', 'target'=>'main','permissions'=>array('basis/statistik')),
			'Filter'=>array('name'=>'Filter', 'link'=>'statistik/filter_frameset.html', 'target'=>'main','permissions'=>array('basis/statistik')),
		),
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
		'name'=>'Admin', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('basis/cronjob'), 'image'=>'vilesci_admin.png',
		'link'=>'left.php?categorie=Admin', 'target'=>'nav',
		'Cronjobs'=>array('name'=>'Cronjobs', 'link'=>'stammdaten/cronjobverwaltung.php', 'target'=>'main','permissions'=>array('basis/cronjob')),
		'Vorlagen'=>array('name'=>'Vorlagen', 'link'=>'../index.ci.php/system/Vorlage', 'target'=>'main','permissions'=>array('basis/cronjob')),
		'Phrasen'=>array('name'=>'Phrasen', 'link'=>'../index.ci.php/system/Phrases', 'target'=>'main','permissions'=>array('basis/cronjob'))
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
			if(file_exists('../addons/'.$row->kurzbz.'/vilesci/menu.inc.php'))
				include('../addons/'.$row->kurzbz.'/vilesci/menu.inc.php');
		}
	}
}
?>
