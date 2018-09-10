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
 * GUI zur einfachen Prüfung von Profilbildern
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/mail.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/person.class.php');
require_once('../../include/fotostatus.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$db = new basis_db();
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet"  href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';

	include('../../include/meta/jquery.php');

echo'	<title>Profilfoto Check</title>
	<script>
	$(document).ready(function()
	{
		var img = document.getElementById("image");

		img.onload = function()
		{
			var width = img.naturalWidth;
			if (width < 240)
				width = "<span style=\'color: red\'>"+width+"</span>";
	
			var height = img.naturalHeight;
			if (height < 320)
				height = "<span style=\'color: red\'>"+height+"</span>";
			$("#imagesize").html(width+" x "+height);
		}
	});
	function RefreshImage()
	{
		document.getElementById("image").src = document.getElementById("image").src+"&foo";
		document.getElementById("imagepreview").src = document.getElementById("imagepreview").src+"&foo";
	}
	</script>
	<style type="text/css">
	.hoverbox
	{
		cursor: default;
		list-style: none;
	}
		
	.hoverbox a .preview
	{
		display: none;
	}
	
	.hoverbox a .previewtext
	{
		display: none;
	}
	
	.hoverbox a:active .preview
	{
		display: block;
		position: absolute;
		top: 0px;
		left: 0px;
		z-index: 1;
	}
	
	.hoverbox a:active .previewtext
	{
		display: block;
		position: absolute;
		top: -35px;
		left: 1px;
		z-index: 1;
		color: #000;
	}
	
	.hoverbox .preview
	{
		border-style: solid;
		border-width: 2px;
		border-color: #000;
		height: 200px;
	}
	
	.hoverbox .image
	{
		width: 150px;
		height: 200px;
	}
	input
	{
		padding: 2px 4px;
	}
	</style>
	
	<!--[if IE]>
	<style type="text/css">
		.hoverbox
	{
		cursor: default;
		list-style: none;
	}
		
	.hoverbox a .preview
	{
		display: none;
	}
	
	.hoverbox a .previewtext
	{
		display: none;
	}
	
	.hoverbox .preview
	{
		border-style: solid;
		border-width: 2px;
		border-color: #000;
		height: 200px;
	}
	
	.hoverbox .image
	{
		width: 150px;
		height: 200px;
	}
	
	.hoverbox a:hover .preview
	{
		display: block;
		position: absolute;
		top: 0px;
		left: 0px;
		z-index: 1;
	}

	.hoverbox a:hover .previewtext
	{
		display: block;
		position: absolute;
		top: -35px;
		left: 1px;
		z-index: 1;
		color: #000;
	}
	</style>
	<![endif]-->
</head>
<body>
<h2>Profilfoto Check</h2>
';
if(!$rechte->isBerechtigt('basis/fhausweis','suid'))
{
	die('Sie haben keine Berechtigung für diese Seite');
}
$error = false;

if(isset($_POST['person_id']))
{
	$person_id=$_POST['person_id'];
	
	//Profilbild OK - Akzeptiert Status setzen
	if(isset($_POST['akzeptieren']))
	{
		$fs = new fotostatus();
		$fs->person_id=$person_id;
		$fs->fotostatus_kurzbz='akzeptiert';
		$fs->datum = date('Y-m-d');
		$fs->insertvon = $uid;
		$fs->insertamum = date('Y-m-d H:i:s');
		$fs->updatevon = $uid;
		$fs->updateamum = date('Y-m-d H:i:s');
		
		if($fs->save(true))
		{
			echo '<span class="ok">Profilbild wurde akzeptiert</span>';
		}
		else
		{
			echo '<span class="error">Fehler beim Akzeptieren:'.$fs->errormsg.'</span>';
			$error = true;
		}		
	}
	
	//Profilbild Fehlerhaft - Infomail an die Person versenden
	if(isset($_POST['fehlerhaft']))
	{
		$benutzer = new benutzer();
		$to='';
		if($benutzer->getBenutzerFromPerson($person_id))
		{
			foreach($benutzer->result as $row)
			{
				if($to!='')
					$to.',';
				$to = $row->uid.'@'.DOMAIN;
			}
		}
		//Wenn kein Benutzer gefunden wurde, ist es ein 
		if($to!='')
		{
			$person = new person();
			$person->load($person_id);
			
			$from = 'fhausweis@technikum-wien.at';
			$subject = 'Profilbild';
			if($person->geschlecht=='m')
				$text = "Sehr geehrter Herr ".$person->vorname.' '.$person->nachname.",\n\n";
			else
				$text = "Sehr geehrte Frau ".$person->vorname.' '.$person->nachname.",\n\n";
			
			$text .= "Ihr Profilbild wurde von uns geprüft und entspricht nicht den Bildkriterien.\n";
			$text .= "Die aktuellen Bildkriterien finden Sie unter folgendem Link:\n";
			$text .= "https://cis.technikum-wien.at/cms/content.php?content_id=6174\n\n";
			$text .= "Bitte Laden Sie ein entsprechendes Profilbild im CIS unter 'Mein CIS'->'Profil' hoch.\n";
			$text .= "\n";
			$text .= "Herzlichen Dank\n";
			$text .= "Fachhochschule Technikum Wien\n";
			$text .= "\n------------------\n\n";
			if($person->geschlecht=='m')
				$text .= "Dear Mr ".$person->vorname.' '.$person->nachname.",\n\n";
			else
				$text .= "Dear Ms ".$person->vorname.' '.$person->nachname.",\n\n";
			$text .= "Your profile photograph has been checked and does not fulfil the photo criteria.\n";
			$text .= "The current criteria can be found under the following link:\n";
			$text .= "https://cis.technikum-wien.at/cms/content.php?content_id=6174\n";
			$text .= "\n";
			$text .= "Please upload a suitable profile photo in the CIS under 'My CIS'->'Profile'.\n";
			$text .= "\n";
			$text .= "Thank you\n";
			$text .= "University of Applied Sciences Technikum Wien";
			
			$mail = new mail($to, $from, $subject, $text);
			if($mail->send())
			{
				echo '<span class="ok">Infomail wurde versendet an '.$to.'</span>';
			}
			else
			{
				echo '<span class="error">Fehler beim Versenden des Mails an '.$to.'</span>';
				$error = true;
			}
		}
		else
		{
			echo '<span class="error">Keine Mail Adresse gefunden</span>';
			$error = true;
		}
		
		if(!$error)
		{
			//Status setzen
			$fs = new fotostatus();
			$fs->person_id=$person_id;
			$fs->fotostatus_kurzbz='abgewiesen';
			$fs->datum = date('Y-m-d');
			$fs->insertvon = $uid;
			$fs->insertamum = date('Y-m-d H:i:s');
			$fs->updatevon = $uid;
			$fs->updateamum = date('Y-m-d H:i:s');
			
			if(!$fs->save(true))
			{
				echo '<span class="error">Fehler beim Setzen des abgewiesen Status:'.$fs->errormsg.'</span>';
				$error = true;
			}
		}
	}
	
	//BestOf Profilbilder werden gesichert
	if(isset($_POST['bestof']))
	{
		echo 'Zu BestOf hinzugefügt';
		$qry = "SELECT inhalt FROM public.tbl_akte 
				WHERE dokument_kurzbz='Lichtbil' AND person_id=".$db->db_add_param($person_id);
		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				file_put_contents('bestof/'.$person_id.'.jpg',base64_decode($row->inhalt));
				//Error setzen damit die Person nochmals angezeigt wird
				$error = true;
			}
		}
	}
	if(isset($_POST['refresh']))
	{
		$error=true;
	}
}

