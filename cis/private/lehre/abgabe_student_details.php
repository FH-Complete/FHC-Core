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
require_once('../../../include/student.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/projektbetreuer.class.php');
require_once('../../../include/sancho.inc.php');

$anzeigesprache = getSprache();
$p = new phrasen($anzeigesprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

// Wenn die Datei zu gross ist, dann ist FILES und POST leer
if(empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD'])=='post')
{
	echo $p->t('abgabetool/dateiZuGross');
	exit;
}

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

$user = get_uid();
if($uid=='-1' || $projektarbeit_id=='-1')
{
	exit;
}

// Bei Studierenden pruefen ob die Uebergebene Projektarbeit zu dem Studierenden passt.
$projektarbeit_obj = new projektarbeit();

if(!$projektarbeit_obj->load($projektarbeit_id))
	die('Ungueltiger Eintrag');

$titel = $projektarbeit_obj->titel;
$person = new person();
$person->load($bid);
$betreuer = $person->titelpre.' '.$person->vorname.' '.$person->nachname.' '.$person->titelpost;

if($uid!=$user)
{
	$student = new student();
	if(!$student->load($uid))
		die($p->t('global/userNichtGefunden'));

	$stg_obj = new studiengang();
	if(!$stg_obj->load($student->studiengang_kz))
		die($p->t('global/fehlerBeimLesenAusDatenbank'));

	//Studentenansicht
	//Rechte Pruefen
	$allowed=false;

	//Berechtigung ueber das Berechtigungssystem
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if($rechte->isBerechtigt('lehre/abgabetool',$stg_obj->oe_kurzbz, 's'))
		$allowed=true;

	//oder Lektor mit Betreuung dieses Studenten
	$qry = "SELECT 1
			FROM
				lehre.tbl_projektarbeit
				JOIN lehre.tbl_projektbetreuer USING(projektarbeit_id)
				JOIN campus.vw_benutzer on(vw_benutzer.person_id=tbl_projektbetreuer.person_id)
			WHERE
				tbl_projektarbeit.student_uid=".$db->db_add_param($uid)." AND
				vw_benutzer.uid=".$db->db_add_param($user).";";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			$allowed=true;
		}
	}

	if(!$allowed)
	{
		die($p->t('abgabetool/keineBerechtigungStudentenansicht'));
	}
}
else
{
	if($uid!=$projektarbeit_obj->student_uid)
		die('Sie haben keine Berechtigung fuer diese Seite');
}

