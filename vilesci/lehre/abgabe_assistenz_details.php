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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
		
/*******************************************************************************************************
 *				abgabe_assistenz
 * 		abgabe_assistenz ist die Assistenzoberfläche des Abgabesystems 
 * 			für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/
//echo Test($_REQUEST);

	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');

require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/mail.class.php');

$fixtermin=false;

if(!isset($_POST['uid']))
{
	$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
	$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
	$titel = (isset($_GET['titel'])?$_GET['titel']:'-1');
	$erst = (isset($_GET['erst'])?$_GET['erst']:'');

	$command = '';
	$paabgabe_id = '';
	$fixtermin = false;
	$datum = '';
	$kurzbz = '';
	if($projektarbeit_id!='-1')
	{
		$qry_stg="SELECT tbl_studiengang.* FROM public.tbl_studiengang 
			JOIN lehre.tbl_lehrveranstaltung USING(studiengang_kz) 
			JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
			JOIN lehre.tbl_projektarbeit USING(lehreinheit_id)
			WHERE projektarbeit_id='$projektarbeit_id'";
		if($result_stg=$db->db_query($qry_stg))
		{
			if($row_stg=$db->db_fetch_object($result_stg))
			{
				$stgbez=$row_stg->bezeichnung;
				$stg_kz=$row_stg->studiengang_kz;
			}
			else 
			{
				echo "<font color=\"#FF0000\">Fehler beim Laden des Studiengangs!</font><br>&nbsp;";
				exit;
			}
		}
		else 
		{
			echo "<font color=\"#FF0000\">Studiengang konnte nicht gefunden werden!</font><br>&nbsp;";
			exit;
		}
	}
	else 
		exit;
}
else 
{
	$uid = (isset($_POST['uid'])?$_POST['uid']:'-1');
	$projektarbeit_id = (isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
	$titel = (isset($_POST['titel'])?$_POST['titel']:'');
	$command = (isset($_POST['command'])?$_POST['command']:'-1');
	$paabgabe_id = (isset($_POST['paabgabe_id'])?$_POST['paabgabe_id']:'-1');
	$paabgabetyp_kurzbz = (isset($_POST['paabgabetyp_kurzbz'])?$_POST['paabgabetyp_kurzbz']:'-1');
	$fixtermin = (isset($_POST['fixtermin'])?1:0);
	$datum = (isset($_POST['datum'])?$_POST['datum']:'');
	$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:'');
	$erst = (isset($_POST['erst'])?$_POST['erst']:'');
	$stgbez = (isset($_POST['stgbez'])?$_POST['stgbez']:'');
	$stg_kz = (isset($_POST['stg_kz'])?$_POST['stg_kz']:'');
}
$user = get_uid();
$datum_obj = new datum();
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', null, 'suid', $fachbereich_kurzbz) )
	die('Sie haben keine Berechtigung f&uuml;r diesen Studiengang');
$htmlstr='';


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

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Abgabe Assistenz Details</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="Javascript">
	function confdel()
	{
		return confirm("Wollen Sie diesen Eintrag wirklich loeschen");
	}
</script>
</head>
<body class="Background_main"  style="background-color:#eeeeee;">
<h3>Abgabe Assistenzbereich</h3>';

// Speichern eines Termines
if(isset($_POST["schick"]))
{
	if($datum)
	{
		$qry_std="SELECT * FROM campus.vw_benutzer where uid='$uid'";
		if(!$result_std=$db->db_query($qry_std))
		{
			echo "<font color=\"#FF0000\">Student konnte nicht gefunden werden!</font><br>&nbsp;";
		}
		else
		{
			$row_std=$db->db_fetch_object($result_std);
			if($command=='insert')
			{
				$qrychk="SELECT * FROM campus.tbl_paabgabe 
					WHERE projektarbeit_id='$projektarbeit_id' AND paabgabetyp_kurzbz='$paabgabetyp_kurzbz' 
					AND fixtermin=".($fixtermin==1?'true':'false')." AND datum='$datum' AND kurzbz='$kurzbz'";
				if($result=$db->db_query($qrychk))
				{
					if($db->db_num_rows($result)>0)
					{
						//Datensatz bereits vorhanden
					}
					else 
					{
						//neuer Termin
						$qry="INSERT INTO campus.tbl_paabgabe (projektarbeit_id, paabgabetyp_kurzbz, fixtermin, datum, kurzbz, abgabedatum, insertvon, insertamum, updatevon, updateamum) 
							VALUES ('$projektarbeit_id', '$paabgabetyp_kurzbz', ".($fixtermin==1?'true':'false').", '$datum', '$kurzbz', NULL, '$user', now(), NULL, NULL)";
						//echo $qry;	
						if(!$result=$db->db_query($qry))
						{
							echo "<font color=\"#FF0000\">Termin konnte nicht eingetragen werden!</font><br>&nbsp;";	
						}
						else 
						{
							$row=$db->db_fetch_object($result);
							$qry_typ="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='".$paabgabetyp_kurzbz."'";
							if($result_typ=$db->db_query($qry_typ))
							{
								$row_typ=$db->db_fetch_object($result_typ);
							}
							else 
							{
								$row_typ->bezeichnung='';
							}
							//Mail an Studierenden
							if($paabgabetyp_kurzbz!='note')
							{
								$mail = new mail($uid."@".DOMAIN, "vilesci@".DOMAIN, "Neuer Termin Bachelor-/Diplomarbeitsbetreuung",
								"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr Studiengang $stgbez hat einen neuen Termin angelegt:\n".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
								$mail->setReplyTo($user."@".DOMAIN);
								if(!$mail->send())
								{
									echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den Studierenden!</font><br>&nbsp;<br>";	
								}
								else
								{
									echo "Mail verschickt an: ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
								}
							}
							
							//Mail an Erstbegutachter
							$qry_betr="SELECT DISTINCT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,  
								public.tbl_mitarbeiter.mitarbeiter_uid, anrede 
								FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
								LEFT JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id) 
								LEFT JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid) 
								WHERE mitarbeiter_uid='$erst'";
							if(!$betr=$db->db_query($qry_betr))
							{
								echo "<font color=\"#FF0000\">Fehler beim Laden der Betreuer!</font><br>&nbsp;";
							}
							else
							{
								if($row_betr=$db->db_fetch_object($betr))
								{
									$mail = new mail($row_betr->mitarbeiter_uid."@".DOMAIN, "vilesci@".DOMAIN, "Neuer Termin Bachelor-/Diplomarbeitsbetreuung bei Studiengang $stgbez",
									"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\nDer Studiengang $stgbez hat einen neuen Termin angelegt für Ihre Betreuung von ".($row_std->anrede=="Herr"?"Herrn":$row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost).":\n".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
									$mail->setReplyTo($user."@".DOMAIN);
									if(!$mail->send())
									{
										echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Erst-)Begutachter! ($erst)</font><br>&nbsp;";	
									}
									else 
									{
										echo "Mail verschickt an: ".$row_betr->first;
									}
								}
								else 
								{
									echo "<font color=\"#FF0000\">Betreuer nicht gefunden. Kein Mail verschickt! ($erst)</font><br>&nbsp;";
								}
							}
						}
						$command='';
					}
				}
				else 
				{
					echo "Datenbank-Zugriffsfehler!";
				}
			}
			if($command=='update')
			{
				//Terminänderung
				//Ermittlung der alten Daten
				$qry_old="SELECT * FROM campus.tbl_paabgabe WHERE paabgabe_id='".$paabgabe_id."' AND insertvon='$user'";
				if(!$result_old=$db->db_query($qry_old))
				{
					echo "<font color=\"#FF0000\">Termin konnte nicht gefunden werden!</font><br>&nbsp;";	
				}
				else 
				{
					$row_old=$db->db_fetch_object($result_old);
					//Abgabetyp
					$qry_told="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='".$row_old->paabgabetyp_kurzbz."'";
					if($result_told=$db->db_query($qry_told))
					{
						$row_told=$db->db_fetch_object($result_told);
					}
					else 
					{
						$row_told->bezeichnung='';
					}
					//Termin updaten
					$qry="UPDATE campus.tbl_paabgabe SET
						projektarbeit_id = '".$projektarbeit_id."', 
						paabgabetyp_kurzbz = '".$paabgabetyp_kurzbz."', 
						fixtermin = ".($fixtermin==1?'true':'false').", 
						datum = '".$datum."', 
						kurzbz = '".$kurzbz."', 
						updatevon = '".$user."', 
						updateamum = now() 
						WHERE paabgabe_id='".$paabgabe_id."' AND insertvon='$user'";
					//echo $qry;	
					if(!$result=$db->db_query($qry))
					{
						echo "<font color=\"#FF0000\">Termin&auml;nderung konnte nicht eingetragen werden!</font><br>&nbsp;";	
					}
					else 
					{
						//Abgabetyp
						$qry_told="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='".$paabgabetyp_kurzbz."'";
						if(!$result=$db->db_query($qry))
						{
							$row_typ=$db->db_fetch_object($result_told);
						}
						else 
						{
							$row_typ->bezeichnung='';
						}
						//Mail an Studierenden
						if($row_old->paabgabetyp_kurzbz !='note')
						{
							$mail = new mail($uid."@".DOMAIN, "vilesci@".DOMAIN, "Termin&auml;nderung Bachelor-/Diplomarbeitsbetreuung",
							"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr Studiengang $stgbez hat einen Termin geändert:\nVon: ".$datum_obj->formatDatum($row_old->datum,'d.m.Y').", ".$row_told->bezeichnung.", ".$row_old->kurzbz."\nAuf: ".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung." ".$kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
							$mail->setReplyTo($user."@".DOMAIN);
							if(!$mail->send())
							{
								echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den Studierenden!</font><br>&nbsp;";	
							}
							else
							{
								echo "Mail verschickt an: ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
							}
						}
							
						//Mail an Erstbegutachter
						$qry_betr="SELECT DISTINCT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,  
							public.tbl_mitarbeiter.mitarbeiter_uid, anrede 
							FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
							LEFT JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id) 
							LEFT JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid) 
							WHERE mitarbeiter_uid='$erst'";
						if(!$betr=$db->db_query($qry_betr))
						{
							echo "<font color=\"#FF0000\">Fehler beim Laden der Betreuer!</font><br>&nbsp;";
						}
						else
						{
							if($row_betr=$db->db_fetch_object($betr))
							{
								$mail = new mail($row_betr->mitarbeiter_uid."@".DOMAIN, "vilesci@".DOMAIN, "Termin&auml;nderung Bachelor-/Diplomarbeitsbetreuung bei Studiengang $stgbez",
								"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\nDer Studiengang $stgbez hat einen Termin geändert f&uuml;r Ihre Betreuung von ".($row_std->anrede=="Herr"?"Herrn":$row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."\nVon: ".$datum_obj->formatDatum($row_old->datum,'d.m.Y').", ".$row_told->bezeichnung.", ".$row_old->kurzbz."\nAuf: ".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung." ".$kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
								$mail->setReplyTo($user."@".DOMAIN);
								if(!$mail->send())
								{
									echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Erst-)Begutachter!</font><br>&nbsp;";	
								}
								else 
								{
									echo "Mail verschickt an: ".$row_betr->first;
								}
							}
							else 
							{
								echo "<font color=\"#FF0000\">Betreuer nicht gefunden. Kein Mail verschickt!</font><br>&nbsp;";
							}
						}
					}
				}
				$command='';
			}
		}
	}
	else 
	{
		echo "<font color=\"#FF0000\">Datumseingabe ung&uuml;ltig!</font><br>&nbsp;";
	}
	unset($_POST["schick"]);
}
//Löschen eines Termines
if(isset($_POST["del"]))
{
	if($datum)
	{
		//Ermittlung der alten Daten
		$qry_old="SELECT * FROM campus.tbl_paabgabe WHERE paabgabe_id='".$paabgabe_id."' AND insertvon='$user'";
		if(!$result_old=$db->db_query($qry_old))
		{
			echo "<font color=\"#FF0000\">Termin konnte nicht gefunden werden!</font><br>&nbsp;";	
		}
		else 
		{
			$row_old=$db->db_fetch_object($result_old);
			$qry_std="SELECT * FROM campus.vw_benutzer where uid='$uid'";
			if(!$result_std=$db->db_query($qry_std))
			{
				echo "<font color=\"#FF0000\">Student konnte nicht gefunden werden!</font><br>&nbsp;";
			}
			else
			{
				$row_std=$db->db_fetch_object($result_std);
				$qry="DELETE FROM campus.tbl_paabgabe WHERE paabgabe_id='".$paabgabe_id."' AND insertvon='$user'";	
				if(!$result=$db->db_query($qry))
				{
					echo "<font color=\"#FF0000\">Fehler beim L&ouml;schen des Termins!</font><br>&nbsp;";
				}
				else 
				{
					//Mail an Studierenden
					if($row_old->paabgabetyp_kurzbz !='note')
					{
						$mail = new mail($uid."@".DOMAIN, "vilesci@".DOMAIN, "Termin Bachelor-/Diplomarbeitsbetreuung",
						"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr Studiengang $stgbez hat einen Termin entfernt:\n".$datum_obj->formatDatum($row_old->datum,'d.m.Y').", ".$row_old->kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
						$mail->setReplyTo($user."@".DOMAIN);
						if(!$mail->send())
						{
							echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails!</font><br>&nbsp;";	
						}
						else
						{
							echo "Mail verschickt an: ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
						}
					}
						
					//Mail an Erstbegutachter
					$qry_betr="SELECT DISTINCT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,  
						public.tbl_mitarbeiter.mitarbeiter_uid, anrede 
						FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
						LEFT JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id) 
						LEFT JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid) 
						WHERE mitarbeiter_uid='$erst'";
					if(!$betr=$db->db_query($qry_betr))
					{
						echo "<font color=\"#FF0000\">Fehler beim Laden der Betreuer!</font><br>&nbsp;";
					}
					else
					{
						if($row_betr=$db->db_fetch_object($betr))
						{
							$mail = new mail($row_betr->mitarbeiter_uid."@".DOMAIN, "vilesci@".DOMAIN, "Termin Bachelor-/Diplomarbeitsbetreuung bei Studiengang $stgbez",
							"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\nDer Studiengang $stgbez hat einen Termin entfernt f&uuml;r Ihre Betreuung von ".($row_std->anrede=="Herr"?"Herrn":$row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."\n".$datum_obj->formatDatum($row_old->datum,'d.m.Y').", ".$row_old->kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
							$mail->setReplyTo($user."@".DOMAIN);
							if(!$mail->send())
							{
								echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Erst-)Begutachter!</font><br>&nbsp;";	
							}
							else 
							{
								echo "Mail verschickt an: ".$row_betr->first;
							}
						}
						else 
						{
							echo "<font color=\"#FF0000\">Betreuer nicht gefunden. Kein Mail verschickt!</font><br>&nbsp;";
						}
					}
				}
			}
		}
	}
	else 
	{
		echo "<font color=\"#FF0000\">Datumseingabe ung&uuml;ltig!</font><br>&nbsp;";
	}
	unset($_POST["del"]);
}
//Bestätigen einer Endabgabe - Endabgabe erfolgt in Sekretariat
if(isset($_POST["enda"]))
{
	//Abgabetermin mit akt. Datum speichern
	$qry="UPDATE campus.tbl_paabgabe SET
		abgabedatum = now(), 
		updatevon = '".$user."', 
		updateamum = now() 
		WHERE paabgabe_id='".$paabgabe_id."' AND insertvon='$user'";
	//echo $qry;	
	if(!$result=$db->db_query($qry))
	{
		echo "<font color=\"#FF0000\">Termin&auml;nderung konnte nicht eingetragen werden!</font><br>&nbsp;";	
	}
	else 
	{
		echo "Endabgabedatum wurde eingetragen.";
	}
}
$studentenname='';
$qry_nam="SELECT trim(COALESCE(vorname,'')||' '||COALESCE(nachname,'')) as studnam FROM campus.vw_student WHERE uid='$uid'";
$result_nam=$db->db_query($qry_nam);
while ($result_nam && $row_nam=$db->db_fetch_object($result_nam))
{
	$studentenname=$row_nam->studnam;
}

$qry="SELECT * FROM campus.tbl_paabgabe WHERE projektarbeit_id='".$projektarbeit_id."' ORDER BY datum;";
$htmlstr .= "<table width=100%>\n";
$htmlstr .= "<tr><td style='font-size:16px'>Student: <b>".$studentenname."</b></td></tr>";
$htmlstr .= "<tr><td style='font-size:16px'>Titel: <b>".$titel."<b><br></td>";
//$htmlstr .= "<td align='right'><a href='../../cis/cisdocs/Projektarbeitsabgabe_FHTW_Anleitung.pdf' target='_blank'><img src='../../skin/images/information.png' alt='Anleitung' title='Anleitung BaDa-Abgabe' border=0></a>&nbsp;&nbsp;&nbsp;</td>";
$htmlstr .= "</tr>\n";
$htmlstr .= "</table>\n";
$htmlstr .= "<br><b>Termine:</b>\n";
$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
$htmlstr .= "<tr></tr>\n";
$htmlstr .= "<tr><td>fix</td><td>Datum</td><td>Abgabetyp</td><td>Kurzbeschreibung der Abgabe</td><td>abgegeben am</td><td></td><td></td><td></td></tr>\n";

	$result=$db->db_query($qry);
	while ($result && $row=$db->db_fetch_object($result))
	{
		$htmlstr .= "<form action='".$_SERVER['PHP_SELF']."' method='POST' name='".$row->projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$row->projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='paabgabe_id' value='".$row->paabgabe_id."'>\n";
		$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
		$htmlstr .= "<input type='hidden' name='command' value='update'>\n";
		$htmlstr .= "<input type='hidden' name='erst' value='".$erst."'>\n";
		$htmlstr .= "<input type='hidden' name='stgbez' value='".$stgbez."'>\n";
		$htmlstr .= "<input type='hidden' name='stg_kz' value='".$stg_kz."'>\n";
		$htmlstr .= "<tr id='".$row->projektarbeit_id."'>\n";
		if(!$row->abgabedatum)
		{
			if ($row->datum<=date('Y-m-d'))
			{
				$bgcol='#FF0000';
				$fcol='#FFFFFF';
			}
			elseif (($row->datum>date('Y-m-d')) && ($row->datum<date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")+11, date("Y")))))
			{
				$bgcol='#FFFF00';
				$fcol='#000000';
			}
			else 
			{
				$bgcol='#FFFFFF';
				$fcol='#000000';
			}
		}
		else 
		{
			if($row->abgabedatum>$row->datum)
			{
				$bgcol='#EA7B7B';
				$fcol='#FFFFFF';
			}
			else 
			{
				$bgcol='#00FF00';
				$fcol='#FFFFFF';
			}
		}
		$htmlstr .= "<td><input type='checkbox' name='fixtermin' ".($row->fixtermin=='t'?'checked=\"checked\"':'')." >";
		$htmlstr .= "		</td>\n";
		$htmlstr .= "		<td><input  type='text' name='datum' style='background-color:".$bgcol.";font-weight:bold; color:".$fcol." ' value='".$datum_obj->formatDatum($row->datum,'d.m.Y')."' size='10' maxlegth='10'></td>\n";
		$htmlstr .= "		<td><select name='paabgabetyp_kurzbz'>\n";
		$htmlstr .= "			<option value=''>&nbsp;</option>";
		$qry_typ="SELECT * FROM campus.tbl_paabgabetyp";
		$result_typ=$db->db_query($qry_typ);
		while ($result_typ && $row_typ=$db->db_fetch_object($result_typ))
		{
			if($row->paabgabetyp_kurzbz==$row_typ->paabgabetyp_kurzbz)
			{
				$htmlstr .= "			<option value='".$row_typ->paabgabetyp_kurzbz."' selected>$row_typ->bezeichnung</option>";
			}
			else 
			{
				$htmlstr .= "			<option value='".$row_typ->paabgabetyp_kurzbz."'>$row_typ->bezeichnung</option>";
			}
		}		
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "		<td><input  type='text' name='kurzbz' value='".$row->kurzbz."' size='60' maxlegth='256'></td>\n";		
		$htmlstr .= "		<td>".($row->abgabedatum==''?'&nbsp;':$datum_obj->formatDatum($row->abgabedatum,'d.m.Y'))."</td>\n";
		if($user==$row->insertvon)
		{		
			$htmlstr .= "		<td><input type='submit' name='schick' value='speichern' title='Termin&auml;nderung speichern'></td>";
		
			if(!$row->abgabedatum)
			{
				$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen' onclick='return confdel()' title='Termin l&ouml;schen'></td>";
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
				if($row->paabgabetyp_kurzbz=='enda')
		{
			$htmlstr .= "		<td width=50px><input type='submit' name='enda' value='best&auml;tigen' title='Endabgabe best&auml;tigen'></td>";
		}
		else 
		{
			if(file_exists(PAABGABE_PATH.$row->paabgabe_id.'_'.$uid.'.pdf'))
			{
				$htmlstr .= "		<td align=center><a href='".$_SERVER['PHP_SELF']."?id=".$row->paabgabe_id."&uid=$uid' target='_blank'><img src='../../skin/images/pdf.ico' alt='PDF' title='abgegebene Datei' border=0></a></td>";
			}
			else 
			{
				$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			}
			if($row->abgabedatum && $row->paabgabetyp_kurzbz=="end")
			{
				$htmlstr .= "		<td align=center><a href='abgabe_assistenz_zusatz.php?paabgabe_id=".$row->paabgabe_id."&uid=$uid&projektarbeit_id=$projektarbeit_id' target='_blank'><img src='../../skin/images/folder.gif' alt='zus&auml;tzliche Daten' title='Kontrolle der Zusatzdaten' border=0></a></td>";
			}
			else 
			{
				$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			}
		}
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</form>\n";
	}	
	
//Eingabezeile für neuen Termin
$htmlstr .= "<form action='".$_SERVER['PHP_SELF']."' method='POST' name='".$projektarbeit_id."'>\n";
$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$projektarbeit_id."'>\n";
$htmlstr .= "<input type='hidden' name='paabgabe_id' value='".$paabgabe_id."'>\n";
$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
$htmlstr .= "<input type='hidden' name='command' value='insert'>\n";
$htmlstr .= "<input type='hidden' name='erst' value='".$erst."'>\n";
$htmlstr .= "<input type='hidden' name='stgbez' value='".$stgbez."'>\n";
$htmlstr .= "<input type='hidden' name='stg_kz' value='".$stg_kz."'>\n";
$htmlstr .= "<tr id='".$projektarbeit_id."'>\n";

$htmlstr .= "<td><input type='checkbox' name='fixtermin'></td>";

$htmlstr .= "		<td><input  type='text' name='datum' size='10' maxlegth='10' style='font-weight:bold'></td>\n";

$htmlstr .= "		<td><select name='paabgabetyp_kurzbz'>\n";
$qry_typ = "SELECT * FROM campus.tbl_paabgabetyp";
$result_typ=$db->db_query($qry_typ);
while ($result_typ && $row_typ=$db->db_fetch_object($result_typ))
{
	$htmlstr .= "		<option value='".$row_typ->paabgabetyp_kurzbz."'>".$row_typ->bezeichnung."</option>";
}		
$htmlstr .= "		</select></td>\n";

$htmlstr .= "		<td><input  type='text' name='kurzbz' size='60' maxlegth='256'></td>\n";		
$htmlstr .= "		<td>&nbsp;</td>\n";		
$htmlstr .= "		<td><input type='submit' name='schick' value='speichern' title='neuen Termin speichern'></td>";

$htmlstr .= "</tr>\n";
$htmlstr .= "</form>\n";
$htmlstr .= "</table>\n";
$htmlstr .= "</body></html>\n";

	echo $htmlstr;
	
function Test($arr=constLeer,$lfd=0,$displayShow=true,$onlyRoot=false )

{

    $tmpArrayString='';

    if (!is_array($arr) && !is_object($arr)) return $arr;

    if (is_array($arr) && count($arr)<1 && $displayShow) return '';

    if (is_array($arr) && count($arr)<1 && $displayShow) return "<br><b>function Test (???)</b><br>";

  

    $lfdnr=$lfd + 1;

    $tmpAnzeigeStufe='';

    for ($i=1;$i<$lfdnr;$i++) $tmpAnzeigeStufe.="=";

    $tmpAnzeigeStufe.="=>";

        while (list( $tmp_key, $tmp_value ) = each($arr) )

        {

        if (!$onlyRoot && (is_array($tmp_value) || is_object($tmp_value)) && count($tmp_value) >0)

        {

                   $tmpArrayString.="<br>$tmpAnzeigeStufe <b>$tmp_key</b>".Test($tmp_value,$lfdnr);

        } else if ( (is_array($tmp_value) || is_object($tmp_value)) )

        {

                   $tmpArrayString.="<br>$tmpAnzeigeStufe <b>$tmp_key -- 0 Records</b>";

                } else if ($tmp_value!='')

                {

                   $tmpArrayString.="<br>$tmpAnzeigeStufe $tmp_key :== ".$tmp_value;

                } else {

                   $tmpArrayString.="<br>$tmpAnzeigeStufe $tmp_key :-- (is Empty :: $tmp_value)";

                } 

    }

     if ($lfd!='') { return $tmpArrayString; }

     if (!$displayShow) { return $tmpArrayString; }

      

    $tmpArrayString.="<br>";

    $tmpArrayString="<br><hr><br>******* START *******<br>".$tmpArrayString."<br>******* ENDE *******<br><hr><br>";

    $tmpArrayString.="<br>Server:: ".$_SERVER['PHP_SELF']."<br>";

    return "$tmpArrayString";

}

//===========================================================================================  
?>