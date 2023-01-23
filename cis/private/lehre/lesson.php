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
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/lehre_tools.class.php');
require_once('../../../include/lvangebot.class.php');
require_once('../../../include/benutzergruppe.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/variable.class.php');
require_once('../../../include/vertrag.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if (!$user=get_uid())
	die($p->t('global/nichtAngemeldet'));

// Init
$user_is_allowed_to_upload=false;
$lektor_der_lv=false;

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

if(isset($_GET['studiensemester_kurzbz']))
	// Uses urlencode to avoid XSS issues
	$studiensemester_kurzbz = urlencode($_GET['studiensemester_kurzbz']);
else
	$studiensemester_kurzbz='';

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
?><!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../skin/jquery.css" rel="stylesheet" type="text/css"/>
	<link href="../../../vendor/components/jqueryui/themes/base/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>

<?php
// Angezeigtes Studiensemester ermitteln
// Wenn ein Studiensemester uebergeben wird, wird das uebergebene angezeigt
$stsem = new studiensemester();
if($studiensemester_kurzbz!='')
	$angezeigtes_stsem=$studiensemester_kurzbz;
else
{
	if($lv->studiengang_kz==0 || (defined('CIS_LEHRVERANSTALTUNG_AKTUELLES_STUDIENSEMESTER_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_AKTUELLES_STUDIENSEMESTER_ANZEIGEN))
		$angezeigtes_stsem = $stsem->getNearest();
	else
	{
		// wenn im nahegelegensten/aktuellen Studiensemester eine Lehreinheit angelegt ist dann diese anzeigen
		$lehreinheit = new lehreinheit();
		if($lehreinheit->load_lehreinheiten($lvid, $stsem->getNearest()) && count($lehreinheit->lehreinheiten)>0)
		{
			$lehreinheit_found=false;
			foreach($lehreinheit->lehreinheiten as $row_lehreinheit)
			{
				if($row_lehreinheit->lehre)
				{
					$angezeigtes_stsem = $stsem->getNearest();
					$lehreinheit_found=true;
					break;
				}
			}
			if($lehreinheit_found==false)
			{
				$angezeigtes_stsem = $stsem->getNearest($semester);
			}
		}
		else
		{
			// fuer ungerade semester das naeheste WS fuer gerade semester das naeheste SS anzeigen
			$angezeigtes_stsem = $stsem->getNearest($semester);
		}
	}
}

// ADDONS laden
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
	if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
		echo '<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php" ></script>';
}

// Wenn Seite fertig geladen ist Addons aufrufen
echo '
<script>
$( document ).ready(function()
{
	if(typeof addon  !== \'undefined\')
	{
		for(i in addon)
		{
			addon[i].init("cis/private/lehre/lesson.php", {uid:\''.$user.'\',lvid:\''.$lvid.'\',studiensemester_kurzbz:\''.$angezeigtes_stsem.'\'});
		}
	}
});
</script>
';

?>
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
<div id="semplanhelp" style="position:absolute; top:200px; left:200px; width:500px; height:250px; background-color:#cccccc; visibility:hidden; border-style:solid; border-width:1px; border-color:#333333;" class="transparent">
<table width="100%">
<tr><td valign="top"><h2>&nbsp;<?php echo $p->t('semesterplan/ErstellungDesSemesterplans');?></h2></td><td align="right" valign="top"><a href="#" onclick="hideSemPlanHelp();">X</a>&nbsp;</td></tr>
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
<tr><td colspan="2" align="center"><a href="#" onClick="hideSemPlanHelp();"><?php echo $p->t('semesterplan/schliessen');?></a></td></tr>
</table>
</div>
<table class="tabcontent" height="100%" id="inhalt">
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td style="vertical-align:top; height: 10px"><h1 style="white-space:normal;">
		<?php
		$lehrfach_id='';
		if(defined('CIS_LEHRVERANSTALTUNG_LEHRFACH_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_LEHRFACH_ANZEIGEN)
		{
			// Wenn der eingeloggte User zu einer der Lehreinheiten zugeteilt ist
			// wird zusätzlich das Lehrfach der Lehreinheit angezeigt.
			if($is_lector)
			{
				$qry = "SELECT
					distinct lehrfach_id
					FROM
						lehre.tbl_lehreinheit
						JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
					WHERE
						studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem)."
						AND mitarbeiter_uid=".$db->db_add_param($user)."
						AND lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER);
			}
			else
			{
				$qry = "SELECT distinct lehrfach_id
					FROM
						campus.vw_student_lehrveranstaltung
					WHERE
						lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)."
						AND studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem)."
						AND uid=".$db->db_add_param($user);
			}

			if($result = $db->db_query($qry))
			{
				// Wenn die LV mehrere verschiedenen Lehrfaecher hat, und der User zu mehreren davon zugeteilt ist
				// wird das Lehrfach nicht angezeigt damit es nicht zu verwirrungen kommt.
				if($db->db_num_rows($result)==1)
				{
					if($row = $db->db_fetch_object($result))
					{
						$lehrfach = new lehrveranstaltung();
						$lehrfach->load($row->lehrfach_id);
						$lehrfach_id=$row->lehrfach_id;
						if($lehrfach->bezeichnung_arr[$sprache]==$lv_obj->bezeichnung_arr[$sprache])
							echo $lv_obj->bezeichnung_arr[$sprache];
						else
							echo $lehrfach->bezeichnung_arr[$sprache].' - '.$lv_obj->bezeichnung_arr[$sprache];
					}
				}
				else
					echo $lv_obj->bezeichnung_arr[$sprache];
			}
		}
		else
			echo $lv_obj->bezeichnung_arr[$sprache];

		echo ' '.$lv_obj->lehrform_kurzbz;

		if(!defined('CIS_LEHRVERANSTALTUNG_SEMESTERINFO_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_SEMESTERINFO_ANZEIGEN)
			echo ' / '.$kurzbz.'-'.$semester.' '.$lv_obj->orgform_kurzbz;



	    echo "&nbsp;($angezeigtes_stsem)";
	    echo '</h1></td>
              </tr>
              <tr>
              <td>&nbsp;</td>
              <td style="vertical-align:top; height: 10px">';

	    $qry = "SELECT * FROM (SELECT distinct on(uid) vorname, nachname, tbl_benutzer.uid as uid,
	    			CASE WHEN lehrfunktion_kurzbz='LV-Leitung' THEN true ELSE false END as lvleiter
	    		FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person
	    		WHERE
	    			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
	    			tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND
	    			tbl_person.person_id=tbl_benutzer.person_id AND
	    			lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND
	    			tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND
	    			tbl_benutzer.aktiv=true AND tbl_person.aktiv=true AND
	    			studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem);

		// Wenn das Lehrfach angezeigt werden nur die Lektoren angezeigt die dieser
		// Lehreinheit / Lehrfach zugeordnet sind
		if($lehrfach_id!='')
			$qry.=" AND tbl_lehreinheit.lehrfach_id=".$db->db_add_param($lehrfach_id);

		$qry.=" ORDER BY uid, lvleiter desc) as a ORDER BY lvleiter desc, nachname, vorname";

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
					// Lektor wird erst angezeigt wenn der Auftrag erteilt wurde
					if (defined('CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON')
					 && CIS_LV_LEKTORINNENZUTEILUNG_VERTRAGSPRUEFUNG_VON != '')
					{
						$vertrag = new vertrag();
						if (!$vertrag->isVertragErteiltLV($lvid, $angezeigtes_stsem, $row_lector->uid))
						{
							continue;
						}
					}

					$i++;
					if($user==$row_lector->uid)
					{
						$lektor_der_lv=true;
						$user_is_allowed_to_upload=true;
					}

					if($row_lector->lvleiter=='t')
						$style='style="font-weight: bold"';
					else
						$style='';

					if ($i != 1)
						echo ', ';
					echo '<a href="mailto:'.$row_lector->uid.'@'.DOMAIN.'" '.$style.'>'.$row_lector->vorname.' '.$row_lector->nachname.'</a>';

				}
			}
		}

		//Berechtigungen auf Fachbereichsebene
		$qry = "SELECT
	  			distinct lehrfach.oe_kurzbz
	  		FROM
	  			lehre.tbl_lehrveranstaltung
	  			JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
	  			JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id)
	  		WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER);

		if(isset($angezeigtes_stsem) && $angezeigtes_stsem!='')
			$qry .= " AND studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem);
		$lehrfach_oe_kurzbz_arr = array();
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$lehrfach_oe_kurzbz_arr[]=$row->oe_kurzbz;
				if($rechte->isBerechtigt('lehre',$row->oe_kurzbz) || $rechte->isBerechtigt('assistenz',$stg_obj->oe_kurzbz))
					$user_is_allowed_to_upload=true;
			}
		}

		// DIV zur Plazierung von Zusatzinformationen durch Addons
		echo '<div id="lesson_infobox_lektor"></div>';
		?>
		</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
		<td style="vertical-align:top; height: 10px">&nbsp;</td>
	</tr>
	<tr>
		<td >&nbsp;</td>
		<td style="vertical-align:top;">
		<?php
		require_once('../../../include/'.EXT_FKT_PATH.'/cis_menu_lv.inc.php');
		?>
		</td>
		<td class="tdwidth30">&nbsp;</td>
	</tr>
</table>
</body>
</html>
