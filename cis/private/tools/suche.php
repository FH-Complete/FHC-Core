<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Globale Suche
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/content.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/dms.class.php');
require_once('../../../include/service.class.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

$uid = get_uid();
$db = new basis_db();
$sprache = getSprache();
$p = new phrasen($sprache);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<script type="text/javascript" src="../../../include/js/jquery.js"></script>
	<script type="text/javascript" src="../../../include/js/flexcroll.js"></script>
	<link href="../../../skin/flexcrollstyles.css" rel="stylesheet" type="text/css" /> 
	<title>Globale Suche</title>
</head>
<body>
<div class="flexcroll" style="outline: none;">';

echo '<h1>',$p->t('tools/suche'),'</h1>';

$search = (isset($_REQUEST['search'])?$_REQUEST['search']:'');

echo '<form action="',$_SERVER['PHP_SELF'],'" name="searchform" method="GET">
	<input type="search" placeholder="'.$p->t('tools/suchbegriff').' ..." size="40" name="search" value="',$db->convert_html_chars($search),'" />
	<img src="../../../skin/images/search.png" onclick="document.searchform.submit()" height="15px" class="suchicon"/>
	</form><br>';

if($search=='')
	exit;
	
//Easter Egg
if(($search=='the answer to life the universe and everything') || ($search=='answer to life the universe and everything'))
{
	echo '<table width="100%"><tr><td align="center"><br><br><br><p style="align:center; font-size: 2000%;"><strong>42</strong></p></td></tr></table>';
	exit;
}

$searchItems = explode(' ',TRIM($search));

$searchPerson = searchPerson($searchItems);
$searchOrt = searchOrt($search);
$searchDms = searchDms($searchItems);
$searchContent = searchContent($searchItems);

if (!$searchPerson && !$searchOrt && !$searchDms && !$searchContent)
	echo $p->t('tools/esWurdenKeineErgebnisseGefunden');

	
function searchPerson($searchItems)
{
	global $db, $p, $noalias;
	$bn = new benutzer();
	$bn->search($searchItems, 21);
	
	if(count($bn->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">',$p->t('global/personen'),'</h2>';
		echo '
		<script type="text/javascript">	
		$(document).ready(function() 
			{ 
			    $("#personentable").tablesorter(
				{
					sortList: [[3,0],[1,0],[0,0]],
					widgets: [\'zebra\'],
					headers: {8:{sorter:false}}
				}); 
			} 
		);
		</script>';
		if(count($bn->result)>20)
		{
			echo '<p style="color:red;">'.$p->t("tools/esWurdenMehrAlsXPersonenGefunden").'</p>';
		}
		echo '<table class="tablesorter" id="personentable">
			<thead>
				<tr>
					<th>',$p->t('global/vorname'),'</th>
					<th>',$p->t('global/nachname'),'</th>
					<th>',$p->t('global/studiengang'),'</th>
					<th>',$p->t('freebusy/typ'),'</th>
					<th>',$p->t('global/telefonnummer'),'</th>
					<th>',$p->t('lvplan/raum'),'</th>
					<th>',$p->t('global/mail'),'</th>';

			if(!defined('CIS_SUCHE_LVPLAN_ANZEIGEN') || CIS_SUCHE_LVPLAN_ANZEIGEN)
				echo '<th>',$p->t('lvplan/lvPlan'),'</th>';
			echo '
				</tr>
			</thead>
			<tbody>
			';
		foreach($bn->result as $row)
		{
			echo '<tr>';
			//echo '<td>',$row->titelpre,'</td>';
			echo '<td>',$row->vorname,'</td>';
			echo '<td><a href="../profile/index.php?uid=',$row->uid,'" title="',$row->titelpre,' ',$row->vorname,' ',$row->nachname,' ',$row->titelpost,'">',$row->nachname,'</a></td>';
			//echo '<td>',$row->titelpost,'</td>';
			echo '<td>',($row->studiengang!=''?$row->studiengang:'-'),'</td>';
			echo '<td>',($row->mitarbeiter_uid==NULL?'StudentIn':'MitarbeiterIn'),'</td>';
			echo '<td>',($row->telefonklappe!=''?$row->telefonklappe:'-'),'</td>';
			echo '<td>',($row->raum!=''?$row->raum:'-'),'</td>';
			if($row->alias!='' && !in_array($row->studiengang_kz, $noalias))
				$mail = $row->alias.'@'.DOMAIN;
			else
				$mail = $row->uid.'@'.DOMAIN;
			echo '<td><a href="mailto:',$mail,'">',$mail,'</a></td>';
			if(!defined('CIS_SUCHE_LVPLAN_ANZEIGEN') || CIS_SUCHE_LVPLAN_ANZEIGEN)
				echo '<td><a href="../../../cis/private/lvplan/stpl_week.php?pers_uid='.$row->uid.($row->mitarbeiter_uid==NULL?'&type=student':'&type=lektor').'">'.$p->t('lvplan/lvPlan').'</a></td>';
			echo '</tr>';
			echo "\n";
		}
		echo '</tbody></table><br>';
		return true;
	}	
	else 
		return false;	
}
function searchOrt($search)
{
	global $db, $p, $noalias;
	$ort = new ort();
	$ort->filter($search, true, true);
	
	$uid = get_uid();
	$berechtigung=new benutzerberechtigung();
	$berechtigung->getBerechtigungen($uid);
	if ($berechtigung->isBerechtigt('lehre/reservierung:begrenzt', null, 'sui'))
		$raumres=true;
	else
		$raumres=false;
	
	if(count($ort->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">',$p->t('lvplan/ort'),'</h2>';
		echo '
		<script type="text/javascript">	
		$(document).ready(function() 
			{ 
			    $("#orttable").tablesorter(
				{
					sortList: [[1,0]],
					widgets: [\'zebra\'],
					headers: {8:{sorter:false}}
				}); 
			} 
		);
		</script>
		<table class="tablesorter" id="orttable">
			<thead>
				<tr>
					<th>',$p->t('global/ort'),'</th>
					<th>',$p->t('global/bezeichnung'),'</th>
					<th>',$p->t('tools/maxPersonen'),'</th>
					<th>',$p->t('tools/telefonklappe'),'</th>';
					if ($raumres)
						echo '<th>',$p->t('tools/reservieren'),'</th>';
					else 
						echo '<th>',$p->t('lvplan/lvPlan'),'</th>';
				echo '</tr>
			</thead>
			<tbody>';
		foreach($ort->result as $row)
		{
			echo '<tr>';
			echo '<td>',$row->planbezeichnung,' (',$row->ort_kurzbz,')</td>';
			echo '<td>',$row->bezeichnung,'</td>';
			echo '<td>',$row->max_person,'</td>';
			echo '<td>',$row->telefonklappe,'</td>';
			if ($raumres)
				echo '<td><a href="../../../cis/private/lvplan/stpl_week.php?type=ort&ort_kurzbz='.$row->ort_kurzbz.'">'.$p->t('tools/reservieren').'</a></td>';
			else 
				echo '<td><a href="../../../cis/private/lvplan/stpl_week.php?type=ort&ort_kurzbz='.$row->ort_kurzbz.'">'.$p->t('lvplan/lvPlan').'</a></td>';
			//else 
			//	echo '<td></td>';
			echo '</tr>';
			echo "\n";
		}
		echo '</tbody></table><br>';
		return true;
	}
	else 
		return false;
}
function searchDms($searchItems)
{
	$mimetypes = array(
		'application/pdf'=>'pdf_icon.png',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'word2007.jpg',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'x-office-presentation.png',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'excel.gif',
		'application/vnd.oasis.opendocument.text'=>'openoffice0.jpg',
		'application/msword'=>'doc_icon.png',
		'application/vnd.ms-excel'=>'xls_icon.png',
		'application/x-zip'=>'zip_icon.png',
		'application/zip'=>'zip_icon.png',
		'application/mspowerpoint'=>'ppt_icon.png',
		'image/jpeg'=>'img_icon.png',
		'image/gif'=>'img_icon.png',
		'image/png'=>'img_icon.png',
	);
	$searchstring = $searchItems;
	global $db, $p;
	$dms = new dms();
	$dms->searchLastVersion($searchstring, 41);	
	
	if(count($dms->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">'.$p->t("tools/dokumente").'</h2>';
		echo '
		<script type="text/javascript">	
		$(document).ready(function() 
			{ 
			    $("#dmstable").tablesorter(
				{
					sortList: [[1,0]],
					widgets: [\'zebra\'],
					headers: {0:{sorter:false}}
				}); 
			} 
		);
		</script>';
		if(count($dms->result)>40)
		{
			echo '<p style="color:red;">'.$p->t("tools/esWurdenMehrAlsXDokumenteGefunden").'</p>';
		}
		echo '<table class="tablesorter" id="dmstable">
			<thead>
				<tr>
					<th></th>
					<th>',$p->t('global/titel'),'</th>
					<th>',$p->t('tools/aktuelleVersion'),'</th>	
				</tr>
			</thead>
			<tbody>
			';
		foreach($dms->result as $row)
		{
			echo '<tr>';
			if(array_key_exists($row->mimetype,$mimetypes))
				echo '<td width="20px" height="20px" style="vertical-align:middle;"><img src="../../../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 20px; vertical-align:middle;"></td><td height="20px" style="vertical-align:middle;"><a href="../../../cms/dms.php?id='.$row->dms_id.'" title="'.$row->name.'">',$row->beschreibung,'</a></td>';
			else
				echo '<td width="20px" height="20px" style="vertical-align:middle;"><img src="../../../skin/images/blank.gif" style="height: 18px; vertical-align:middle;"></td><td height="20px" style="vertical-align:middle;"><a href="../../../cms/dms.php?id='.$row->dms_id.'">',$row->beschreibung,'</a></td>';
			echo '<td style="vertical-align:middle;">',$row->version,'</td>';
			echo '</tr>';
			echo "\n";
		}
		echo '</tbody></table><br>';
		return true;
	}
	else 
		return false;
}
function searchContent($searchItems)
{
	global $db,$p;
	$cms = new content();
	$cms->search($searchItems, 21);

	if(count($cms->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">',$p->t('tools/content'),'</h2>';
		if(count($cms->result)>20)
		{
			echo '<p style="color:red;">'.$p->t("tools/esWurdenMehrAlsXInhalteGefunden").'</p>';
		}
		echo '<ul>';
		foreach($cms->result as $row)
		{
			echo '<li><div class="suchergebnis">';
			echo '<a href="../../../cms/content.php?content_id=',$db->convert_html_chars($row->content_id),'">',$db->convert_html_chars($row->titel),'</a><br>';
			$preview = findAndMark($row->content, $searchItems);
			
			echo $preview;
			echo '<br /><br /></div></li>';
		}	
		echo '</ul>';
		return true;
	}
	else
		return false;
}
function findAndMark($content, $items)
{
	foreach($items as $row)
	{
		if($row!='')
		{
			$item = $row;
			break;
		}
	}	
	//CDATA und HTML Tags entfernen
	$content = mb_str_replace('<[CDATA[', '', $content);
	$content = mb_str_replace(']]>', '', $content);
	$content = strip_tags($content);
	$item = mb_strtolower($item);
	
	$beginn = mb_strpos(mb_strtolower($content), $item);
	$len = mb_strlen($item);
	
	//Im Content sind die Umlaute teilweise codiert gespeichert
	//Wenn der Eintrag nicht gefunden wird, wird mit Codierten Zeichen nochmals gesucht
	if($beginn=='')
	{
		$beginn = mb_strpos(mb_strtolower($content), htmlentities($item,ENT_NOQUOTES,'UTF-8'));
		$len = mb_strlen(htmlentities($item,ENT_NOQUOTES,'UTF-8'));
	}
	
	if($beginn=='')
	{
		$beginn=0;
		$len=0;
	}
	$start = (($beginn-100)>0?($beginn-100):0);
	//echo "start: $start<br>beginn: $beginn<br>beginn-start: ".($beginn-$start);
	//echo "<br>item:".$item.'<br>';
	$preview='';
	if($start!=0)
		$preview='...';
	
	$preview .= mb_substr($content, $start, ($beginn-$start));
	$preview.='<span class="suchmarker">';
	$preview.= mb_substr($content, $beginn, $len);
	$preview.='</span>';
	$preview.= mb_substr($content, $beginn+$len, 300);

	$laenge = $beginn+$len+300;
	if($laenge<mb_strlen($content))
		$preview.='...';
	return $preview;
}

echo '</div></body></html>';
?>
