<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Manfred Kindl < manfred.kindl@technikum-wien.at >,
 *			Andreas Österreicher < andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Dokumentvorlagen
 *
 * - Anlegen und Bearbeiten von Dokumentvorlagen
 *
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/vorlage.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/dokument.class.php');
require_once('../../include/sprache.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/datum.class.php');

if (!$db = new basis_db())
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$user = get_uid();
$oe_kurzbz = (isset($_REQUEST['oe_kurzbz']) ? $_REQUEST['oe_kurzbz'] : null);
$oe_auswahl = (isset($_REQUEST['oe_auswahl']) ? $_REQUEST['oe_auswahl'] : $oe_kurzbz);
$vorlage_kurzbz = (isset($_REQUEST['vorlage_kurzbz']) ? $_REQUEST['vorlage_kurzbz'] : null);
$vorlagestudiengang_id = (isset($_REQUEST['vorlagestudiengang_id']) ? $_REQUEST['vorlagestudiengang_id'] : null);
$neu = (isset($_REQUEST['neu']) ? true : false);
$templatesprache = (isset($_REQUEST['templatesprache']) ? $_REQUEST['templatesprache'] : DEFAULT_LANGUAGE);
$orgform_template = (isset($_REQUEST['orgform_template']) ? $_REQUEST['orgform_template'] : null);
$datum = new datum();

$studiengang = new studiengang();
$studiengang->load('0');
$default_oe = $studiengang->oe_kurzbz;


$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/dokumente'))
{
	die($rechte->errormsg);
}

echo '<!DOCTYPE HTML>
<html>
	<head>
		<title>Dokumentvorlagen Verwaltung</title>
		<meta charset="UTF-8" />
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
		<script type="text/javascript" src="../../include/tiny_mce/tiny_mce.js"></script>
		<script type="text/javascript">
			$(document).ready(function()
			{
				$("#t1").tablesorter(
				{
					sortList: [[1,0],[0,0],[3,1]],
					widgets: ["zebra"]
				});
			});
			function confdel(val1,val2)
			{
				return confirm("Wollen Sie die Vorlage "+val1+" Version "+val2+" wirklich loeschen?");
			};
			tinyMCE.init({
				mode: \'specific_textareas\',
				editor_selector: "mceEditor",
				theme: "advanced",
				language: "de",
				file_browser_callback: "FHCFileBrowser",
				plugins: "spellchecker,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking",
				// Theme options
				theme_advanced_buttons1: "code, bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontsizeselect",
				theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_toolbar_location: "top",
				theme_advanced_toolbar_align: "center",
				theme_advanced_statusbar_location: "bottom",
				theme_advanced_resizing: true,
				force_br_newlines: true,
				force_p_newlines: false,
				forced_root_block: \'\',
				editor_deselector: "mceNoEditor",
				content_css : "../../skin/styles/tinymce.css"
			});
		</script>
		<style>
		textarea
		{
			font-family: Tahoma, Arial, Helvetica, Verdana, Geneva, sans-serif; 
			font-size: small;
		}
		#tinymce
		{
			font-size: 14px;
		}
		body
		{
			font-size: 14px;
		}
		</style>
	</head>
	<body class="Background_main">
		<h2>Dokumentvorlagen Verwaltung</h2>';

