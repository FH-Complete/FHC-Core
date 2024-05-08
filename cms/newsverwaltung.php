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
/**
 * Diese Seite dient zum Anlegen und aendern von Newseintraegen
 */
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/news.class.php');
require_once('../include/content.class.php');
require_once('../include/phrasen.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');
require_once('../include/mail.class.php');
require_once('../include/benutzerfunktion.class.php');

$uid = get_uid();
$sprache = getSprache();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$p = new phrasen($sprache);

if(isset($_REQUEST['studiengang_kz']))
	$studiengang_kz=$_REQUEST['studiengang_kz'];
else
	$studiengang_kz='0';

if(isset($_REQUEST['semester']))
	$semester = $_REQUEST['semester'];
else
	$semester = null;

if(check_lektor($uid))
	$is_lector=true;
else
	$is_lector=false;

if(!$rechte->isBerechtigt('basis/news'))
	$berechtigt=false;
else
	$berechtigt=true;

//Lektoren duerfen nur Studiengangsspezifische und Freifaecher News Eintragen
//Fuer allgemeine News wird die berechtigung basis/news benoetigt
if(!$is_lector && !$berechtigt)
	die('Sie haben keine Berechtigung zum Eintragen/Bearbeiten von News');

$news_id = (isset($_REQUEST['news_id'])?$_REQUEST['news_id']:null);

if($studiengang_kz=='0' && is_null($semester) && $news_id=='')
{
	if(!$berechtigt)
		die('Sie haben keine Berechtigung zum Eintragen/Bearbeiten von allgemeinen News');
}

$datum_obj = new datum();
$content = new content();

