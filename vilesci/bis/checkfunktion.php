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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/bisfunktion.class.php');
require_once('../../include/studiengang.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('mitarbeiter/stammdaten',null,'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

$funktion_geaendert=0;
$funktion_hinzugefuegt=0;
$funktion_error=0;
$verwendung_not_found=0;
$verwendung_multiple=0;
$funktion_ohne_lehrauftrag=0;
$user = get_uid();
$wochen=BIS_SWS_WOCHEN;

$stg_arr = array();
$stg_obj = new studiengang();
$stg_obj->getAll(null, false);
$lastbismeldung = date('Y-m-d',mktime(0,0,0,9,1,date('Y')-1));
$aktbismeldung = date('Y-m-d',mktime(0,0,0,9,1,date('Y')));
foreach ($stg_obj->result as $stg)
{
	$stg_arr[$stg->studiengang_kz] = $stg->kuerzel;
}

$stsem = new studiensemester();
$stsemprev = $stsem->getPrevious();
$stsemprevprev = $stsem->getBeforePrevious();

echo '<html>
	<head>
	<title>Check Funktion</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	</head>
	<body class="Background_main">
	<h2>Mitarbeiter BIS-Funktion Check für '.$stsemprevprev.'/'.$stsemprev.'</h2>
	';

if(isset($_POST['action']) && $_POST['action'] == 'delete')
{
	$qry = "DELETE FROM bis.tbl_bisfunktion where (studiengang_kz, bisverwendung_id) in (SELECT studiengang_kz, bisverwendung_id FROM bis.tbl_bisfunktion JOIN bis.tbl_bisverwendung USING(bisverwendung_id)
		WHERE (mitarbeiter_uid, studiengang_kz) NOT IN (
			SELECT mitarbeiter_uid, studiengang_kz
			FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			(tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsemprevprev)." OR tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsemprev)."))
			AND (ende>".$db->db_add_param($lastbismeldung)." OR ende is null))";
	if($db->db_query($qry))
	{
		echo 'Falsche Funktionszuordnungen wurden entfernt';
	}
	else
	{
		echo 'Fehler beim Löschen der Zuordnungen';
	}
}
if(isset($_POST['action']) && $_POST['action'] == 'run')
{
	$qry =  "SELECT tbl_lehreinheitmitarbeiter.mitarbeiter_uid, tbl_lehrveranstaltung.studiengang_kz, sum(tbl_lehreinheitmitarbeiter.semesterstunden) as semstd
			FROM lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
			WHERE
			tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
			tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
			(studiensemester_kurzbz=".$db->db_add_param($stsemprev)." OR studiensemester_kurzbz=".$db->db_add_param($stsemprevprev).") AND
			bismelden=true AND tbl_lehreinheitmitarbeiter.semesterstunden>0 GROUP BY mitarbeiter_uid, studiengang_kz";

	if($result = $db->db_query($qry))
	{
		$lastuid='';
		while($row = $db->db_fetch_object($result))
		{
			if($lastuid!=$row->mitarbeiter_uid)
			{
				$lastuid=$row->mitarbeiter_uid;
				//Verwendung suchen
				$person_error=false;
				$qry_verw = "SELECT * FROM bis.tbl_bisverwendung
							WHERE
								(ende>now() OR ende is null OR ende>".$db->db_add_param($lastbismeldung).")
								AND (beginn<".$db->db_add_param($aktbismeldung)." OR beginn is null)
								AND mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid)."
								AND tbl_bisverwendung.beschausmasscode!=5
							ORDER BY beginn DESC";
				if($result_verw = $db->db_query($qry_verw))
				{
					if($db->db_num_rows($result_verw)==0)
					{
						echo "<br>Es wurde keine Verwendung fuer <b>$row->mitarbeiter_uid</b> gefunden";
						$person_error = true;
						$verwendung_not_found++;
					}
					else
					{
						if($row_verw = $db->db_fetch_object($result_verw))
							$verwendung_id = $row_verw->bisverwendung_id;
						else
						{
							echo "<br>Fehler beim Holen der Verwendung von $row->mitarbeiter_uid";
							$person_error = true;
						}
					}

					if($db->db_num_rows($result_verw)>1)
					{
						echo "<br>Es wurde mehr als eine Verwendung bei $row->mitarbeiter_uid gefunden - es wird die Verwendung $verwendung_id verwendet";
						$verwendung_multiple++;
					}
				}
				else
				{
					echo "<br>Fehler beim Ermitteln der Verwendung ".$db->db_last_error();
					$person_error = true;
				}
			}

			if(!$person_error)
			{
				//SWS berechnen
				$swsneu = number_format(round($row->semstd/$wochen, 2),2,'.','');

				//Funktion fuer diesen Studiengang suchen
				$bisfunktion = new bisfunktion();

				if($bisfunktion->load($verwendung_id, $row->studiengang_kz))
				{
					$bisfunktion->new = false;

					if($bisfunktion->sws!=$swsneu)
					{
						echo "<br>$row->mitarbeiter_uid: Funktion bei Studiengang ".$stg_arr[$row->studiengang_kz]." ($row->studiengang_kz) wird von $bisfunktion->sws auf $swsneu geaendert";
						$bisfunktion->sws = $swsneu;
						$funktion_geaendert++;
					}
				}
				else
				{
					$bisfunktion->insertamum = date('Y-m-d H:i:s');
					$bisfunktion->insertvon = $user;
					$bisfunktion->studiengang_kz = $row->studiengang_kz;
					$bisfunktion->sws = $swsneu;
					$bisfunktion->new = true;
					$bisfunktion->bisverwendung_id = $verwendung_id;
					$funktion_hinzugefuegt++;
				}
				$bisfunktion->updateamum = date('Y-m-d H:i:s');
				$bisfunktion->updatevon = $user;

				if(!$bisfunktion->save())
				{
					echo "<br>$row->mitarbeiter_uid: Fehler beim Anlegen der Funktion ".$bisfunktion->errormsg;
					if($bisfunktion->new)
						$funktion_hinzugefuegt--;
					else
						$funktion_geaendert--;
					$funktion_error++;
				}
			}
		}

		echo '<br><br>';
		echo '<b>Check fuer nicht benoetigte Funktionen</b>';
		$qry = "SELECT * FROM bis.tbl_bisfunktion JOIN bis.tbl_bisverwendung USING(bisverwendung_id)
				WHERE (mitarbeiter_uid, studiengang_kz) NOT IN (
					SELECT mitarbeiter_uid, studiengang_kz
					FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter
					WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
					(tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsemprev)." OR tbl_lehreinheit.studiensemester_kurzbz=".$db->db_add_param($stsemprevprev)."))
					AND (ende>".$db->db_add_param($lastbismeldung)." OR ende is null)
				ORDER BY mitarbeiter_uid, studiengang_kz";
		if($result = $db->db_query($qry))
		{
			$funktion_ohne_lehrauftrag = $db->db_num_rows($result);

			while($row = $db->db_fetch_object($result))
			{
				echo "<br><b>$row->mitarbeiter_uid</b> hat im Studiengang ".$stg_arr[$row->studiengang_kz]." ($row->studiengang_kz) eine Funktion ohne Lehrauftrag";
			}
		}
		echo '<br><br>Loeschen der Funktionen:
		<form method="POST" action="checkfunktion.php">
			<input type="hidden" name="action" value="delete" />
			<input type="submit" value="Falsche Funktionen jetzt löschen" />
		</form>';
		echo '<br><br>';
		echo '<h3>Uebersicht</h3>';
		echo '<table>';
		echo "<tr><td>Nicht vorhandene Verwendungen</td><td>$verwendung_not_found</td></tr>";
		echo "<tr><td>Mehrere moegliche Verwendungen vorhanden</td><td>$verwendung_multiple</td></tr>";
		echo "<tr><td>Fehler bei Funktionen</td><td>$funktion_error</td></tr>";
		echo "<tr><td>Funktionen ohne Lehrauftrag</td><td>$funktion_ohne_lehrauftrag</td></tr>";
		echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
		echo "<tr><td>Funktionen hinzugefuegt</td><td>$funktion_hinzugefuegt</td></tr>";
		echo "<tr><td>Funktionen geaendert</td><td>$funktion_geaendert</td></tr>";
		echo '</table>';
	}
}
else
{
	echo '
	<br>
	Diese Seite erstellt automatisch die Funktionen der Lektoren aus den Lehraufträgen.

	<form method="POST" action="checkfunktion.php">
		<input type="hidden" name="action" value="run" />
		<input type="submit" value="Funktionen generieren" />
	</form>';
}