//je nach ansicht (mitarbeiter/student/beides) wird $qry veraendert
if (isset($_GET['ansicht']))
{
	if ($_GET['ansicht'] == 'mitarbeiter')
	{
		$ansicht = 'AND tbl_benutzer.uid IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter)';
		$mitarbeiterSubmit = '<input type="submit" name="MitarbeiterSubmit" sytle="background-color:#F5F5F5" value="Mitarbeiter" />';
	}
	if ($_GET['ansicht'] == 'studenten')
		$ansicht = 'AND tbl_benutzer.uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter)';
}
else
{
	$ansicht = '';
}

//anzahl mitarbeiter
$qry_anzahl_mitarbeiter = "
	SELECT
		count(*) as anzahl
	FROM
		public.tbl_person
		JOIN public.tbl_benutzer USING(person_id)
		LEFT JOIN PUBLIC.tbl_akte ON (tbl_akte.person_id = tbl_person.person_id 
										AND tbl_akte.dokument_kurzbz = 'Lichtbil'
										AND (tbl_akte.inhalt IS NOT NULL OR tbl_akte.dms_id IS NOT NULL))
		LEFT JOIN public.tbl_mitarbeiter ON (tbl_benutzer.uid=mitarbeiter_uid)
	WHERE
		foto is not NULL
		AND tbl_benutzer.aktiv
		AND 'akzeptiert' NOT IN(SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus
					WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)
		AND 'abgewiesen' NOT IN (SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus
						WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)
		AND tbl_benutzer.uid IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter)
	";