// Speichern einer Dokumentvorlage
if(isset($_POST['speichern']) || isset($_POST['kopieren']))
{

	if(!$rechte->isBerechtigt('basis/dokumente', $oe_kurzbz, 'sui'))
	{
		die($rechte->errormsg);
	}

	$dokumentvorlage = new vorlage();

	if(isset($_POST['vorlagestudiengang_id']) && $_POST['vorlagestudiengang_id']!='')
	{
		//Vorlage laden
		if(!$dokumentvorlage->loadVorlageOE($_POST['vorlagestudiengang_id']))
		{
			die($dokumentvorlage->errormsg);
		}

		$dokumentvorlage->new=false;
		$dokumentvorlage->vorlagestudiengang_id = $_POST['vorlagestudiengang_id'];
		$dokumentvorlage->version = $_POST['version'];
		$dokumentvorlage->updateamum = date('Y-m-d H:i:s');
		$dokumentvorlage->updatevon = $user;
	}
	else
	{
		//Neue Vorlage anlegen
		$dokumentvorlage->new=true;
		$dokumentvorlage->version = $_POST['version'];
		$dokumentvorlage->insertamum = date('Y-m-d H:i:s');
		$dokumentvorlage->insertvon = $user;
	}

	if (isset($_POST['kopieren']))
	{
		$newVersion = new vorlage();
		$newVersion = ($newVersion->getMaxVersion($_POST['oe_kurzbz'], $_POST['vorlage_kurzbz']))+1;
		$dokumentvorlage->new=true;
		$dokumentvorlage->version = $newVersion;
		$dokumentvorlage->insertamum = date('Y-m-d H:i:s');
		$dokumentvorlage->insertvon = $user;
	}

	$studiengang = new studiengang();
	$studiengang->getStudiengangFromOe($_POST['oe_kurzbz']);

	if ($studiengang->studiengang_kz=='')
		$studiengang_kz = 0;
	else
		$studiengang_kz = $studiengang->studiengang_kz;

	$dokumentvorlage->vorlage_kurzbz = $_POST['vorlage_kurzbz'];
	$dokumentvorlage->studiengang_kz = $studiengang_kz;
	$dokumentvorlage->text = $_POST['content'];
	$dokumentvorlage->oe_kurzbz = $_POST['oe_kurzbz'];
	$dokumentvorlage->style = $_POST['style'];
	$dokumentvorlage->berechtigung = $_POST['berechtigung'];
	$dokumentvorlage->anmerkung_vorlagestudiengang = $_POST['anmerkung'];
	$dokumentvorlage->aktiv = isset($_POST['aktiv']);
	$dokumentvorlage->sprache = $templatesprache;
	$dokumentvorlage->subject = $_POST['subject'];
	$dokumentvorlage->orgform_kurzbz = $orgform_template;

	if($dokumentvorlage->saveVorlageOE())
	{
		echo '<b>Daten wurden erfolgreich gespeichert</b>';
		//$reihungstest_id = $reihungstest->reihungstest_id;
		//$stg_kz = $reihungstest->studiengang_kz;
	}
	else
	{
		echo '<span class="input_error">'.$db->convert_html_chars($dokumentvorlage->errormsg).'</span>';
	}
	$neu=false;
}

// Loeschen einer Dokumentvorlage
if(isset($_GET['delete']))
{

	if(!$rechte->isBerechtigt('basis/dokumente', $oe_kurzbz, 'suid'))
	{
		die($rechte->errormsg);
	}

	if(isset($_GET['vorlagestudiengang_id']) && $_GET['vorlagestudiengang_id']!='')
	{
		$dokumentvorlage = new vorlage();
		if($dokumentvorlage->deleteVorlagestudiengang($_GET['vorlagestudiengang_id']))
			echo '<b>Vorlage wurde erfolgreich gelöscht</b>';
		else
			echo '<span class="input_error">'.$dokumentvorlage->errormsg.'</span>';
	}
	else
		echo '<span class="input_error">Vorlagestudiengang_ID ist nicht gesetzt</span>';

	$neu=true;
}


echo '<br><table width="100%"><tr><td>';

//Vorlagen DropDown
$vorlage = new vorlage();
$vorlage->getAllVorlagen('bezeichnung');

echo "<SELECT name='vorlage_kurzbz' id='vorlage' onchange='window.location.href=this.value'>";
if($vorlage_kurzbz=='')
	$selected='selected';
else
	$selected='';
echo "<OPTION value='".$_SERVER['PHP_SELF']."?vorlage_kurzbz=&oe_kurzbz=$oe_kurzbz' $selected>Alle Vorlagen</OPTION>";
foreach ($vorlage->result as $row)
{
	//if($reihungstest_id=='')
	//	$reihungstest_id=$row->reihungstest_id;
	if($row->vorlage_kurzbz==$vorlage_kurzbz)
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$_SERVER['PHP_SELF'].'?vorlage_kurzbz='.$row->vorlage_kurzbz.'" '.$selected.'>'.$db->convert_html_chars(($row->bezeichnung==''?$row->vorlage_kurzbz:$row->bezeichnung)).'</OPTION>';
	echo "\n";
}
echo '</SELECT>';

