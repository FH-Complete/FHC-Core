<?php
/* Copyright (C) 2008 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>
 *          Alexander Nimmervoll <alexander.nimmervoll@technikum-wien.at>
 */
 
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/studiensemester.class.php'); 
require_once('../../../include/benutzer.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
  	  	
$sprache = getSprache(); 
$p=new phrasen($sprache); 

$uid=get_uid();
$berechtigung=new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if ($berechtigung->isBerechtigt('lehre/reservierung:begrenzt', null, 'sui'))
	$raumres=true;
else
	$raumres=false;

/*$benutzer = new benutzer(); 

foreach($benutzer->result as $row)
{
	$item['vorname']=html_entity_decode($row->vorname);
	$item['nachname']=html_entity_decode($row->nachname);
	$item['uid']=html_entity_decode($row->uid);
	$item['mitarbeiter_uid']=html_entity_decode($row->mitarbeiter_uid);
	$result_obj[]=$item;
}
echo $benutzer;*/

//echo json_encode($result_obj);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
  
if (!$uid=get_uid())
	die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');
	

$sql_query="SELECT titelpre, titelpost, uid, nachname, vorname FROM campus.vw_benutzer WHERE uid LIKE '$uid'";
	//echo $sql_query;
$result=$db->db_query($sql_query);

if($db->db_num_rows($result)==0)
{
	//GastAccount
	$titelpre='';
	$titelpost='';
	$uid='';
	$nachname='';
	$vornamen='';
	//echo "User not found!";
}
else
{
	$titelpre=$db->db_result($result,0,'"titelpre"');
	$titelpost=$db->db_result($result,0,'"titelpost"');
	$uid=$db->db_result($result,0,'"uid"');
	$nachname=$db->db_result($result,0,'"nachname"');
	$vornamen=$db->db_result($result,0,'"vorname"');
}
$sql_query="SELECT studiengang_kz, kurzbz, kurzbzlang, bezeichnung, typ, english FROM public.tbl_studiengang WHERE aktiv ORDER BY typ, kurzbz";
$result_stg=$db->db_query($sql_query);
if(!$result_stg)
	die ("Studiengang not found!");	
$num_rows_stg=$db->db_num_rows($result_stg);

$sql_query="SELECT ort_kurzbz, bezeichnung FROM public.tbl_ort WHERE aktiv AND lehre ORDER BY ort_kurzbz";
$result_ort=$db->db_query($sql_query);
if(!$result_ort)
  	die("ort not found!");  	
$num_rows_ort=$db->db_num_rows($result_ort);

/*$sql_query="SELECT student_uid FROM public.tbl_student ORDER BY student_uid";
$result_lektor=$db->db_query($sql_query);
if(!$result_lektor)
	die("lektor not found!");
	
$num_rows_lektor=$db->db_num_rows($result_lektor);*/


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Lehrveranstaltungsplan</title>
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
<script src="../../../include/js/jquery1.9.min.js" type="text/javascript"></script>
<script type="text/javascript" language="JavaScript">

function MM_jumpMenu(targ,selObj,restore){ //v3.0
  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
  if (restore) selObj.selectedIndex=0;
}
function jumpKalender(){
	if (document.getElementById('stg_kz_semplan').value == '') {
	    alert("<?php echo $p->t('lvplan/bitteEinenStudiengangAuswaehlen');?>");
	  }
	else if (document.getElementById('studiensemester').value == '') {
	    alert("<?php echo $p->t('lvplan/bitteEinStudiensemesterAuswaehlen');?>");
	  } 
	  else {window.open ('stpl_kalender.php?type=verband&stg_kz='+document.getElementById('stg_kz_semplan').value+'&sem='+document.getElementById('sem').value
			+'&ver='+document.getElementById('ver').value+'&grp='+document.getElementById('grp').value+'&begin='+document.getElementById('studiensemester').value+'&format=html', '_blank');
	  }
}
function checkSetStudiengang(){
	if (document.getElementById('stg_kz').value == '') {
		alert("<?php echo $p->t('lvplan/bitteEinenStudiengangAuswaehlen');?>");
		return false;
	}
	else
		return true;
}
function checkSetBenutzer(){
	if (document.getElementById('benutzer').value == '') {
		alert("<?php echo $p->t('lvplan/bitteEinenLektorAuswaehlen');?>");
		return false;
	}
	else
		return true;
}

$(document).ready(function() 
	{ 
	    $("#benutzer").autocomplete({
			source: "lvplan_autocomplete.php?autocomplete=benutzer",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				if (ui.item.mitarbeiter_uid=='')
				{
					$("#mitarbeiter_uid").val(ui.item.uid);
					$("#uid").val("student");
				}
				else
				{
					$("#mitarbeiter_uid").val(ui.item.uid);
					$("#uid").val("lektor");
				}
			}
			});
	});
