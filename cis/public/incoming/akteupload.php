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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

// Oberflaeche zur Aenderung von Beispielen und Upload von Bildern
require_once 'auth.php';
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/dokument.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php'); 

header("Content-Type: text/html; charset=utf-8");

if(isset($_GET['lang']))
	setSprache($_GET['lang']);
	
$code = $_SESSION['incoming/user'];

$person = new person();
$person_id = $person->checkZugangscode($code);

if($person_id!=$_GET['person_id'])
	die('Sie haben keine Berechtigung für diese Seite');

$dokumenttyp = (isset($_GET['dokumenttyp']))? $_GET['dokumenttyp'] : 'LearnAgr';
$sprache = getSprache(); 
$p=new phrasen($sprache); 

$PHP_SELF = $_SERVER['PHP_SELF'];
echo "<html>
		<head><title>File-Upload</title></head>
		<body>";

//Bei Upload einer Datei
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{		
		$filename = $_FILES['bild']['tmp_name'];
		
		$fp = fopen($filename,'r');
		//auslesen
		$content = fread($fp, filesize($filename));
		fclose($fp);
		
		$akte = new akte();
		
		if($akte->getAkten($_GET['person_id'], 'Lichtbil'))
		{
			if(count($akte->result)>0)
			{
				$akte = $akte->result[0];
				$akte->new = false;
			}
			else 
				$akte->new = true;
		}
		else 
		{
			$akte->new = true;
		}
		
		$dokument = new dokument(); 
		$dokument->loadDokumenttyp($_REQUEST['dokumenttyp']);

		$tmp = explode(".",strtolower($_FILES['bild']['name']));
		$extension = end($tmp);
		$titel = '';
		
		// da nur 32 zeichen gespeichert werden dürfen, muss anhand vom typ gekürzt werden
		/*if($_REQUEST['dokumenttyp']=='Lebenslf')
			$titel = $p->t('incoming/lebenslauf').".".$extension;
		if($_REQUEST['dokumenttyp']=='LearnAgr')
			$titel = $p->t('incoming/learningAgreement').".".$extension;
		if($_REQUEST['dokumenttyp']=='Motivat')
			$titel = $p->t('incoming/motivationsschreiben').".".$extension;
		if($_REQUEST['dokumenttyp']=='Zeugnis')
			$titel = $p->t('incoming/zeugnis').".".$extension;
		if($_REQUEST['dokumenttyp']=='Lichtbil')
			$titel = $p->t('incoming/lichtbild').".".$extension;*/
		
		$titel = StringCut($dokument->bezeichnung,25,false,'');
		$titel = $titel.'.'.$extension;
			
		$akte->dokument_kurzbz = $_REQUEST['dokumenttyp'];
		$akte->person_id = $_GET['person_id'];
		$akte->inhalt = base64_encode($content);
		$akte->mimetype = $_FILES['bild']['type'];
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = $titel; 
		//$akte->bezeichnung = $dokument->bezeichnung;
		$akte->updateamum = date('Y-m-d H:i:s');
		$akte->updatevon = 'Incoming-Online';
		$akte->insertamum = date('Y-m-d H:i:s');
		$akte->insertvon = 'Incoming-Online';
		$akte->uid = '';
		$akte->new = true; 
		
		if(!$akte->save())
		{
			echo "<b>Fehler: $akte->errormsg</b>";
		}
		else
			echo "<b>".$p->t('global/erfolgreichgespeichert')."</b>"; 
		echo "<script>window.opener.location.reload();</script>"; 			
		if($akte->dokument_kurzbz == "LearnAgr")
		{
			// sende Email zu Assistenz
			$person = new person(); 
			$person->load($_GET['person_id']); 
			
			$emailtext= "Dies ist eine automatisch generierte E-Mail.<br><br>";
			$emailtext.= "Es wurde ein neues Dokument auf das System hochgeladen.<br><br>Vorname/Nachname: <b>".$person->vorname." ".$person->nachname."</b><br>Dokumenttyp: <b>Learning Agreement</b>"; 
			$mail = new mail(MAIL_INTERNATIONAL, 'no-reply', 'Learning-Agreement Upload', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
			$mail->setHTMLContent($emailtext); 
			if(!$mail->send())
				$msg= '<span class="error">Fehler beim Senden des Mails</span><br />';
			else
				$msg= $p->t('global/emailgesendetan');
		}
	}
}

if(isset($_GET['person_id']))
{
	$dokument = new dokument(); 
	$dokument->getDokumente('10006'); 
	echo "	<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?person_id=".$_GET['person_id']."'>
			<table>
				<tr>
					<td>".$p->t('incoming/dokument').":</td>
					<td>
						<input type='file' name='bild' />
					</td>
				</tr>
				<tr>
					<td>".$p->t('incoming/dokumenttyp').":</td>
					<td>
					 <SELECT name='dokumenttyp'>";
				foreach ($dokument->result as $dok)
				{
					$selected =""; 
					if($dok->dokument_kurzbz == $dokumenttyp)
						$selected = "selected";
					echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" >'.$dok->bezeichnung_mehrsprachig[$sprache]."</option>\n";
				}
echo "				</select>
					</td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td></td>
					<td><input type='submit' name='submitbild' value='Upload' /></td>
				</tr>
			</table>
			</form>";
}
else
{
	echo "Es wurde keine Person_id angegeben";
}

?>
</body>
</html>
