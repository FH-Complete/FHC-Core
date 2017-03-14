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
 * 		abgabe_lektor ist die Lektorenseite des Abgabesystems
 * 			fuer Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

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


$user = get_uid();
$datum_obj = new datum();
$datum=$datum_obj->formatDatum($datum,'Y-m-d');
$error='';
$neu = (isset($_GET['neu'])?true:false);
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$lektor = check_lektor($user);
if(!$rechte->isBerechtigt('admin') && !$lektor)
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}
if($irgendwas=='')
	die('Es wurden keine Eintraege markiert');

if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('lehre/abgabetool'))
{
	// Pruefen ob der Lektor zu diesen Projektarbeiten zugeteilt ist
	$ids = explode(";",$irgendwas);
	foreach($ids as $id)
	{
		if($id!='')
		{
			$qry = "SELECT
					projektarbeit_id
				FROM
					lehre.tbl_projektbetreuer
					JOIN public.tbl_benutzer USING(person_id)
				 WHERE projektarbeit_id=".$db->db_add_param($id)."
				AND uid=".$db->db_add_param($user);
			if($result = $db->db_query($qry))
			{
				if($db->db_num_rows($result)==0)
				{
					die('Sie sind nicht zu dieser Projektarbeit zugeteilt');
				}
			}
		}
	}
}
//echo $irgendwas."<br>";

