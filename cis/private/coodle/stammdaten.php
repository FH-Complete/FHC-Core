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
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/coodle.class.php');
require_once('../../../include/datum.class.php');

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);
$datum_obj = new datum();
$db = new basis_db();

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet"  href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" href="../../../skin/styles/jquery.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/styles/jquery-ui.css" type="text/css">
	
	<script src="../../../include/js/jquery.js" type="text/javascript"></script>
	<script src="../../../include/js/jquery-ui.js" type="text/javascript"></script>
	<script type="text/javascript" src="../../../include/tiny_mce/tiny_mce.js"></script>
	
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
		theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,|,bullist,pastetext", 
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
	</script>
	<title>'.$p->t('coodle/coodle').'</title>
</head>
<body>';
echo '<h1>'.$p->t('coodle/coodle').'</h1>';

if(isset($_POST['save']))
{
	//Speichern
	$titel = $_POST['titel'];
	$beschreibung = $_POST['beschreibung'];
	$dauer = $_POST['dauer'];
	$endedatum = $_POST['endedatum'];
	$coodle_id = $_POST['coodle_id'];
	
	$coodle = new coodle();
	
	if($coodle_id!='')
	{
		if(!$coodle->load($coodle_id))
			die($coodle->errormsg);
			
		if($coodle->ersteller_uid!=$user)
		{
			die($p->t('basis/keineBerechtigung'));
		}
		$coodle->new=false;
	}
	else
	{
		$coodle->new=true;
		$coodle->ersteller_uid = $user;
		$coodle->insertamum = date('Y-m-d H:i:s');
		$coodle->insertvon = $user;
		$coodle->coodle_status_kurzbz='neu';
	}
		
	$coodle->titel = $titel;
	$coodle->beschreibung = $beschreibung;
	$coodle->dauer = $dauer;
	$coodle->endedatum = $datum_obj->formatDatum($endedatum, 'Y-m-d');
	$coodle->updateamum = date('Y-m-d H:i:s');
	$coodle->updatevon = $user;
	
	if($coodle->save())
	{
		echo '<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
	}
	else
	{
		echo '<span class="error">'.$coodle->errormsg.'</span>';
	}
}
elseif(isset($_GET['coodle_id']))
{
	// Bearbeiten
	$coodle = new coodle();
	if($coodle->load($_GET['coodle_id']))
	{
		if($coodle->ersteller_uid!=$user)
			die($p->t('global/keineBerechtigungFuerDieseSeite'));
	}
}
else
{
	// Neu
	$coodle = new coodle();
	$coodle->endedatum=date('d.m.Y',strtotime("+7 day"));
	$coodle->dauer=60;
}
echo '
<form method="POST">
<input type="hidden" name="coodle_id" value="'.$db->convert_html_chars($coodle->coodle_id).'" />
<table>
	<tr>
		<td>'.$p->t('coodle/titel').'</td>
		<td><input type="text" name="titel" value="'.$db->convert_html_chars($coodle->titel).'" maxlength="64" size="50"/></td>
	</tr>
	<tr>
		<td>'.$p->t('coodle/beschreibung').'</td>
		<td><textarea name="beschreibung" rows="6" cols="50">'.$db->convert_html_chars($coodle->beschreibung).'</textarea></td>
	</tr>
	<tr>
		<td>'.$p->t('coodle/dauer').'</td>
		<td><input type="text" name="dauer" value="'.$db->convert_html_chars($coodle->dauer).'" maxlength="5" size="2"/></td>
	</tr>
	<tr>
		<td>'.$p->t('coodle/endedatum').'</td>
		<td><input type="text" name="endedatum" value="'.$db->convert_html_chars($datum_obj->formatDatum($coodle->endedatum,'d.m.Y')).'" maxlength="10" size="5"/></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" name="save" value="'.$p->t('global/speichern').'"/></td>
	</tr>
</table>
</form>';

echo '</body>
</html>';
?>