$message = '';

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../skin/styles/jquery.css" type="text/css">
	<link rel="stylesheet" href="../vendor/components/jqueryui/themes/base/jquery-ui.min.css" type="text/css">

	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script>
	<script src="../vendor/components/jqueryui/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce.js"></script>

	<title>'.$p->t('news/newsverwaltung').'</title>
	<script type="text/javascript">
	$(document).ready(function()
		{
			$( ".datepicker_datum" ).datepicker({
				 changeMonth: true,
				 dateFormat: "dd.mm.yy",
				 minDate: "getDate",
				 maxDate: "+30d"
				 });
		});

	tinyMCE.init
	(
		{
		mode : "textareas",
		theme : "advanced",
		language : "de",
		file_browser_callback: "FHCFileBrowser",

		plugins : "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,image,|,bullist,formatselect,fontsizeselect,pastetext",
    	theme_advanced_buttons2 : "",
    	theme_advanced_buttons3 : "",
	    theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "center",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        force_br_newlines : true,
        force_p_newlines : false,
        forced_root_block : "",
        //Formatierungen beim Einfuegen entfernen
        paste_auto_cleanup_on_paste : true,
        paste_remove_styles: true,
        paste_remove_styles_if_webkit: true,
        paste_strip_class_attributes: true,
        paste_retain_style_properties: "",
        paste_text_sticky: true,
        setup : function(ed)
        {
        	ed.onInit.add(function(ed)
            {    ed.pasteAsPlainText = true;
                ed.controlManager.setActive("pastetext", true);
            });
           }

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

//Uebersetzung anlegen
if(isset($_GET['action']) && $_GET['action']=='add_uebersetzung')
{
	$news = new news();
	$news->load($_GET['news_id']);

	$content = new content();
	$content->getContent($news->content_id);

	$content->new = true;
	$content->sichtbar=false;
	$content->sprache=$_GET['lang'];
	$content->insertvon = $uid;
	$content->insertamum = date('Y-m-d H:i:s');
	$content->updatevon = $uid;
	$content->updateamum = date('Y-m-d H:i:s');
	$content->reviewvon='';
	$content->reviewamum='';
	$content->gesperrt_uid='';

	if($content->saveContentSprache())
		$message.= '<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
	else
		$message.= '<span class="error">'.$content->errormsg.'</span>';
}

//Eintrag entfernen
if(isset($_GET['action']) && $_GET['action']=='delete')
{
	if(!$rechte->isBerechtigt('basis/news',null, 'suid') && !$is_lector)
		die($p->t('global/keineBerechtigungFuerDieseSeite'));

	if(isset($_GET['news_id']) && is_numeric($_GET['news_id']))
	{
		$news_id = $_GET['news_id'];
		$news = new news();
		if($news->load($news_id))
		{
			$studiengang_kz=$news->studiengang_kz;
			$semester = $news->semester;

			if($news->delete($news_id))
			{
				$message.= '<span class="ok">'.$p->t('global/erfolgreichgelöscht').'</span>';
				$news_id='';
			}
			else
			{
				$message.= '<span class="error">'.$news->errormsg.'</span>';
			}
		}
		else
		{
			$message.= '<span class="error">'.$p->t('global/fehlerBeimLesenAusDatenbank').'</span>';
		}
	}
	else
		die($p->t('global/fehlerBeiDerParameteruebergabe'));


}

//Speichern eines Eintrags
if(isset($_POST['save']))
{
	$save_error=false;
	$news_id = $_POST['news_id'];
	$mail = false;

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
		$mail = true;
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
		if (isset($_POST['sichtbar_'.$lang]))
			$sichtbar = true;
		else
			$sichtbar = false;

		if(isset($_POST['contentsprache_id_'.$lang]) && $_POST['contentsprache_id_'.$lang]!='')
		{
			$content->loadContentSprache($_POST['contentsprache_id_'.$lang]);
			$content->new = false;
		}
		else
		{
			$content->insertamum = date('Y-m-d H:i:s');
			$content->insertvon = $uid;
			$content->sichtbar = $sichtbar;
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
		$content->sichtbar = $sichtbar;
		$content->updateamum = date('Y-m-d H:i:s');
		$content->updatevon = $uid;
		$content->titel = $_POST['betreff_'.$lang];
		if(!$content->saveContentSprache())
		{
			$message.= '<span class="error">'.$content->errormsg.'</span>';
			$save_error=true;
		}
		if ($sichtbar == true)
			$message.='<span class="ok">'.$p->t('news/eintragVeroeffentlicht',array($lang)).'</span><br/>';
		else
			$message.='<span class="error">'.$p->t('news/eintragNochNichtVeroeffentlicht',array($lang)).'</span><br/>';
	}
	if(!$save_error)
	{
		$message.= '<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
	}

	if ($mail && $_POST['studiengang_kz']=='0' && $_POST['semester']==NULL)
	{
		$oe = new studiengang();
		$oe->load($_POST['studiengang_kz']);
		$oe_translate = $oe->oe_kurzbz;

		$translate = new benutzerfunktion();
		$translate->getBenutzerFunktionen('translate', $oe_translate);

		if(count($translate->result)==0)
			$translate->getBenutzerFunktionen('translate');
		$to='';
		foreach($translate->result as $row)
		{
			if($to!='')
				$to.=',';
			$to .= $row->uid.'@'.DOMAIN;
		}
		if($to!='')
		{
			$from = 'no-reply@'.DOMAIN;
			$subject = $p->t('news/neuerNewseintrag');
			$text = $p->t('news/mailtext');
			$texthtml = $p->t('news/mailtextHTML',array(APP_ROOT."cms/newsverwaltung.php?news_id=".$news_id,$content->titel,$_POST['text_'.DEFAULT_LANGUAGE])) ;

			$mail = new mail($to, $from, $subject, $text);
			$mail->setHTMLContent($texthtml);
			if($mail->send())
			{
				$message.='<br><span class="ok">'.$p->t('news/uebersetzungsanforderungGesendet',array($to)).'</span>';
			}
			else
			{
				$message.='<br><span class="error">'.$p->t('news/fehlerBeimSenden',array($to)).'</span>';
			}
		}
		else
		{
			$message.='<br><span class="error">'.$p->t('news/keinUebersetzerVorhanden').'</span>';
		}
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

	if($studiengang_kz=='0' && $semester=='' && !$berechtigt)
	{
		die($p->t('global/keineBerechtigungFuerDieseSeite'));
	}

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
		<table  width="100%">
			<tr>
				<td width="30%">
					<table>
						<tr>
							<td nowrap>'.$p->t('news/sichtbarab').'</td>
							<td><input class="datepicker_datum" type="text" name="datum" size="10" value="'.($news->datum!=''?$datum_obj->formatDatum($news->datum,'d.m.Y'):date('d.m.Y')).'"></td>
						</tr>
						<tr>
							<td valign="top" nowrap>'.$p->t('news/sichtbarbis').'</td>
							<td><input class="datepicker_datum" type="text" name="datum_bis" size="10" value="'.$datum_obj->formatDatum($news->datum_bis,'d.m.Y').'">'.$p->t('news/maximal30Tage').'</td>
						</tr>
					</table>
				</td>
				<td>';

//DropDown fuer Studiengang und Semester anzeigen
if($studiengang_kz!='')
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
		echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.' ('.$row->bezeichnung.')</OPTION>';
	}
	echo '		</SELECT>
				</td>
			</tr>';

	echo '<tr>
			<td>'.$p->t('global/semester').'</td>
			<td>
			<SELECT name="semester">';
	echo '<OPTION value="">'.$p->t('news/allesemester').'</OPTION>';

	for($i=0;$i<=8;$i++)
	{
		if($semester!='' && $i==$semester)
			$selected='selected';
		else
			$selected='';

		echo '<OPTION value="'.$i.'" '.$selected.'>'.$p->t('news/xsemester',array($i)).'</OPTION>';
	}
	if($studiengang_kz=='10006')
	{
		echo '<OPTION value=""NULL"">'.$p->t('news/keinSemester').'</OPTION>';
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

echo '</td><td align="right" valign="top">'.$message.'</td></tr></table>';

//Tabs fuer alle vorhandenen Sprachen anlegen
echo '<div id="tabs" style="font-size:80%;">
		<ul class="css-tabs">';

foreach($sprachen as $lang)
{
	$sprache_obj = new sprache();
	$bezeichnung = $sprache_obj->getBezeichnung($lang, $sprache);
	echo '<li><a href="#'.$lang.'">'.$bezeichnung.'</a></li>';
}
if($news->content_id!='')
{
	echo '<li><a href="#add" title="'.$p->t('news/uebersetzen').'">+</a></li>';
}
echo '</ul>';
$idx=0;
foreach($sprachen as $lang)
{
	$sprachindex[$lang]=$idx;
	$idx++;

	$verfasser='';
	$betreff='';
	$text='';
	$sichtbar='';
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

		$sichtbar = $content->sichtbar;
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
			<tr>
				<td>'.$p->t('news/veroeffentlichen').'</td>
				<td><input type="checkbox" name="sichtbar_'.$lang.'" '.($sichtbar==true?'checked':'').'></td>
			</tr>
		</table>';

	echo '</div>';
}

//Anlegen von Uebersetzungen
if($news->content_id!='')
{
	echo '<div id="add">';
	$content = new content();
	if(!$vorhandene_sprachen = $content->getLanguages($news->content_id))
		die($content->errormsg);
	$sprache_obj = new sprache();
	$sprache_obj->getAll(true);

	//Wenn noch nicht alle Uebersetzungen vorhanden sind,
	//wird ein Link zum Erstellen der Uebersetzung angezeigt.
	if(count($vorhandene_sprachen)<count($sprache_obj->result))
	{

		echo $p->t('news/uebersetzunganlegen');
		foreach($sprache_obj->result as $row)
		{
			if(!in_array($row->sprache, $vorhandene_sprachen))
				echo '<br /><a style="color:#008381" href="'.$_SERVER['PHP_SELF'].'?news_id='.$news_id.'&action=add_uebersetzung&lang='.$row->sprache.'">'.$row->bezeichnung_arr[$sprache].'</a>';
		}
	}
	else
	{
		echo '<br />'.$p->t('news/uebersetzungenvorhanden').'<br />';
	}
	echo '  </div>';
}
echo '</div><br />';
//Beim Speichern wird der Index des Tabs gespeichert damit nachher der richtige wieder markiert werden kann
echo '<input type="hidden" id="tabselect" name="tabselect" value="">';
echo '<input type="submit" name="save" value="'.$p->t('global/speichern').'" onclick="var idx=$( \'#tabs\').tabs(\'option\',\'selected\');$(\'#tabselect\').val(idx);">';
echo '</form>';
if (isset($_POST['tabselect']) && $_POST['tabselect'] != '')
{
	$tabselect = $_POST['tabselect'];
}
else
{
	if (isset($_GET['lang']))
	{
		$tabselect = $sprachindex[$_GET['lang']];
	}
	else
	{
		$tabselect = $sprachindex[DEFAULT_LANGUAGE];
	}
}
echo '<script type="text/javascript">
		$(document).ready(function() {
			$("#tabs").tabs({
				active: 1
			});
			$("#tabs").tabs("option", "active", '.$tabselect.');
		});
	</script>
';

// Newseintraege Anzeigen
echo '<hr>
<table style="width:100%;height:100%;vertical-align:top">
<tr>
	<td style="height:100%;" valign="top">
		<h3>Nicht veröffentlicht</h3>
		<iframe src="news.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'&edit=true&sichtbar=false" style="width: 95%;height:100%;"></iframe>
	</td>
	<td valign="top">
		<h3>Veröffentlicht</h3>
		<iframe src="news.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'&edit=true" style="width: 95%;height:100%;"></iframe>
	</td>
</tr></table>';
echo '</body>
</html>';
?>
