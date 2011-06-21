<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/news.class.php');
require_once('../include/content.class.php');
require_once('../include/phrasen.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');

$uid = get_uid();
$sprache = getSprache();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/news'))
	die('Sie haben keine Berechtigung für diese Seite');

$p = new phrasen($sprache);

if(isset($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else
	$studiengang_kz='0';
	
if(isset($_GET['semester']))
	$semester = $_GET['semester'];
else
	$semester = null;

$news_id = (isset($_REQUEST['news_id'])?$_REQUEST['news_id']:null);
$datum_obj = new datum();
//ToDo: markieren des richtigen Tabs
$tabselect=0;
$content = new content();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../skin/styles/jquery.css" type="text/css">
	<link rel="stylesheet" href="../skin/styles/jquery-ui.css" type="text/css">
	
	<script src="../include/js/jquery.js" type="text/javascript"></script>
	<script src="../include/js/jquery-ui.js" type="text/javascript"></script>
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce.js"></script>
	
	<title>'.$p->t('news/newsverwaltung').'</title>
	<script language="Javascript">
	$(document).ready(function() {
		$("#tabs").tabs();
		$( "#tabs" ).tabs( "option", "selected", '.$tabselect.');
	})
	

	tinyMCE.init
	(
		{
		mode : "textareas",
		theme : "advanced",
		language : "de",
		file_browser_callback: "FHCFileBrowser",
		
		plugins : "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
			
		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,image,|,bullist,formatselect,fontsizeselect", 
    	theme_advanced_buttons2 : "", 
    	theme_advanced_buttons3 : "",
	    theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "center",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        force_br_newlines : true,
        force_p_newlines : false,
        forced_root_block : ""
		
		}
	);
	function FHCFileBrowser(field_name, url, type, win) 
	{
		cmsURL = "'.APP_ROOT.'cms/tinymce_dms.php?type="+type;
		tinyMCE.activeEditor.windowManager.open({
			file: cmsURL,
			title : "FHComplete File Browser",
			width: 750,
			height: 550,
			resizable: "yes",
			close_previous: "no",
			scrollbars: "yes",
			popup_css : false
		},{
			window: win,
			input: field_name
		});
		return false;
	}
	
	</script>
</head>
<body>';
if(isset($_GET['action']) && $_GET['action']=='add_uebersetzung')
{
	$news = new news();
	$news->load($_GET['news_id']);
	
	$content = new content();
	$content->getContent($news->content_id);
	
	$content->new = true;
	$content->sichtbar=true;
	$content->sprache=$_GET['lang'];
	$content->insertvon = $uid;
	$content->insertamum = date('Y-m-d H:i:s');
	$content->updatevon = $uid;
	$content->updateamum = date('Y-m-d H:i:s');
	$content->reviewvon='';
	$content->reviewamum='';
	$content->gesperrt_uid='';
	
	if($content->saveContentSprache())
		echo '<span class="ok">Eintrag wurde erfolgreich angelegt</span>';
	else
		echo '<span class="ok">'.$content->errormsg.'</span>';
}

if(isset($_GET['action']) && $_GET['action']=='delete')
{
	if(!$rechte->isBerechtigt('basis/news',null, 'suid'))
		die('Sie haben keine Berechtigung zum Löschen von Einträgen');
		
	if(isset($_GET['news_id']) && is_numeric($_GET['news_id']))
	{
		$news_id = $_GET['news_id'];
		$news = new news();
		if($news->delete($news_id))
		{
			echo '<span class="ok">News wurde erfolgreich gelöscht</span>';
			$news_id='';
		}
		else
		{
			echo '<span class="error">'.$news->errormsg.'</span>';
		}		
	}
	else
		die('NewsID ist ungueltig');
	
	
}

if(isset($_POST['save']))
{
	$news_id = $_POST['news_id'];
	
	$news = new news();
	
	if($news_id!='')
	{
		$news->load($news_id);
		$news->new=false;
	}
	else
	{
		$news->new = true;
		$news->uid = $uid;
		$news->insertamum = date('Y-m-d H:i:s');
		$news->insertvon = $uid;
	}
	$news->studiengang_kz=$_POST['studiengang_kz'];
	$news->semester = $_POST['semester'];
	$news->updateamum=date('Y-m-d H:i:s');
	$news->updatevon = $uid;
	$news->datum = $datum_obj->formatDatum($_POST['datum'],'Y-m-d');
	$news->datum_bis = $datum_obj->formatDatum($_POST['datum_bis'],'Y-m-d');
	
	if($news->content_id=='')
	{
		$studiengang = new studiengang();
		$studiengang->load($_POST['studiengang_kz']);
		
		$content = new content();
		$content->template_kurzbz='news';
		$content->oe_kurzbz=$studiengang->oe_kurzbz;
		$content->aktiv=true;
		$content->menu_open=false;
		$content->insertamum=date('Y-m-d H:i:s');
		$content->insertvon = $uid;
		if(!$content->save(true))
			die($content->errormsg);
		
		$news->content_id = $content->content_id;
	}
		
	if(!$news->save())
		die($news->errormsg);
	$news_id = $news->news_id;

	//ContentSprache
	$sprachen = array(DEFAULT_LANGUAGE);
	foreach($_POST as $key=>$value)
		if(mb_strstr($key,'contentsprache_id_'))
			$sprachen[] = mb_substr($key, strlen('contentsprache_id_'));
	
	$sprachen = array_unique($sprachen);

	foreach($sprachen as $lang)
	{
		$content = new content();
		if(isset($_POST['contentsprache_id_'.$lang]) && $_POST['contentsprache_id_'.$lang]!='')
		{
			$content->loadContentSprache($_POST['contentsprache_id_'.$lang]);
			$content->new = false;
		}
		else
		{
			$content->insertamum = date('Y-m-d H:i:s');
			$content->insertvon = $uid;
			$content->sichtbar=true;
			$content->version=1;
			$content->content_id=$news->content_id;
			$content->new = true;
			$content->sprache = $lang;
		}
		
		$xml = '<news>';
		$xml.='<verfasser><![CDATA['.$_POST['verfasser_'.$lang].']]></verfasser>';
		$xml.='<betreff><![CDATA['.$_POST['betreff_'.$lang].']]></betreff>';
		$xml.='<text><![CDATA['.$_POST['text_'.$lang].']]></text>';
		$xml.='</news>';
		
		$content->content = $xml;
		$content->updateamum = date('Y-m-d H:i:s');
		$content->updatevon = $uid;
		$content->titel = $_POST['betreff_'.$lang];
		$content->saveContentSprache();
	}
}

$sprachen = array(DEFAULT_LANGUAGE);

$news = new news();
if($news_id!='')
{
	$news->load($news_id);	
	$sprachen = $content->getLanguages($news->content_id);
	$studiengang_kz = $news->studiengang_kz;
	$semester = $news->semester;
}
if($studiengang_kz=='0' && $semester=='')
	$type=$p->t('news/allgemein');
elseif($studiengang_kz=='0' && $semester=='0')
	$type=$p->t('news/freifach');
else
	$type=$p->t('news/studiengang');

echo '<h1>'.$p->t('news/newsverwaltung').' - '.$type.'</h1>';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
	<input type="hidden" name="news_id" value="'.$news_id.'">
		<table>
			<tr>
				<td>
					<table>
						<tr>
							<td>'.$p->t('news/sichtbarab').'</td>
							<td><input type="text" name="datum" size="10" value="'.($news->datum!=''?$datum_obj->formatDatum($news->datum,'d.m.Y'):date('d.m.Y')).'"></td>
						</tr>
						<tr>
							<td>'.$p->t('news/sichtbarbis').'</td>
							<td><input type="text" name="datum_bis" size="10" value="'.$datum_obj->formatDatum($news->datum_bis,'d.m.Y').'"></td>
						</tr>
					</table>
				</td>
				<td>';

//DropDown fuer Studiengang und Semester anzeigen
if($studiengang_kz!='0')
{
	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbz', false);
	
	echo '<table>
			<tr>
				<td>'.$p->t('global/studiengang').'</td>
				<td>
				<SELECT name="studiengang_kz">';
	foreach($studiengang->result as $row)
	{
		if($row->studiengang_kz==$studiengang_kz)
			$selected = 'selected';
		else
			$selected = '';
		echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.' ('.$row->kurzbzlang.')</OPTION>';
	}
	echo '		</SELECT>
				</td>
			</tr>';
	
	echo '<tr>
			<td>'.$p->t('global/semester').'</td>
			<td>
			<SELECT name="semester">';
	echo '<OPTION value="0">'.$p->t('news/allesemester').'</OPTION>';
	for($i=1;$i<=8;$i++)
	{
		if($i==$semester)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$i.'" '.$selected.'>'.$p->t('news/xsemester',array($i)).'</OPTION>';
	}
	echo '</SELECT>
			</td>
		</tr>
		</table>
		';
}
else
{
	echo '<input type="hidden" name="studiengang_kz" value="'.$studiengang_kz.'">
			<input type="hidden" name="semester" value="'.$semester.'">';
}

echo '</td></tr></table>';
echo '<div id="tabs" style="font-size:80%;">
		<ul class="css-tabs">';
	
foreach($sprachen as $lang)
{
	echo '<li><a href="#'.$lang.'">'.$lang.'</a></li>';
}
if($news->content_id!='')
{
	echo '<li><a href="#add" title="'.$p->t('news/uebersetzen').'">+</a></li>';
}
echo '</ul>';

foreach($sprachen as $lang)
{
	$verfasser='';
	$betreff='';
	$text='';
	if($news->content_id!='')
	{
		$content->getContent($news->content_id, $lang, null, null, false);
	
		$xml_inhalt = new DOMDocument();
		if($content->content!='')
		{
			$xml_inhalt->loadXML($content->content);
		}

		if($xml_inhalt->getElementsByTagName('verfasser')->item(0))
			$verfasser = $xml_inhalt->getElementsByTagName('verfasser')->item(0)->nodeValue;
		if($xml_inhalt->getElementsByTagName('betreff')->item(0))
			$betreff = $xml_inhalt->getElementsByTagName('betreff')->item(0)->nodeValue;
		if($xml_inhalt->getElementsByTagName('text')->item(0))
			$text = $xml_inhalt->getElementsByTagName('text')->item(0)->nodeValue;
	}
	echo '<div id="'.$lang.'">';
	echo '<input type="hidden" name="contentsprache_id_'.$lang.'" value="'.$content->contentsprache_id.'">';
	echo '<table>
			<tr>
				<td>'.$p->t('news/verfasser').'</td>
				<td><input type="text" name="verfasser_'.$lang.'" size="40" value="'.$verfasser.'"></td>
			</tr>
			<tr>
				<td>'.$p->t('news/betreff').'</td>
				<td><input type="text" name="betreff_'.$lang.'" size="40" value="'.$betreff.'"></td>
			</tr>
			<tr>
				<td>'.$p->t('news/text').'</td>
				<td><textarea name="text_'.$lang.'" rows="15" cols="80">'.$text.'</textarea></td>
			</tr>
		</table>';
	
	echo '</div>';
}

//DropDown zum Anlegen von Uebersetzungen
if($news->content_id!='')
{
	echo '<div id="add">';
	$content = new content();
	if(!$vorhandene_sprachen = $content->getLanguages($news->content_id))
		die($content->errormsg);
	$sprache_obj = new sprache();
	$sprache_obj->getAll(true);
	
	//Wenn noch nicht alle Uebersetzungen vorhanden sind, 
	//wird ein Formular zum Erstellen der Uebersetzung angezeigt.
	if(count($vorhandene_sprachen)<count($sprache_obj->result))
	{

		echo $p->t('news/uebersetzunganlegen');
		foreach($sprache_obj->result as $row)
		{
			if(!in_array($row->sprache, $vorhandene_sprachen))
				echo '<br /><a href="'.$_SERVER['PHP_SELF'].'?news_id='.$news_id.'&action=add_uebersetzung&lang='.$row->sprache.'">'.$row->bezeichnung_arr[$sprache].'</a>';		
		}
	}
	else
	{
		echo '<br />'.$p->t('news/uebersetzungenvorhanden').'<br />';
	}
	echo '  </div>';
}
echo '</div><br />';
echo '<input type="submit" name="save" value="'.$p->t('global/speichern').'">';
echo '</form>';

// Newseintraege Anzeigen
echo '<hr>
<iframe src="news.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'&edit=true" style="width:99%; height:100%;position:absolute;">';
echo '</body>
</html>';
?>