$anzahl_ma = '';
if($result_anzahl = $db->db_query($qry_anzahl_mitarbeiter))
	if($row_anzahl = $db->db_fetch_object($result_anzahl))
		$anzahl_ma = $row_anzahl->anzahl;
echo '<br>Mitarbeiter: '.$anzahl_ma;

//anzahl studenten
$qry_anzahl_studenten = "
	SELECT
		count(*) as anzahl
	FROM
		public.tbl_person
		JOIN public.tbl_benutzer USING(person_id)
		LEFT JOIN PUBLIC.tbl_akte ON (tbl_akte.person_id = tbl_person.person_id 
										AND tbl_akte.dokument_kurzbz = 'Lichtbil' 
										AND (tbl_akte.inhalt IS NOT NULL OR tbl_akte.dms_id IS NOT NULL))
		LEFT JOIN public.tbl_mitarbeiter ON (tbl_benutzer.uid=mitarbeiter_uid)
	WHERE
		foto is not NULL
		AND tbl_benutzer.aktiv
		AND 'akzeptiert' NOT IN(SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus
					WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)
		AND 'abgewiesen' NOT IN (SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus
						WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)
		AND tbl_benutzer.uid NOT IN (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter)
	";
$anzahl_std = '';
if($result_anzahl = $db->db_query($qry_anzahl_studenten))
	if($row_anzahl = $db->db_fetch_object($result_anzahl))
		$anzahl_std = $row_anzahl->anzahl;
echo '<br>Studenten: '.$anzahl_std;

//anzahl gesamt
$qry_anzahl_gesamt = "
	SELECT
		count(*) as anzahl
	FROM
		public.tbl_person
		JOIN public.tbl_benutzer USING(person_id)
		LEFT JOIN PUBLIC.tbl_akte ON (tbl_akte.person_id = tbl_person.person_id 
										AND tbl_akte.dokument_kurzbz = 'Lichtbil'
										AND (tbl_akte.inhalt IS NOT NULL OR tbl_akte.dms_id IS NOT NULL))
	WHERE
		foto is not NULL
		AND tbl_benutzer.aktiv
		AND 'akzeptiert' NOT IN(SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus
					WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)
		AND 'abgewiesen' NOT IN (SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus
						WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)
	";
