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
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/mail.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$i=0;
$zaehl=0;

$irgendwas=''; //projektarbeit-ids der ausgwaehlten projekte
foreach($_POST as $key=>$value)
{
	if(stristr($key, "mc_"))
	{
		$irgendwas.=substr($key, 3).";";
		//echo $irgendwas."<br>";
		$i++;
	}
}
if($i==0 && !isset($_POST["schick"]) && !isset($_POST["plus"]))
{
	echo "<font color=\"#FF0000\">Es wurden keine Betreuungen ausgewählt!</font><br>&nbsp;";
	exit;
}
$i = (isset($_REQUEST['i'])?$_REQUEST['i']:0);
$irgendwas = (isset($_POST['irgendwas'])?$_POST['irgendwas']:$irgendwas);
$projektarbeit_id = (isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
$titel = (isset($_POST['titel'])?$_POST['titel']:'');
//$command = (isset($_POST['command'])?$_POST['command']:'-1');
$paabgabe_id = (isset($_POST['paabgabe_id'])?$_POST['paabgabe_id']:'-1');

$fixtermin = (isset($_POST['fixtermin'])?$_POST['fixtermin']:array());
$datum = (isset($_POST['datum'])?$_POST['datum']:array());
$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:array());
$paabgabetyp_kurzbz = (isset($_POST['paabgabetyp_kurzbz'])?$_POST['paabgabetyp_kurzbz']:array());


$stg_kz = (isset($_POST['stg_kz'])?$_POST['stg_kz']:'');
$p2id = (isset($_POST['p2id'])?$_POST['p2id']:'');

$qry_stg="SELECT * FROM public.tbl_studiengang WHERE studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER);
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
$mailtermine_st='';
$mailtermine_lk='';
$neu = (isset($_GET['neu'])?true:false);
$stg_arr = array();
//$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid'))
	die('Sie haben keine Berechtigung für diesen Studiengang');

$datum_obj = new datum();
for ($x=0;$x<count($paabgabetyp_kurzbz);$x++)
{
	$fixtermin[$x] = (isset($fixtermin[$x])&&!empty($fixtermin[$x])?1:0);
	if(isset($datum[$x])&&!empty($datum[$x]))
	{
		@list($day, $month, $year) = @explode(".", $datum[$x]);
        if (@checkdate($month, $day, $year))
        {
			if(!$datum[$x]=$datum_obj->checkformatDatum($datum[$x],'Y-m-d'))
			{
				$error.='Datum '.$datum[$x].' falsch! Kurzbeschreibung:'.$kurzbz[$x];
			}
        }
        else
        {
			$error.='Datum '.$datum[$x].' falsch! Kurzbeschreibung:'.$kurzbz[$x];
			$datum[$x]='';
        }
	}
	else
	{
		$datum[$x]='';
		$error.='Datum fehlt! Kurzbeschreibung:'.$kurzbz[$x];
	}
}
//echo $irgendwas."<br>";

