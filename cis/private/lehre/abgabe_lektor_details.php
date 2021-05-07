<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*******************************************************************************************************
 *				abgabe_lektor
 * 		abgabe_lektor ist die Lektorenmaske des Abgabesystems
 * 			fuer Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/projektbetreuer.class.php');
require_once('../../../include/sancho.inc.php');

if (!$db = new basis_db())
	$db=false;
$sprache = getSprache();
$p = new phrasen($sprache);

$fixtermin=false;

if(!isset($_POST['uid']))
{
	$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
	$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
	$titel = (isset($_GET['titel'])?$_GET['titel']:'-1');
	$betreuerart = (isset($_GET['betreuerart'])?$_GET['betreuerart']:'-1');

	$command = '';
	$paabgabe_id = '';
	$fixtermin = false;
	$datum = '';
	$kurzbz = '';
}
else
{
	$uid = (isset($_POST['uid'])?$_POST['uid']:'-1');
	$projektarbeit_id = (isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
	$titel = (isset($_POST['titel'])?$_POST['titel']:'');
	$command = (isset($_POST['command'])?$_POST['command']:'-1');
	$paabgabe_id = (isset($_POST['paabgabe_id'])?$_POST['paabgabe_id']:'-1');
	$fixtermin = (isset($_POST['fixtermin'])?1:0);
	$datum = (isset($_POST['datum'])?$_POST['datum']:'');
	$kurzbz = (isset($_POST['kurzbz'])?htmlspecialchars_decode($_POST['kurzbz']):'');
	$paabgabetyp_kurzbz = (isset($_POST['paabgabetyp_kurzbz'])?$_POST['paabgabetyp_kurzbz']:'');
	$betreuerart = (isset($_POST['betreuerart'])?$_POST['betreuerart']:'-1');
}

$user = get_uid();
$datum_obj = new datum();
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if (isset($_GET['user']))
{
	if ($rechte->isBerechtigt('admin',null,'suid'))
		$user = $_GET['user'];
}

$htmlstr='';

if(!check_lektor($user))
{
	die("Sie haben keine Berechtigung fuer diese Seite");
}
$datum = $datum_obj->formatDatum($datum, $format='Y-m-d');
if($uid==-1 && $projektarbeit_id==-1&& $titel==-1)
{
	//echo "Fehler bei der Daten&uuml;bergabe";
	exit;
}

if(isset($_GET['id']) && isset($_GET['uid']))
{
	if(!is_numeric($_GET['id']) || $_GET['id']=='')
		die('Fehler bei Parameteruebergabe');

	$file = $_GET['id'].'_'.$_GET['uid'].'.pdf';
	$filename = PAABGABE_PATH.$file;
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="'.$file.'"');
	readfile($filename);
	exit;
}

$projektarbeit_obj = new projektarbeit();
if($projektarbeit_id==-1)
	exit;

if(!$projektarbeit_obj->load($projektarbeit_id))
	die('Fehler beim Laden der Projektarbeit');
$titel = $projektarbeit_obj->titel;
$student_uid = $projektarbeit_obj->student_uid;

// paarbeit sollte nur ab SS2021 online bewertet werden
$qry_sem="SELECT 1
		FROM lehre.tbl_projektarbeit
    	JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
		WHERE projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER)."
		AND tbl_studiensemester.start::date >= (SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz = 'SS2021')::date
		LIMIT 1";
$result_sem=$db->db_query($qry_sem);
$num_rows_sem = $db->db_num_rows($result_sem);
if($num_rows_sem < 0)
{
	echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerAktualitaetProjektarbeit')."</font><br>&nbsp;";
}