echo '<!DOCTYPE HTML>
	<html>
	<head>
		<title>'.$p->t('abgabetool/ueberschrift').'</title>
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<style>
		table.detail
		{
		    border-style:solid;
		    border-color:#777777;
		    border-width:1px;
		    width:100%;
		    padding:2px;

		}
		</style>
		<script type="text/javascript">
			function checkEid()
			{
				if(document.projektabgabe.eiderklaerung.checked == false)
				{
					alert("'.$p->t('abgabetool/erklaerungNichtAkzeptiert').'!");
					return false;
				}
					return true;
			}
		</script>

	</head>
	<body>';
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
	if(!isAbgabeOutOfDate($paabgabe_id))
	{
		//zusätzliche Daten bearbeiten
		//Check der Eingabedaten
		if(strlen($kontrollschlagwoerter)<1)
		{
			$error=true;
		}
		if(mb_strlen($kontrollschlagwoerter)>=150)
		{
			$kontrollschlagwoerter = mb_substr($kontrollschlagwoerter, 0, 146).'...';
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
					seitenanzahl = ".$db->db_add_param($seitenanzahl).",
					abgabedatum = now(),
					sprache = ".$db->db_add_param($sprache).",
					kontrollschlagwoerter = ".$db->db_add_param($kontrollschlagwoerter).",
					schlagwoerter_en = ".$db->db_add_param($schlagwoerter_en).",
					schlagwoerter = ".$db->db_add_param($schlagwoerter).",
					abstract = ".$db->db_add_param($abstract).",
					abstract_en = ".$db->db_add_param($abstract_en)."
					WHERE projektarbeit_id = ".$db->db_add_param($projektarbeit_id, FHC_INTEGER);

			if($result=$db->db_query($qry_upd))
			{
				$qry="UPDATE campus.tbl_paabgabe SET
								abgabedatum = now(),
								updatevon = ".$db->db_add_param($user).",
								updateamum = now()
								WHERE paabgabe_id=".$db->db_add_param($paabgabe_id, FHC_INTEGER);

				if($db->db_query($qry))
					echo '<font color="green">'.$p->t('global/erfolgreichgespeichert').'</font><br>';
				$command="update";
			}
			else
			{
				echo "<font color=\"#FF0000\">".$p->t('global/fehleraufgetreten')."</font><br>&nbsp;";
				$command='';
			}
		}
		else
		{
			echo "<font color=\"#FF0000\">".$p->t('abgabetool/dateneingabeUnvollstaendig')."</font><br>&nbsp;";
			$command='';
		}
	}
	else
	{
		echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerTerminVorbei').'</font>';
	}
}
if($command=="update" && $error!=true)
{
	if(!isAbgabeOutOfDate($paabgabe_id))
	{
		//Dateiupload bearbeiten
		if ((isset($_FILES) and isset($_FILES['datei']) and ! $_FILES['datei']['error']))
		{
			$extensions = explode(".", $_FILES['datei']['name']);
			if(strtoupper(end($extensions))=='PDF')
			{
				if($paabgabetyp_kurzbz!='end')
				{
					//"normaler" Upload
					move_uploaded_file($_FILES['datei']['tmp_name'], PAABGABE_PATH.$paabgabe_id.'_'.$uid.'.pdf');
					if(file_exists(PAABGABE_PATH.$paabgabe_id.'_'.$uid.'.pdf'))
					{
						exec('chmod 640 "'.PAABGABE_PATH.$paabgabe_id.'_'.$uid.'.pdf'.'"');

						$qry="UPDATE campus.tbl_paabgabe SET
							abgabedatum = now(),
							updatevon = ".$db->db_add_param($user).",
							updateamum = now()
							WHERE paabgabe_id=".$db->db_add_param($paabgabe_id, FHC_INTEGER);
						$result=$db->db_query($qry);
						echo $p->t('global/dateiErfolgreichHochgeladen');
					}
					else
					{
						echo $p->t('global/dateiNichtErfolgreichHochgeladen');
					}
				}
				else
				{
					//Upload der Endabgabe - Eingabe der Zusatzdaten
					$command='add';
					if(!$error)
					{
						move_uploaded_file($_FILES['datei']['tmp_name'], PAABGABE_PATH.$paabgabe_id.'_'.$uid.'.pdf');
					}
					if(file_exists(PAABGABE_PATH.$paabgabe_id.'_'.$uid.'.pdf'))
					{
						/*$qry="UPDATE campus.tbl_paabgabe SET
							abgabedatum = now(),
							updatevon = '".$user."',
							updateamum = now()
							WHERE paabgabe_id='".$paabgabe_id."'";
						$result=$db->db_query($qry);*/

						echo '<h2>'.$p->t('abgabetool/abgabeStudentenbereich').' - '.$p->t('abgabetool/abgabeZusatzdaten').'</h2>';

						$qry_zd="SELECT * FROM lehre.tbl_projektarbeit WHERE projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER);

						if($result_zd=@$db->db_query($qry_zd))
						{
							if($row_zd=@$db->db_fetch_object($result_zd))
							{
								$htmlstr = "<div>".$p->t('abgabetool/betreuer').": <b>".$db->convert_html_chars($betreuer)."</b><br>".$p->t('abgabetool/titel').": <b>".$db->convert_html_chars($titel)."<b><br><br></div>\n";
								$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
								$htmlstr .= "<tr></tr>\n";
								$htmlstr .= '<form accept-charset="UTF-8" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="POST" name="projektabgabe">'."\n";
								$htmlstr .= '<input type="hidden" name="projektarbeit_id" value="'.$db->convert_html_chars($projektarbeit_id).'">'."\n";
								$htmlstr .= '<input type="hidden" name="paabgabe_id" value="'.$db->convert_html_chars($paabgabe_id).'">'."\n";
								$htmlstr .= '<input type="hidden" name="paabgabetyp_kurzbz" value="'.$db->convert_html_chars($paabgabetyp_kurzbz).'">'."\n";
								$htmlstr .= '<input type="hidden" name="abgabedatum" value="'.$db->convert_html_chars($abgabedatum).'">'."\n";
								$htmlstr .= '<input type="hidden" name="titel" value="'.$db->convert_html_chars($titel).'">'."\n";
								$htmlstr .= '<input type="hidden" name="uid" value="'.$db->convert_html_chars($uid).'">'."\n";
								$htmlstr .= '<input type="hidden" name="betreuer" value="'.$db->convert_html_chars($betreuer).'">'."\n";
								$htmlstr .= '<input type="hidden" name="bid" value="'.$db->convert_html_chars($bid).'">'."\n";
								$htmlstr .= '<input type="hidden" name="command" value="add">'."\n";
								$htmlstr .= "<tr>\n";
								$htmlstr .= "<td><b>".$p->t('abgabetool/spracheDerArbeit').":</b></td><td>";
								$sprache = @$db->db_query("SELECT sprache FROM public.tbl_sprache");
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
								$htmlstr .= '<tr><td width="30%"><b>'.$p->t('abgabetool/kontrollierteSchlagwoerter').':*</b></td>
											<td width="40%"><input type="text" name="kontrollschlagwoerter" id="kontrollschlagwoerter" value="'.$db->convert_html_chars($kontrollschlagwoerter).'" size="60" maxlength="150"></td>
											<td  width="30%" align="left"><input type="button" name="SWD" value="    SWD    " onclick="window.open(\'swd.php\')"></td></tr>'."\n";
								$htmlstr .= '<tr><td><b>'.$p->t('abgabetool/deutscheSchlagwoerter').':</b></td>
											<td><input  type="text" name="schlagwoerter" value="'.$db->convert_html_chars($schlagwoerter).'" size="60" maxlength="150"></td></tr>'."\n";
								$htmlstr .= '<tr><td><b>'.$p->t('abgabetool/englischeSchlagwoerter').':</b></td>
											<td><input type="text" name="schlagwoerter_en" value="'.$db->convert_html_chars($schlagwoerter_en).'" size="60" maxlength="150"></td></tr>'."\n";
								$htmlstr .= '<tr><td valign="top"><b>'.$p->t('abgabetool/abstract').' </b>'.$p->t('abgabetool/maxZeichen').':*</td>
											<td><textarea name="abstract" cols="46"  rows="7">'.$db->convert_html_chars($abstract).'</textarea></td></tr>'."\n";
								$htmlstr .= '<tr><td valign="top"><b>'.$p->t('abgabetool/abstractEng').'</b>'.$p->t('abgabetool/maxZeichen').':*</td>
											<td><textarea name="abstract_en" cols="46"  rows="7">'.$db->convert_html_chars($abstract_en).'</textarea></td></tr>'."\n";
								$htmlstr .= '<tr><td><b>'.$p->t('abgabetool/seitenanzahl').':*</b></td>
											<td><input  type="text" name="seitenanzahl" value="'.$db->convert_html_chars($seitenanzahl).'" size="5" maxlength="4"></td></tr>'."\n";
								$htmlstr .="<tr><td>&nbsp;</td></tr>\n";
								$htmlstr .="<tr><td colspan='2'><p align='justify'>".$p->t('abgabetool/eidesstattlicheErklaerung')."</p></td><td></td></tr>\n";
								$htmlstr .= "<tr><td><b>".$p->t('abgabetool/gelesenUndAkzeptiert').":* <input type='checkbox' name='eiderklaerung'></b></td></tr>";
								$htmlstr .="<tr></tr><td>&nbsp;</td><tr><td style='font-size:70%'>* ".$p->t('abgabetool/pflichtfeld')."</td></tr>
											<tr><td>&nbsp;</td></tr>\n";
								$htmlstr .= "<tr><td><input type='submit' name='schick' onclick='return checkEid();' value='".$p->t('global/abschicken')."'></td>";
								$htmlstr .= "</tr>\n";
								$htmlstr .= "</form>\n";
								$htmlstr .= "</table>\n";
								$htmlstr .= "</body></html>";
								echo $htmlstr;
							}
							else
							{
								echo $p->t('global/dateiNichtErfolgreichHochgeladen');
							}
						}
						else
						{
							echo $p->t('global/dateiNichtErfolgreichHochgeladen');
						}
					}
					else
					{
						echo $p->t('global/dateiNichtErfolgreichHochgeladen');
					}
				}
				//E-Mail an 1.Begutachter und 2.Begutachter
				if($bid!='' && $bid!=NULL)
				{
					$qry_betr="SELECT distinct trim(COALESCE(titelpre,'')||' '||COALESCE(vorname,'')||' '||COALESCE(nachname,'')||' '||COALESCE(titelpost,'')) as first,
						public.tbl_mitarbeiter.mitarbeiter_uid, anrede
						FROM public.tbl_person JOIN lehre.tbl_projektbetreuer ON(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id)
						JOIN public.tbl_benutzer ON(public.tbl_benutzer.person_id=public.tbl_person.person_id)
						JOIN public.tbl_mitarbeiter ON(public.tbl_benutzer.uid=public.tbl_mitarbeiter.mitarbeiter_uid)
						WHERE public.tbl_person.person_id=".$db->db_add_param($bid, FHC_INTEGER);

					if(!$betr=$db->db_query($qry_betr))
					{
						echo "<font color=\"#FF0000\">".$p->t('global/fehlerBeimLesenAusDatenbank')."</font><br>&nbsp;";
					}
					else
					{
						if($row_betr=$db->db_fetch_object($betr))
						{
							$qry_std="SELECT * FROM campus.vw_benutzer where uid=".$db->db_add_param($uid);
							if(!$result_std=$db->db_query($qry_std))
							{
								echo "<font color=\"#FF0000\">".$p->t('global/fehlerBeimLesenAusDatenbank')."</font><br>&nbsp;";
							}
							else
							{
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

								$row_std=$db->db_fetch_object($result_std);

								// 1. Begutachter mail ohne Token
								$mail_baselink = APP_ROOT."index.ci.php/extensions/FHC-Core-Projektarbeitsbeurteilung/Projektarbeitsbeurteilung";
								$mail_fulllink = "$mail_baselink?projektarbeit_id=".$projektarbeit_id."&uid=".$row_std->uid;
								$maildata = array();
								$maildata['geehrt'] = "geehrte".($row_betr->anrede=="Herr"?"r":"");
								$maildata['anrede'] = $row_betr->anrede;
								$maildata['betreuer_voller_name'] = $row_betr->first;
								$maildata['student_anrede'] = $row_std->anrede;
								$maildata['student_voller_name'] = trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost);
								$maildata['parbeituebersichtlink'] = "<p><a href='".APP_ROOT."cis/private/lehre/abgabe_lektor_frameset.html'>Zur Projektarbeitsübersicht</a></p>";
								$maildata['bewertunglink'] = $num_rows_sem >= 1 ? "<p><a href='$mail_fulllink'>Zur Beurteilung der Arbeit</a></p>" : "";
								$maildata['token'] = "";

								$mailres = sendSanchoMail(
									'ParbeitsbeurteilungEndupload',
									$maildata,
									$row_betr->mitarbeiter_uid."@".DOMAIN,
									"Bachelor-/Masterarbeitsbetreuung",
									'sancho_header_min_bw.jpg',
									'sancho_footer_min_bw.jpg',
									$user."@".DOMAIN);

								if(!$mailres)
								{
									echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerMailBegutachter')."</font><br>&nbsp;";
								}

								// 2. Begutachter mail, wenn Endabgabe, mit Token wenn extern
								if ($paabgabetyp_kurzbz == 'end')
								{
									$projektbetreuer = new projektbetreuer();
									$zweitbetr = $projektbetreuer->getZweitbegutachterWithToken($bid, $projektarbeit_id, $row_std->uid);

									if ($zweitbetr)
									{
										$tokenGenRes = $projektbetreuer->generateZweitbegutachterToken($zweitbetr->person_id, $projektarbeit_id);

										if (!$tokenGenRes)
											echo "<font color=\"#FF0000\">" . $p->t('abgabetool/fehlerMailZweitBegutachter') . "</font><br>&nbsp;";

										$zweitbetr = $projektbetreuer->getZweitbegutachterWithToken($bid, $projektarbeit_id, $row_std->uid);

										if (!$zweitbetr)
											echo "<font color=\"#FF0000\">" . $p->t('abgabetool/fehlerMailZweitBegutachter') . "</font><br>&nbsp;";

										$intern = isset($zweitbetr->uid);
										$mail_link = $intern ? $mail_fulllink : $mail_baselink;

										$zweitbetmaildata = array();
										$zweitbetmaildata['geehrt'] = "geehrte" . ($zweitbetr->anrede == "Herr" ? "r" : "");
										$zweitbetmaildata['anrede'] = $zweitbetr->anrede;
										$zweitbetmaildata['betreuer_voller_name'] = $zweitbetr->voller_name;
										$zweitbetmaildata['student_anrede'] = $maildata['student_anrede'];
										$zweitbetmaildata['student_voller_name'] = $maildata['student_voller_name'];
										$zweitbetmaildata['parbeituebersichtlink'] = $intern ? $maildata['parbeituebersichtlink'] : "";
										$zweitbetmaildata['bewertunglink'] = $num_rows_sem >= 1 ? "<p><a href='$mail_link'>Zur Beurteilung der Arbeit</a></p>" : "";
										$zweitbetmaildata['token'] = $num_rows_sem >= 1 && isset($zweitbetr->zugangstoken) && !$intern ? "<p>Zugangstoken: " . $zweitbetr->zugangstoken . "</p>" : "";

										$mailres = sendSanchoMail(
											'ParbeitsbeurteilungEndupload',
											$zweitbetmaildata,
											$zweitbetr->email,
											"Masterarbeitsbetreuung",
											'sancho_header_min_bw.jpg',
											'sancho_footer_min_bw.jpg',
											$user . "@" . DOMAIN
										);

										if (!$mailres)
										{
											echo "<font color=\"#FF0000\">" . $p->t('abgabetool/fehlerMailZweitBegutachter') . "</font><br>&nbsp;";
										}
									}
								}
							}
						}
						else
						{
							echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerBetreuerNichtGefundenKeinMail')."</font><br>&nbsp;";
						}
					}
				}
			}
			else
			{
				echo $p->t('abgabetool/keinPDF');
			}
		}
	}
	else
	{
		echo "<font color=\"#FF0000\">".$p->t('abgabetool/fehlerTerminVorbei').'</font>';
	}

	$error=false;
}
if($command!="add" && $command!="update")
{
	$command="update";
}