if(isset($_POST["schick"]) && $error=='')
{
	$termine=explode(";",$irgendwas);
	//var_dump($termine);

	for($j=0;$j<count($termine)-1;$j++)
	{
		//schleife projektarbeit_id
		$mailtermine_st='';
		$mailtermine_lk='';
		for ($x=0;$x<count($paabgabetyp_kurzbz);$x++)
		{
			//schleife termine
			$qrychk="SELECT * FROM campus.tbl_paabgabe
				WHERE projektarbeit_id=".$db->db_add_param($termine[$j], FHC_INTEGER)."
				AND paabgabetyp_kurzbz=".$db->db_add_param($paabgabetyp_kurzbz[$x])."
				AND fixtermin=".($fixtermin[$x]==1?'true':'false')."
				AND datum=".$db->db_add_param($datum[$x])."
				AND kurzbz=".$db->db_add_param($kurzbz[$x]);

			if($result=$db->db_query($qrychk))
			{
				if($db->db_num_rows($result)>0)
				{
					echo "Datensatz bereits vorhanden";
				}
				else
				{
					//echo "neuer Termin";
					$qry="INSERT INTO campus.tbl_paabgabe (projektarbeit_id, paabgabetyp_kurzbz, fixtermin,
						datum, kurzbz, abgabedatum, insertvon, insertamum, updatevon, updateamum)
						VALUES (".$db->db_add_param($termine[$j]).", ".
						$db->db_add_param($paabgabetyp_kurzbz[$x]).", ".
						($fixtermin[$x]==1?'true':'false').", ".
						$db->db_add_param($datum[$x]).", ".
						$db->db_add_param($kurzbz[$x]).", NULL, ".
						$db->db_add_param($user).", now(), NULL, NULL)";

					//echo $qry;
					if(!$result=$db->db_query($qry))
					{
						echo "<font color=\"#FF0000\">Termin ($datum[$x], $kurzbz[$x]) konnte nicht eingetragen werden!</font><br>&nbsp;";
					}
					else
					{
						$row=@$db->db_fetch_object($result);
						$qry_typ="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz=".$db->db_add_param($paabgabetyp_kurzbz[$x]);
						if($result_typ=$db->db_query($qry_typ))
						{
							$row_typ=$db->db_fetch_object($result_typ);
						}
						else
						{
							$row_typ->bezeichnung='';
						}
						if($paabgabetyp_kurzbz[$x] !='note')
						{
							$mailtermine_st.="\n".($fixtermin[$x]==1?'Fixer Termin':'Variabler Termin').", ".$datum_obj->formatDatum($datum[$x],'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz[$x];
						}
						$mailtermine_lk.="\n".($fixtermin[$x]==1?'Fixer Termin':'Variabler Termin').", ".$datum_obj->formatDatum($datum[$x],'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz[$x];
					}
				}
			}
		}
		//Student zu projektarbeit_id suchen
		$qry_std="SELECT * FROM campus.vw_student WHERE uid IN(SELECT student_uid FROM lehre.tbl_projektarbeit WHERE projektarbeit_id=".$db->db_add_param($termine[$j]).")";
		if($result_std=$db->db_query($qry_std))
		{
			//Mail an Studierenden
			$row_std=$db->db_fetch_object($result_std);
			if($mailtermine_st !='')
			{
				$mail = new mail($row_std->uid."@".DOMAIN, "no-reply@".DOMAIN, "Neuer Termin Bachelor-/Masterarbeitsbetreuung",
				"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr Studiengang $stgbez hat (einen) neue(n) Termin(e) angelegt:".$mailtermine_st."\n\nMfG\nIhr(e) Studiengangsassistent(in)\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Masterarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Masterarbeitsabgabe\n--------------------------------------------------------------------------");
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
					WHERE projektarbeit_id=".$db->db_add_param($termine[$j])." AND (tbl_benutzer.aktiv OR tbl_benutzer.aktiv IS NULL)
					AND (tbl_projektbetreuer.betreuerart_kurzbz='Erstbegutachter' OR tbl_projektbetreuer.betreuerart_kurzbz='Betreuer' OR tbl_projektbetreuer.betreuerart_kurzbz = 'Begutachter')
					ORDER BY mitarbeiter_uid NULLS LAST";
			if(!$betr=$db->db_query($qry_betr))
			{
				echo "<font color=\"#FF0000\">Fehler beim Laden des Begutachters (Diplomand: $row->nachname)!</font><br>&nbsp;";
			}
			else
			{
				if($db->db_num_rows($betr)>0)
				{
					if($row_betr=$db->db_fetch_object($betr))
					{
						if($row_betr->mitarbeiter_uid!='')
						{
							$mail = new mail($row_betr->mitarbeiter_uid."@".DOMAIN, "no-reply@".DOMAIN, "Neuer Termin Bachelor-/Masterarbeitsbetreuung im Studiengang $stgbez",
							"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\nDer Studiengang $stgbez hat (einen) neue(n) Termin(e) angelegt für Ihre Betreuung von ".($row_std->anrede=="Herr"?"Herrn":$row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost).":".$mailtermine_lk."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Masterarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Masterarbeitsabgabe\n--------------------------------------------------------------------------");
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
							echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Erst-)Begutachter(in)! ($row_betr->first ist kein Mitarbeiter)</font><br>&nbsp;<br>";
						}
					}
					else
						echo "<font color=\"#FF0000\">Erstbegutachter(in) nicht gefunden. Kein Mail verschickt! (Diplomand: $row->nachname)</font><br>&nbsp;";
				}
				else
				{
					echo "Erstbegutachter(in) nicht gefunden. Kein Mail verschickt!<br>";
				}
			}
			//Mail an Zweitbegutachter
			$qry_betr="SELECT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,
					public.tbl_mitarbeiter.mitarbeiter_uid, anrede, kontakt
					FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
					JOIN public.tbl_kontakt ON(tbl_person.person_id=tbl_kontakt.person_id)
					LEFT JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id)
					LEFT JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid)
					WHERE projektarbeit_id=".$db->db_add_param($termine[$j])." AND (tbl_benutzer.aktiv OR tbl_benutzer.aktiv IS NULL)
					AND (tbl_projektbetreuer.betreuerart_kurzbz='Zweitbegutachter') AND kontakttyp='email' AND zustellung LIMIT 1";
			if(!$betr=$db->db_query($qry_betr))
			{
				echo "<font color=\"#FF0000\">Fehler beim Laden des Zweitbegutachters!</font><br>";
			}
			else
			{
				if($db->db_num_rows($betr)>0)
				{
					if($row_betr=$db->db_fetch_object($betr))
					{
                        if($row_betr->mitarbeiter_uid!='')
                            $to = $row_betr->mitarbeiter_uid.'@'.DOMAIN;
                        else
                            $to = $row_betr->kontakt;
						$mail = new mail($to, "no-reply@".DOMAIN, "Neuer Termin Bachelor-/Masterarbeitsbetreuung im Studiengang $stgbez",
						"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\nDer Studiengang $stgbez hat (einen) neue(n) Termin(e) angelegt für Ihre Betreuung von ".($row_std->anrede=="Herr"?"Herrn":$row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost).":".$mailtermine_lk."\n\nMfG\nDie Studiengangsassistenz\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Masterarbeitsabgabesystem generiertes Info-Mail\n--------------------------------------------------------------------------");
						$mail->setReplyTo($user."@".DOMAIN);
						if(!$mail->send())
						{
							echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Zweit-)Begutachter(in)! ($erst)</font><br>";
						}
						else
						{
							echo "Mail verschickt an Zweitbetreuer(in): ".$row_betr->first.' '.$to."<br>";
						}
					}
					else
						echo "<font color=\"#FF0000\">Zweitbegutachter(in) nicht gefunden. Kein Mail verschickt!</font><br>";
				}
				else
				{
					echo "Zweitbegutachter(in) nicht gefunden. Kein Mail verschickt!<br>";
				}
			}

		}
	}
	exit();
}