$anzahl_gesamt = '';
if($result_anzahl = $db->db_query($qry_anzahl_gesamt))
	if($row_anzahl = $db->db_fetch_object($result_anzahl))
		$anzahl_gesamt = $row_anzahl->anzahl;
	echo '<br>Gesamt: '.$anzahl_gesamt.'<br />';

echo '<form action="'.$_SERVER['PHP_SELF'].'?ansicht=mitarbeiter" method="POST" style="float:left; margin-left:44%">
		<input type="submit" name="MitarbeiterSubmit" id="MitarbeiterSubmit" value="Mitarbeiter" ';  if (isset($_GET['ansicht']) && $_GET['ansicht'] == 'mitarbeiter') echo 'disabled'; echo '/></form>';
echo '<form action="'.$_SERVER['PHP_SELF'].'?ansicht=studenten" method="POST" style="float:left">
		<input type="submit" name="StudentenSubmit" id="StudentenSubmit" value="Studenten" '; if (isset($_GET['ansicht']) && $_GET['ansicht'] == 'studenten') echo 'disabled'; echo '/></form>';
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" style="float:left">
		<input type="submit" name="BeideGruppenSubmit" id="BeideGruppenSubmit" value="Beide Gruppen" '; if (!isset($_GET['ansicht'])) echo 'disabled'; echo '/></form>';
	
// Laden einer Person deren Profilfoto noch nicht akzeptiert wurde
$qry = "
	SELECT 
		tbl_person.person_id,
		tbl_person.vorname,
		tbl_person.nachname,
		tbl_akte.dokument_kurzbz,
		tbl_akte.dms_id,
		(SELECT 1 FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) 
		 WHERE person_id=tbl_person.person_id LIMIT 1) as mitarbeiter
	FROM 
		public.tbl_person 
		JOIN public.tbl_benutzer USING(person_id)
		LEFT JOIN PUBLIC.tbl_akte ON (tbl_akte.person_id = tbl_person.person_id 
										AND tbl_akte.dokument_kurzbz = 'Lichtbil'
										AND (tbl_akte.inhalt IS NOT NULL OR tbl_akte.dms_id IS NOT NULL))
	WHERE 
		foto is not NULL
		AND tbl_benutzer.aktiv
		".$ansicht;
		

if($error==true && $person_id!='')
{
	// Wenn ein Fehler auftritt oder Bestof geklickt wird, wird die Person erneut angezeigt
	$qry.=" AND tbl_person.person_id=".$db->db_add_param($person_id);
}
else
{
	// Wenn es weniger als 100 Eintraege sind kommen die Bilder nicht mehr Random, da es sonst
	// vorkommen kann, dass kein Ergebnis geliefert wird
	if(isset($_GET['ansicht']) && (($_GET['ansicht'] == 'mitarbeiter' && $anzahl_ma>100) || ($_GET['ansicht'] == 'studenten' && $anzahl_std>100)) || ($ansicht == '' && $anzahl_gesamt>100))
	{
		// Zufaellige Reihenfolge
		$qry.=" AND random() <0.05";
	}
	
	// Keine Eintraege die bereits akzeptiert wurden
	$qry.="	AND 'akzeptiert' NOT IN(SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus 
					WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)";

	// Keine Eintraege bei denen Abgewiesen der letzte Status ist
	$qry.="	AND 'abgewiesen' NOT IN (SELECT fotostatus_kurzbz FROM public.tbl_person_fotostatus
						WHERE person_id=tbl_person.person_id ORDER BY datum desc, person_fotostatus_id desc LIMIT 1)";
}
$qry.="	LIMIT 1";

//echo $qry;