//OE-Dropdown
$organisationseinheit = new vorlage();
$organisationseinheit->getOEsFromVorlage($vorlage_kurzbz);

if ($organisationseinheit->result=='')
{
	$organisationseinheit = new organisationseinheit();
	$organisationseinheit->getAll();
}

echo "<SELECT name='oe_kurzbz' id='organisationseinheit' onchange='window.location.href=this.value'>";
if($oe_kurzbz==$default_oe)
	$selected='selected';
else
	$selected='';

echo "<OPTION value='".$_SERVER['PHP_SELF']."?vorlage_kurzbz=$vorlage_kurzbz&oe_kurzbz=' $selected>Alle Organisationseinheiten</OPTION>";
foreach ($organisationseinheit->result as $row)
{
	if($row->oe_kurzbz==$oe_auswahl)
		$selected='selected';
	else
		$selected='';

	echo "<OPTION value='".$_SERVER['PHP_SELF']."?vorlage_kurzbz=$vorlage_kurzbz&oe_kurzbz=$row->oe_kurzbz' $selected>".$db->convert_html_chars($row->organisationseinheittyp_kurzbz." ".$row->bezeichnung)."</OPTION>"."\n";
}
echo "</SELECT>";
echo "<INPUT type='button' value='Anzeigen' onclick='window.location.href=document.getElementById(\"organisationseinheit\").value;'>";
echo "</td></tr></table><br>";

if($vorlagestudiengang_id=='')
	$neu=true;
$vorlageOE = new vorlage();

if(!$neu)
{
	if(!$vorlageOE->loadVorlageOE($vorlagestudiengang_id))
		die('Vorlage existiert nicht');
}
else
{
	$vorlageOE->vorlage_kurzbz = $vorlage_kurzbz;
	$vorlageOE->oe_kurzbz = $oe_kurzbz;
	$vorlageOE->version = ($vorlageOE->getMaxVersion($oe_kurzbz, $vorlage_kurzbz))+1;
}