// Zweitbegutachter holen
if($betreuerart=="Erstbegutachter")
{
	$projektbetreuer = new projektbetreuer();
	$alleBegutachter = $projektbetreuer->getProjektbetreuer($projektarbeit_id);

	if ($alleBegutachter)
	{
		$alleBegutachterResults = $projektbetreuer->result;

		foreach ($alleBegutachterResults as $begutachter)
		{
			if ($begutachter->betreuerart_kurzbz == "Erstbegutachter")
			{
				$erstbetreuer_id = $begutachter->person_id;
				$zweitbegutachter = $projektbetreuer->getZweitbegutachterWithToken($erstbetreuer_id, $projektarbeit_id, $student_uid);
				break;
			}
		}

		// Mail mit Token an Zweitbegutachter senden
		if ($zweitbegutachter && $num_rows_sem >= 1 && isset($_GET['zweitbegutachtertoken']))
		{
			$qry_std="SELECT * FROM campus.vw_benutzer where uid=".$db->db_add_param($uid);
			if(!$result_std=$db->db_query($qry_std))
			{
				echo "<font color=\"#FF0000\">Student konnte nicht gefunden werden!</font><br>&nbsp;";
			}
			else
			{
				$row_std=@$db->db_fetch_object($result_std);
				$mailres = sendZweitbegutachterMail($zweitbegutachter, $erstbetreuer_id, $row_std);

				if ($mailres)
				{
					echo "<br><span style='color: green; '>".$p->t('abgabetool/zweitbegutachterMailGesendet', $zweitbegutachter->email)."</span><br>&nbsp;";
				}
				else
				{
					echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerMailZweitBegutachter')." Mail: ".$zweitbegutachter->email."</font><br>&nbsp;";
				}
			}
		}
	}
}

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>'.$p->t('abgabetool/abgabetool').'</title>
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<style>
			#beurteilungheadertable td {
				height: 30px;
				overflow: hidden;
			}

			#beurteilungheadertable form {
				margin: 0;
			}

			/* Bild statt submit button, styling entfernen*/
			button[name="zweitbegutachtertoken"] {
				background: none;
				color: inherit;
				border: none;
				font: inherit;
				cursor: pointer;
				outline: inherit;
			}
			#zweitbetrmailicon {
				top: 4px;
				height: 18px;
				width: 18px;
				position: relative;
			}
			#tokenmailicon {
				top: 2px;
				height: 15px;
				width: 15px;
				position: relative;
			}

		</style>

		<script language="Javascript">
			function confdel()
			{
				return confirm("'.$p->t('global/warnungWirklichLoeschen').'");
			}
		</script>
	</head>
<body>
<h3>'.$p->t('abgabetool/abgabeLektorenbereich').'</h3>';