if($result = $db->db_query($qry))
{
	if($row = $db->db_fetch_object($result))
	{
		//Anzeige des Profilbildes
		echo '
		<br><br><br>
		<center>
		<table style="position: relative;">	
			<tr>
				<td class="hoverbox">
					<a href="#">';
				// Wenn es eine Akte mit Lichtbild gibt, das Foto der Akte laden, sonst aus der Person
				if ($row->dokument_kurzbz == 'Lichtbil')
				{
					echo '	<p class="previewtext">Originalvorschau</p>
							<img id="image" class="image" src="../../content/bild.php?src=akte&person_id='.$row->person_id.'">
							<img id="imagepreview" src="../../content/bild.php?src=akte&person_id='.$row->person_id.'" class="preview">';
				}
				else 
				{
					echo '	<p class="previewtext">Originalvorschau</p>
							<img id="image" class="image" src="../../content/bild.php?src=person&person_id='.$row->person_id.'">
							<img id="imagepreview" src="../../content/bild.php?src=person&person_id='.$row->person_id.'" class="preview">';
				}
				echo '	
					</a>
				<br>
				<div id="imagesize"></div>
				</td>
				<td>&nbsp;</td>
				<td>
				Person ID: '.$db->convert_html_chars($row->person_id).'<br>
				Vorname: '.$db->convert_html_chars($row->vorname).'<br>
				Nachname: '.$db->convert_html_chars($row->nachname).'<br>
				'.($row->mitarbeiter=='1'?'MitarbeiterIn':'StudentIn').'
				</td>
			</tr>
		</table>';
		
		echo '<br><br>';
		echo '<form action="'.$_SERVER['PHP_SELF']; if (isset($_GET['ansicht'])) echo '?ansicht='.$_GET['ansicht']; echo '" method="POST">';
		echo '<input type="hidden" name="person_id" value="'.$db->convert_html_chars($row->person_id).'" />';
		echo '<input type="submit" name="akzeptieren" value="Akzeptieren" /> &nbsp;&nbsp;&nbsp;';
		echo '<input type="submit" name="fehlerhaft" value="Fehlerhaft / Infomail" /> &nbsp;&nbsp;&nbsp;';
		echo '<input type="submit" name="bestof" value="BestOf" />&nbsp;&nbsp;&nbsp; ';
		echo '<input type="submit" name="refresh" value="Refresh" /> ';
		echo '</form>';
		echo '<br><br><br>';
		// Wenn es eine Akte mit Lichtbild gibt, das Foto aus der Akte laden (Parameter Akte im Link) sonst aus der Person
		if ($row->dokument_kurzbz == 'Lichtbil')
			echo '<a href="#FotoUpload" onclick="window.open(\'bildzuschnitt.php?person_id='.$row->person_id.'&typ=akte\',\'BildUpload\', \'height=900,width=700,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\'); return false;">Bild zuschneiden/Upload</a>';
		else 
			echo '<a href="#FotoUpload" onclick="window.open(\'bildzuschnitt.php?person_id='.$row->person_id.'&typ=person\',\'BildUpload\', \'height=900,width=700,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\'); return false;">Bild zuschneiden/Upload</a>';
		
		if ($row->dms_id !='')
		{
			echo '<br><br>';
			echo '<a href="#dms_download" onclick="window.open(\''.APP_ROOT.'cms/dms.php?id='.$row->dms_id.'\',\'DmsDownload\', \'height=800,width=900,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\'); return false;">DMS Download</a>';
		}
		echo '</center>';
	}
	else
	{
		if (isset($_GET['ansicht']) && $_GET['ansicht'] == 'mitarbeiter')
		{
			echo '<br><br><br><span style="margin-left:40%">Es sind keine ungeprüften Bilder von Mitarbeitern vorhanden</span>';
		}
		elseif (isset($_GET['ansicht']) && $_GET['ansicht'] == 'studenten')
		{
			echo '<br><br><br><span style="margin-left:40%">Es sind keine ungeprüften Bilder von Studenten vorhanden</span>';
		}
		else 
		{
			echo '<br><br><br><span style="margin-left:42%">Es sind keine ungeprüften Bilder vorhanden</span>';
		}
	}
}

echo '</body>
</html>';
?>