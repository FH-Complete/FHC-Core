<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *
 * Beschreibung:
 * Dieses Skript prueft die gesamte Systemumgebung und sollte nach jedem Update gestartet werden.
 * Geprueft wird: die Datenbank per "dbupdate_VERSION.php" auf aktualitaet, dabei werden fehlende Attribute angelegt.
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../version.php');
require_once('../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();
echo '<html>
<head>
	<title>CheckSystem</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
</head>
<body>';

if (php_sapi_name() != 'cli')
{
	$uid = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	if(!$rechte->isBerechtigt('admin'))
	{
		exit('Sie haben keine Berechtigung');
	}
}

echo '<H1>Systemcheck!</H1>';
echo '<H2>DB-Updates!</H2>';



echo '<div>';
	$dbupdStr = 'dbupdate_'.$fhcomplete_version.'.php';
	echo $dbupdStr . ' wird aufgerufen...';
echo '</div>';
echo '<div>';
	require_once($dbupdStr);
echo '</div>';


// ******** phrasenupdate ************/
echo '<H2>Phrasen-Updates!</H2>';

echo '<div>';
	echo 'phrasesupdate.php wird aufgerufen...';
echo '</div>';
echo '<div>';
	require_once('phrasesupdate.php');
echo '</div>';

// ******** filtersupdate ************/
echo '<H2>Filters time!</H2>';

echo '<div>';
	echo 'filtersupdate.php wird aufgerufen...';
echo '</div>';
echo '<div>';
	require_once('filtersupdate.php');
echo '</div>';


// ******** Berechtigungen Prüfen ************/
echo '<h2>Berechtigungen pruefen</h2>';
$neue=false;
$berechtigung_kurzbz=0;
$beschreibung=1;
$berechtigungen = array(
	array('admin','Super User Rechte'),
	array('assistenz','Assistenz'),
	array('student','Defaultberechtigung für Studierende'),
	array('basis/addon','Addons verwalten'),
	array('basis/ampel','Ampeln Administrieren'),
	array('basis/ampeluebersicht','Ampel Übersicht für Leiter'),
	array('basis/benutzer','API-Recht zur Benutzer-Auth'),
	array('basis/berechtigung','Berechtigungsverwaltung'),
	array('basis/betriebsmittel','Betriebsmittel'),
	array('basis/cms','CMS Administration'),
	array('basis/cms_review','CMS Review Berechtigung (nur für admin Reviewer! Normale Reviewer bekommen Benutzerfunktion review)'),
	array('basis/cms_sperrfreigabe','Berechtigung zum Freigeben von gesperrtem Content'),
	array('basis/cronjob','Cronjobverwaltung'),
	array('basis/dms','DMS Download'),
	array('basis/dmsAdmin','DMS-Kategorien editieren'),
	array('basis/fas','FAS Zugriff'),
	array('basis/ferien','Verwaltung der Ferien und Feiertage im System'),
	array('basis/fhausweis','Verwaltungstools für FH Ausweis – Kartentausch, Bildpruefung, Druck'),
	array('basis/firma','Firmenverwaltung'),
	array('basis/firma:begrenzt','Firmenverwaltung'),
	array('basis/geschaeftsjahr','Geschäftsjahr'),
	array('basis/infoscreen','Infoscreenverwaltung'),
	array('basis/konto','Kontenverwaltung'),
	array('basis/kostenstelle','Kostenstellenverwaltung'),
	array('basis/message','Nachrichten'),
	array('basis/moodle','basis/moodle'),
	array('basis/news','Newsverwaltung'),
	array('basis/notiz','Notizen'),
	array('basis/organisationseinheit','Organisationseinheiten Verwalten'),
	array('basis/orgform','Orgformen Verwalten'),
	array('basis/ort','Raum-/Ortverwaltung'),
	array('basis/person','Personen Zusammenlegen, Stg-Wiederholer anlegen, etc'),
	array('basis/planner','Planner Zugriff'),
	array('basis/service','Services Administrieren (SLAs)'),
	array('basis/statistik','Statistiken Administrieren'),
	array('basis/studiengang','Studiengangsverwaltung'),
	array('basis/tempus','Tempus zugriff'),
	array('basis/testtool','Administrationseite, Gebiete löschen/zurücksetzen'),
	array('basis/variable','Variablenverwaltung'),
	array('basis/vilesci','Grundrecht, um in VileSci irgendwelche Menüpunkte zu sehen'),
	array('basis/servicezeitaufzeichnung','Erlaubt Erfassung von servicebezogenen (Service, OE, Kunde) Daten in der Zeitaufzeichnung'),
	array('buchung/typen','Verwaltung von Buchungstypen'),
	array('buchung/mitarbeiter','Verwaltung von Buchungen fuer Mitarbeiter'),
	array('inout/incoming','Incomingverwaltung'),
	array('inout/outgoing','Outgoingverwaltung'),
	array('inout/uebersicht','Verbandsanzeige fuer Incoming/Outgoing im FAS'),
	array('lehre','Berechtigung fuer CIS-Seite'),
	array('lehre/abgabetool','Projektabgabetool, Studentenansicht'),
	array('lehre/abgabetool:download','Download von Projektarbeitsabgaben'),
	array('lehre/freifach','Freifachverwaltung'),
	array('lehre/lehrfach','Lehrfachverwaltung'),
	array('lehre/lehrfach:begrenzt','Lehrfachverwaltung - nur aktiv aenderbar, nur aktive LF werden angezeigt'),
	array('lehre/lehrveranstaltung','Lehrveranstaltungsverwaltung'),
	array('lehre/lehrveranstaltung:begrenzt','nur die Felder Lehre, Sort, Zeugnis, BA/DA, FBK und LVInfo dürfen geändert werden (eventuelle Aufteilung in einzelne Berechtigungen??)'),
	array('lehre/lvplan','Tempus'),
	array('lehre/lvinfo','LVInfo editieren'),
	array('lehre/pruefungsanmeldungAdmin','Erlaubt die Verwaltung der Prüfungsanmeldungen.'),
	array('lehre/pruefungsbeurteilung','Erlaubt dem Benutzer Beurteilungen zu Prüfungen einzutragen.'),
	array('lehre/pruefungsbeurteilungAdmin','Erlaubt dem Benutzer für alle Prüfungen Beurteilungen einzutragen.'),
	array('lehre/pruefungsterminAdmin','Recht für jeden Lektor eine Prüfung anzulegen'),
	array('lehre/pruefungsfenster','Erlaubt dem Benutzer Prüfungsfenster anzulegen.'),
	array('lehre/reihungstest','Reihungstestverwaltung'),
	array('lehre/reihungstestOeffentlich','Erlaubt das Veröffentlichen von Reihungstests (Sichtbarkeit für BewerberInnen)'),
	array('lehre/reihungstestOrt','Erlaubt die Zuteilung von Raeumen zu Reihungstests'),
	array('lehre/reihungstestAufsicht','RT-Aufsichtspersonen dürfen zB Tests freischalten, Personen hinzufügen, Antworten löschen'),
	array('lehre/reservierung','erweiterte Reservierung inkl. Lektorauswahl, Stg, Sem und Gruppe'),
	array('lehre/reservierung:begrenzt','normale Raumreservierung im CIS'),
	array('lehre/reservierungAdvanced','Unbegrenztes Einfügen von Reservierungen auch über bestehende'),
	array('lehre/studienordnung','Studienordnung'),
	array('lehre/studienordnungInaktiv','Studienordnung Inaktiv'),
	array('lehre/studienplan','Studienplan'),
	array('lehre/vorrueckung','Lehreinheitenvorrückung'),
	array('lehre/zgvpruefung','Berechtigung um ZGV Überprüfungen vorzunehmen'),
	array('lv-plan','Stundenplan'),
	array('lv-plan/gruppenentfernen','Erlaut das Entfernen von Gruppen aus LVPlan vom FAS aus'),
	array('lv-plan/lektorentfernen','Erlaut das Entfernen von Lektoren aus LVPlan vom FAS aus'),
	array('mitarbeiter','FAS Mitarbeitermodul'),
	array('mitarbeiter/bankdaten','Bankdaten für Mitarbeiter und Studierende anzeigen'),
	array('mitarbeiter/personalnummer','Editieren der Personalnummer im FAS'),
	array('mitarbeiter/stammdaten','Stammdaten der Mitarbeiter'),
	array('mitarbeiter/urlaube','Mit diesem Recht werden im CIS die Urlaube von allen Mitarbeiter sichtbar'),
	array('mitarbeiter/zeitsperre','Zeitsperren- und Urlaubsverwaltung'),
	array('news','News eintragen'),
	array('planner','Planner Verwaltung'),
	array('preinteressent','Verwaltung der Preinteressenten'),
	array('raumres','Raumreservierung'),
	array('reihungstest','Recht für Anzeige des Reihungstests im Vilesci'),
	array('sdTools','Recht für Anzeige der SD-Tools im Vilesci'),
	array('soap/lv','Recht für LV Webservice'),
	array('soap/lvplan','Recht für LV-Plan Webservice'),
	array('soap/mitarbeiter','Recht für Mitarbeiter-Webservice'),
	array('soap/ort','Recht für Ort Webservice'),
	array('soap/pruefungsfenster','Recht für Pruefungsfenster Webservice'),
	array('soap/student','Recht für Student Webservice'),
	array('soap/studienordnung','Recht für Studienordnung Webservice'),
	array('soap/benutzer','Berechtigung für Bentutzerabfrage Addon Kontoimport'),
	array('soap/buchungen','Berechtigung für Buchungsabfrage Addon Kontoimport'),
	array('student/alias','Berechtigung zum Aendern von Alias falls deaktiviert 	'),
	array('student/bankdaten','Bankdaten des Studenten'),
	array('student/anrechnung','Anrechnungen des Studenten'),
	array('student/anwesenheit','Anwesenheiten im FAS'),
	array('student/dokumente','Wenn SUID dann dürfen Dokumente auch wieder entfernt werden'),
	array('student/editBakkZgv','Bearbeiten der Bachelor ZGV eines PreStudenten'),
	array('student/noten','Notenverwaltung'),
	array('student/stammdaten','Stammdaten der Studenten'),
	array('student/vorrueckung','Studentenvorrückung'),
	array('student/zahlungAdmin','Zahlungsadministration'),
	array('system/developer','Anzeige zusätzlicher Developerinfos'),
	array('system/loginasuser','Berechtigung zum Einloggen als anderer User'),
	array('system/phrase','Bearbeiten von Textphrasen'),
	array('system/vorlage','Erstellen und Bearbeiten von Vorlagen'),
	array('system/vorlagestudiengang','Bearbeiten der Texte zu den Vorlagen'),
	array('user','Normale User ohne besonere Rechte'),
	array('veranstaltung','Berechtigungen fuer Veranstaltungen wie Jahresplan'),
	array('vertrag/mitarbeiter','Verwalten von Vertraegen'),
	array('vertrag/typen','Verwalten von Vertragstypen'),
	array('wawi/berichte','Alle Berichte anzeigen'),
	array('wawi/bestellung','Bestellungen verwalten'),
	array('wawi/bestellung_advanced','Bestellungen editieren nach dem Abschicken'),
	array('wawi/budget','Budgeteingabe'),
	array('wawi/delete_advanced','Loeschen von freigegebenen Bestellungen'),
	array('wawi/firma','Firmenverwaltung abgespeckt'),
	array('wawi/freigabe','Bestellungen freigeben, entweder oe_kurzbz oder kostenstelle_id muss gesetzt sein'),
	array('wawi/freigabe_advanced','Berechtigung zum Freigeben von ALLEN Bestellungen'),
	array('wawi/inventar','Inventar Administration'),
	array('wawi/inventar:begrenzt','Inventarverwaltung'),
	array('wawi/konto','Kontoverwaltung'),
	array('wawi/kostenstelle','Kostenstellenverwaltung'),
	array('wawi/rechnung','Rechnungen verwalten'),
	array('wawi/rechnung_freigeben','Rechnungen Freigeben (bei Gutschriften)'),
	array('wawi/rechnung_transfer','Rechnungen - Eintragen des TransferDatums'),
	array('wawi/storno','Bestellung stornieren')
);

foreach($berechtigungen as $row)
{
	$qry = "SELECT * FROM system.tbl_berechtigung
			WHERE berechtigung_kurzbz=".$db->db_add_param($row[$berechtigung_kurzbz]);

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)==0)
		{
			// Nicht vorhanden -> anlegen
			$qry_insert="INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz, beschreibung) VALUES(".
				$db->db_add_param($row[$berechtigung_kurzbz]).','.
				$db->db_add_param($row[$beschreibung]).');';

			if($db->db_query($qry_insert))
			{
				echo '<br>'.$row[$berechtigung_kurzbz].' -> '.$row[$beschreibung].' <b>hinzugefügt</b>';
				$neue=true;
			}
			else
				echo '<br><span class="error">Fehler: '.$row[$berechtigung_kurzbz].' -> '.$row[$beschreibung].' hinzufügen nicht möglich</span>';

			//Wenn das Recht basis/vilesci neu angelegt wurde, dann dieses Recht jedem geben, der bisher auch Zugriff auf Vilesci hatte.
			if ($row[$berechtigung_kurzbz]=='basis/vilesci')
			{
				$qry_userrecht="SELECT DISTINCT uid, funktion_kurzbz
								FROM system.tbl_benutzerrolle
								LEFT JOIN public.tbl_benutzer USING (uid)
								WHERE berechtigung_kurzbz IN ('admin','support','preinteressent','lehre','basis/statistik','basis/fhausweis','wawi/inventar','assistenz','lv-plan')
								AND (tbl_benutzerrolle.ende>=now() OR tbl_benutzerrolle.ende IS NULL)
								AND (tbl_benutzerrolle.start<=now() OR tbl_benutzerrolle.start IS NULL)
								AND (tbl_benutzer.aktiv=true OR tbl_benutzerrolle.uid IS NULL)
								UNION
								SELECT DISTINCT uid, funktion_kurzbz
								FROM system.tbl_benutzerrolle
								JOIN system.tbl_rolleberechtigung USING(rolle_kurzbz)
								LEFT JOIN public.tbl_benutzer USING (uid)
								WHERE tbl_rolleberechtigung.berechtigung_kurzbz IN ('admin','support','preinteressent','lehre','basis/statistik','basis/fhausweis','wawi/inventar','assistenz','lv-plan')
								AND (tbl_benutzerrolle.ende>=now() OR tbl_benutzerrolle.ende IS NULL)
								AND (tbl_benutzerrolle.start<=now() OR tbl_benutzerrolle.start IS NULL)
								AND (tbl_benutzer.aktiv=true OR tbl_benutzerrolle.uid IS NULL) ORDER BY uid";

				if($result_insert_userrecht = $db->db_query($qry_userrecht))
				{
					while ($row_user=$db->db_fetch_object($result_insert_userrecht))
					{
						$qry_insert_userrecht="	INSERT INTO system.tbl_benutzerrolle (rolle_kurzbz, berechtigung_kurzbz, uid, funktion_kurzbz, oe_kurzbz, art, studiensemester_kurzbz, start, ende, negativ, updateamum, updatevon, insertamum, insertvon, kostenstelle_id)
												VALUES (NULL, 'basis/vilesci', ".($row_user->funktion_kurzbz!=""?"NULL,".$db->db_add_param($row_user->funktion_kurzbz):$db->db_add_param($row_user->uid).",NULL").", NULL, 's', NULL, NULL, NULL, FALSE, NULL, NULL, now(), 'checksystem', NULL)";

						if($db->db_query($qry_insert_userrecht))
							echo '<br>Recht "basis/vilesci" an '.$row_user->uid.' '.($row_user->funktion_kurzbz!=''?'Funktion '.$row_user->funktion_kurzbz:'').' vergeben';
						else
							echo '<br><span class="error">Fehler: Recht "basis/vilesci" konnte nicht an '.$row_user->uid.' '.($row_user->funktion_kurzbz!=''?'Funktion '.$row_user->funktion_kurzbz:'').' vergeben werden</span>';
					}
				}

			}
		}
	}
}
if($neue==false)
	echo '<br>Keine neuen Berechtigungen';