$htmlstr='';

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>Mehrfachtermin PA-Abgabe</title>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script>
			function checkdatum(datum)
			{
				if(!datum.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/))
				{
					alert("Datum muss im Format dd.mm.YYYY eingegeben werden");
					return false;
				}
				return true;
			}
		</script>
		</head>
		<body class="Background_main"  style="background-color:#eeeeee;">
		<h3>Eingabe von Terminen f&uuml;r mehrere Personen</h3><br>';
		$htmlstr .= $error;
		$error='';
		$htmlstr .= "<table class='detail' style='padding-top:10px;' >\n";
		$htmlstr .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="POST" name="multitermin">';
		$htmlstr .= "<tr></tr>\n";
		$htmlstr .= "<tr><td>fix</td><td>Datum</td><td>Abgabetyp</td><td>Kurzbeschreibung der Abgabe</td></tr>\n";
		for ($x=0;$x<count($paabgabetyp_kurzbz);$x++)
		{
			if(!isset($datum[$x])||empty($datum[$x]))
			{
				continue;
			}
			$htmlstr .= "<tr id='termin".$x."'>\n";
			if(isset($fixtermin[$x])&&!empty($fixtermin[$x]))
			{
				$htmlstr .= "<td><input type='checkbox' checked='checked' name='fixterminx' onclick='if (this.checked) {document.getElementById(\"fixtermin".($x+1)."\").value=1;}else{document.getElementById(\"fixtermin".($x+1)."\").value=0;}'>";
			}
			else
			{
				$htmlstr .= "<td><input type='checkbox' name='fixterminx' onclick='if (this.checked) {document.getElementById(\"fixtermin".($x+1)."\").value=1;}else{document.getElementById(\"fixtermin".($x+1)."\").value=0;}'>";
			}
			$htmlstr .= "<input type='text'  style='display:none;' id='fixtermin".($x+1)."'  name='fixtermin[]' value='".$fixtermin[$x]."'></td>";
			$htmlstr .= "		<td><input  type='text' name='datum[]' size='10' onchange='checkdatum(this.value)' maxlegth='10' value='".$datum_obj->checkformatDatum($datum[$x],'d.m.Y')."'></td>\n";
			$htmlstr .= "		<td><select name='paabgabetyp_kurzbz[]'>\n";
			$qry_typ = "SELECT * FROM campus.tbl_paabgabetyp";
			$result_typ=$db->db_query($qry_typ);
			while ($result_typ && $row_typ=$db->db_fetch_object($result_typ))
			{
				if($paabgabetyp_kurzbz[$x]==$row_typ->paabgabetyp_kurzbz)
				{
					$htmlstr .= "		<option value='".$row_typ->paabgabetyp_kurzbz."' selected>".$row_typ->bezeichnung."</option>";
				}
				else
				{
					$htmlstr .= "		<option value='".$row_typ->paabgabetyp_kurzbz."'>".$row_typ->bezeichnung."</option>";
				}
			}
			$htmlstr .= "		</select></td>\n";
			$htmlstr .= "		<td><input  type='text' name='kurzbz[]' size='100' maxlegth='256' value='".$kurzbz[$x]."'></td>\n";
			$htmlstr .= "		<td>&nbsp;</td>\n";

		}
		//Eingabezeile für neuen Termin
		//$htmlstr .= "<b>Abgabetermin:</b>\n";
		$htmlstr .= '<input type="hidden" name="irgendwas" value="'.$db->convert_html_chars($irgendwas).'">';
		$htmlstr .= '<input type="hidden" name="stg_kz" value="'.$db->convert_html_chars($stg_kz).'">';
		$htmlstr .= '<input type="hidden" name="p2id" value="'.$db->convert_html_chars($p2id).'">';
		$htmlstr .= "<tr></tr>\n";
		$htmlstr .= '<tr id="termin'.($x+1).'">';
		$htmlstr .= '<td><input type="checkbox" name="fixterminx" onclick="if (this.checked) {document.getElementById(\'fixtermin'.($x+1).'\').value=1;}else{document.getElementById(\'fixtermin'.($x+1).'\').value=0;}">';
		$htmlstr .= "<input type='text' style='display:none;' id='fixtermin".($x+1)."' name='fixtermin[]' value='0'></td>";
		$htmlstr .= "		<td><input  type='text' name='datum[]' onchange='checkdatum(this.value)' size='10' maxlegth='10'></td>\n";
		$htmlstr .= "		<td><select name='paabgabetyp_kurzbz[]'>\n";
		$qry_typ = "SELECT * FROM campus.tbl_paabgabetyp";
		$result_typ=$db->db_query($qry_typ);
		while ($result_typ && $row_typ=$db->db_fetch_object($result_typ))
		{
			$htmlstr .= "		<option value='".$row_typ->paabgabetyp_kurzbz."'>".$row_typ->bezeichnung."</option>";
		}
		$htmlstr .= "		</select></td>\n";
		$htmlstr .= "		<td><input  type='text' name='kurzbz[]' size='100' maxlegth='256'></td>\n";
		$htmlstr .= "		<td><input type='submit' name='plus' value='  +  ' title='weiterer Termin'></td>";
		$htmlstr .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><input type='submit' name='schick' value='speichern' title='neue(n) Termin(e) speichern'></td></tr>";
		$htmlstr .= "</tr>\n";
		$htmlstr .= "</form>\n";
		$htmlstr .= "</table>\n";
		$htmlstr .= "</body></html>\n";

		echo $htmlstr;
		echo '</body></html>';
?>
