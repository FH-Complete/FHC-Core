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
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/content.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/globals.inc.php');

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
	<title>Globale Suche</title>
</head>
<body>';

echo '<h1>',$p->t('tools/suche'),'</h1>';

$search = (isset($_REQUEST['search'])?$_REQUEST['search']:'');

echo '<form action="',$_SERVER['PHP_SELF'],'" name="searchform" method="GET">
	<input type="search" placeholder="'.$p->t('tools/suchbegriff').' ..." size="40" name="search" value="',$db->convert_html_chars($search),'" />
	<img src="../../../skin/images/search.png" onclick="document.searchform.submit()" height="15px" class="suchicon"/>
	</form>';

if($search=='')
	exit;

$searchItems = explode(' ',$search);

searchPerson($searchItems);
searchContent($searchItems);



function searchPerson($searchItems)
{
	global $db, $p, $noalias;
	$bn = new benutzer();
	$bn->search($searchItems);
	
	if(count($bn->result)>0)
	{
		echo '<h2>',$p->t('global/personen'),'</h2>';
		echo '
		<script type="text/javascript">	
		$(document).ready(function() 
			{ 
			    $("#personentable").tablesorter(
				{
					sortList: [[2,0]],
					widgets: [\'zebra\']
				}); 
			} 
		);
		</script>
		<table class="tablesorter" id="personentable">
			<thead>
				<tr class="liste">
					<th>',$p->t('global/titel'),'</th>
					<th>',$p->t('global/vorname'),'</th>
					<th>',$p->t('global/nachname'),'</th>
					<th>',$p->t('global/titel'),'</th>
					<th>',$p->t('global/studiengang'),'</th>
					<th>',$p->t('global/telefonnummer'),'</th>
					<th>',$p->t('global/mail'),'</th>
				</tr>
			</thead>
			<tbody>
			';
		foreach($bn->result as $row)
		{
			echo '<tr>';
			echo '<td>',$row->titelpre,'</td>';
			echo '<td>',$row->vorname,'</td>';
			echo '<td><a href="../profile/index.php?uid=',$row->uid,'">',$row->nachname,'</a></td>';
			echo '<td>',$row->titelpost,'</td>';
			echo '<td>',$row->studiengang,'</td>';
			echo '<td>',$row->telefonklappe,'</td>';
			if($row->alias!='' && !in_array($row->studiengang_kz, $noalias))
				$mail = $row->alias.'@'.DOMAIN;
			else
				$mail = $row->uid.'@'.DOMAIN;
			echo '<td><a href="mailto:',$mail,'">',$mail,'</a></td>';		
			echo '</tr>';
			echo "\n";
		}
		echo '</tbody></table>';
	}		
}
function searchContent($searchItems)
{
	global $db,$p;
	$cms = new content();
	$cms->search($searchItems);

	if(count($cms->result)>0)
	{
		echo '<h2>',$p->t('tools/content'),'</h2>';
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
	}
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

echo '</body></html>';
?>