//Formular zum Bearbeiten der Vorlage
echo '
<input type="button" value="Neue OE-Vorlage anlegen" onclick="window.location.href=\''.$_SERVER['PHP_SELF'].'?vorlage_kurzbz='.$vorlage_kurzbz.'&oe_kurzbz='.$oe_kurzbz.'&neu=true\'" >
<input type="button" value="Neue Vorlage anlegen" onclick="window.location.href=\''.$_SERVER['PHP_SELF'].'?neueVorlage=true\'" >
<input type="button" value="Vorlage bearbeiten" onclick="window.location.href=\''.$_SERVER['PHP_SELF'].'?vorlageBearbeiten='.$vorlage_kurzbz.'\'" >
<hr>';
if ((isset($_GET['neueVorlage']) && $_GET['neueVorlage'] == 'true'))
{
	$dokument = new dokument();
	$dokument->getAllDokumente();
	echo'
			<form action="'.$_SERVER['PHP_SELF'].'?neueVorlage=save" method="POST">
				<table>
					<tr>
						<td>Vorlagenkurzbezeichnung</td>
						<td>
							<input type="text" size="50" maxlength="32" name="neueVorlage_vorlage_kurzbz">
						</td>
					</tr>
					<tr>
						<td>Bezeichnung</td>
						<td>
							<input type="text" size="80" maxlength="64" name="neueVorlage_bezeichnung">
						</td>
					</tr>
					<tr>
						<td>Anmerkung</td>
						<td>
							<textarea cols="80" rows="4" name="neueVorlage_anmerkung"></textarea>
						</td>
					</tr>
					<tr>
						<td>Mimetype</td>
						<td>
							<input type="text" size="80" maxlength="64" name="neueVorlage_mimetype" value="application/vnd.oasis.opendocument.text">
						</td>
					</tr>
					<tr>
						<td>Dokument</td>
						<td>
						<SELECT name="neueVorlage_dokument_kurzbz">
						<option value="">-- keine Auswahl --</option>';
		foreach($dokument->result as $dok)
		{
			if ($dok->dokument_kurzbz == $vorlage->dokument_kurzbz)
				$selected = 'selected';
			else
				$selected = '';

			echo '<option value="'.$dok->dokument_kurzbz.'" '.$selected.'>'.$dok->bezeichnung.'</option>';
		}
		echo '			</SELECT></td>
					</tr>
					<tr>
						<td>Archivierbar</td>
						<td>
							<input type="checkbox" name="neueVorlage_archivierbar" '.($vorlage->archivierbar?'checked="checked"':'').'/>
						</td>
					</tr>
					<tr>
						<td>Signierbar</td>
						<td>
							<input type="checkbox" name="neueVorlage_signierbar" '.($vorlage->signierbar?'checked="checked"':'').'/>
						</td>
					</tr>
					<tr>
						<td>Studierenden Selfservice</td>
						<td>
							<input type="checkbox" name="neueVorlage_stud_selfservice" '.($vorlage->stud_selfservice?'checked="checked"':'').'/>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="submit" value="Neu anlegen">
						</td>
					</tr>
				</table>
			</form>
		';
}
elseif (isset($_GET['vorlageBearbeiten']))
{
	$dokument = new dokument();
	$dokument->getAllDokumente();

	$vorlage = new vorlage();
	$vorlage->loadVorlage($_GET['vorlageBearbeiten']);
	echo'
			<form action="'.$_SERVER['PHP_SELF'].'?updateVorlage=true&vorlage_kurzbz='.$vorlage->vorlage_kurzbz.'" method="POST">
				<table>
					<tr>
						<td>Vorlagenkurzbezeichnung</td>
						<td>
							<input type="text" size="50" maxlength="32" name="updateVorlage_vorlage_kurzbz" value="'.$db->convert_html_chars($vorlage->vorlage_kurzbz).'">
						</td>
					</tr>
					<tr>
						<td>Bezeichnung</td>
						<td>
							<input type="text" size="80" maxlength="64" name="updateVorlage_bezeichnung" value="'.$db->convert_html_chars($vorlage->bezeichnung).'">
						</td>
					</tr>
					<tr>
						<td>Anmerkung</td>
						<td>
							<textarea cols="80" rows="4" name="updateVorlage_anmerkung">'.$db->convert_html_chars($vorlage->anmerkung).'</textarea>
						</td>
					</tr>
					<tr>
						<td>Mimetype</td>
						<td>
							<input type="text" size="80" maxlength="64" name="updateVorlage_mimetype" value="'.$db->convert_html_chars($vorlage->mimetype).'">
						</td>
					</tr>
					<tr>
						<td>Dokument</td>
						<td>
						<SELECT name="updateVorlage_dokument_kurzbz">
						<option value="">-- keine Auswahl --</option>';
		foreach($dokument->result as $dok)
		{
			if ($dok->dokument_kurzbz == $vorlage->dokument_kurzbz)
				$selected = 'selected';
			else
				$selected = '';

			echo '<option value="'.$dok->dokument_kurzbz.'" '.$selected.'>'.$dok->bezeichnung.'</option>';
		}
		echo '			</SELECT></td>
					</tr>
					<tr>
						<td>Archivierbar</td>
						<td>
							<input type="checkbox" name="updateVorlage_archivierbar" '.($vorlage->archivierbar?'checked="checked"':'').'/>
						</td>
					</tr>
					<tr>
						<td>Signierbar</td>
						<td>
							<input type="checkbox" name="updateVorlage_signierbar" '.($vorlage->signierbar?'checked="checked"':'').'/>
						</td>
					</tr>
					<tr>
						<td>Studierenden Selfservice</td>
						<td>
							<input type="checkbox" name="updateVorlage_stud_selfservice" '.($vorlage->stud_selfservice?'checked="checked"':'').'/>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<input type="submit" style="padding: 2px 4px" name="updateVorlage_speichern" value="Änderungen Speichern">&nbsp;&nbsp;
							<input type="submit" style="padding: 2px 4px" name="updateVorlage_kopieren" value="Kopie anlegen">
						</td>
					</tr>
				</table>
			</form>
		';
}
else
{
	if ((isset($_GET['neueVorlage']) && $_GET['neueVorlage'] == 'save'))
	{
		$neuevorlage = new vorlage();
		$neuevorlage->vorlage_kurzbz = htmlspecialchars($_POST['neueVorlage_vorlage_kurzbz']);
		$neuevorlage->bezeichnung = htmlspecialchars($_POST['neueVorlage_bezeichnung']);
		$neuevorlage->anmerkung = htmlspecialchars($_POST['neueVorlage_anmerkung']);
		$neuevorlage->mimetype = htmlspecialchars($_POST['neueVorlage_mimetype']);
		$neuevorlage->dokument_kurzbz = htmlspecialchars($_POST['neueVorlage_dokument_kurzbz']);
		$neuevorlage->archivierbar = isset($_POST['neueVorlage_archivierbar']);
		$neuevorlage->signierbar = isset($_POST['neueVorlage_signierbar']);
		$neuevorlage->stud_selfservice = isset($_POST['neueVorlage_stud_selfservice']);
		$neuevorlage->insertamum = date('Y-m-d H:i:s');
		$neuevorlage->insertvon = $user;
		if (!($neuevorlage->saveVorlage(true)))
		{
			echo 'Fehler beim Speichern';
		}
	}
	elseif (isset($_GET['updateVorlage']) && $_GET['updateVorlage'] == 'true')
	{
		$updatevorlage = new vorlage();

		if (isset($_POST['updateVorlage_speichern']))
		{
			$updatevorlage->loadVorlage($_GET['vorlage_kurzbz']);
		}
		else
		{
			$updatevorlage->vorlage_kurzbz = htmlspecialchars($_POST['updateVorlage_vorlage_kurzbz']);
		}

		$updatevorlage->bezeichnung = htmlspecialchars($_POST['updateVorlage_bezeichnung']);
		$updatevorlage->anmerkung = htmlspecialchars($_POST['updateVorlage_anmerkung']);
		$updatevorlage->mimetype = htmlspecialchars($_POST['updateVorlage_mimetype']);
		$updatevorlage->dokument_kurzbz = htmlspecialchars($_POST['updateVorlage_dokument_kurzbz']);
		$updatevorlage->archivierbar = isset($_POST['updateVorlage_archivierbar']);
		$updatevorlage->signierbar = isset($_POST['updateVorlage_signierbar']);
		$updatevorlage->stud_selfservice = isset($_POST['updateVorlage_stud_selfservice']);
		if (isset ($_POST['updateVorlage_kopieren']))
		{
			$updatevorlage->insertamum = date('Y-m-d H:i:s');
			$updatevorlage->insertvon = $user;
		}
		else
		{
			$updatevorlage->updateamum = date('Y-m-d H:i:s');
			$updatevorlage->updatevon = $user;
		}
		if (!($updatevorlage->saveVorlage((isset ($_POST['updateVorlage_kopieren']) ? true : false))))
		{
			echo 'Fehler beim Speichern';
		}
	}

	$vorlageOeVorlagedata = new $vorlage();
	$vorlageOeVorlagedata->loadVorlage($vorlageOE->vorlage_kurzbz);

	echo '
		<form method="POST" action="'.$_SERVER['PHP_SELF'].'">
			<table>
				<tr>
					<td>Vorlage</td>
					<td>';

			if($vorlageOE->oe_kurzbz!='')
				$oe=$vorlageOE->oe_kurzbz;
			elseif($oe_kurzbz!='')
			$oe=$oe_kurzbz;
			else
				$oe=$default_oe;
			//Vorlagen DropDown
			$vorlage = new vorlage();
			$vorlage->getAllVorlagen('bezeichnung');

			echo '<SELECT name="vorlage_kurzbz" id="vorlage">';
			foreach ($vorlage->result as $row)
			{
				//if($reihungstest_id=='')
				//	$reihungstest_id=$row->reihungstest_id;
				if($row->vorlage_kurzbz==$vorlageOE->vorlage_kurzbz)
					$selected='selected';
				else
					$selected='';

				echo '<OPTION value="'.$row->vorlage_kurzbz.'" '.$selected.'>'.$db->convert_html_chars(($row->bezeichnung==''?$row->vorlage_kurzbz:$row->bezeichnung)).'</OPTION>';
				echo "\n";
			}
			echo '</SELECT>

					</td>
				<tr>
					<td>Organisationseinheit</td>
					<td>';
			//OE-Dropdown
			$organisationseinheit = new organisationseinheit();
			$organisationseinheit->getAll();

			echo "<SELECT name='oe_kurzbz'>";

			foreach ($organisationseinheit->result as $row)
			{
				//Wenn keine OE uebergeben wurde, nimm die OE vom Studiengang 0
				if($row->oe_kurzbz==$oe)
					$selected='selected';
				else
					$selected='';

				$style='';
				if ($row->aktiv==false)
					$style='style="text-decoration: line-through"';
				echo '<OPTION value="'.$row->oe_kurzbz.'" '.$selected.' '.$style.'>'.$db->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung).'</OPTION>';
				echo "\n";
			}
			echo '</SELECT>
					</td>
				</tr>
				<tr>
					<td>Version</td>
					<td><input type="text" size="4" maxlength="3" name="version" id="version" value="'.$db->convert_html_chars($vorlageOE->version).'"></td>
				</tr>
				<tr>
					<td>Content</td>
					<td><textarea ' . ($vorlageOeVorlagedata->mimetype == 'text/html'?'class="mceEditor" cols="200" rows="20"':'cols="200" rows="10"') . ' name="content">'.$db->convert_html_chars($vorlageOE->text).'</textarea></td>
				</tr>
				<tr>
					<td>Style</td>
					<td><textarea ' . ($vorlageOeVorlagedata->mimetype == 'text/html'?'cols="200" rows="5"':'cols="200" rows="10"') . ' name="style">'.$db->convert_html_chars($vorlageOE->style).'</textarea></td>
				</tr>
				<tr>
					<td>Berechtigung</td>
					<td><input type="text" size="80" maxlength="32" name="berechtigung" value="'.$db->convert_html_chars($vorlageOE->berechtigung).'"></td>
				</tr>
				<tr>
					<td>Betreff</td>
					<td><input type="text" size="80" name="subject" value="'.$db->convert_html_chars($vorlageOE->subject).'"></td>
				</tr>
				<tr>
					<td>Organisationsform</td>
					<td>';
					echo '<SELECT name="orgform_template"><option value=""></option>';

					$orgform = new organisationsform();
					$orgform->getOrgformLV();
					foreach ($orgform->result as $of)
					{
						if($vorlageOE->orgform_kurzbz == $of->orgform_kurzbz)
							$selected = 'selected';
						else
							$selected = '';

						echo '<OPTION value="'.$db->convert_html_chars($of->orgform_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($of->orgform_kurzbz).' - '.$db->convert_html_chars($of->bezeichnung).'</OPTION>';
					}
					echo '</SELECT>
				</tr>
					<tr>
					<td>Sprache</td>
					<td>';
					//OE-Dropdown
					$sprachen = new sprache();
					$sprachen->getAll(true);

					echo "<SELECT name='templatesprache'>";
					echo '<OPTION value="" '.($vorlageOE->sprache == ''?'selected':'').'></OPTION>';

					foreach ($sprachen->result as $row)
					{
						//Wenn keine Sprache uebergeben wurde, nimm die Defaultsprache
						if($row->sprache == $vorlageOE->sprache)
							$selected = 'selected';
						else
							$selected = '';

						$style = '';

						echo '<OPTION value="'.$row->sprache.'" '.$selected.'>'.$row->sprache.'</OPTION>';
						echo "\n";
					}
					echo '</SELECT>
					</td>
				</tr>
				<tr>
					<td>Anmerkung</td>
					<td><textarea cols="80" rows="2" name="anmerkung">'.$db->convert_html_chars($vorlageOE->anmerkung_vorlagestudiengang).'</textarea></td>
				</tr>
				<tr>
					<td>Aktiv</td>
					<td><input type="checkbox" name="aktiv" id="aktiv" '.($vorlageOE->aktiv==true?'checked="checked"':'').'></td>
				</tr>';
				echo '<tr><td>&nbsp;</td></tr>';

			echo '<tr>
					<td></td>
					<td>
						<input type="hidden" value="'.$vorlageOE->vorlagestudiengang_id.'" name="vorlagestudiengang_id" />
						<input type="hidden" value="'.$oe_auswahl.'" name="oe_auswahl" />';

						
						if(!$neu)
						{
							echo '<input type="submit" style="padding: 2px 4px" name="speichern" value="Änderung Speichern">&nbsp;&nbsp;';
							echo '<input type="submit" style="padding: 2px 4px" name="kopieren" value="Kopie anlegen">';
						}
						else
						{
							echo '<input type="submit" name="speichern" value="Neu anlegen">';
						}

			echo '	</td>
				</tr>
			</table>
		</form>';
}

