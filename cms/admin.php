<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
session_start();
require_once('../config/cis.config.inc.php');
require_once('../include/content.class.php');
require_once('../include/template.class.php');
require_once('../include/functions.inc.php');
require_once('../include/sprache.class.php');
require_once('../include/gruppe.class.php');
require_once('../include/datum.class.php');
require_once('../include/mail.class.php');
require_once('../include/benutzerfunktion.class.php');
require_once('../include/organisationseinheit.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/xsdformprinter/xsdformprinter.php');
require_once('../include/DifferenceEngine/DifferenceEngine.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/cms'))
	die('Sie haben keine Berechtigung fuer diese Seite');
	
$berechtigte_oe = $rechte->getOEkurzbz('basis/cms')
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>FH Complete CMS ContentEditor</title>
	<link href="../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<link href="../skin/jquery.css" rel="stylesheet" type="text/css"/>
	<link href="../skin/fhcomplete.css" rel="stylesheet" type="text/css">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../include/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="../include/js/jquery.js"></script>
		
	<script type="text/javascript">

	tinyMCE.init
	(
		{
		mode : "textareas",
		theme : "advanced",
		language : "de",
		file_browser_callback: "FHCFileBrowser",
		
		plugins : "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
			
		// Theme options
        theme_advanced_buttons1 : "code, bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,|,print,|,ltr,rtl,|,fullscreen",
        //theme_advanced_buttons4 : "insertfile,insertimage",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "center",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        force_br_newlines : true,
        force_p_newlines : false,
        forced_root_block : '',
        editor_deselector : "mceNoEditor"		
		}
	);
	function FHCFileBrowser(field_name, url, type, win) 
	{
		cmsURL = "<?php echo APP_ROOT;?>cms/tinymce_dms.php?type="+type;
		tinyMCE.activeEditor.windowManager.open({
			file: cmsURL,
			title : "FHComplete File Browser",
			width: 800,
			height: 600,
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

<body>
<?php

$sprache = isset($_GET['sprache'])?$_GET['sprache']:DEFAULT_LANGUAGE;
$version = isset($_GET['version'])?$_GET['version']:null;
$content_id = isset($_GET['content_id'])?$_GET['content_id']:null;
$action = isset($_GET['action'])?$_GET['action']:'';
$method = isset($_GET['method'])?$_GET['method']:null;
$message = '';
$submenu_depth=0;
$datum_obj = new datum();

//Inhalt Speichern
if(isset($_POST['XSDFormPrinter_XML']))
{
	$content = new content();
	$content->getContent($content_id, $sprache, $version);

	
	if($content->saveContent($content->contentsprache_id, $_POST['XSDFormPrinter_XML']))
		$message.= '<span class="ok">Inhalt wurde erfolgreich gespeichert</span>';
	else
		$message.= '<span class="error">'.$content->errormsg.'</span>';
}

if(!is_null($method))
{
	switch($method)
	{
		case 'content_sperre':
			//Sperren und Freigeben von Content
			if(!isset($_GET['contentsprache_id']))
				die('Falsche Parameteruebergabe');
				
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
				
			$contentsprache_id=$_GET['contentsprache_id'];
			if(isset($_POST['sperren']))
			{
				$content = new content();
				if($content->sperren($contentsprache_id, $user))
					$message.='<span class="ok">Eintrag gesperrt</span>';
				else
					$message.='<span class="error">'.$content->errormsg.'</span>';
			}
			elseif(isset($_POST['freigeben']))
			{
				$content = new content();
				if($content->freigeben($contentsprache_id, $user))
					$message.='<span class="ok">Eintrag freigegeben</span>';
				else
					$message.='<span class="error">'.$content->errormsg.'</span>';
			}
			else
			{
				$message.='<span class="error">Unbekannte Sperre</span>';
			}
			break;
		case 'add_new_content':
			//Anlegen von neuem Content
			if(!$rechte->isBerechtigt('basis/cms', null, 'sui'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			$template = new template();
			$template->getAll();
			if(!isset($template->result[0]))
				die('Es ist kein Template vorhanden');
				
			if(in_array('etw',$berechtigte_oe))
				$oe = 'etw';
			else
				$oe = $berechtigte_oe[0];
			
			$content = new content();
			$content->new = true;
			$content->oe_kurzbz=$oe;
			$content->template_kurzbz=$template->result[0]->template_kurzbz;
			$content->titel = 'Neuer Eintrag';
			$content->aktiv=true;
			$content->menu_open=false;
			$content->content = '<?xml version="1.0" encoding="UTF-8" ?><content></content>';		
			$content->sichtbar=true;
			$content->version='1';
			$content->sprache=DEFAULT_LANGUAGE;
			$content->insertvon = $user;
			$content->insertamum = date('Y-m-d H:i:s');
			$content->beschreibung = '';
			
			if($content->save())
			{
				if($content->saveContentSprache())
				{
					$message .= '<span class="ok">Eintrag wurde erfolgreich angelegt</span>';
					$action='prefs';
					$content_id=$content->content_id;
					$version=1;
					$sprache=DEFAULT_LANGUAGE;
				}
				else
					$message .= '<span class="error">'.$content->errormsg.'</span>';
			}
			else
				$message .= '<span class="error">'.$content->errormsg.'</span>';

			break;
		case 'add_uebersetzung':
			//Anlegen von Uebersetzungen
			if(!$rechte->isBerechtigt('basis/cms', null, 'sui'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			$content = new content();
			$content->getContent($content_id);
			
			$content->new = true;
			$content->sichtbar=true;
			$content->sprache=$_POST['sprache'];
			$content->insertvon = $user;
			$content->insertamum = date('Y-m-d H:i:s');
			$content->updatevon = $user;
			$content->updateamum = date('Y-m-d H:i:s');
			$content->reviewvon='';
			$content->reviewamum='';
			$content->gesperrt_uid='';
			
			if($content->saveContentSprache())
			{
				$message .= '<span class="ok">Eintrag wurde erfolgreich angelegt</span>';
				$action='prefs';
				$sprache=$_POST['sprache'];
			}
			else
				$message .= '<span class="error">'.$content->errormsg.'</span>';

			break;
		case 'add_newversion':
			//Neue Version anlegen
			if(!$rechte->isBerechtigt('basis/cms', null, 'sui'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			$content = new content();
			$content->getContent($content_id, $sprache);
			$maxversion = $content->getMaxVersion($content_id, $content->sprache);
			
			$content->new = true;
			$content->sichtbar=false;
			$content->reviewvon='';
			$content->reviewamum='';
			$content->version=$maxversion+1;
			$content->insertvon = $user;
			$content->insertamum = date('Y-m-d H:i:s');
			$content->updatevon = $user;
			$content->updateamum = date('Y-m-d H:i:s');
			$content->gesperrt_uid='';
			
			if($content->saveContentSprache())
			{
				$message .= '<span class="ok">Eintrag wurde erfolgreich angelegt</span>';
				$action='prefs';
				$version = $content->version;
			}
			else
				$message .= '<span class="error">'.$content->errormsg.'</span>';

			break;
		case 'rights_add_group':
			//Gruppe fuer Berechtigung hinzufuegen
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			if(!isset($_POST['gruppe_kurzbz']))
				die('Fehlender Parameter');
			
			$content = new content();
			$content->gruppe_kurzbz = $_POST['gruppe_kurzbz'];
			$content->insertamum = date('Y-m-d H:i:s');
			$content->insertvon = $user;
			$content->content_id=$content_id;
			
			if(!$content->addGruppe())
				$message .= '<span class="error">'.$content->errormsg.'</span>';
			else
				$message .= '<span class="ok">Gruppe wurde erfolgreich hinzugefügt</span>';
			
			break;
		case 'rights_delete_group':
			//Gruppe fuer Berechtigung entfernen
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			if(!isset($_GET['gruppe_kurzbz']))
				die('Fehlender Parameter');
			
			$content = new content();
			if(!$content->deleteGruppe($content_id, $_GET['gruppe_kurzbz']))
				$message .= '<span class="error">'.$content->errormsg.'</span>';
			else
				$message .= '<span class="ok">Gruppe wurde erfolgreich entfernt</span>';
			
			break;
		case 'prefs_save':
			//Einstellungen speichern
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			$content = new content();
			$titel = $_POST['titel'];
			$oe_kurzbz=$_POST['oe_kurzbz'];
			$sichtbar=isset($_POST['sichtbar']);
			$aktiv=isset($_POST['aktiv']);
			$menu_open=isset($_POST['menu_open']);
			$template_kurzbz = $_POST['template_kurzbz'];
			$beschreibung = $_POST['beschreibung'];
			
			if($content->getContent($content_id, $sprache, $version))
			{
				$content->titel = $titel;
				$content->oe_kurzbz = $oe_kurzbz;
				$content->sichtbar = $sichtbar;
				$content->aktiv = $aktiv;
				$content->menu_open = $menu_open;
				$content->template_kurzbz = $template_kurzbz;
				$content->updateamum=date('Y-m-d H:i:s');
				$content->updatevon=$user;
				$content->beschreibung = $beschreibung;
				
				if($content->save())
				{
					if($content->saveContentSprache())
						$message.='<span class="ok">Daten erfolgreich gespeichert</span>';
					else
						$message.='<span class="error">'.$content->errormsg.'</span>';
				}
				else
					$message.='<span class="error">'.$content->errormsg.'</span>';
			}
			else
				$message.='<span class="error">'.$content->errormsg.'</span>';
			break;
		case 'prefs_reviewed':
			//Review und sichtbar schalten
			$bf = new benutzerfunktion();
			if($bf->benutzerfunktion_exists($user, 'review') || $rechte->isBerechtigt('basis/cms_review'))
			{
				$content = new content();
				$content->getContent($content_id, $sprache, $version);
				
				$content->reviewamum = date('Y-m-d H:i:s');
				$content->reviewvon = $user;
				$content->sichtbar = true;
				
				if($content->saveContentSprache(false))
					$message.='<span class="ok">Erfolgreich reviewed</span>';
				else
					$message.='<span class="error">'.$content->errormsg.'</span>';
			}
			else
			{
				$message.='<span class="error">Sie dürfen kein Review durchführen</span>';
			}
			break;
		case 'prefs_requestreview':
			//Review beantragen
			$content = new content();
			$content->getContent($content_id, $sprache, $version);

			$oe = new organisationseinheit();
			$oe_arr = $oe->getParents($content->oe_kurzbz);
			
			foreach($oe_arr as $organisationseinheit)
			{
				echo $organisationseinheit;
				$fkt = new benutzerfunktion();
				$fkt->getBenutzerFunktionen('review', $organisationseinheit);
				if(count($fkt->result)>0)
					break;
			}
			
			if(count($fkt->result)==0)
				$fkt->getBenutzerFunktionen('review');
			$to='';
			foreach($fkt->result as $row)
			{
				if($to!='')
					$to.=',';
				$to .= $row->uid.'@'.DOMAIN;
			}
			if($to!='')
			{
				$from = 'no-reply@'.DOMAIN;
				$subject = 'CMS Review Request';
				$text = "Dies ist eine automatisch generierte E-Mail.\n\n
						Es wurde ein Review für die Seite '$content->titel' ($sprache, Version $version) angefordert.\n
						\n
						(um den Link anzuzeigen müssen Sie in die HTML Ansicht wechseln)
						\n
						\n
						Mit freundlichen Grüßen\n
						\n
						FH Technikum Wien\n
						Hoechstaedtplatz 5, 1200 Wien, AUSTRIA";
				$texthtml = "Dies ist eine automatisch generierte E-Mail.<br><br>
						Es wurde ein Review für die Seite '$content->titel' ($sprache, Version $version) angefordert.<br>
						<br>
						<a href=\"".APP_ROOT."cms/admin.php?content_id=".$content->content_id."&sprache=$sprache&version=$version&action=content\">zum Artikel</a>
						<br>
						<br>
						Mit freundlichen Grüßen<br>
						<br>
						FH Technikum Wien<br>
						Hoechstaedtplatz 5, 1200 Wien, AUSTRIA
						";
				
				$mail = new mail($to, $from, $subject, $text);
				$mail->setHTMLContent($texthtml);
				if($mail->send())
				{
					$message.='<span class="ok">Review Anforderung wurde an '.$to.' versendet</span>';
				}
				else
				{
					$message.='<span class="error">Fehler beim Senden des Mails an '.$to.'</span>';
				}
			}
			else
			{
				$message.='<span class="error">Es ist kein Review Team vorhanden</span>';
			}
			break;
		case 'prefs_requesttranslate':
			//Uebersetzer Informieren
			$content = new content();
			$content->getContent($content_id, $sprache, $version);

			$oe = new organisationseinheit();
			$oe_arr = $oe->getParents($content->oe_kurzbz);
			
			foreach($oe_arr as $organisationseinheit)
			{
				echo $organisationseinheit;
				$fkt = new benutzerfunktion();
				$fkt->getBenutzerFunktionen('translate', $organisationseinheit);
				if(count($fkt->result)>0)
					break;
			}
			
			if(count($fkt->result)==0)
				$fkt->getBenutzerFunktionen('translate');
			$to='';
			foreach($fkt->result as $row)
			{
				if($to!='')
					$to.=',';
				$to .= $row->uid.'@'.DOMAIN;
			}
			if($to!='')
			{
				$from = 'no-reply@'.DOMAIN;
				$subject = 'CMS Review Request';
				$text = "Dies ist eine automatisch generierte E-Mail.\n\n
						Es wurde ein Artikel angelegt/bearbeitet. Dieser kann nun übersetzt werden: '$content->titel'.\n
						\n
						(um den Link anzuzeigen müssen Sie in die HTML Ansicht wechseln)
						\n
						\n
						Mit freundlichen Grüßen\n
						\n
						FH Technikum Wien\n
						Hoechstaedtplatz 5, 1200 Wien, AUSTRIA";
				$texthtml = "Dies ist eine automatisch generierte E-Mail.<br><br>
						Es wurde ein Artikel angelegt/bearbeitet. Dieser kann nun übersetzt werden: '$content->titel'<br>
						<br>
						<a href=\"".APP_ROOT."cms/admin.php?content_id=".$content->content_id."&sprache=$sprache&version=$version)&action=content\">zum Artikel</a>
						<br>
						<br>
						Mit freundlichen Grüßen<br>
						<br>
						FH Technikum Wien<br>
						Hoechstaedtplatz 5, 1200 Wien, AUSTRIA
						";
				
				$mail = new mail($to, $from, $subject, $text);
				$mail->setHTMLContent($texthtml);
				if($mail->send())
				{
					$message.='<span class="ok">Übersetzungsanforderung wurde an '.$to.' versendet</span>';
				}
				else
				{
					$message.='<span class="error">Fehler beim Senden des Mails an '.$to.'</span>';
				}
			}
			else
			{
				$message.='<span class="error">Es ist kein Übersetzer eingetragen</span>';
			}
			break;
		case 'childs_add':
			//Untereintraege zuordnen
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			$content = new content();
			$content->content_id = $content_id;
			$content->child_content_id = $_POST['child_content_id'];
			$content->insertamum = date('Y-m-d');
			$content->insertvon = $user;
			$content->sort=$content->getMaxSort($content_id)+1;
			
			if($content->addChild())
				$message.='<span class="ok">Daten erfolgreich gespeichert</span>';
			else
				$message.='<span class="error">'.$content->errormsg.'</span>';
			break;
		case 'childs_delete':
			//Untereintraege entfernen
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			if(isset($_GET['contentchild_id']))
			{
				$contentchild_id = $_GET['contentchild_id'];
				$content = new content();
				if($content->deleteChild($contentchild_id))
					$message.='<span class="ok">Zuordnung wurde erfolgreich entfernt</span>';
				else
					$message.='<span class="error">'.$content->errormsg.'</span>';				
			}
			else
			{
				$message.='<span class="error">Fehler: ID wurde nicht uebergeben</span>';
			}
			break;
		case 'childs_sort_up':
			//hochsortieren von Untereintraegen
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}
			
			if(isset($_GET['contentchild_id']))
			{
				$contentchild_id = $_GET['contentchild_id'];
				$content = new content();
				if($content->SortUp($contentchild_id))
					$message.='<span class="ok">Sortieren erfolgreich</span>';
				else
					$message.='<span class="error">'.$content->errormsg.'</span>';				
			}
			else
			{
				$message.='<span class="error">Fehler: ID wurde nicht uebergeben</span>';
			}
			break;
		case 'childs_sort_down':
			//runtersortieren von Untereintraegen
			if(!$rechte->isBerechtigt('basis/cms', null, 'su'))
			{
				$message.='<span class="error">Sie haben keine Berechtigung fuer diese Aktion</span>';
				break;
			}				
			
			if(isset($_GET['contentchild_id']))
			{
				$contentchild_id = $_GET['contentchild_id'];
				$content = new content();
				if($content->SortDown($contentchild_id))
					$message.='<span class="ok">Sortieren erfolgrecih</span>';
				else
					$message.='<span class="error">'.$content->errormsg.'</span>';				
			}
			else
			{
				$message.='<span class="error">Fehler: ID wurde nicht uebergeben</span>';
			}
			break;
		default: break;
	}
}
//Menue Baum
echo '<table width="100%">
	<tr>
		<td colspan="2">
		<h1>FH Complete CMS</h1>
		</td>
	</tr>
	<tr>
		<td valign="top" width="200px">';


$db = new basis_db();

echo '
<a href="'.$_SERVER['PHP_SELF'].'?action=prefs&method=add_new_content">Neuen Eintrag hinzufügen</a>
<br><br>

<a href="admin.php?content_id='.$content_id.'&action='.$action.'&sprache='.$sprache.'&menu=content">Content</a> | 
<a href="admin.php?content_id='.$content_id.'&action='.$action.'&sprache='.$sprache.'&menu=news">News</a>

<table class="treetable" >';

$menu='content';
if(isset($_GET['menu']))
{
	$_SESSION['cms/menu']=$_GET['menu'];
	$menu=$_GET['menu'];
}
else
{
	if(isset($_SESSION['cms/menu']))
		$menu=$_SESSION['cms/menu'];
	else
		$menu='content';		
}

if($menu=='news')
{
	$rootcontent = new content();
	$rootcontent->getNews();
}
else
{
	$rootcontent = new content();
	$rootcontent->getRootContent();
}

foreach($rootcontent->result as $row)
{
	$output='';
	$output.= '<tr>';
	$content = new content();
	$content->getContent($row->content_id, $sprache, null, null, true);
	
	if($menu=='news' && $content->template_kurzbz!='news')
		continue;
	if($menu=='content' && $content->template_kurzbz=='news')
		continue;
	
	if($content->template_kurzbz=='news')
	{
		$output.= '<td>';
		$output.= drawmenulink($row->content_id, mb_substr($content->titel,0,15).' '.$datum_obj->formatDatum($content->insertamum,'d.m.Y'), $content->oe_kurzbz);
		
		$output.= '</td>';
	}
	else
	{
		$output.= '<td><br>';
		$output.= drawmenulink($row->content_id, $content->titel, $content->oe_kurzbz);
		
		$output.= '</td>';
		$submenu_depth=0;
		$output .= drawsubmenu($row->content_id);
	}
	
	//Wenn im gesamten Subtree kein Eintrag vorhanden ist auf den eine Berechtigung vorhanden ist,
	//dann wird der ganze Subtree nicht angezeigt.
	if($output!='' && strstr($output,'<a href='))
		echo $output.'</tr>';
			
}


echo '</table>';

echo '</td><td valign="top">';

//Editieren
if(!is_null($content_id) && $content_id!='')
{
	echo '<h2>Content ID: '.$content_id.' | Version:'.$version.' | Sprache:'.$sprache.'</h2>';
	$content = new content();
	$oe = $content->getOrganisationseinheit($content_id);
	if(!in_array($oe, $berechtigte_oe))
		die('Sie haben keine Berechtigung fuer diesen Eintrag');
	
	drawheader();
	
	echo '<div style="float: right;">'.$message.'</div>';
	echo '<br><br>';

	
	switch($action)
	{
		case 'prefs':
					print_prefs(); 
					break;
		case 'content': 
					print_content();
					break;
		//case 'preview': 
					
			//		break;
		case 'rights': 
					print_rights();
					break;
		case 'childs':
					print_childs();
					break;
		case 'history':
					print_history();
					break;
		default: break;
	}
	
}
echo '</td></tr></table>';
echo '</body>
</html>';

/******* FUNCTIONS **********/
/**
 * Header fuer Content
 */
function drawheader()
{
	global $content_id, $action, $sprache, $version, $action;
	
	//vorhandene Versionen dieser Sprache anzeigen
	$content = new content();
	$content->loadVersionen($content_id, $sprache);
	echo '<table width="100%">
		<tr>
			<td width="33%">';
	echo 'Versionen: ';

	foreach($content->result as $row)
	{
		if($version=='')
			$version=$row->version;
		
		if($version==$row->version)
			$class='marked';
		else
			$class='';
		
		echo ' <a href="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$row->version.'&action='.$action.'" class="'.$class.'">';
		echo $row->version;
		echo '</a>, ';
	}
	echo '<br>';
	//vorhandene Sprachen dieses Contents anzeigen
	$content = new content();
	if(!$vorhandene_sprachen = $content->getLanguages($content_id, $version))
		die($content->errormsg);
	echo 'Sprachen: ';
	foreach($vorhandene_sprachen as $lang)
	{
		if($sprache==$lang)
			$class='marked';
		else
			$class='';
		echo ' <a href="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$lang.'&action='.$action.'" class="'.$class.'">'; //&version='.$version.'
		echo $lang;
		echo '</a>,';
	}
	echo '</td><td align="center" width="33%">';
	echo '<form action="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&action='.$action.'&method=add_newversion" method="POST">';
	echo '<input type="submit" value="Neue Version anlegen">';
	echo '</form>';
	echo '</td><td align="right" width="33%">';
	$sprache_obj = new sprache();
	$sprache_obj->getAll();
	
	//Wenn noch nicht alle Uebersetzungen vorhanden sind, 
	//wird ein Formular zum Erstellen der Uebersetzung angezeigt.
	if(count($vorhandene_sprachen)!=count($sprache_obj->result))
	{	
		echo '<form action="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&action='.$action.'&method=add_uebersetzung" method="POST">';
		echo 'Übersetzung in <SELECT name="sprache">';
		foreach($sprache_obj->result as $row)
		{
			if(!in_array($row->sprache, $vorhandene_sprachen))
				echo '<option value="'.$row->sprache.'">'.$row->bezeichnung_arr[$sprache].'</option>';		
		}
		echo '</SELECT>';
		echo '<input type="submit" value="anlegen">';
		echo '</form>';
	}
	echo '</td></tr>';
	echo '</table><hr>';
	
	echo get_content_link('prefs','Eigenschaften').' | ';
	echo get_content_link('content','Inhalt').' | ';
	echo get_content_link('rights','Rechte').' | ';
	echo get_content_link('childs','Childs').' | ';
	echo get_content_link('history','History');
}
/**
 * Gibt einen Menue Link aus
 * @param $id
 * @param $titel
 */
function drawmenulink($id, $titel, $oe_kurzbz)
{
	global $content_id, $action, $sprache, $berechtigte_oe;
	if(in_array($oe_kurzbz, $berechtigte_oe))
		return '<a href="admin.php?content_id='.$id.'&action='.$action.'&sprache='.$sprache.'" '.($content_id==$id?'class="marked"':'').'>'.$titel.'</a> ('.$id.')';
	else
		return $titel.' ('.$id.')';
}

/**
 * Zeichnet ein Submenue unterhalb eines Contents
 * 
 * @param $content_id Content ID des Parents
 * @param $einrueckung Einrueckungszeichen fuer den Content
 */
function drawsubmenu($content_id, $einrueckung="&nbsp;&nbsp;")
{
	global $db, $action, $submenu_depth, $sprache;
	$output='';
	$submenu_depth++;
	if($submenu_depth>10000)
	{
		echo 'Menürekursion?! -> Abbruch';
		return 0;
	}
	$childcontent = new content();
	$childcontent->getChilds($content_id);
	
	foreach($childcontent->result as $row)
	{
		$content = new content();
		$content->getContent($row->content_id, $sprache, null, null, true);
		$output.= "<tr>\n";
		$output.= '<td>';
		$output.= $einrueckung;
		$output.=drawmenulink($row->child_content_id, $content->titel, $content->oe_kurzbz);
		$output.=drawsubmenu($row->child_content_id, $einrueckung."&nbsp;&nbsp;");
		$output.= "</td>\n";
		$output.= "</tr>\n";
	}
	return $output;
}

/**
 * Liefert den Link zum Anzeigen von Content Modulen
 * @param $key Action Key
 * @param $name Name des Links
 */
function get_content_link($key, $name)
{
	global $action, $content_id, $sprache, $version;	
	return '<a href="'.$_SERVER['PHP_SELF'].'?action='.$key.'&content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'" '.($action==$key?'class="marked"':'').'>'.$name.'</a>';
}

/**
 * Erstellt den Karteireiter zum Verwalten der Kindelemente eines Contents
 */
function print_childs()
{
	global $content_id, $sprache, $version, $action;
	
	$content = new content();
	$content->getChilds($content_id);
	
	echo 'Folgende Einträge sind diesem Untergeordnet:<br><br>';
	echo '
	<script type="text/javascript">
		$(document).ready(function() 
		{ 
			$("#childs_table").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			});
		});
	</script>';
	echo '<table id="childs_table" class="tablesorter" style="width: auto;">
		<thead>
		<tr>
			<th>Sortierung</th>
			<th>ID</th>
			<th>Titel</th>
			<th></th>
		</tr>
		</thead>
		<tbody>';
	foreach($content->result as $row)
	{
		$child = new content();
		$child->getContent($row->child_content_id);
		
		echo '<tr>';
		echo '<td>',$row->sort;
		echo '   <a href="'.$_SERVER['PHP_SELF'].'?action=childs&content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&contentchild_id='.$row->contentchild_id.'&method=childs_sort_up" title="Nach oben sortieren"><img src="../skin/images/up.png" alt="up"></a>';
		echo '   <a href="'.$_SERVER['PHP_SELF'].'?action=childs&content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&contentchild_id='.$row->contentchild_id.'&method=childs_sort_down" title="Nach unten sortieren"><img src="../skin/images/down.png" alt="down"></a>';
		echo '</td>';
		echo '<td>',$row->child_content_id,'</td>';
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?action='.$action.'&sprache='.$sprache.'&content_id='.$row->child_content_id.'">',$child->titel,'</a></td>';
		
		echo '<td>
				<a href="'.$_SERVER['PHP_SELF'].'?action=childs&content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&contentchild_id='.$row->contentchild_id.'&method=childs_delete" title="entfernen">
					<img src="../skin/images/delete_x.png">
				</a>
			</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
	
	$content = new content();
	$content->getpossibleChilds($content_id);
	echo '<form action="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=childs&method=childs_add" method="POST">';
	
	echo '<select name="child_content_id">';
	foreach($content->result as $row)
	{
		echo '<option value="'.$row->content_id.'">'.$row->titel.' ('.$row->content_id.')</option>';
	}
	echo '</select>';
	echo '<input type="submit" value="Hinzufügen" name="add">';
	echo '</form>';
}

/**
 * Erstellt den Karteireiter zum Eintragen der Eigenschaften eines Contents
 * 
 */
function print_prefs()
{
	global $content_id, $sprache, $version, $user, $rechte;
	
	$content = new content();
	if(!$content->getContent($content_id, $sprache, $version))
		die($content->errormsg);
		
	echo '<form name="form_pref" action="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=prefs&method=prefs_save" method="POST">
	<table>
		
		<tr>
			<td>Vorlage</td>
			<td>
				<SELECT name="template_kurzbz" onchange="alert(\'Achtung: Das Ändern der Vorlage kann zum Datenverlust des Contents führen!\n\nÄndern Sie die Vorlage nur wenn Sie wirklich wissen was sie tun.\');">';
	$template = new template();
	$template->getAll();
	foreach($template->result as $row)
	{
		if($row->template_kurzbz==$content->template_kurzbz)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$row->template_kurzbz.'" '.$selected.'>'.$row->bezeichnung.'</OPTION>';
	}
	echo '	
				</SELECT>
			</td>
		</tr>
		<tr>
			<td>Organisationseinheit</td>
			<td>
				<SELECT name="oe_kurzbz">
	';
	$oe = new organisationseinheit();
	$oe->getAll();
	foreach($oe->result as $row)
	{
		if($row->oe_kurzbz==$content->oe_kurzbz)	
			$selected='selected';
		else
			$selected='';
		if($row->aktiv)
			$class='';
		else
			$class='class="inactive"';
		echo '<OPTION value="'.$row->oe_kurzbz.'" '.$selected.' '.$class.'>'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</OPTION>';
	}
	echo '	
				</SELECT>
			</td>
		</tr>
		<tr>
			<td>Aktiv</td>
			<td><input type="checkbox" name="aktiv" '.($content->aktiv?'checked':'').'></td>
		</tr>
		<tr>
			<td>Menü offen</td>
			<td><input type="checkbox" name="menu_open" '.($content->menu_open?'checked':'').'></td>
		</tr>
		<tr>
			<td>Beschreibung</td>
			<td><textarea name="beschreibung" cols="50" class="mceNoEditor" >'.$content->beschreibung.'</textarea></td>
		</tr>
		<tr>
			<td></td>
			<td><hr></td>
		</tr>
		<tr>
			<td>Titel</td>
			<td><input type="text" name="titel" size="40" maxlength="256" value="'.$content->titel.'"></td>
		</tr>
		
		<tr>
			<td>Sichtbar</td>
			<td><input type="checkbox" name="sichtbar" '.($content->sichtbar?'checked':'').'></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" value="Speichern">';
	if($content->reviewvon!='')
	{
		$datum_obj = new datum();
		echo 'Reviewed von '.$content->reviewvon.' am '.$datum_obj->formatDatum($content->reviewamum,'d.m.Y H:i');
	}
	
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<input type="submit" value="Review anfordern" onclick="document.form_pref.action=\''.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=prefs&method=prefs_requestreview\'">';
	$bf = new benutzerfunktion();
	if($bf->benutzerfunktion_exists($user, 'review')  || $rechte->isBerechtigt('basis/cms_review'))
		echo '<input type="submit" value="Review OK / Publish" onclick="document.form_pref.action=\''.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=prefs&method=prefs_reviewed\'">';
	
	echo '<input type="submit" value="Übersetzer benachrichtigen" onclick="document.form_pref.action=\''.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=prefs&method=prefs_requesttranslate\'">';
	
	
	echo '
			</td>
		</tr>
	</table>';	 
	
}

/**
 * Erstellt den Karteireiter zum Verwalten der Zugriffsrechte auf einen Content
 * Zu einem Content können Gruppen zugeteilt werden. Diese haben dann zugriff auf den Content
 * Wenn keine Gruppen zugeordnet sind, können alle Personen auf den Content zugreifen
 */
function print_rights()
{
	global $content_id, $sprache, $version;
	$content = new content();
	$content->loadGruppen($content_id);
	
	if(count($content->result)>0)
	{
		echo 'Die Mitglieder der folgenden Gruppen dürfen die Seite ansehen:<br><br>';
		echo '
		<script type="text/javascript">
			$(document).ready(function() 
			{ 
				$("#rights_table").tablesorter(
				{
					sortList: [[1,1]],
					widgets: ["zebra"]
				});
			});
		</script>';
		echo '<table id="rights_table" class="tablesorter" style="width: auto;">
			<thead>
			<tr>
				<th>Gruppe Kurzbz</th>
				<th>Bezeichnung</th>
				<th></th>
			</tr>
			</thead>
			<tbody>';
		foreach($content->result as $row)
		{
			echo '<tr>';
			echo '<td>',$row->gruppe_kurzbz,'</td>';
			echo '<td>',$row->bezeichnung,'</td>';
			echo '<td>
					<a href="'.$_SERVER['PHP_SELF'].'?action=rights&content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&gruppe_kurzbz='.$row->gruppe_kurzbz.'&method=rights_delete_group" title="entfernen">
						<img src="../skin/images/delete_x.png">
					</a>
				</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
	else
		echo 'Diese Seite darf von allen angezeigt werden!<br><br>';
		
	$gruppe = new gruppe();
	$gruppe->getgruppe(null, null, null, null, true);
	
	echo '<form action="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=rights&method=rights_add_group" method="POST">';
	echo 'Gruppe <select name="gruppe_kurzbz">';
	foreach($gruppe->result as $row)
	{
		echo '<option value="'.$row->gruppe_kurzbz.'">'.$row->gruppe_kurzbz.'</option>';
	}
	echo '</select>';
	echo '<input type="submit" value="Hinzufügen" name="addgroup">';
	echo '</form>';
}

/**
 * Erstellt den Karteireiter zum Eintragen des Contents
 * 
 * Hier wird Aufgrund der XSD Vorlage des Templates ein Formular erstellt und mit den
 * entsprechenden Werten des XML Files vorausgefuellt. 
 * 
 */
function print_content()
{
	global $content_id, $sprache, $version, $user;

	$content = new content();

	if(!$content->getContent($content_id, $sprache, $version))
		die($content->errormsg);
		
	if($content->gesperrt_uid!='' && $content->gesperrt_uid!=$user)
	{
		$content->getSperrLog($content->contentsprache_id);
		echo "Dieser Content ist gesperrt von $content->uid seit $content->start!";
		return 0;
	}
	
	
	echo '<form action="'.$_SERVER['PHP_SELF'].'?content_id='.$content_id.'&contentsprache_id='.$content->contentsprache_id.'&sprache='.$sprache.'&version='.$version.'&action=content&method=content_sperre" method="POST">';
	if($content->gesperrt_uid=='')
		echo '<input type="submit" value="Zur Bearbeitung sperren" name="sperren">';
	else
		echo '<input type="submit" value="Sperre Freigeben" name="freigeben">';
	echo '</form>';
	
	if($content->gesperrt_uid!='' && $content->gesperrt_uid==$user)
	{
		echo '<div>';
		$template = new template();
		$template->load($content->template_kurzbz);
	
		$xfp = new XSDFormPrinter();
		$xfp->getparams='?content_id='.$content_id.'&sprache='.$sprache.'&version='.$version.'&action=content';
		$xfp->output($template->xsd,$content->content);
		echo '</div>';
	}
	echo '
	<br>
	<h3>Vorschau</h3>';
	//Bei Redirects wird die Vorschau nicht im IFrame gezeigt, da durch eventuelles weiterleiten durch 
	// Javascript in der Vorschau die CMS Seite geschlossen wird.

	if($content->template_kurzbz=='redirect')
		echo '<a href="content.php?content_id='.$content_id.'&version='.$version.'&sprache='.$sprache.'&sichtbar" target="_blank">Vorschau in eigenem Fenster öffnen</a>';
	else
		echo '<iframe src="content.php?content_id='.$content_id.'&version='.$version.'&sprache='.$sprache.'&sichtbar" style="width: 800px; height: 500px; border: 1px solid black;">';
}

/**
 * Zeigt die Historie eines Contents an. 
 * 
 */
function print_history()
{
	global $content_id, $sprache, $version, $method;
	if($method=='history_changes')
	{
		if(!isset($_GET['v1']) || !isset($_GET['v2']))
		{
			echo 'Invalid Parameter';
			return false;
		}
		
		$v1 = $_GET['v1'];
		$v2 = $_GET['v2'];
		
		$content_old = new content();
		$content_old->getContent($content_id, $sprache, $v1);
		$dom = new DOMDocument();
		$dom->loadXML($content_old->content);
		$content_old = $dom->getElementsByTagName('inhalt')->item(0)->nodeValue;
		
		$content_new = new content();
		$content_new->getContent($content_id, $sprache, $v2);
		$dom = new DOMDocument();
		$dom->loadXML($content_new->content);
		$content_new = $dom->getElementsByTagName('inhalt')->item(0)->nodeValue;
		
		$arr_old = explode("\n",trim($content_old));
		$arr_new = explode("\n",trim($content_new));
		
		$diff = new Diff($arr_new, $arr_old);
		$tdf = new TableDiffFormatter();
		echo '<table>';
		echo html_entity_decode($tdf->format($diff));
		echo '</table>';
	}
	else
	{
		$content = new content();
		$content->loadVersionen($content_id, $sprache);
		
		$datum_obj = new datum();
		echo '<h3>Versionen</h3>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
		echo '
			<input type="hidden" name="action" value="history">
			<input type="hidden" name="method" value="history_changes">
			<input type="hidden" name="sprache" value="'.$sprache.'">
			<input type="hidden" name="version" value="'.$version.'">
			<input type="hidden" name="content_id" value="'.$content_id.'">';
		echo 'Änderungen von Version
			<input type="text" value="1" size="2" name="v1"> zu 
			<input type="text" value="2" size="2" name="v2"> 
			<input type="submit" value="Anzeigen">
			</form>'; 
		echo '<ul>';
		foreach($content->result as $row)
		{
			echo '<li>';
			echo '<b>Version '.$row->version.'</b><br>Erstellt am '.$datum_obj->formatDatum($row->insertamum,'d.m.Y').' von '.$row->insertvon;
			if($row->updateamum!='' || $row->updatevon!='')
				echo '<br>Letzte Änderung von '.$row->updatevon.' am '.$datum_obj->formatDatum($row->updateamum,'d.m.Y');
			if($row->reviewvon!='' || $row->reviewamum!='')
				echo '<br>Review von '.$row->reviewvon.' am '.$datum_obj->formatDatum($row->reviewamum,'d.m.Y');
			echo '<br><br>';
			echo '</li>';
		}
		echo '</ul>';
	}
}
?>