// Speichern eines Termines
if(isset($_POST["schick"]))
{
	if($datum)
	{
		$qry_std="SELECT * FROM campus.vw_benutzer where uid=".$db->db_add_param($uid);
		if(!$result_std=$db->db_query($qry_std))
		{
			echo "<font color=\"#FF0000\">Student konnte nicht gefunden werden!</font><br>&nbsp;";
		}
		else
		{
			$row_std=@$db->db_fetch_object($result_std);
			if($command=='insert')
			{
				$qrychk="SELECT * FROM campus.tbl_paabgabe
				WHERE
					projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER)."
					AND paabgabetyp_kurzbz=".$db->db_add_param($paabgabetyp_kurzbz)."
					AND fixtermin=".($fixtermin==1?'true':'false')."
					AND datum=".$db->db_add_param($datum)."
					AND kurzbz=".$db->db_add_param($kurzbz);

				if($result=$db->db_query($qrychk))
				{
					if($db->db_num_rows($result)>0)
					{
						//Datensatz bereits vorhanden
					}
					else
					{
						//neuer Termin
						$qry="INSERT INTO campus.tbl_paabgabe (projektarbeit_id, paabgabetyp_kurzbz,
							fixtermin, datum, kurzbz, abgabedatum, insertvon, insertamum, updatevon, updateamum)
							VALUES (".$db->db_add_param($projektarbeit_id, FHC_INTEGER).", ".
							$db->db_add_param($paabgabetyp_kurzbz).", ".
							($fixtermin==1?'true':'false').", ".
							$db->db_add_param($datum).", ".
							$db->db_add_param($kurzbz).", NULL, ".
							$db->db_add_param($user).", now(), NULL, NULL)";

						if(!$result=$db->db_query($qry))
						{
							echo "<span class=\"error\">".$p->t('global/fehleraufgetreten')."</span><br>&nbsp;";
						}
						else
						{
							$qry_typ="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz=".$db->db_add_param($paabgabetyp_kurzbz);
							if($result_typ=$db->db_query($qry_typ))
							{
								$row_typ=@$db->db_fetch_object($result_typ);
							}
							else
							{
								$row_typ = new stdClass();
								$row_typ->bezeichnung='';
							}
							$mail = new mail($uid."@".DOMAIN, "no-reply@".DOMAIN, "Neuer Termin Bachelor-/Masterarbeitsbetreuung",
							"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr(e) Betreuer(in) hat einen neuen Termin angelegt:\n".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nIhr(e) Betreuer(in)\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Masterarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Masterarbeitsabgabe\n--------------------------------------------------------------------------");
							if(!$mail->send())
							{
								echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerMailStudent')."</font><br>&nbsp;";
							}
							else
							{
								echo $p->t('abgabetool/mailVerschicktAn').": ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
							}
						}
						$command='';
					}
				}
				else
				{
					echo $p->t('global/fehlerBeimLesenAusDatenbank');
				}
			}
			if($command=='update')
			{
				//Terminänderung
				//Ermittlung der alten Daten
				$qry_old="SELECT * FROM campus.tbl_paabgabe
				WHERE paabgabe_id=".$db->db_add_param($paabgabe_id, FHC_INTEGER)."
				AND insertvon=".$db->db_add_param($user);

				if(!$result_old=$db->db_query($qry_old))
				{
					echo "<font color=\"#FF0000\">".$p->t('abgabetool/terminNichtGefunden')."</font><br>&nbsp;";
				}
				else
				{
					$row_old=@$db->db_fetch_object($result_old);
					//Abgabetyp
					$qry_told="SELECT bezeichnung FROM campus.tbl_paabgabetyp
					WHERE paabgabetyp_kurzbz=".$db->db_add_param($row_old->paabgabetyp_kurzbz);

					if($result_told=$db->db_query($qry_told))
					{
						$row_told=@$db->db_fetch_object($result_told);
					}
					else
					{
						$row_told->bezeichnung='';
					}
					//Termin updaten
					$qry="UPDATE campus.tbl_paabgabe SET
						projektarbeit_id = ".$db->db_add_param($projektarbeit_id, FHC_INTEGER).",
						paabgabetyp_kurzbz = ".$db->db_add_param($paabgabetyp_kurzbz).",
						fixtermin = ".($fixtermin==1?'true':'false').",
						datum = ".$db->db_add_param($datum).",
						kurzbz = ".$db->db_add_param($kurzbz).",
						updatevon = ".$db->db_add_param($user).",
						updateamum = now()
						WHERE paabgabe_id=".$db->db_add_param($paabgabe_id, FHC_INTEGER)." AND insertvon=".$db->db_add_param($user);

					if(!$result=$db->db_query($qry))
					{
						echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerTerminEintragen')."</font><br>&nbsp;";
					}
					else
					{
						//Abgabetyp
						$qry_typ="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz=".$db->db_add_param($paabgabetyp_kurzbz);
						if($result_typ=$db->db_query($qry_typ))
						{
								$row_typ=$db->db_fetch_object($result_typ);
						}
						else
						{
								$row_typ = new stdClass();
								$row_typ->bezeichnung='';
						}
						$mail = new mail($uid."@".DOMAIN, "no-reply@".DOMAIN, "Terminänderung Bachelor-/Masterarbeitsbetreuung",
							"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr(e) Betreuer(in) hat einen Termin geändert:\nVon: ".$datum_obj->formatDatum($row_old->datum,'d.m.Y').", ".$row_told->bezeichnung.", ".$row_old->kurzbz."\nAuf: ".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nIhr(e) Betreuer(in)\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Masterarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Masterarbeitsabgabe\n--------------------------------------------------------------------------");
						if(!$mail->send())
						{
							echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerMailStudent')."</font><br>&nbsp;";
						}
						else
						{
							echo $p->t('abgabetool/mailVerschicktAn').": ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
						}
					}
				}
				$command='';
			}
			/*
			if(isset($mail))
			{
				$mail->setReplyTo($user."@".DOMAIN);
				if(!$mail->send())
				{
					echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerMail')."</font><br>&nbsp;";
				}
			}*/
		}
	}
	else
	{
		echo "<font color=\"#FF0000\">".$p->t('lvplan/datumUngueltig')."</font><br>&nbsp;";
	}
	unset($_POST["schick"]);
}
//Loeschen eines Termines
if(isset($_POST["del"]))
{
	if($datum)
	{
		//Ermittlung der alten Daten
		$qry_old="SELECT * FROM campus.tbl_paabgabe WHERE paabgabe_id=".$db->db_add_param($paabgabe_id, FHC_INTEGER)." AND insertvon=".$db->db_add_param($user);
		if(!$result_old=$db->db_query($qry_old))
		{
			echo "<font color=\"#FF0000\">".$p->t('abgabetool/terminNichtGefunden')."</font><br>&nbsp;";
		}
		else
		{
			$row_old=@$db->db_fetch_object($result_old);
			$qry_std="SELECT * FROM campus.vw_benutzer where uid=".$db->db_add_param($uid);
			if(!$result_std=$db->db_query($qry_std))
			{
				echo "<font color=\"#FF0000\">".$p->t('global/userNichtGefunden')."</font><br>&nbsp;";
			}
			else
			{
				$row_std=@$db->db_fetch_object($result_std);
				$qry="DELETE FROM campus.tbl_paabgabe WHERE paabgabe_id=".$db->db_add_param($paabgabe_id, FHC_INTEGER)." AND insertvon=".$db->db_add_param($user);
				if(!$result=$db->db_query($qry))
				{
					echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerTerminLoeschen')."</font><br>&nbsp;";
				}
				else
				{
					$mail = new mail($uid."@".DOMAIN, "no-reply@".DOMAIN, "Termin Bachelor-/Masterarbeitsbetreuung",
					"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr(e) Betreuer(in) hat einen Termin entfernt:\n".$datum_obj->formatDatum($row_old->datum,'d.m.Y').", ".$row_old->kurzbz."\n\nMfG\nIhr(e) Betreuer(in)\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Masterarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Masterarbeitsabgabe\n--------------------------------------------------------------------------");
					$mail->setReplyTo($user."@".DOMAIN);
					if(!$mail->send())
					{
						echo "<font color=\"#FF0000\">".$p->t('fehlerMailStudent')."</font><br>&nbsp;";
					}
					else
					{
						echo $p->t('abgabetool/mailVerschicktAn').": ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
					}
				}
			}
		}
	}
	else
	{
		echo "<font color=\"#FF0000\">".$p->t('lvplan/datumUngueltig')."</font><br>&nbsp;";
	}
	unset($_POST["del"]);
}

$qry="SELECT * FROM campus.tbl_paabgabe WHERE projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER)." ORDER BY datum;";
$studentenname='';
$qry_nam="SELECT trim(COALESCE(vorname,'')||' '||COALESCE(nachname,'')) as studnam FROM campus.vw_student WHERE uid=".$db->db_add_param($uid);
$result_nam=$db->db_query($qry_nam);
while ($result_nam && $row_nam=$db->db_fetch_object($result_nam))
{
	$studentenname=$row_nam->studnam;
}

$htmlstr .= "<table id='beurteilungheadertable' width=100%>\n";
$htmlstr .= "<tr><td style='font-size:16px'>".$p->t('abgabetool/student').": <b>".$db->convert_html_chars($studentenname)."</b></td>";
$htmlstr .= "<td width=10% align=center>";
if ($num_rows_sem >= 1)
{
	$htmlstr .= "<form action='../../../index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/Projektarbeitsbeurteilung' title='Benotungsformular' target='_blank' method='GET'>";
	$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$projektarbeit_id."'>\n";
	$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
	$htmlstr .= "<input type='submit' name='note' value='".$p->t('abgabetool/benoten')."'></form>";
}
else
{
	$htmlstr .= "<form action='javascript:void(0);'>";
	$htmlstr .= "<input type='submit' value='".$p->t('abgabetool/benoten')."' title='".$p->t('abgabetool/aeltereParbeitBenoten')."'
					alt='".$p->t('abgabetool/aeltereParbeitBenoten')."' disabled>";
	$htmlstr .= "</form>";
}
$htmlstr .= "</td>";

if($betreuerart!="Zweitbegutachter")
{
	$htmlstr .= "<td width=10% align=center><form action='https://technikum-wien.turnitin.com' title='plagiatsprüfung' target='_blank' method='GET'>";
	$htmlstr .= "<input type='submit' name='ephorus' value='".$p->t('abgabetool/plagiatspruefung')."'></form></td>";
	$htmlstr .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
	$htmlstr .= "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>\n";
}
else
{
	$htmlstr .= "<td>&nbsp;</td></tr>";
}
$htmlstr .= "<tr><td style='font-size:16px'>" . $p->t('abgabetool/titel') . ": <b>".$db->convert_html_chars($titel)."<b></td><td></td><td valign=\"right\"><a href='abgabe_student_frameset.php?uid=$uid' target='_blank'>".$p->t('abgabetool/studentenansicht')."</a></td>";
$htmlstr .= "</tr>\n";
if (isset($zweitbegutachter) && $zweitbegutachter) // wenn es Zweitbegutachter gibt
{
	// Zweitbegutachter anzeigen
	$htmlstr .= "<tr>\n";
	$htmlstr .= "<td style='font-size:16px'>" . $p->t('abgabetool/zweitBegutachter') . ": <b>" . $zweitbegutachter->voller_name . "</b>";

	// keine Mail -> Fehler anzeigen
	if (!isset($zweitbegutachter->email))
		$htmlstr .= "&nbsp;&nbsp;<img src='../../../skin/images/exclamation.png' title='" . $p->t('abgabetool/zweitBegutachterEmailFehlt') . "' alt='" . $p->t('abgabetool/zweitBegutachterEmailFehlt') . "'/>";

	// Token senden button wenn Zweitbegutachter extern ist und Projektarbeit nicht für altes Semester ist
	if (isset($zweitbegutachter->email) && !isset($zweitbegutachter->uid) && $num_rows_sem >= 1)
	{
		$htmlstr .= "<form action='" . htmlspecialchars($_SERVER['PHP_SELF']) . "' method='GET' style='display: inline'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='" . $student_uid . "'>";
		$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='" . $projektarbeit_id . "'>";
		$htmlstr .= "<input type='hidden' name='betreuerart' value='" . $betreuerart . "'>";
		$htmlstr .= "&nbsp;<a href='mailto:".$zweitbegutachter->email."'><img id='zweitbetrmailicon' src='../../../skin/images/email.png'
						title='" . $p->t('abgabetool/zweitbetreuerMailSenden', $zweitbegutachter->email) . "' alt='" . $p->t('abgabetool/zweitbetreuerMailSenden', $zweitbegutachter->email) . "'/></a>\n";
		$htmlstr .= "&nbsp;<button type='submit' name='zweitbegutachtertoken' title='" . $p->t('abgabetool/zweitbetreuerTokenMailSenden') . "'>
						<img id='tokenmailicon' src='../../../skin/images/repeat.png' alt='" . $p->t('abgabetool/zweitbetreuerTokenMailSenden') . "'/></button>\n";
		$htmlstr .= "</form>";
	}

	$htmlstr .= "</td>\n";
	$htmlstr .= "<td></td>\n";
	$htmlstr .= "<td></td>\n";
	$htmlstr .= "</tr>\n";
}
$htmlstr .= "</table>\n";
$htmlstr .= "<table style='width: 100%'><tr>";
$htmlstr .= "<td><br><b>".$p->t('abgabetool/abgabetermine').":</b></td>\n";
$htmlstr .= "</tr>\n";
$htmlstr .= "</table>\n";
$htmlstr .= "<table class='detail' style='padding-top:10px;' >\n";
$htmlstr .= "<tr></tr>\n";
$htmlstr .= "<tr>
				<td>".$p->t('abgabetool/fix')."</td>
				<td>".$p->t('abgabetool/datum')."</td>
				<td>".$p->t('abgabetool/abgabetyp')."</td>
				<td>".$p->t('abgabetool/beschreibungAbgabe')."</td>
				<td>".$p->t('abgabetool/abgegebenAm')."</td>
				<td></td>
				<td></td>
				<td></td>
			</tr>\n";
$result=@$db->db_query($qry);
	while ($row=@$db->db_fetch_object($result))
	{
		$htmlstr .= "<form action='".$_SERVER['PHP_SELF']."' method='POST' name='".$row->projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$row->projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='paabgabe_id' value='".$row->paabgabe_id."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
		$htmlstr .= "<input type='hidden' name='betreuerart' value='".$betreuerart."'>\n";
		$htmlstr .= "<input type='hidden' name='command' value='update'>\n";
		$htmlstr .= "<tr id='".$row->projektarbeit_id."'>\n";
		if(!$row->abgabedatum)
		{
			if ($row->datum<date('Y-m-d'))
			{
				//Termin vorbei - weiß auf rot
				$bgcol='#FF0000';
				$fcol='#FFFFFF';
			}
			elseif (($row->datum>=date('Y-m-d')) && ($row->datum<date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")+11, date("Y")))))
			{
				//Termin nahe - schwarz auf gelb
				$bgcol='#FFFF00';
				$fcol='#000000';
			}
			else
			{
				//"normaler" Termin - schwarz auf weiß
				$bgcol='#FFFFFF';
				$fcol='#000000';
			}
		}
		else
		{
			if($row->abgabedatum>$row->datum)
			{
				//Abgabe nach Termin - weiß auf hellrot
				$bgcol='#EA7B7B';
				$fcol='#FFFFFF';
			}
			else
			{
				//Abgabe vor Termin - schwarz auf grün
				$bgcol='#00FF00';
				$fcol='#000000';
			}
		}
		//$htmlstr .= "<td><input type='checkbox' name='fixtermin' ".($row->fixtermin=='t'?'checked=\"checked\"':'')." >";
		//$htmlstr .= "<td><input type='checkbox' name='fixtermin' ".($row->fixtermin=='t'?'checked="checked" style="background-color:#FF0000;"':'')." disabled>";
		if($row->fixtermin=='t')
		{
			$htmlstr .= "<td><img src='../../../skin/images/bullet_red.png' alt='J' title='".$p->t('abgabetool/fixerAbgabetermin')."' border=0></td>";
		}
		else
		{
			$htmlstr .= "<td><img src='../../../skin/images/bullet_green.png' alt='N' title='".$p->t('abgabetool/variablerAbgabetermin')."' border=0></td>";
		}
		$htmlstr .= "		</td>\n";
		$htmlstr .= "		<td><input  type='text' name='datum' style='background-color:".$bgcol.";font-weight:bold; color:".$fcol." ' value='".$datum_obj->formatDatum($row->datum,'d.m.Y')."' size='10' maxlegth='10'></td>\n";
		$htmlstr .= "		<td><select name='paabgabetyp_kurzbz'>\n";
		//$htmlstr .= "			<option value=''>&nbsp;</option>";
		$qry_typ="SELECT * FROM campus.tbl_paabgabetyp";
		$result_typ=@$db->db_query($qry_typ);
		while ($row_typ=@$db->db_fetch_object($result_typ))
		{
			if($row->paabgabetyp_kurzbz==$row_typ->paabgabetyp_kurzbz)
			{
				$htmlstr .= "			<option value='".$row_typ->paabgabetyp_kurzbz."' selected>$row_typ->bezeichnung</option>";
			}
			else
			{
				if($row_typ->paabgabetyp_kurzbz!='end' && $row_typ->paabgabetyp_kurzbz!='note' && $row_typ->paabgabetyp_kurzbz!='enda')
				{
					$htmlstr .= "			<option value='".$row_typ->paabgabetyp_kurzbz."'>$row_typ->bezeichnung</option>";
				}
			}
		}
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "		<td><input type='text' name='kurzbz' value='".htmlspecialchars($row->kurzbz,ENT_QUOTES)."' size='60' maxlegth='256'></td>\n";
		$htmlstr .= "		<td>".($row->abgabedatum==''?'&nbsp;':$datum_obj->formatDatum($row->abgabedatum,'d.m.Y'))."</td>\n";
		if($user==$row->insertvon && $betreuerart!="Zweitbegutachter")
		{
			$htmlstr .= "		<td><input type='submit' name='schick' value='".$p->t('global/speichern')."' title='".$p->t('abgabetool/terminaenderungSpeichern')."'></td>";

			if(!$row->abgabedatum)
			{
				$htmlstr .= "		<td><input type='submit' name='del' value='".$p->t('global/loeschen')."' onclick='return confdel()' title='".$p->t('abgabetool/terminLoeschen')."'></td>";
			}
			else
			{
				$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			}
		}
		else
		{
			$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
		}
		if(file_exists(PAABGABE_PATH.$row->paabgabe_id.'_'.$uid.'.pdf'))
		{
			$htmlstr .= "		<td><a href='".$_SERVER['PHP_SELF']."?id=".$row->paabgabe_id."&uid=$uid' target='_blank'><img src='../../../skin/images/pdf.ico' alt='PDF' title='".$p->t('abgabetool/abgegebeneDatei')."' border=0></a></td>";
		}
		else
		{
			$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
		}
		if($row->abgabedatum && $row->paabgabetyp_kurzbz=="end")
		{
			$htmlstr .= "		<td><a href='abgabe_lektor_zusatz.php?paabgabe_id=".$row->paabgabe_id."&uid=$uid&projektarbeit_id=$projektarbeit_id' target='_blank'><img src='../../../skin/images/folder.gif' alt='zusätzliche Daten' title='".$p->t('abgabetool/kontrolleZusatzdaten')."' border=0></a></td>";
		}
		else
		{
			$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
		}
		$htmlstr .= "	</tr>\n";


		$htmlstr .= "</form>\n";
	}

//Eingabezeile fuer neuen Termin
$htmlstr .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="POST" name="'.$db->convert_html_chars($projektarbeit_id).'">'."\n";
$htmlstr .= '<input type="hidden" name="projektarbeit_id" value="'.$db->convert_html_chars($projektarbeit_id).'">'."\n";
$htmlstr .= '<input type="hidden" name="paabgabe_id" value="'.$db->convert_html_chars($paabgabe_id).'">'."\n";
$htmlstr .= '<input type="hidden" name="uid" value="'.$db->convert_html_chars($uid).'">'."\n";
$htmlstr .= '<input type="hidden" name="betreuerart" value="'.$db->convert_html_chars($betreuerart).'">'."\n";
$htmlstr .= '<input type="hidden" name="command" value="insert">'."\n";
$htmlstr .= '<tr id="'.$db->convert_html_chars($projektarbeit_id).'">'."\n";

//$htmlstr .= "<td><input type='checkbox' name='fixtermin'></td>";
$htmlstr .= "<td>&nbsp;&nbsp;</td>";

$htmlstr .= "		<td><input  type='text' name='datum' size='10' maxlegth='10' style='font-weight:bold;' ></td>\n";

$htmlstr .= "		<td><select name='paabgabetyp_kurzbz'>\n";
$qry_typ = "SELECT * FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz!='end' AND paabgabetyp_kurzbz!='enda' AND paabgabetyp_kurzbz!='note'";
$result_typ=$db->db_query($qry_typ);
while ($row_typ=@$db->db_fetch_object($result_typ))
{
	$htmlstr .= "		<option value='".$row_typ->paabgabetyp_kurzbz."'>".$row_typ->bezeichnung."</option>";
}
$htmlstr .= "		</select></td>\n";

$htmlstr .= "		<td><input  type='text' name='kurzbz' size='60' maxlegth='256'></td>\n";
$htmlstr .= "		<td>&nbsp;</td>\n";
if($betreuerart!="Zweitbegutachter")
{
	$htmlstr .= "		<td><input type='submit' name='schick' value='".$p->t('global/speichern')."' title='".$p->t('abgabetool/neuenTerminSpeichern')."'></td>";
}
else
{
	$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
}
$htmlstr .= "</tr>\n";
$htmlstr .= "</form>\n";
$htmlstr .= "</table>\n";
$htmlstr .= "</body></html>\n";
echo $htmlstr;

/**
 * Mail zum Zweitbegutachter mit Info dass Projektarbeit zur Bewertung bereit ist, inklusive Zugangstoken wenn nötig.
 * @param object $zweitbegutachter
 * @param $int erstbegutachter_person_id
 * @param object $student
 * @return bool|projektbetreuer|void|null
 */
function sendZweitbegutachterMail($zweitbegutachter, $erstbegutachter_person_id, $student)
{
	if (!isset($zweitbegutachter->email) || $zweitbegutachter->email == '')
		return false;

	// send Mail to 2. Begutachter
	$projektbetreuer = new projektbetreuer();

	$projektbetreuer->generateZweitbegutachterToken($zweitbegutachter->person_id, $zweitbegutachter->projektarbeit_id);

	if (!$projektbetreuer)
		return $projektbetreuer;

	$zweitbetr = $projektbetreuer->getZweitbegutachterWithToken($erstbegutachter_person_id, $zweitbegutachter->projektarbeit_id, $student->uid);

	if ($zweitbetr)
	{
		$intern = isset($zweitbetr->uid);
		$mail_baselink = APP_ROOT."index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/Projektarbeitsbeurteilung";
		$mail_fulllink = "$mail_baselink?projektarbeit_id=".$zweitbegutachter->projektarbeit_id."&uid=".$student->uid;
		$mail_link = $intern ? $mail_fulllink : $mail_baselink;

		$maildata = array();
		$maildata['geehrt'] = "geehrte".($zweitbegutachter->anrede=="Herr"?"r":"");
		$maildata['anrede'] = $zweitbegutachter->anrede;
		$maildata['betreuer_voller_name'] = $zweitbegutachter->voller_name;
		$maildata['student_anrede'] = $student->anrede;
		$maildata['student_voller_name'] = trim($student->titelpre." ".$student->vorname." ".$student->nachname." ".$student->titelpost);
		$maildata['abgabetyp'] = isset($zweitbetr->abgabedatum) ? 'Endabgabe' : 'Abgabe';
		$maildata['parbeituebersichtlink'] =  $intern ? "<p><a href='".APP_ROOT."cis/private/lehre/abgabe_lektor_frameset.html'>Zur Projektarbeitsübersicht</a></p>" : "";
		$maildata['bewertunglink'] = "<p><a href='$mail_link'>Zur Beurteilung der Arbeit</a></p>";
		$maildata['token'] = isset($zweitbetr->zugangstoken) && !$intern ? "<p>Zugangstoken: ".$zweitbetr->zugangstoken."</p>" : "";

		return sendSanchoMail(
			'ParbeitsbeurteilungEndupload',
			$maildata,
			$zweitbetr->email,
			"Masterarbeitsbetreuung",
			'sancho_header_min_bw.jpg',
			'sancho_footer_min_bw.jpg'
		);
	}

	return false;
}

?>