if($uid==-1||$projektarbeit_id==-1)
{
	//echo "Fehler bei der Daten&uuml;bergabe";
	exit;
}

if($command!="add")
{
	echo '<h2>'.$p->t('abgabetool/abgabeStudentenbereich').'</h2>';

	//Einlesen der Termine
	$qry="";
	$htmlstr = "<div>".$p->t('abgabetool/betreuer').": <b>".$db->convert_html_chars($betreuer)."</b><br>".$p->t('abgabetool/titel').": <b>".$db->convert_html_chars($titel)."<b><br><br><b>".$p->t('abgabetool/abgabetermine').":</b></div>\n";
	$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
	$htmlstr .= "<tr></tr>\n";

	$qry="SELECT * FROM campus.tbl_paabgabe WHERE projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER)." AND paabgabetyp_kurzbz!='note' ORDER BY datum;";

	$htmlstr .= "<tr><td>".$p->t('abgabetool/fix')."</td><td>".$p->t('abgabetool/datum')." </td><td>".$p->t('abgabetool/abgabetyp')."</td><td>".$p->t('abgabetool/beschreibungAbgabe')."</td><td>".$p->t('abgabetool/abgegebenAm')."</td><td colspan='2'>".$p->t('abgabetool/dateiupload')."(<b>".$p->t('abgabetool/nurPDF')."</b>)</td><td></td></tr>\n";
	$result=@$db->db_query($qry);
		while ($row=@$db->db_fetch_object($result))
		{
			$htmlstr .= '<form accept-charset="UTF-8" action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="POST" enctype="multipart/form-data" name="'.$db->convert_html_chars($row->projektarbeit_id).'">'."\n";
			$htmlstr .= '<input type="hidden" name="projektarbeit_id" value="'.$db->convert_html_chars($row->projektarbeit_id).'">'."\n";
			$htmlstr .= '<input type="hidden" name="paabgabe_id" value="'.$db->convert_html_chars($row->paabgabe_id).'">'."\n";
			$htmlstr .= '<input type="hidden" name="paabgabetyp_kurzbz" value="'.$db->convert_html_chars($row->paabgabetyp_kurzbz).'">'."\n";
			$htmlstr .= '<input type="hidden" name="titel" value="'.$db->convert_html_chars($titel).'">'."\n";
			$htmlstr .= '<input type="hidden" name="uid" value="'.$db->convert_html_chars($uid).'">'."\n";
			$htmlstr .= '<input type="hidden" name="betreuer" value="'.$db->convert_html_chars($betreuer).'">'."\n";
			$htmlstr .= '<input type="hidden" name="command" value="update">'."\n";
			$htmlstr .= '<input type="hidden" name="kontrollschlagwoerter" value="'.$db->convert_html_chars($kontrollschlagwoerter).'">'."\n";
			$htmlstr .= '<input type="hidden" name="schlagwoerter" value="'.$db->convert_html_chars($schlagwoerter).'">'."\n";
			$htmlstr .= '<input type="hidden" name="schlagwoerter_en" value="'.$db->convert_html_chars($schlagwoerter_en).'">'."\n";
			$htmlstr .= '<input type="hidden" name="abstract" value="'.$db->convert_html_chars($abstract).'">'."\n";
			$htmlstr .= '<input type="hidden" name="abstract_en" value="'.$db->convert_html_chars($abstract_en).'">'."\n";
			$htmlstr .= '<input type="hidden" name="seitenanzahl" value="'.$db->convert_html_chars($seitenanzahl).'">'."\n";
			$htmlstr .= '<input type="hidden" name="sprache" value="'.$db->convert_html_chars($sprache).'">'."\n";
			$htmlstr .= '<input type="hidden" name="bid" value="'.$db->convert_html_chars($bid).'">'."\n";
			$htmlstr .= '<tr id="'.$db->convert_html_chars($row->projektarbeit_id).'">'."\n";
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
			//$htmlstr .= "<td><input type='checkbox' name='fixtermin' ".($row->fixtermin=='t'?'checked=\"checked\"':'')." disabled>";
			if($row->fixtermin=='t')
			{
				$htmlstr .= "<td><img src='../../../skin/images/bullet_red.png' alt='J' title='".$p->t('abgabetool/fixerAbgabetermin')."' border=0></td>";
			}
			else
			{
				$htmlstr .= "<td><img src='../../../skin/images/bullet_green.png' alt='N' title='".$p->t('abgabetool/variablerAbgabetermin')."' border=0></td>";
			}
			$htmlstr .= "		</td>\n";
			$htmlstr .= "		<td align='center' style='background-color:".$bgcol.";font-weight:bold; color:".$fcol."'>".$datum_obj->formatDatum($row->datum,'d.m.Y')."</td>\n";

			$qry_typ="SELECT * FROM campus.tbl_paabgabetyp WHERE paabgabetyp_kurzbz=".$db->db_add_param($row->paabgabetyp_kurzbz);

			$result_typ=$db->db_query($qry_typ);
			$row_typ=$db->db_fetch_object($result_typ);
			$htmlstr .= "              <td>$row_typ->bezeichnung</td>\n";
			$htmlstr .= "		<td width='250'>$row->kurzbz</td>\n";
			$htmlstr .= "		<td align='center'>".$datum_obj->formatDatum($row->abgabedatum,'d.m.Y');
			if($row->abgabedatum!='')
				$htmlstr .= ' <a href="abgabe_student_file.php?abgabe_id='.$row->paabgabe_id.'&student_uid='.$uid.'" target="_blank" title="'.$p->t('abgabetool/downloadProjektarbeit').'"><img src="../../../skin/images/pdfpic.gif"></a>';
			$htmlstr .= "</td>\n";

			//Überschrittene Termine
			if($row->paabgabetyp_kurzbz=='enda')
			{
				//Bei Endabgabe kein Upload - Abgabe erfolgt im Sekretariat
				$htmlstr .= "		<td>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;</td>";
			}
			else
			{
				if($row->fixtermin=='t' && $row->datum<date('Y-m-d'))
				{
					//Termin ist überschritten - es wird kein Upload für diesen Termin mehr angeboten
					$htmlstr .= "		<td>&nbsp;&nbsp;</td><td> ".$p->t('abgabetool/terminVorbei')."</td>";
				}
				else
				{
					//Datei kann hochgeladen werden
					$htmlstr .= "		<td><input  type='file' name='datei' size='60' accept='application/pdf'></td>\n";
					$htmlstr .= "		<td><input type='submit' name='schick' value='".$p->t('global/abschicken')."'></td>";
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

function isAbgabeOutOfDate($paabgabe_id)
{
	$db = new basis_db();
	$qry = "SELECT * FROM campus.tbl_paabgabe WHERE paabgabe_id=".$db->db_add_param($paabgabe_id);
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			if($row->datum < date('Y-m-d') && $row->fixtermin=='t')
			{
				return true;
			}
			else
				return false;
		}
	}
	return false;
}
?>
