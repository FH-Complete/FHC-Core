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
	'Lehre'=> 		array
	(
		'name'=>'Lehre', 'opener'=>'true', 'hide'=>'false', 'permissions'=>array('admin','lv-plan','support', 'lehre'), 'image'=>'x-office-presentation.png',
		'link'=>'left.php?categorie=Lehre', 'target'=>'nav',
		'Gruppenverwaltung'=>array('name'=>'Gruppen', 'permissions'=>array('admin','lv-plan','support'), 'link'=>'stammdaten/lvbgruppenverwaltung.php', 'target'=>'main'),
		'Lehrveranstaltung'=>array('name'=>'Lehrveranstaltung', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
		'Lehrfach'=>array('name'=>'Lehrfach', 'link'=>'lehre/lehrfach.php', 'target'=>'main'),
		
		'Moodle'=>array
		(
			'name'=>'Moodle', 'permissions'=>array('admin','lv-plan','support'),
			'Kursverwaltung'=>array('name'=>'Kurs entfernen', 'link'=>'moodle/kurs_verwaltung.php', 'target'=>'main'),
			'Account'=>array('name'=>'Account', 'link'=>'moodle/account_verwaltung.php', 'target'=>'main'),
			'Zuteilung Verwalten'=>array('name'=>'Zuteilung Verwalten', 'link'=>'moodle/zuteilung_verwaltung.php', 'target'=>'main')			
		),
				
		
		
		'Freifach'=>array
		(
			'name'=>'Freifach', 'permissions'=>array('admin','lv-plan','support', 'lehre'),
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
			'LVPlanSync'=>array('name'=>'Sync', 'link'=>'../system/sync/index.html', 'target'=>'main'),
			'Ueberbuchungen'=>array('name'=>'Überbuchungen', 'link'=>'lehre/check/ueberbuchung.php', 'target'=>'main'),
			//'Studenten'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
			//'Insert'=>array('name'=>'Insert', 'link'=>'lehre/stdplan_insert.php', 'target'=>'main'),
			//'Delete'=>array('name'=>'Delete', 'link'=>'lehre/stdplan_delete.php', 'target'=>'main'),
			//'Import'=>array('name'=>'Import', 'link'=>'lehre/import/index.hml', 'target'=>'main'),
			//'Export'=>array('name'=>'Export', 'link'=>'lehre/export/index.html', 'target'=>'main')
		)
	),
	'Personen'=> 	array
	(
		'name'=>'Personen', 'opener'=>'true', 'hide'=>'true', 'image'=>'system-users.png',
		'link'=>'left.php?categorie=Personen', 'target'=>'nav',
		'Suche'=>array('name'=>'Suche', 'link'=>'personen/suche.php', 'target'=>'main','permissions'=>array('admin','lv-plan','support')),
		'Zusammenlegen'=>array('name'=>'Zusammenlegen', 'link'=>'stammdaten/personen_wartung.php', 'target'=>'main', 'permissions'=>array('admin','lv-plan','support')),
		'Gruppen'=>array
		(
			'name'=>'Gruppen', 'permissions'=>array('admin','lv-plan','support'),
			'Übersicht'=>array('name'=>'Übersicht', 'link'=>'lehre/einheit_menu.php', 'target'=>'main'),
			'Neu'=>array('name'=>'Neu', 'link'=>'lehre/einheit_menu.php?newFrm=true', 'target'=>'main')
		),
		'Benutzer'=>array
		(
			'name'=>'Benutzer','permissions'=>array('admin','lv-plan','support'),
			'Funktionen'=>array('name'=>'Funktionen', 'link'=>'personen/funktion.php', 'target'=>'main')
		),
		'Mitarbeiter'=>array
		(
			'name'=>'Mitarbeiter','permissions'=>array('admin','lv-plan','support'),
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
		'Betriebsmittel'=>array('name'=>'Betriebsmittel', 'link'=>'stammdaten/betriebsmittel_frameset.html', 'target'=>'main','permissions'=>array('admin','lv-plan','support')),
		'Preinteressenten'=>array('name'=>'Preinteressenten', 'link'=>'personen/preinteressent_frameset.html', 'target'=>'_blank','permissions'=>array('admin','lv-plan','support','preinteressent'))
	),
	'Stammdaten'=>	array
	(
		'name'=>'Stammdaten', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support','basis/berechtigung','basis/variable','basis/studiengang','basis/ort','basis/firma'), 'image'=>'folder.png',
		'link'=>'left.php?categorie=Stammdaten', 'target'=>'nav',
		'Betriebsmittel'=>array('name'=>'Betriebsmittel', 'link'=>'stammdaten/betriebsmittel_frameset.html', 'target'=>'main','permissions'=>array('admin')),
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
		'ImExport'=>array
		(
			'name'=>'ImExport','permissions'=>array('admin'),
			'Zutrittskarten'=>array('name'=>'Zutrittskarten', 'link'=>'stammdaten/imexport/zutrittskarten/index.html', 'target'=>'main')
		)
	),
	'Wartung'=>	array
	(
		'name'=>'Wartung', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support'), 'image'=>'edit-clear.png',
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
		

	),
	'Auswertung'=>	array
	(
		'name'=>'Auswertung', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support'), 'image'=>'statistic.png',
		'link'=>'left.php?categorie=Auswertung', 'target'=>'nav',
		'Raumauslastung'=>array('name'=>'Raumauslastung', 'link'=>'lehre/raumauslastung.php', 'target'=>'main'),
		'Verplanungsuebersicht'=>array('name'=>'Verplanungsübersicht', 'link'=>'lehre/check/verplanungsuebersicht.php', 'target'=>'main'),
		'Zeitwünsche'=>array('name'=>'Zeitwünsche', 'link'=>'lehre/zeitwuensche.php', 'target'=>'main'),
		'Institute'=>array('name'=>'Institute', 'link'=>'personen/institutsliste.php', 'target'=>'main'),
	),
	'Admin'=>	array
	(
		'name'=>'Admin', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support'), 'image'=>'emblem-system.png',
		'link'=>'left.php?categorie=Admin', 'target'=>'nav',
		'Tools'=>	array
		(
			'name'=>'Tools', 'permissions'=>array('admin'),
			'phpPgAdmin'=>array('name'=>'phpPgAdmin', 'link'=>'https://vilesci.technikum-wien.at/phppgadmin/index.php', 'target'=>'_blank'),
			'phpMyAdmin'=>array('name'=>'phpMyAdmin', 'link'=>'https://vilesci.technikum-wien.at/phpmyadmin/index.php', 'target'=>'_blank'),
			'SiPassDB'=>array('name'=>'SiPass Datenbank', 'link'=>'admin/sipassdb.php', 'target'=>'main'),
			'ServerTests'=>array('name'=>'Server-Tests', 'link'=>'admin/test/index.html', 'target'=>'main'),
			'htaccessGenerator'=>array('name'=>'.htaccess-Generator', 'link'=>'admin/htaccess/access.php', 'target'=>'main'),
		),
		'FAS-Installation'=>array('name'=>'FAS-Installation', 'link'=>'admin/fasinstall.html', 'target'=>'main'),
		'ViReferenz'=>array('name'=>'VI-Kurzreferenz', 'link'=>'admin/VI-Kurzreferenz.html', 'target'=>'main'),
	),
	'SD-Tools'=>	array
	(
		'name'=>'SD-Tools', 'opener'=>'true', 'hide'=>'true', 'permissions'=>array('admin','lv-plan','support'), 'image'=>'network-workgroup.png',
		'link'=>'https://sdtools.technikum-wien.at', 'target'=>'_blank',
	)
	
);
?>