</script>
</head>

<body id="inhalt">
<h1><?php echo $p->t("lvplan/lehrveranstaltungsplan");?></h1>
<table class="cmstable" cellspacing="0" cellpadding="0">
<tbody>
<tr>
<td class="cmscontent" rowspan="3" valign="top">

<FORM name="Auswahl" action="stpl_week.php">
		
	<table class="tabcontent"><tr><td valign="top"  width="30%">
	<?php
		if (isset($uid))
			echo '<h2>'.$titelpre.' '.$vornamen." ".$nachname.' '.$titelpost.'</h2>';
		else
			echo $p->t('lvplan/nichtVorhanden').' '.$p->t('lvplan/bitteWendenSieSichAn').'<A href="mailto:'.MAIL_ADMIN.'">Admin</A>!';
	?>
  	<a class="Item" href="stpl_week.php?pers_uid=<?php echo $uid; ?>"><?php echo $p->t("lvplan/persoenlicherLvPlan");?></a>
	</td><td valign="top">	
	<?php
	echo' 
	<h2>'.$p->t('lvplan/persoenlichenAbonnieren').'</h2>
	<div>	
	<a class="Item" href="../../../cms/content.php?content_id='.$p->t('dms_link/lvplanSyncFAQ').'" target="_blank">'.$p->t('lvplan/anleitungLVPlanSync').'</a>
	<br>';

	echo '<ul>';
	$caldavurl = APP_ROOT.'webdav/lvplan.php/calendars/'.$uid.'/LVPlan-'.$uid;
  	echo '<li><a class="Item" href="'.$caldavurl.'">'.$p->t('lvplan/caldavURL').'</a></li>';
  	echo '<li><a class="Item" href="'.APP_ROOT.'webdav/lvplan.php/principals/'.$uid.'">'.$p->t('lvplan/caldavURLMac').'</a></li>';
  	echo '<li><a class="Item" href="'.APP_ROOT.'webdav/google.php?cal='.encryptData($uid,LVPLAN_CYPHER_KEY).'&'.microtime(true).'">'.$p->t('lvplan/googleURL').'</a></li>';
  	echo '</ul>';
  	echo '	</div>';
  	?>
  	</td></tr>
	
		<tr>
			<td width="30%">
				<h2><?php echo $p->t("lvplan/saalplan")." (".$p->t("lvplan/saalreservierung"); ?>)</h2>
			</td>
			<td>
				<h2><?php echo $p->t("lvplan/lektorInStudentIn"); ?></h2>
			</td>
		</tr>
		<tr>
			<td valign="top">
			<select name="select" style="width:200px;" onChange="MM_jumpMenu('self',this,0)">
        		<option value="stpl_week.php" selected><?php echo $p->t('lvplan/raumAuswaehlen'); ?></option>
        	  	<?php
				for ($i=0;$i<$num_rows_ort;$i++)
				{
					$row=$db->db_fetch_object ($result_ort, $i);
					echo "<option value=\"stpl_week.php?type=ort&amp;ort_kurzbz=$row->ort_kurzbz\">$row->ort_kurzbz ($row->bezeichnung)</option>";
				}
				?>
			</select>
			<?php 
			if ($raumres)
			{
				echo '<BR><BR><A class="Item" href="stpl_reserve_list.php">'.$p->t("lvplan/reservierungenLoeschen").'</A><BR>';
				//echo '<A class="Item" href="raumsuche.php">'.$p->t('lvplan/raumsuche').'</A><BR>'; Findet sich nun rechts in der menubox
			}			
			?>
			</td>
			<td valign="top">
			<?php
			echo "<input class='search' placeholder='".$p->t('lvplan/nameEingeben')."' type='text' id='benutzer' size='32' value=''>";
			echo "<input type='hidden' id='mitarbeiter_uid' name='pers_uid'>";
			echo "<input type='hidden' id='uid' name='type' value='student'>";			
			echo "<input type='submit' value='Go' onclick='return checkSetBenutzer();'>";
			?>
			</td>
		</tr>
		</table>
		</FORM>
		<br>
		<FORM name="Auswahl" action="stpl_week.php">
		<table class="tabcontent"><tr><td><h2><?php echo $p->t('lvplan/lehrverband');?></h2></td></tr></table>
		<table width="10%" border="0" cellpadding="0" cellspacing="3">
		<tr>
		<td width="20%" valign="middle">
			<select style="width:200px;" id="stg_kz" name="stg_kz">
				<option value="" selected><?php echo $p->t('lvplan/studiengangAuswaehlen');?></option>
				<?php
				$num_rows=$db->db_num_rows($result_stg);
				for ($i=0;$i<$num_rows;$i++)
				{
					$row=$db->db_fetch_object ($result_stg, $i);
					echo '<option value="'.$row->studiengang_kz.'">'.strtoupper($row->typ.$row->kurzbz).' ('.($sprache=='English' && $row->english!=''?$row->english:$row->bezeichnung).')</option>';
				}
				?>
			</select>
		</td>
		<td valign="middle">
			<select name="sem">
			<option value="0"><?php echo $p->t('lvplan/sem');?></option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			</select>
		</td>
		<td valign="middle">
			<select name="ver">
			<option value="0" selected><?php echo $p->t('lvplan/ver');?></option>
			<option value="A">A</option>
			<option value="B">B</option>
			<option value="C">C</option>
			<option value="D">D</option>
			<option value="E">E</option>
			<option value="F">F</option>
			<option value="V">V</option>
			</select>
		</td>
		<td valign="middle" >
			<select name="grp">
			<option value="0" selected><?php echo $p->t('lvplan/grp');?></option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="4">5</option>
			<option value="4">6</option>
			</select>
		</td>
		<td valign="bottom">
			<input type="hidden" name="type" value="verband">
			<input type="submit" name="Abschicken" value="Go" onclick="return checkSetStudiengang();">
		</td>
		</tr>
		</table>
	</form>
	<br>
	<!--<a class="Item" href="verband_uebersicht.php"><?php echo $p->t('lvplan/uebersichtDerLehrverbaende');?></a><BR>  Auskommentiert, da vemutlich nicht mehr benötigt-->
	
