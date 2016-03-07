<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/konto.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else
	$stsem = '';

$uid=get_uid();

if(isset($_GET['uid']))
{
	// Administratoren duerfen die UID als Parameter uebergeben um die Notenliste
	// von anderen Personen anzuzeigen

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
    {
		$uid = $_GET['uid'];
        $getParam = "&uid=" . $uid;
    }
    else
        $getParam = "";
}
else
	$getParam='';

$student_studiengang = new student();
$student_studiengang->load($uid);
$xsl_stg_kz = $student_studiengang->studiengang_kz;

$stg = '';

if(!($erg=$db->db_query("SELECT * FROM campus.vw_benutzer WHERE uid='".addslashes($uid)."'")))
	die($db->db_last_error());
$num_rows=$db->db_num_rows($erg);
if ($num_rows==1)
{
	$vorname=$db->db_result($erg,0,"vorname");
	$vornamen=$db->db_result($erg,0,"vornamen");
	$nachname=$db->db_result($erg,0,"nachname");
	$gebdatum=$db->db_result($erg,0,"gebdatum");
	$gebort=$db->db_result($erg,0,"gebort");
	$titelpre=$db->db_result($erg,0,"titelpre");
	$titelpost=$db->db_result($erg,0,"titelpost");
	$email=$db->db_result($erg,0,"uid").'@'.DOMAIN;
	$email_alias=$db->db_result($erg,0,"alias");
	$hp=$db->db_result($erg,0,"homepage");
}
if(!($erg_stud=$db->db_query("SELECT studiengang_kz, semester, verband, gruppe, matrikelnr, typ::varchar(1) || kurzbz AS stgkz, tbl_studiengang.bezeichnung AS stgbz FROM public.tbl_student JOIN public.tbl_studiengang USING(studiengang_kz) WHERE student_uid='".addslashes($uid)."'")))
	die($db->db_last_error());
$stud_num_rows=$db->db_num_rows($erg_stud);

if ($stud_num_rows==1)
{
	$stg=$db->db_result($erg_stud,0,"studiengang_kz");
	$stgbez=$db->db_result($erg_stud,0,"stgbz");
	$stgkz=$db->db_result($erg_stud,0,"stgkz");
	$semester=$db->db_result($erg_stud,0,"semester");
	$verband=$db->db_result($erg_stud,0,"verband");
	$gruppe=$db->db_result($erg_stud,0,"gruppe");
	$matrikelnr=$db->db_result($erg_stud,0,"matrikelnr");
}
if(!($erg_lekt=$db->db_query("SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='".addslashes($uid)."'")))
	die($db->db_last_error());
$lekt_num_rows=$db->db_num_rows($erg_lekt);
if ($lekt_num_rows==1)
{
	$row=$db->db_fetch_object($erg_lekt,0);
	$kurzbz=$row->kurzbz;
	$tel=$row->telefonklappe;
}

// Mail-Groups
if(!($erg_mg=$db->db_query("SELECT gruppe_kurzbz, beschreibung FROM campus.vw_persongruppe WHERE mailgrp AND uid='".addslashes($uid)."' ORDER BY gruppe_kurzbz")))
	die($db->db_last_error());
$nr_mg=$db->db_num_rows($erg_mg);

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>'.$p->t('tools/dokumente').'</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location=\'" + selObj.options[selObj.selectedIndex].value + "'.$getParam.'\'");

	  if(restore)
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
</script>
</head>

<body>
<h1>'.$p->t('tools/dokumente').'</h1>';


//Aktuelles Studiensemester oder gewaehltes Studiensemester
$stsem_obj = new studiensemester();
	if($stsem=='')
		$stsem = $stsem_obj->getaktorNext();

$stsem_obj->getAll();

echo "<br><hr>";
echo $p->t('global/studiensemester')."</b> <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">";
	foreach ($stsem_obj->studiensemester as $semrow)
	{
		if($stsem == $semrow->studiensemester_kurzbz)
			echo "<OPTION value='dokumente.php?stsem=$semrow->studiensemester_kurzbz' selected>$semrow->studiensemester_kurzbz</OPTION>";
		else
			echo "<OPTION value='dokumente.php?stsem=$semrow->studiensemester_kurzbz'>$semrow->studiensemester_kurzbz</OPTION>";
	}
	echo "</SELECT><br />";

$konto = new konto();

$buchungstypen = array();
if(defined("CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN"))
{
    $buchungstypen = unserialize (CIS_DOKUMENTE_STUDIENBEITRAG_TYPEN);
}

$stsem_zahlung = $konto->getLastStSemBuchungstypen($uid, $buchungstypen, $stsem);
if ($stsem_zahlung != FALSE && $stsem == $stsem_zahlung)
{
	echo "<a href='../pdfExport.php?xsl=Inskription&xml=student.rdf.php&ss=".$stsem."&uid=".$uid."&xsl_stg_kz=".$xsl_stg_kz."'>".$p->t('tools/inskriptionsbestaetigung')."</a>";
	echo ' - '.$p->t('tools/studienbeitragFuerSSBezahlt',array($stsem));
}
else
	echo $p->t('tools/inskriptionsbestaetigung')." - ".$p->t('tools/studienbeitragFuerSSNochNichtBezahlt',array($stsem));

echo "<hr>";

if(defined('CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN') && CIS_DOKUMENTE_STUDIENBUCHLBATT_DRUCKEN)
{
    if ($stsem_zahlung != FALSE && $stsem == $stsem_zahlung)
    {
	    echo "<a href='../pdfExport.php?xsl=Studienblatt&xml=studienblatt.xml.php&ss=".$stsem."&uid=".$uid."'>".$p->t('tools/studienbuchblatt')."</a>";
	    echo ' - '.$p->t('tools/studienbeitragFuerSSBezahlt',array($stsem));
    }
    else
	    echo $p->t('tools/studienbuchblatt')." - ".$p->t('tools/studienbeitragFuerSSNochNichtBezahlt',array($stsem));

    echo "<hr>";
}

if(defined('CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN') && CIS_DOKUMENTE_STUDIENERFOLGSBESTAETIGUNG_DRUCKEN)
{
	echo "<a href='studienerfolgsbestaetigung.php?".$getParam."' class='Item'>".$p->t('tools/studienerfolgsbestaetigung')." Deutsch</a><br>";
	echo "<a href='studienerfolgsbestaetigung.php?lang=en".$getParam."' class='Item'>".$p->t('tools/studienerfolgsbestaetigung')." Englisch</a>";
	echo "<hr>";
}
echo "<br>";

echo '</body>
</html>
';
?>