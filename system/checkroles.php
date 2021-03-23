<?php
/* Copyright (C) 2016 fhcomplete.org
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
	<title>CheckRoles</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css" />
</head>
<body>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(! $rechte->isBerechtigt('admin'))
	exit('Sie ('.$uid.') haben keine Berechtigung');

echo '<H2>Check and set roles!</H2>';

$neue=false;
$data = array
(
	array
	(
		'rolle_kurzbz' => 'admin',
		'berechtigung' => array
		(
			'admin', 'assistenz', 'basis/addon', 'basis/ampel', 'basis/ampeluebersicht', 'basis/benutzer', 'basis/berechtigung', 'basis/betriebsmittel', 'basis/cms', 'basis/cms_review', 'basis/cms_sperrfreigabe', 'basis/cronjob', 'basis/dms', 'basis/fas', 'basis/ferien', 'basis/fhausweis','basis/firma', 'basis/infoscreen', 'basis/moodle', 'basis/moodle','basis/news', 'basis/notiz', 'basis/organisationseinheit', 'basis/ort', 'basis/orgform', 'basis/person', 'basis/planner', 'basis/service', 'basis/statistik', 'basis/studiengang', 'basis/tempus', 'basis/testtool', 'basis/variable', 'basis/vilesci', 'buchung/typen', 'buchung/mitarbeiter', 'inout/incoming', 'inout/outgoing', 'inout/uebersicht', 'lehre', 'lehre/abgabetool', 'lehre/freifach', 'lehre/lehrfach', 'lehre/lehrveranstaltung', 'lehre/lvplan', 'lehre/lvinfo', 'lehre/pruefungsanmeldungAdmin', 'lehre/pruefungsbeurteilung', 'lehre/pruefungsbeurteilungAdmin', 'lehre/pruefungsterminAdmin', 'lehre/pruefungsfenster', 'lehre/reihungstest', 'lehre/reservierung', 'lehre/studienordnung', 'lehre/studienordnungInaktiv', 'lehre/studienplan', 'lehre/vorrueckung', 'lehre/zgvpruefung', 'lv-plan', 'lv-plan/gruppenentfernen', 'lv-plan/lektorentfernen', 'mitarbeiter', 'mitarbeiter/bankdaten', 'mitarbeiter/personalnummer', 'mitarbeiter/stammdaten', 'mitarbeiter/urlaube', 'mitarbeiter/zeitsperre', 'news', 'planner', 'preinteressent', 'raumres', 'reihungstest', 'sdTools', 'soap/lv', 'soap/lvplan', 'soap/mitarbeiter', 'soap/ort', 'soap/pruefungsfenster', 'soap/student', 'soap/studienordnung', 'soap/benutzer', 'soap/buchungen', 'student/bankdaten', 'student/anrechnung', 'student/anwesenheit', 'student/dokumente', 'student/noten', 'system/phrase', 'system/vorlage', 'system/vorlagestudiengang', 'student/stammdaten', 'student/vorrueckung', 'system/developer', 'system/loginasuser', 'user', 'veranstaltung', 'vertrag/mitarbeiter', 'vertrag/typen', 'wawi/berichte', 'wawi/bestellung', 'wawi/bestellung_advanced', 'wawi/budget', 'wawi/delete_advanced', 'wawi/firma', 'wawi/freigabe', 'wawi/freigabe_advanced', 'wawi/inventar', 'wawi/konto', 'wawi/kostenstelle', 'wawi/rechnung', 'wawi/rechnung_freigeben', 'wawi/rechnung_transfer', 'wawi/storno'
		)
	)
);

foreach ($data as $rb)
	foreach ($rb['berechtigung'] as $b)
	{
		$qry = 'SELECT * FROM system.tbl_rolleberechtigung
				WHERE rolle_kurzbz='.$db->db_add_param($rb['rolle_kurzbz']).' 
				AND berechtigung_kurzbz='.$db->db_add_param($b);

		if($result = $db->db_query($qry))
		{
			if($db->db_num_rows($result)==0)
			{
				// Nicht vorhanden -> anlegen
				$qry_insert="INSERT INTO system.tbl_rolleberechtigung (rolle_kurzbz, berechtigung_kurzbz, art) VALUES(".
					$db->db_add_param($rb['rolle_kurzbz']).','.
					$db->db_add_param($b).", 'suid');";

				if($db->db_query($qry_insert))
				{
					echo '<br>'.$rb['rolle_kurzbz'].' -> '.$b.' <b>hinzugefügt</b>';
					$neue=true;
				}
				else
					echo '<br><span class="error">Fehler: '.$rb['rolle_kurzbz'].' -> '.$b.' hinzufügen nicht möglich</span>';
			}
		}
	}

if($neue==false)
	echo '<br>Keine neuen Berechtigungen fuer eine Rolle!';

echo '</body></html>';
?>