echo '<hr>';

if($vorlage_kurzbz!='' || $oe_kurzbz!='')
{

	$vorlage_version = new vorlage();
	$vorlage_version->getAllVersions($vorlage_kurzbz, $oe_auswahl);
	$oe = new organisationseinheit();
	$vorlage = new vorlage();
	//echo '<span style="font-size: 9pt">Anzahl: '.$db->db_num_rows($vorlage_version->result).'</span>';

	echo '<table class="tablesorter" id="t1">
				<thead>
				<tr class="liste">
					<th>Vorlage</th>
					<th>Organisationseinheit</th>
					<th>Studiengang</th>
					<th>Version</th>
					<th>Berechtigung</th>
					<th>Anmerkung</th>
					<th>Aktualisiert</th>
					<th colspan="2"></th>
				</tr>
				</thead>
				<tbody>';

	foreach($vorlage_version->result as $row)
	{
		$oe->load($row->oe_kurzbz);
		$vorlage->loadVorlage($row->vorlage_kurzbz);
		$vorlage_bezeichnung = ($vorlage->bezeichnung==''?$vorlage->vorlage_kurzbz:$vorlage->bezeichnung);
		$style='';
		$insertdata='-';
		if ($oe->aktiv==false)
			$style='style="text-decoration: line-through"';

		if ($row->updateamum != '' && $row->updateamum > $row->insertamum)
		{
			if ($row->updatevon != '')
			{
				$insertdata = 'Am '.$datum->formatDatum($row->updateamum, 'd.m.Y H:i:s').' von '.$row->updatevon;
			}
			else
			{
				$insertdata = 'Am '.$datum->formatDatum($row->updateamum, 'd.m.Y H:i:s');
			}
		}
		elseif ($row->insertamum != '')
		{
			if ($row->insertvon != '')
			{
				$insertdata = 'Am '.$datum->formatDatum($row->insertamum, 'd.m.Y H:i:s').' von '.$row->insertvon;
			}
			else
			{
				$insertdata = 'Am '.$datum->formatDatum($row->insertamum, 'd.m.Y H:i:s');
			}

		}
		echo '
			<tr '.($row->aktiv==false?'style="color:grey"':'').'>
				<td>'.$db->convert_html_chars($vorlage_bezeichnung).'</td>
				<td '.$style.'>'.$db->convert_html_chars($oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung).'</td>
				<td>'.$db->convert_html_chars($row->studiengang_kz).'</td>
				<td>'.$db->convert_html_chars($row->version).'</td>
				<td>'.$db->convert_html_chars($row->berechtigung).'</td>
				<td>'.$db->convert_html_chars($row->anmerkung_vorlagestudiengang).'</td>
				<td>'.$insertdata.'</td>
				<td><a href="'.$_SERVER['PHP_SELF'].'?vorlagestudiengang_id='.$row->vorlagestudiengang_id.'&vorlage_kurzbz='.$vorlage_kurzbz.'&oe_auswahl='.$oe_auswahl.'">Edit</a></td>
				<td><a href="'.$_SERVER['PHP_SELF'].'?vorlagestudiengang_id='.$row->vorlagestudiengang_id.'&vorlage_kurzbz='.$vorlage_kurzbz.'&oe_auswahl='.$oe_auswahl.'&delete" onclick="return confdel(\''.$vorlage_bezeichnung.'\',\''.$row->version.'\')">Delete</a></td>

			</tr>';
	}
	echo '</tbody></table>';
}

echo '	</body>
</html>';
