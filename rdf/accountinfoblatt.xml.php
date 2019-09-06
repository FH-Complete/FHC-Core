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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */
/**
 * Erstellt das XML fuer das AccountInfoBlatt 
 */
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/benutzerberechtigung.class.php');

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else 
	die('UID muss uebergeben werden');

if(isset($_SERVER['REMOTE_USER']))
{
	// Wenn das Script direkt aufgerufen wird muss es ein Admin sein
	$user=get_uid();
	$berechtigung = new benutzerberechtigung();
	$berechtigung->getBerechtigungen($user);
	if(!$berechtigung->isBerechtigt('admin'))
		die('Sie haben keine Berechtigung fuer diese Seite');
}

$uid_arr = explode(";",$uid);
	
echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
echo '<accountinfoblaetter>';

$db = new basis_db();

foreach ($uid_arr as $uid)
{
	if($uid=='')
		continue;
		
	if(check_lektor($uid))
	{
		//Mitarbeiter
		$qry = "SELECT vorname, nachname, uid, gebdatum, aktivierungscode,alias FROM campus.vw_mitarbeiter WHERE uid=".$db->db_add_param($uid);
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$vorname = convertProblemChars($row->vorname);
				$vorname1 = $row->vorname;
				$nachname = convertProblemChars($row->nachname);
				$nachname1 = $row->nachname;
				$uid = $row->uid;
				$gebdatum = $row->gebdatum;
			}
			else 
				die("User nicht gefunden");
		}
		else 
			die("User nicht gefunden");
		
		$fileserver = 'fhe.'.DOMAIN;
		$studiengang='';
	}
	else 
	{
		//Student
		$qry ="SELECT vorname, nachname, matrikelnr, uid, tbl_studiengang.bezeichnung, tbl_studiengang.english, aktivierungscode, alias, tbl_studienordnung.studiengangbezeichnung, tbl_studienordnung.studiengangbezeichnung_englisch
		       FROM campus.vw_student
		       JOIN public.tbl_studiengang USING(studiengang_kz)
		       LEFT JOIN public.tbl_prestudentstatus USING(prestudent_id)
		       LEFT JOIN lehre.tbl_studienplan USING (studienplan_id)
			   LEFT JOIN lehre.tbl_studienordnung USING (studienordnung_id)
		       WHERE uid=".$db->db_add_param($uid)."
		       ORDER BY tbl_prestudentstatus.datum DESC LIMIT 1";

		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
			{
				$vorname = $row->vorname;
				$nachname = $row->nachname;
				$matrikelnr = $row->matrikelnr;
				$studiengang = $row->bezeichnung;
				$studiengang_eng = $row->english;
				$studiengangbezeichnung = $row->studiengangbezeichnung;
				$studiengangbezeichnung_englisch = $row->studiengangbezeichnung_englisch;
				$studiengang_bezeichnung = empty($studiengangbezeichnung) ? $studiengang : $studiengangbezeichnung;
				$studiengang_bezeichnung_englisch = empty($studiengangbezeichnung_englisch) ? $studiengang_eng : $studiengangbezeichnung_englisch;
				$uid = $row->uid;
			}
			else 
				die("User $uid nicht gefunden");
		}
		else
			die("User $uid nicht gefunden");		
		
		$fileserver = 'stud'.substr($matrikelnr,0,2).'.'.DOMAIN;
	}

	echo "\n		<infoblatt>";
	echo "\n			<name><![CDATA[".$vorname.' '.$nachname."]]></name>";
	echo "\n			<account><![CDATA[".$uid."]]></account>";
	echo "\n			<aktivierungscode><![CDATA[".$row->aktivierungscode."]]></aktivierungscode>";
	if($row->alias!='')
		echo "\n			<alias><![CDATA[".$row->alias.'@'.DOMAIN."]]></alias>";
	else
		echo "\n			<alias><![CDATA[]]></alias>";
	if($studiengang!='')
	{
		echo "\n			<bezeichnung><![CDATA[".$studiengang_bezeichnung."]]></bezeichnung>";
		echo "\n			<bezeichnung_english><![CDATA[".$studiengang_bezeichnung_englisch."]]></bezeichnung_english>";
	}
	echo "\n			<email><![CDATA[".$uid.'@'.DOMAIN."]]></email>";
	echo "\n			<fileserver><![CDATA[".$fileserver."]]></fileserver>";
	echo "\n			<logopath>".DOC_ROOT."skin/styles/".EXT_FKT_PATH."/</logopath>";
	echo "\n		</infoblatt>";
}
echo '</accountinfoblaetter>';
?>