<form name="Auswahl" action="stpl_kalender.php">
		<table class="tabcontent"><tr><td><h2><?php echo $p->t('lvplan/semesterplaenearchiv');?></h2></td></tr></table>
		<table border="0" cellpadding="0" cellspacing="3">
		<tr>
		<td valign="bottom">
			<select style="width:200px;" name="stg_kz_semplan" id="stg_kz_semplan">
				<option value="" selected><?php echo $p->t('lvplan/studiengangAuswaehlen');?></option>
				<?php
				$num_rows=$db->db_num_rows($result_stg);
				for ($i=0;$i<$num_rows;$i++)
				{
					$row=$db->db_fetch_object ($result_stg, $i);
					echo '<option value="'.$row->studiengang_kz.'">'.strtoupper($row->typ.$row->kurzbz).' ('.($sprache=='English' && $row->english!=''?$row->english:$row->bezeichnung).')</option>';
				}
				?>
			</select>
		</td>
		<td valign="middle">
			<select name="sem" id="sem">
			<option value="01"><?php echo $p->t('lvplan/sem');?></option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			</select>
		</td>
		<td valign="middle">
			<select name="ver" id="ver">
			<option value="0" selected><?php echo $p->t('lvplan/ver');?></option>
			<option value="A">A</option>
			<option value="B">B</option>
			<option value="C">C</option>
			<option value="D">D</option>
			<option value="E">E</option>
			<option value="F">F</option>
			<option value="V">V</option>
			</select>
		</td>
		<td valign="middle" >
			<select name="grp" id="grp">
			<option value="0" selected><?php echo $p->t('lvplan/grp');?></option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="4">5</option>
			<option value="4">6</option>
			</select>
		</td></tr><tr>
		<td valign="middle" >
		<?php
		$studiensemester = new studiensemester();
		$studiensemester->getFinished();	
				
		echo '<SELECT style="width:200px;" name="begin" id="studiensemester">';
		echo '<OPTION value="" selected>'.$p->t('lvplan/studiensemesterAuswaehlen').'</OPTION>';
		foreach($studiensemester->studiensemester as $row)
				{
					$studiensemester->getTimestamp($row->studiensemester_kurzbz);
					echo '<OPTION value="'.$studiensemester->begin->start.'&amp;ende='.$studiensemester->ende->ende.'">'.$row->studiensemester_kurzbz.'</OPTION>';
				}
				
		echo '</SELECT>';
		?>
		</td>
		<td colspan="3" valign="bottom">
			<input type="button" name="Abschicken" value="<?php echo $p->t('lvplan/semesterplanLaden');?>" onClick="jumpKalender()">
		</td>
		</tr>
		</table>
	</form>
</td>
<td class="menubox">
<p><a href="raumsuche.php"><?php echo $p->t('lvplan/raumsuche');?></a></p>
<p><a class="Item" href="mailto:<?php echo MAIL_LVPLAN?>"><?php echo $p->t('lvplan/fehlerUndFeedback');?></a></p>
<p><a href="../../../cms/content.php?content_id=<?php echo $p->t('dms_link/lvPlanFAQ');?>" class="hilfe" target="_blank"><?php echo $p->t('global/hilfe');?></a></p>
</td>
</tr>
<tr>
<td class="teambox" style="width: 20%;"></td>
</tr>
</tbody>
</table>
</body>
</html>