// ******** Pruefen ob die Webservice Berechtigungen alle gesetzt sind **********

echo '<h2>Webservice Berechtigungen pruefen</h2>';

// berechtigung_kurzbz,methode,klasse
$neue=false;
$berechtigung_kurzbz=0;
$methode=1;
$klasse=2;
$webservicerecht = array(
	array('soap/studienordnung','load_lva_oe','lehrveranstaltung'),
	array('soap/studienordnung','load','lehrveranstaltung'),
	array('soap/studienordnung','deleteStudienplanLehrveranstaltung','studienplan'),
	array('soap/studienordnung','containsLehrveranstaltung','studienplan'),
	array('soap/studienordnung','loadStudienplanLehrveranstaltung','studienplan'),
	array('soap/studienordnung','saveStudienplanLehrveranstaltung','studienplan'),
	array('soap/studienordnung','loadStudienordnung','studienordnung'),
	array('soap/studienordnung','delete','lvregel'),
	array('soap/studienordnung','save','lvregel'),
	array('soap/studienordnung','load','lvregel'),
	array('soap/studienordnung','loadLVRegelTypen','lvregel'),
	array('soap/studienordnung','load_lva','lehrveranstaltung'),
	array('soap/studienordnung','getAll','lehrtyp'),
	array('soap/studienordnung','getAll','organisationseinheit'),
	array('soap/studienordnung','getLVRegelTree','lvregel'),
	array('soap/studienordnung','save','studienplan'),
	array('soap/studienordnung','save','studienordnung'),
	array('soap/studienordnung','loadStudienplanSTO','studienplan'),
	array('soap/studienordnung','loadStudienordnungSTG','studienordnung'),
	array('soap/studienordnung','loadStudienordnungSTGInaktiv','studienordnung'),
	array('soap/studienordnung','loadStudienplan','studienplan'),
	array('soap/studienordnung','saveSemesterZuordnung','studienordnung'),
	array('soap/studienordnung','deleteSemesterZuordnung','studienordnung'),
	array('soap/studienordnung','getLVkompatibel','lehrveranstaltung'),
	array('soap/studienordnung','getLvTree','lehrveranstaltung'),
	array('soap/pruefungsfenster','getByStudiensemester','pruefungsfenster'),
	array('soap/studienordnung','exists','lvregel'),
	array('soap/studienordnung','saveSortierung','studienplan'),
	array('soap/benutzer','search','benutzer'),
	array('soap/buchungen','getBuchungen','konto')
);

