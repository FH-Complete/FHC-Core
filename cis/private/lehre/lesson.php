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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/moodle.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/lehre_tools.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if (!$user=get_uid())
	die($p->t('global/nichtAngemeldet'));

// Init
$user_is_allowed_to_upload=false;

// Plausib
if(check_lektor($user))
	$is_lector=true;
else
	$is_lector=false;
	   
if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');
	
$lv_obj = new lehrveranstaltung();
$lv_obj->load($lvid);
$lv=$lv_obj;

$studiengang_kz = $lv->studiengang_kz;
$semester = $lv->semester;
$short = $lv->lehreverzeichnis;

$stg_obj = new studiengang();
$stg_obj->load($lv->studiengang_kz);

$kurzbz = $stg_obj->kuerzel;

$short_name = $lv->bezeichnung;

$short_short_name = $lv->lehreverzeichnis;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$sprache = getSprache();
$p = new phrasen($sprache);

//Handbuch ausliefern
if (isset($_GET["handbuch"])){
	$filename = BENOTUNGSTOOL_PATH."handbuch_benotungstool.pdf";
	header('Content-Type: application/octet-stream');
	header('Content-disposition: attachment; filename="handbuch_benotungstool.pdf"');
	readfile($filename);
	exit;
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../include/js/flexcroll.js"></script>
	<link href="../../../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
	.transparent {
	    filter:alpha(opacity=90);
	    -moz-opacity:0.9;
	    -khtml-opacity: 0.9;
	    opacity: 0.9;
	}
	</style> 

	<script language="JavaScript">
	function showSemPlanHelp(){
		document.getElementById("semplanhelp").style.visibility = "visible";
	}
	function hideSemPlanHelp(){
		document.getElementById("semplanhelp").style.visibility = "hidden";
	}

	</script>   
</head>
<body>
<div class="flexcroll" style="outline: none;">
<div id="semplanhelp" style="position:absolute; top:200px; left:200px; width:500px; height:250px; background-color:#cccccc; visibility:hidden; border-style:solid; border-width:1px; border-color:#333333;" class="transparent">
<table width="100%">
<tr><td valign="top"><h2>&nbsp;Erstellung des Semesterplanes</h2></td><td align="right" valign="top"><a href="#" onclick="hideSemPlanHelp();">X</a>&nbsp;</td></tr>
<tr>
<td colspan="2">
<ol style="font-size:8pt;">
	<li><?php echo $p->t('semesterplan/speichernSieDieVorlage');?>.</li>
	<li><?php echo $p->t('semesterplan/oeffnenSieDieGespeicherteDatei');?>.</li>
	<li><?php echo $p->t('semesterplan/erstellenSieIhrenSemesterplan');?>.</li>
	<li><?php echo $p->t('semesterplan/speichernSieDasDokument');?><br><?php echo $p->t('semesterplan/inMSWord');?></li>
	<li><?php echo $p->t('semesterplan/ladenSieDieDateiHoch');?>.</li>
	<li><?php echo $p->t('semesterplan/fertig');?>!</li>
</ol>
</td>
</tr>
<tr><td colspan="2" align="center"><a href="#" onClick="hideSemPlanHelp();">schlie&szlig;en</a></td></tr>
</table>
</div>
<table class="tabcontent" height="100%" id="inhalt">
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td><h1>
		<?php
		echo $lv_obj->bezeichnung_arr[$sprache].' '.$lv_obj->lehrform_kurzbz.' / '.$kurzbz.'-'.$semester.' '.$lv_obj->orgform_kurzbz;

		$qry = "SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) 
				WHERE lehrveranstaltung_id='".addslashes($lvid)."' ORDER BY ende DESC LIMIT 1";
		$stsem = new studiensemester();
		if($lv->studiengang_kz==0)
			$angezeigtes_stsem = $stsem->getNearest();
		else
			$angezeigtes_stsem = $stsem->getNearest($semester);
						
	    echo "&nbsp;($angezeigtes_stsem)";
	    echo '</h1></td>
              </tr>
              <tr>
              <td>&nbsp;</td>
              <td>';

	    $qry = "SELECT * FROM (SELECT distinct on(uid) vorname, nachname, tbl_benutzer.uid as uid, 
	    			CASE WHEN lehrfunktion_kurzbz='LV-Leitung' THEN true ELSE false END as lvleiter 
	    		FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person 
	    		WHERE 
	    			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND 
	    			tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND 
	    			tbl_person.person_id=tbl_benutzer.person_id AND 
	    			lehrveranstaltung_id='".addslashes($lvid)."' AND 
	    			tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND 
	    			tbl_benutzer.aktiv=true AND tbl_person.aktiv=true AND 
	    			studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."' 
	    		ORDER BY uid, lvleiter desc) as a ORDER BY lvleiter desc, nachname, vorname";

		if(!$result = $db->db_query($qry))
		{
			echo $p->t('lehre/keineLektorenZugeordnet');
		}
		else
		{
			$num_rows_result = $db->db_num_rows($result);

			if(!($num_rows_result > 0))
			{
				echo $p->t('lehre/keineLektorenZugeordnet');
			}
			else
			{
				$i=0;
				while($row_lector = $db->db_fetch_object($result))
				{
					$i++;
					if($user==$row_lector->uid)
						$user_is_allowed_to_upload=true;

					if($row_lector->lvleiter=='t')
						$style='style="font-weight: bold"';
					else 
						$style='';
					echo '<a href="mailto:'.$row_lector->uid.'@'.DOMAIN.'" '.$style.'>'.$row_lector->vorname.' '.$row_lector->nachname.'</a>';
					if($i!=$num_rows_result)
						echo ', ';
				}
			}
		}

				//Berechtigungen auf Fachbereichsebene
	  $qry = "SELECT 
	  			distinct fachbereich_kurzbz, tbl_lehrveranstaltung.studiengang_kz, tbl_fachbereich.oe_kurzbz 
	  		FROM 
	  			lehre.tbl_lehrveranstaltung 
	  			JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id) 
	  			JOIN lehre.tbl_lehrfach USING(lehrfach_id)
	  			JOIN public.tbl_fachbereich USING(fachbereich_kurzbz) 
	  		WHERE lehrveranstaltung_id='".addslashes($lvid)."'";

	  if(isset($angezeigtes_stsem) && $angezeigtes_stsem!='')
	  	$qry .= " AND studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."'";

	  if($result = $db->db_query($qry))
	  {
	  	while($row = $db->db_fetch_object($result))
	  	{
	  		if($rechte->isBerechtigt('lehre',$row->oe_kurzbz) || $rechte->isBerechtigt('assistenz',$stg_obj->oe_kurzbz))
	  			$user_is_allowed_to_upload=true;
	  	}
	  }
		?></td>
	</tr>
	<tr>
		<td >&nbsp;</td>
		<td >&nbsp;</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
		<td >
		<?php
		require_once('../../../include/'.EXT_FKT_PATH.'/cis_menu_lv.inc.php');
		?>
		</td>
		<td class="tdwidth30">&nbsp;</td>
	</tr>
</table>
</div>
</body>
</html>
