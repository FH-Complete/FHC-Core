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
 * 			für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/


require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');		
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/phrasen.class.php');

$anzeigesprache = getSprache();
$p = new phrasen($anzeigesprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if(!isset($_POST['uid']))
{
	$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
	$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
	$titel = (isset($_GET['titel'])?$_GET['titel']:'-1');
	$betreuer = (isset($_GET['betreuer'])?$_GET['betreuer']:'-1');

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
$datum_obj = new datum();
$error='';
$neu = (isset($_GET['neu'])?true:false);
$stg_arr = array();
$error = false;
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$htmlstr='';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>'.$p->t('abgabetool/abgabeZusatzdaten').'</title>
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
	
	<body class="Background_main"  style="background-color:#eeeeee;">
	<h3>'.$p->t('abgabetool/abgabeZusatzdaten').'</h3>';
	$qry_zd="SELECT * FROM lehre.tbl_projektarbeit WHERE projektarbeit_id=".$db->db_add_param($projektarbeit_id, FHC_INTEGER);
	$result_zd=@$db->db_query($qry_zd);
	if ($row_zd=@$db->db_fetch_object($result_zd))
	{
		echo '<div>'.$p->t('abgabetool/student').': <b>'.$db->convert_html_chars($uid).'</b>
			<br>'.$p->t('abgabetool/titel').': <b>'.$db->convert_html_chars($row_zd->titel).'<b>
			<br><br></div>
			<table class="detail" style="padding-top:10px;">
			<tr></tr>
			<tr>
				<td><b>'.$p->t('abgabetool/spracheDerArbeit').':</b></td>
				<td><input  type="text" name="sprache" id="sprache" value="'.$db->convert_html_chars($row_zd->sprache).'" size="10" maxlength="8" readonly="readonly"></td>
			</tr>
			<tr>
				<td width="30%"><b>'.$p->t('abgabetool/kontrollierteSchlagwoerter').':</b></td>
				<td width="40%"><input type="text" name="kontrollschlagwoerter" id="kontrollschlagwoerter" value="'.$db->convert_html_chars($row_zd->kontrollschlagwoerter).'" size="60" maxlength="150" readonly="readonly"></td>
			</tr>
			<tr>
				<td><b>'.$p->t('abgabetool/deutscheSchlagwoerter').':* </b></td>
				<td><input type="text" name="schlagwoerter" value="'.$db->convert_html_chars($row_zd->schlagwoerter).'" size="60" maxlength="150" readonly="readonly"></td>
			</tr>
			<tr>
				<td><b>'.$p->t('abgabetool/englischeSchlagwoerter').':* </b></td>
				<td><input type="text" name="schlagwoerter_en" value="'.$db->convert_html_chars($row_zd->schlagwoerter_en).'" size="60" maxlength="150" readonly="readonly"></td>
			</tr>
			<tr>
				<td valign="top"><b>'.$p->t('abgabetool/abstract').' </b>'.$p->t('abgabetool/maxZeichen').':*</td>
				<td><textarea name="abstract" cols="46" rows="7" readonly="readonly">'.$db->convert_html_chars($row_zd->abstract).'</textarea></td>
			</tr>
			<tr>
				<td valign="top"><b>'.$p->t('abgabetool/abstractEng').' </b>'.$p->t('abgabetool/maxZeichen').':*</td>
				<td><textarea name="abstract_en" cols="46" rows="7" readonly="readonly">'.$db->convert_html_chars($row_zd->abstract_en).'</textarea></td>
			</tr>
			<tr>
				<td><b>'.$p->t('abgabetool/seitenanzahl').':*</b></td>
				<td><input type="text" name="seitenanzahl" value="'.$db->convert_html_chars($row_zd->seitenanzahl).'" size="5" maxlength="4" readonly="readonly"></td>
			</tr>
			</tr>
			</table>
			<table>
				<tr><td>&nbsp;</td></tr>
				<tr><td style="font-size:70%">* '.$p->t('abgabetool/pflichtfeld').'</td></tr>
				<tr><td>&nbsp;</td></tr>
			</table>';
	}

	echo '</body></html>';

?>
