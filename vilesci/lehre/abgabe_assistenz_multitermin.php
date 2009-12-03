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
 *				abgabe_assistenz
 * 		abgabe_assistenz ist die Assistenzoberfläche des Abgabesystems 
 * 			für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/datum.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/mail.class.php');

$i=0;
$irgendwas='';
foreach($_POST as $key=>$value)
{
	if(stristr($key, "mc_"))
	{
		$irgendwas.=substr($key, 3).";";
		//echo $irgendwas."<br>";
		$i++;
	}	
}
$irgendwas = (isset($_POST['irgendwas'])?$_POST['irgendwas']:$irgendwas);
$projektarbeit_id = (isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
$titel = (isset($_POST['titel'])?$_POST['titel']:'');
$command = (isset($_POST['command'])?$_POST['command']:'-1');
$paabgabe_id = (isset($_POST['paabgabe_id'])?$_POST['paabgabe_id']:'-1');
$fixtermin = (isset($_POST['fixtermin'])?1:0);
$datum = (isset($_POST['datum'])?$_POST['datum']:'');
$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:'');
$paabgabetyp_kurzbz = (isset($_POST['paabgabetyp_kurzbz'])?$_POST['paabgabetyp_kurzbz']:'');
$stg_kz = (isset($_POST['stg_kz'])?$_POST['stg_kz']:'');
$qry_stg="SELECT * FROM public.tbl_studiengang WHERE studiengang_kz='$stg_kz'";
if($result_stg=$db->db_query($qry_stg))
{
	if($row_stg=$db->db_fetch_object($result_stg))
	{
		$stgbez=$row_stg->bezeichnung;
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

if (!$user = get_uid())
		die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
			

$datum_obj = new datum();
$error='';
$neu = (isset($_GET['neu'])?true:false);
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', null, 'suid', $fachbereich_kurzbz) )
	die('Sie haben keine Berechtigung für diesen Studiengang');

$datum_obj = new datum();
$datum=$datum_obj->formatDatum($datum,'Y-m-d');
//echo $irgendwas."<br>";

if(isset($_POST["schick"]))
{
	$termine=explode(";",$irgendwas);
	//var_dump($termine);

	for($j=0;$j<count($termine)-1;$j++)
	{
		$qrychk="SELECT * FROM campus.tbl_paabgabe 
			WHERE projektarbeit_id='".$termine[$j]."' AND paabgabetyp_kurzbz='$paabgabetyp_kurzbz' 
			AND fixtermin=".($fixtermin==1?'true':'false')." AND datum='$datum' AND kurzbz='$kurzbz'";
		//echo $qrychk;
		if($result=$db->db_query($qrychk))
		{
			if($db->db_num_rows($result)>0)
			{
				echo "Datensatz bereits vorhanden";
			}
			else 
			{
				//echo "neuer Termin";
				$qry="INSERT INTO campus.tbl_paabgabe (projektarbeit_id, paabgabetyp_kurzbz, fixtermin, datum, kurzbz, abgabedatum, insertvon, insertamum, updatevon, updateamum) 
					VALUES ('".$termine[$j]."', '$paabgabetyp_kurzbz', ".($fixtermin==1?'true':'false').", '$datum', '$kurzbz', NULL, '$user', now(), NULL, NULL)";
				//echo $qry;	
				if(!$result=$db->db_query($qry))
				{
					echo "<font color=\"#FF0000\">Termin konnte nicht eingetragen werden!</font><br>&nbsp;";	
				}
				else 
				{
					$row=@$db->db_fetch_object($result);
					$qry_typ="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='".$paabgabetyp_kurzbz."'";
					if($result_typ=$db->db_query($qry_typ))
					{
						$row_typ=$db->db_fetch_object($result_typ);
					}
					else 
					{
						$row_typ->bezeichnung='';
					}
					//Student zu projektarbeit_id suchen
					$qry_std="SELECT * FROM campus.vw_student WHERE uid IN(SELECT student_uid FROM lehre.tbl_projektarbeit WHERE projektarbeit_id=$termine[$j])";
					if($result_std=$db->db_query($qry_std))
					{
						//Mail an Studierenden
						$row_std=$db->db_fetch_object($result_std);
						if($paabgabetyp_kurzbz !='note')
						{
							$mail = new mail($row_std->uid."@".DOMAIN, "vilesci@".DOMAIN, "Neuer Termin Bachelor-/Diplomarbeitsbetreuung",
							"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr Studiengang $stgbez hat einen neuen Termin angelegt:\n".($fixtermin==1?'Fixer Termin':'Variabler Termin').", ".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nIhr(e) Studiengangsassistent(in)\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
							$mail->setReplyTo($user."@".DOMAIN);
							if(!$mail->send())
							{
								echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an Studierende(n) ($row->nachname)!</font><br>&nbsp;<br>";	
							}
							else 
							{
								echo "Mail verschickt an Studierende(n): ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
							}
						}
						
						//Mail an EINEN Erstbegutachter oder Betreuer
						$qry_betr="SELECT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,  
								public.tbl_mitarbeiter.mitarbeiter_uid, anrede 
								FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
								LEFT JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id) 
								LEFT JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid) 
								WHERE projektarbeit_id=$termine[$j] AND (tbl_benutzer.aktiv OR tbl_benutzer.aktiv IS NULL) 
								AND (tbl_projektbetreuer.betreuerart_kurzbz='Erstbegutachter' OR tbl_projektbetreuer.betreuerart_kurzbz='Betreuer')";
						if(!$betr=$db->db_query($qry_betr))
						{
							echo "<font color=\"#FF0000\">Fehler beim Laden des Begutachters (Diplomand: $row->nachname)!</font><br>&nbsp;";
						}
						else
						{
							if($row_betr=$db->db_fetch_object($betr))
							{
								$mail = new mail($row_betr->mitarbeiter_uid."@".DOMAIN, "vilesci@".DOMAIN, "Neuer Termin Bachelor-/Diplomarbeitsbetreuung im Studiengang $stgbez",
								"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\nDer Studiengang $stgbez hat einen neuen Termin angelegt für Ihre Betreuung von ".($row_std->anrede=="Herr"?"Herrn":$row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost).":\n".($fixtermin==1?'Fixer Termin':'Variabler Termin').", ".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
								$mail->setReplyTo($user."@".DOMAIN);
								if(!$mail->send())
								{
									echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Erst-)Begutachter(in)! ($row_betr->first)</font><br>&nbsp;<br>";	
								}
								else 
								{
									echo "Mail verschickt an Erstbegutachter(in): ".$row_betr->first."<br>";
								}
							}
							else 
							{
								echo "<font color=\"#FF0000\">Erstbegutachter(in) nicht gefunden. Kein Mail verschickt! (Diplomand: $row->nachname)</font><br>&nbsp;";
							}
						}
						//Mail an Zweitbegutachter
						if($p2id!='')
						{
							$qry_betr="SELECT DISTINCT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,  
								anrede, kontakt 
								FROM public.tbl_person JOIN public.tbl_kontakt USING(person_id) 
								WHERE person_id='$p2id' AND kontakttyp='email' AND zustellung LIMIT 1";
							if(!$betr=$db->db_query($qry_betr))
							{
								echo "<font color=\"#FF0000\">Fehler beim Laden des Zweitbegutachters!</font><br>";
							}
							else
							{
								if($row_betr=$db->db_fetch_object($betr))
								{
									$mail = new mail($row_betr->kontakt, "vilesci@".DOMAIN, "Neuer Termin Bachelor-/Diplomarbeitsbetreuung bei Studiengang $stgbez",
									"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\nDer Studiengang $stgbez hat einen neuen Termin angelegt für Ihre Betreuung von ".($row_std->anrede=="Herr"?"Herrn":$row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost).":\n".($fixtermin==1?'Fixer Termin':'Variabler Termin').", ".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\n--------------------------------------------------------------------------");
									$mail->setReplyTo($user."@".DOMAIN);
									if(!$mail->send())
									{
										echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Zweit-)Begutachter(in)! ($erst)</font><br>";	
									}
									else 
									{
										echo "Mail verschickt an Zweitbetreuer(in): ".$row_betr->first."<br>";
									}
								}
								else 
								{
									echo "<font color=\"#FF0000\">Zweitbegutachter(in) nicht gefunden. Kein Mail verschickt! ($p2id)</font><br>";
								}
							}
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
}

$htmlstr='';

	echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>Mehrfachtermin PA-Abgabe</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		</head>
		<body class="Background_main"  style="background-color:#eeeeee;">
		<h3>Eingabe eines Termins f&uuml;r mehrere Personen</h3>';
		//Eingabezeile für neuen Termin
		$htmlstr .= "<br><b>Abgabetermin:</b>\n";
		$htmlstr .= "<table class='detail' style='padding-top:10px;' >\n";
		$htmlstr .= "<form action='".$_SERVER['PHP_SELF']."' method='POST' name='multitermin'>\n";
		$htmlstr .= "<input type='hidden' name='irgendwas' value='".$irgendwas."'>\n";
		$htmlstr .= "<input type='hidden' name='stg_kz' value='".$stg_kz."'>\n";
		$htmlstr .= "<tr></tr>\n";
		$htmlstr .= "<tr><td>fix</td><td>Datum</td><td>Abgabetyp</td><td>Kurzbeschreibung der Abgabe</td></tr>\n";
		$htmlstr .= "<tr id='termin'>\n";
		$htmlstr .= "<td><input type='checkbox' name='fixtermin'></td>";
		$htmlstr .= "		<td><input  type='text' name='datum' size='10' maxlegth='10'></td>\n";
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
		echo '</body></html>';

?>