foreach($webservicerecht as $row)
{
	$qry = "SELECT * FROM system.tbl_webservicerecht
			WHERE berechtigung_kurzbz=".$db->db_add_param($row[$berechtigung_kurzbz])."
			AND methode=".$db->db_add_param($row[$methode])."
			AND klasse=".$db->db_add_param($row[$klasse]);

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)==0)
		{
			// Nicht vorhanden -> anlegen
			$qry_insert="INSERT INTO system.tbl_webservicerecht (berechtigung_kurzbz, methode, insertamum, insertvon, klasse) VALUES(".
				$db->db_add_param($row[$berechtigung_kurzbz]).','.
				$db->db_add_param($row[$methode]).','.
				"now(),'checksystem',".
				$db->db_add_param($row[$klasse]).');';

			if($db->db_query($qry_insert))
			{
				echo '<br>'.$row[$berechtigung_kurzbz].'/'.$row[$methode].'->'.$row[$klasse].' hinzugefügt';
				$neue=true;
			}
			else
				echo '<br><span class="error">Fehler: '.$row[$berechtigung_kurzbz].'/'.$row[$methode].'->'.$row[$klasse].' hinzufügen nicht möglich</span>';
		}
	}
}
if($neue==false)
	echo '<br>Keine neuen Webservicerechte';

echo '</body></html>';
?>