if(isset($_POST["schick"]))
{
	$termine=explode(";",$irgendwas);
	//var_dump($termine);
	for($j=0;$j<count($termine)-1;$j++)
	{
		$qrychk="SELECT * FROM campus.tbl_paabgabe
			WHERE projektarbeit_id=".$db->db_add_param($termine[$j])."
			AND paabgabetyp_kurzbz=".$db->db_add_param($paabgabetyp_kurzbz)."
			AND fixtermin=".($fixtermin==1?'true':'false')."
			AND datum=".$db->db_add_param($datum)."
			AND kurzbz=".$db->db_add_param($kurzbz);

		//echo $qrychk;
		if($result=$db->db_query($qrychk))
		{
			if($db->db_num_rows($result)>0)
			{
				echo $p->t('abgabetool/terminVorhanden');
			}
			else
			{
				//pruefen, ob user zweitbetreuer
				$qry2="SELECT * FROM lehre.tbl_projektbetreuer WHERE
					projektarbeit_id=".$db->db_add_param($termine[$j])."
					AND betreuerart_kurzbz='Zweitbegutachter'
					AND person_id=(SELECT person_id FROM campus.vw_mitarbeiter
						WHERE uid=".$db->db_add_param($user).")";

				$result2=$db->db_query($qry2);
				//zweitbetreuer koennen keine termine eintragen
				if($db->db_num_rows($result2)==0)
				{
					//echo "neuer Termin";
					$qry="INSERT INTO campus.tbl_paabgabe (projektarbeit_id, paabgabetyp_kurzbz,
						fixtermin, datum, kurzbz, abgabedatum, insertvon, insertamum, updatevon, updateamum)
						VALUES (".$db->db_add_param($termine[$j]).", ".
						$db->db_add_param($paabgabetyp_kurzbz).", ".
						($fixtermin==1?'true':'false').", ".
						$db->db_add_param($datum).", ".
						$db->db_add_param($kurzbz).", NULL, ".
						$db->db_add_param($user).", now(), NULL, NULL)";
					//echo $qry;
					if(!$result=$db->db_query($qry))
					{
						echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerTerminEintragen')."</font><br>&nbsp;";
					}
					else
					{
						$row=$db->db_fetch_object($result);
						$qry_typ="SELECT bezeichnung FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz=".$db->db_add_param($paabgabetyp_kurzbz);
						if($result_typ=$db->db_query($qry_typ))
						{
							$row_typ=$db->db_fetch_object($result_typ);
						}
						else
						{
							$row_typ->bezeichnung='';
						}
						//Student zu projektarbeit_id suchen
						$qry_std="SELECT * FROM campus.vw_student WHERE uid IN(SELECT student_uid FROM lehre.tbl_projektarbeit WHERE projektarbeit_id=".$db->db_add_param($termine[$j]).")";
						if($result_std=@$db->db_query($qry_std))
						{
							$row_std=$db->db_fetch_object($result_std);
							$mail = new mail($row_std->uid."@".DOMAIN, "no-reply@".DOMAIN, "Neuer Termin Bachelor-/Masterarbeitsbetreuung",
							"Sehr geehrte".($row_std->anrede=="Herr"?"r":"")." ".$row_std->anrede." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."!\n\nIhr(e) Betreuer(in) hat einen neuen Termin angelegt:\n".$datum_obj->formatDatum($datum,'d.m.Y').", ".$row_typ->bezeichnung.", ".$kurzbz."\n\nMfG\nIhr(e) Betreuer(in)\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Masterarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Masterarbeitsabgabe\n--------------------------------------------------------------------------");
							if(!$mail->send())
							{
								echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerMailStudent')." ($row_std->uid)</font><br>&nbsp;";
							}
							else
							{
								echo $p->t('abgabetool/mailVerschicktAn').": ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)."<br>";
							}
						}
					}
				}
				else
				{
					echo $p->t('abgabetool/zweitbetreuerBei')." ".$termine[$j]." <br>";
				}
				$command='';
			}
		}
		else
		{
			echo $p->t('global/fehlerBeimLesenAusDatenbank');
		}
	}
}

$htmlstr='';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Mehrfachtermin PA-Abgabe</title>
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<h2>'.$p->t('abgabetool/eingabeTerminPersonen').'</h2>';

//Eingabezeile f&uuml;r neuen Termin
$htmlstr .= "<br><b>".$p->t('abgabetool/abgabetermine').":</b>\n";
$htmlstr .= "<table class='detail' style='padding-top:10px;' >\n";
$htmlstr .= '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="POST" name="multitermin">';
$htmlstr .= '<input type="hidden" name="irgendwas" value="'.$irgendwas.'">';
$htmlstr .= "<tr></tr>\n";
$htmlstr .= "<tr>
				<td>".$p->t('abgabetool/datum')."</td>
				<td>".$p->t('abgabetool/abgabetyp')."</td>
				<td>".$p->t('abgabetool/beschreibungAbgabe')."</td></tr>\n";
$htmlstr .= "<tr id='termin'>\n";
//$htmlstr .= "<td><input type='checkbox' name='fixtermin'></td>";
$htmlstr .= "		<td><input  type='text' name='datum' size='10' maxlength='10'></td>\n";
$htmlstr .= "		<td><select name='paabgabetyp_kurzbz'>\n";
$qry_typ = "SELECT * FROM campus.tbl_paabgabetyp";
$result_typ=$db->db_query($qry_typ);
while ($row_typ=@$db->db_fetch_object($result_typ))
{
	if($row_typ->paabgabetyp_kurzbz!='end' && $row_typ->paabgabetyp_kurzbz!='note' && $row_typ->paabgabetyp_kurzbz!='enda')
	{
		$htmlstr .= "			<option value='".$row_typ->paabgabetyp_kurzbz."'>$row_typ->bezeichnung</option>";
	}
}
$htmlstr .= "		</select></td>\n";
$htmlstr .= "		<td><input  type='text' name='kurzbz' size='60' maxlength='256'></td>\n";
$htmlstr .= "		<td>&nbsp;</td>\n";
$htmlstr .= "		<td><input type='submit' name='schick' value='".$p->t('global/speichern')."'></td>";

$htmlstr .= "</tr>\n";
$htmlstr .= "</form>\n";
$htmlstr .= "</table>\n";

echo $htmlstr;
echo '</body></html>';

?>
