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
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/mail.class.php');
	require_once('../../../include/benutzerberechtigung.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
				
	//require_once('../../../include/Excel/excel.php');
if(!isset($_POST['uid']))
{
	$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
	$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
	$titel = (isset($_GET['titel'])?$_GET['titel']:'-1');
	$betreuer = (isset($_GET['betreuer'])?$_GET['betreuer']:'-1');
	$bid = (isset($_GET['bid'])?$_GET['bid']:'-1');

	$command = '';
	$paabgabe_id = '';
	$fixtermin = false;
	$datum = '01.01.1980';
	$kurzbz = '';
	$kontrollschlagwoerter = '';
	$schlagwoerter = '';
	$schlagwoerter_en = '';
	$abstract = '';
	$abstract_en = '';
	$seitenanzahl = '';
	$abgabedatum = '01.01.1980';
	$sprache='German';
}
else 
{
	$uid = (isset($_POST['uid'])?$_POST['uid']:'-1');
	$projektarbeit_id = (isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
	$bid = (isset($_POST['bid'])?$_POST['bid']:'-1');
	$titel = (isset($_POST['titel'])?$_POST['titel']:'');
	$command = (isset($_POST['command'])?$_POST['command']:'');
	$paabgabe_id = (isset($_POST['paabgabe_id'])?$_POST['paabgabe_id']:'-1');
	$paabgabetyp_kurzbz = (isset($_POST['paabgabetyp_kurzbz'])?$_POST['paabgabetyp_kurzbz']:'-1');
	$fixtermin = (isset($_POST['fixtermin'])?1:0);
	$datum = (isset($_POST['datum'])?$_POST['datum']:'');
	$abgabedatum = (isset($_POST['abgabedatum'])?$_POST['abgabedatum']:'01.01.1980');
	$kurzbz = (isset($_POST['kurzbz'])?$_POST['kurzbz']:'');
	$betreuer = (isset($_POST['betreuer'])?$_POST['betreuer']:'-1');
	$sprache = (isset($_POST['sprache'])?$_POST['sprache']:'German');
	$kontrollschlagwoerter = (isset($_POST['kontrollschlagwoerter'])?$_POST['kontrollschlagwoerter']:'-1');
	$schlagwoerter = (isset($_POST['schlagwoerter'])?$_POST['schlagwoerter']:'-1');
	$schlagwoerter_en = (isset($_POST['schlagwoerter_en'])?$_POST['schlagwoerter_en']:'-1');
	$abstract = (isset($_POST['abstract'])?$_POST['abstract']:'-1');
	$abstract_en = (isset($_POST['abstract_en'])?$_POST['abstract_en']:'-1');
	$seitenanzahl = (isset($_POST['seitenanzahl'])?$_POST['seitenanzahl']:'-1');
}

//$user='ie07m102';
$user = get_uid();
if($uid=='-1' || $uid!=$user)
{
	exit;		
}	
	
$datum_obj = new datum();
$error='';
$neu = (isset($_GET['neu'])?true:false);
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$htmlstr='';

if($command=='add')
{
	//zusätzliche Daten bearbeiten
	//Check der Eingabedaten
	if(strlen($kontrollschlagwoerter)<1)
	{
		$error=true;
	}
	if(strlen($abstract)<1)
	{
		$error=true;
	}
	if(strlen($abstract_en)<1)
	{
		$error=true;
	}
	if($seitenanzahl<1)
	{
		$error=true;
	}
	if(!$error)
	{	
		$qry_upd="UPDATE lehre.tbl_projektarbeit SET 
				seitenanzahl = '".$seitenanzahl."', 
				abgabedatum = now(),
				sprache = '".addslashes($sprache)."',  
				kontrollschlagwoerter = '".addslashes($kontrollschlagwoerter)."', 
				schlagwoerter_en = '".addslashes($schlagwoerter_en)."', 
				schlagwoerter = '".addslashes($schlagwoerter)."',  
				abstract = '".addslashes($abstract)."', 
				abstract_en = '".addslashes($abstract_en)."' 
				WHERE projektarbeit_id = '".$projektarbeit_id."'";
		if($result=$db->db_query($qry_upd))
		{
			$qry="UPDATE campus.tbl_paabgabe SET
							abgabedatum = now(),
							updatevon = '".$user."', 
							updateamum = now() 
							WHERE paabgabe_id='".$paabgabe_id."'";
						$result=$db->db_query($qry);
			$command="update";
		}
		else 
		{
			echo "<font color=\"#FF0000\">DB: Update fehlgeschlagen!</font><br>&nbsp;";
			$command='';
		}
	}
	else 
	{
		echo "<font color=\"#FF0000\">Dateneingabe unvollst&auml;ndig!</font><br>&nbsp;";
		$command='';
	}
}
if($command=="update" && $error!=true)
{
	//Dateiupload bearbeiten
	if ((isset($_FILES) and isset($_FILES['datei']) and ! $_FILES['datei']['error']))
	{
		if(strtoupper(end(explode(".", $_FILES['datei']['name'])))=='PDF')
		{
			if($paabgabetyp_kurzbz!='end')
			{
				//"normaler" Upload
				move_uploaded_file($_FILES['datei']['tmp_name'], PAABGABE_PATH.$paabgabe_id.'_'.$user.'.pdf');
				if(file_exists(PAABGABE_PATH.$paabgabe_id.'_'.$user.'.pdf'))
				{
					$qry="UPDATE campus.tbl_paabgabe SET
						abgabedatum = now(),
						updatevon = '".$user."', 
						updateamum = now() 
						WHERE paabgabe_id='".$paabgabe_id."'";
					$result=$db->db_query($qry);
				} 
				else 
				{
					echo "Upload nicht gefunden! Bitte wiederholen Sie den Fileupload.";
				}
			}
			else 
			{
				//Upload der Endabgabe - Eingabe der Zusatzdaten
				$command='add';
				if(!$error)
				{
					move_uploaded_file($_FILES['datei']['tmp_name'], PAABGABE_PATH.$paabgabe_id.'_'.$user.'.pdf');
				}
				if(file_exists(PAABGABE_PATH.$paabgabe_id.'_'.$user.'.pdf'))
				{
					/*$qry="UPDATE campus.tbl_paabgabe SET
						abgabedatum = now(),
						updatevon = '".$user."', 
						updateamum = now() 
						WHERE paabgabe_id='".$paabgabe_id."'";
					$result=$db->db_query($qry);*/

					echo '
					<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
					<html>
					<head>
					<title>PA-Abgabe</title>
					<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
					<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
					<meta http-equiv="content-type" content="text/html; charset=utf-8" />
					<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
					</head>
					<body class="Background_main"  style="background-color:#eeeeee;">
					<h3>Abgabe Studentenbereich - Zus&auml;tzliche Daten f&uuml;r die Abgabe</h3>';
					$qry_zd="SELECT * FROM lehre.tbl_projektarbeit WHERE projektarbeit_id='".$projektarbeit_id."'";
					$result_zd=@$db->db_query($qry_zd);
					$row_zd=@$db->db_fetch_object($result_zd);
					$htmlstr = "<div>Betreuer: <b>".$betreuer."</b><br>Titel: <b>".$titel."<b><br><br></div>\n";
					$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
					$htmlstr .= "<tr></tr>\n";
					$htmlstr .= "<form accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."' method='POST' name='".$projektarbeit_id."'>\n";
					$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$projektarbeit_id."'>\n";
					$htmlstr .= "<input type='hidden' name='paabgabe_id' value='".$paabgabe_id."'>\n";
					$htmlstr .= "<input type='hidden' name='paabgabetyp_kurzbz' value='".$paabgabetyp_kurzbz."'>\n";
					$htmlstr .= "<input type='hidden' name='abgabedatum' value='".$abgabedatum."'>\n";
					$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
					$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
					$htmlstr .= "<input type='hidden' name='betreuer' value='".$betreuer."'>\n";
					$htmlstr .= "<input type='hidden' name='command' value='add'>\n";
					$htmlstr .= "<tr>\n";
					$htmlstr .= "<td><b>Sprache der Arbeit:</b></td><td>";
					$sprache = @$db->db_query("SELECT sprache FROM tbl_sprache");
				    $num = $db->db_num_rows($sprache);
				    if ($num > 0) 
				    {
				        $htmlstr .= "<SELECT NAME=\"sprache\" SIZE=1> \n";
				        while ($mrow=@$db->db_fetch_object($sprache)) 
				        {
				            $htmlstr .= "<OPTION VALUE=\"$mrow->sprache\"";
				            if ($mrow->sprache == $sprache) 
	            			{
	            				$htmlstr .= " SELECTED";
	            			}
				            $htmlstr .= ">$mrow->sprache \n";
				        }
				        $htmlstr .= "</SELECT> \n";
				    }
				    $htmlstr .= "</td></tr>\n";
					$htmlstr .= "<tr><td width='30%'><b>Kontrollierte Schlagw&ouml;rter:*</b></td><td width='40%'><input  type='text' name='kontrollschlagwoerter'  id='kontrollschlagwoerter' value='".$kontrollschlagwoerter."' size='60' maxlength='150'></td>
						<td  width='30%' align='left'><input type='button' name='SWD' value='    SWD    ' onclick='window.open(\"abgabe_student_swd.php\")'></td></tr>\n";
					$htmlstr .= "<tr><td><b>Dt. Schlagw&ouml;rter:</b></td><td><input  type='text' name='schlagwoerter' value='".$schlagwoerter."' size='60' maxlength='150'></td></tr>\n";
					$htmlstr .= "<tr><td><b>Engl. Schlagw&ouml;rter:</b></td><td><input  type='text' name='schlagwoerter_en' value='".$schlagwoerter_en."' size='60' maxlength='150'></td></tr>\n";
					$htmlstr .= "<tr><td valign='top'><b>Abstract </b>(max. 5000 Zeichen):*</td><td><textarea name='abstract' cols='46'  rows='7'>$abstract</textarea></td></tr>\n";
					$htmlstr .= "<tr><td valign='top'><b>Abstract engl.</b>(max. 5000 Zeichen):*</td><td><textarea name='abstract_en' cols='46'  rows='7'>$abstract_en</textarea></td></tr>\n";
					$htmlstr .= "<tr><td><b>Seitenanzahl:*</b></td><td><input  type='text' name='seitenanzahl' value='".$seitenanzahl."' size='5' maxlength='4'></td></tr>\n";
					$htmlstr .= "<tr></tr><td>&nbsp;</td><tr><td style='font-size:70%'>* Pflichtfeld - bitte immer bef&uuml;llen</td></tr><tr><td>&nbsp;</td></tr>\n";
					$htmlstr .= "<tr><td><input type='submit' name='schick' value='abschicken'></td>";
					$htmlstr .= "</tr>\n";
					$htmlstr .= "</form>\n";
					$htmlstr .= "</table>\n";	
					$htmlstr .= "</body></html>";
					echo $htmlstr;
				} 
				else 
				{
					echo "Upload nicht gefunden! Bitte wiederholen Sie den Fileupload.";
				}
			}
			//E-Mail an 1.Begutachter
			if($bid!='' && $bid!=NULL)
			{
				$qry_betr="SELECT trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,  
					public.tbl_mitarbeiter.mitarbeiter_uid, anrede 
					FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
					LEFT JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id) 
					LEFT JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid) 
					WHERE public.tbl_person.person_id='$bid'";
				if(!$betr=$db->db_query($qry_betr))
				{
					echo "<font color=\"#FF0000\">Fehler beim Laden der Betreuer!</font><br>&nbsp;";
				}
				else
				{
					if($row_betr=$db->db_fetch_object($betr))
					{
						$qry_std="SELECT * FROM campus.vw_benutzer where uid='$uid'";
						if(!$result_std=$db->db_query($qry_std))
						{
							echo "<font color=\"#FF0000\">Datensatz konnte nicht gefunden werden!</font><br>&nbsp;";
						}
						else
						{
							$row_std=$db->db_fetch_object($result_std);
							
							$mail = new mail($row_betr->mitarbeiter_uid."@".DOMAIN, "vilesci@".DOMAIN, "Bachelor-/Diplomarbeitsbetreuung",
							"Sehr geehrte".($row_betr->anrede=="Herr"?"r":"")." ".$row_betr->anrede." ".$row_betr->first."!\n\n".($row_std->anrede)." ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost)." hat eine Abgabe vorgenommen.\n\n--------------------------------------------------------------------------\nDies ist ein vom Bachelor-/Diplomarbeitsabgabesystem generiertes Info-Mail\ncis->Mein CIS->Bachelor- und Diplomarbeitsabgabe\n--------------------------------------------------------------------------");
							$mail->setReplyTo($user."@".DOMAIN);
							if(!$mail->send())
							{
								echo "<font color=\"#FF0000\">Fehler beim Versenden des Mails an den (Erst-)Begutachter!</font><br>&nbsp;";	
							}
						}
					}
					else 
					{
						echo "<font color=\"#FF0000\">Betreuer nicht gefunden. Kein Mail verschickt!</font><br>&nbsp;";
					}
				}
			}
		}
		else 
		{
			echo "Upload ist keine pdf-Datei! Bitte wiederholen Sie den Fileupload.";
		}
	}
	$error=false;		
}
if($command!="add" && $command!="update") 
{
	$command="update";
}

if($uid==-1||$projektarbeit_id==-1||$titel==-1)
{
	//echo "Fehler bei der Daten&uuml;bergabe";
	exit;
}

if($command!="add")
{
	echo '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>PA-Abgabe</title>
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	<body class="Background_main"  style="background-color:#eeeeee;">
	<h3>Abgabe Studentenbereich</h3>';

	//Einlesen der Termine
	$qry="";	
	$htmlstr = "<div>Betreuer: <b>".$betreuer."</b><br>Titel: <b>".$titel."<b><br><br><b>Abgabetermine:</b></div>\n";
	$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
	$htmlstr .= "<tr></tr>\n";
	$qry="SELECT * FROM campus.tbl_paabgabe WHERE projektarbeit_id='".$projektarbeit_id."' AND paabgabetyp_kurzbz!='note' ORDER BY datum;";
	$htmlstr .= "<tr><td>fix</td><td>Datum </td><td>Abgabetyp</td><td>Kurzbeschreibung der Abgabe</td><td>abgegeben am</td><td colspan='2'>Dateiupload</td><td></td></tr>\n";
	$result=@$db->db_query($qry);
		while ($row=@$db->db_fetch_object($result))
		{
			$htmlstr .= "<form accept-charset='UTF-8' action='".$_SERVER['PHP_SELF']."' method='POST' enctype='multipart/form-data' name='".$row->projektarbeit_id."'>\n";
			$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$row->projektarbeit_id."'>\n";
			$htmlstr .= "<input type='hidden' name='paabgabe_id' value='".$row->paabgabe_id."'>\n";
			$htmlstr .= "<input type='hidden' name='paabgabetyp_kurzbz' value='".$row->paabgabetyp_kurzbz."'>\n";
			$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
			$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
			$htmlstr .= "<input type='hidden' name='betreuer' value='".$betreuer."'>\n";
			$htmlstr .= "<input type='hidden' name='command' value='update'>\n";
			$htmlstr .= "<input type='hidden' name='kontrollschlagwoerter' value='".$kontrollschlagwoerter."'>\n";
			$htmlstr .= "<input type='hidden' name='schlagwoerter' value='".$schlagwoerter."'>\n";
			$htmlstr .= "<input type='hidden' name='schlagwoerter_en' value='".$schlagwoerter_en."'>\n";
			$htmlstr .= "<input type='hidden' name='abstract' value='".$abstract."'>\n";
			$htmlstr .= "<input type='hidden' name='abstract_en' value='".$abstract_en."'>\n";
			$htmlstr .= "<input type='hidden' name='seitenanzahl' value='".$seitenanzahl."'>\n";
			$htmlstr .= "<input type='hidden' name='sprache' value='".$sprache."'>\n";
			$htmlstr .= "<input type='hidden' name='bid' value='".$bid."'>\n";
			$htmlstr .= "<tr id='".$row->projektarbeit_id."'>\n";
			if(!$row->abgabedatum)
			{
				if ($row->datum<=date('Y-m-d'))
				{
					//Termin vorbei - weiß auf rot
					$bgcol='#FF0000';
					$fcol='#FFFFFF';
				}
				elseif (($row->datum>date('Y-m-d')) && ($row->datum<date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")+11, date("Y")))))
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
					//Abgabe vor Termin - weiß auf grün
					$bgcol='#00FF00';
					$fcol='#FFFFFF';
				}
			}
			//$htmlstr .= "<td><input type='checkbox' name='fixtermin' ".($row->fixtermin=='t'?'checked=\"checked\"':'')." disabled>";
			if($row->fixtermin=='t')
			{
				$htmlstr .= "<td><img src='../../../skin/images/bullet_red.png' alt='J' title='Fixer Abgabetermin' border=0></td>";
			}
			else 
			{
				$htmlstr .= "<td><img src='../../../skin/images/bullet_green.png' alt='N' title='Variabler Abgabetermin' border=0></td>";
			}
			$htmlstr .= "		</td>\n";
			$htmlstr .= "		<td align='center' style='background-color:".$bgcol.";font-weight:bold; color:".$fcol."'>".$datum_obj->formatDatum($row->datum,'d.m.Y')."</td>\n";
			$qry_typ="SELECT * FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz='".$row->paabgabetyp_kurzbz."'";
			$result_typ=$db->db_query($qry_typ);
			$row_typ=$db->db_fetch_object($result_typ);
			$htmlstr .= "              <td>$row_typ->bezeichnung</td>\n";
			$htmlstr .= "		<td width='250'>$row->kurzbz</td>\n";		
			$htmlstr .= "		<td align='center'>".$datum_obj->formatDatum($row->abgabedatum,'d.m.Y')."</td>\n";		
			
			//Überschrittene Termine
			if($row->paabgabetyp_kurzbz=='enda')
			{
				//Bei Endabgabe kein Upload - Abgabe erfolgt im Sekretariat
				$htmlstr .= "		<td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td>";
			}
			else 
			{
				if($row->fixtermin=='t' && $row->datum>date('Y-m-d'))
				{
					//Termin ist überschritten - es wird kein Upload für diesen Termin mehr angeboten
					$htmlstr .= "		<td>Termin vorbei</td>";
				}
				else 
				{
					//Datei kann hochgeladen werden
					$htmlstr .= "		<td><input  type='file' name='datei' size='60' accept='application/pdf'></td>\n";
					$htmlstr .= "		<td><input type='submit' name='schick' value=' abgeben ' title='ausgew&auml;hlte Datei hochladen'></td>";
				}
			}
			$htmlstr .= "	</tr>\n";
			
			$htmlstr .= "</form>\n";
		}
		
	$command!="";
	$htmlstr .= "</table>\n";
	echo $htmlstr;
	echo '</body></html>';
}

?>