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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

// Oberflaeche zur Aenderung von Beispielen und Upload von Bildern
require_once 'auth.php';
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/akte.class.php');
require_once('../../../include/dokument.class.php');
require_once('../../../include/mail.class.php');

header("Content-Type: text/html; charset=utf-8");

$PHP_SELF = $_SERVER['PHP_SELF'];
echo "<html><body>";

//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['bild']['name']);
        $ext = mb_strtolower($ext[count($ext)-1]);
		
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
		 
		$akte->dokument_kurzbz = $_REQUEST['dokumenttyp'];
		$akte->person_id = $_GET['person_id'];
		$akte->inhalt = base64_encode($content);
		$akte->mimetype = $_FILES['bild']['type'];
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = $_FILES['bild']['name'];
		$akte->bezeichnung = $dokument->bezeichnung;
		$akte->updateamum = date('Y-m-d H:i:s');
	//	$akte->updatevon = $user;
		$akte->insertamum = date('Y-m-d H:i:s');
	//	$akte->insertvon = $user;
		$akte->uid = '';
		$akte->new = true; 
		
		if(!$akte->save())
		{
			echo "<b>Fehler: $akte->errormsg</b>";
		}
		else
			echo "<b>Erfolgreich gespeichert.</b>"; 
		if($akte->dokument_kurzbz == "LearnAgr")
		{
			// sende Email zu Assistenz
			$emailtext= "Dies ist eine automatisch generierte E-Mail.<br><br>";
			$emailtext.= "Es wurde ein Learning Agreement auf das System hochgeladen."; 
			$mail = new mail(MAIL_INTERNATIONAL, 'no-reply', 'Learning-Agreement Upload', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
			$mail->setHTMLContent($emailtext); 
			if(!$mail->send())
				$msg= '<span class="error">Fehler beim Senden des Mails</span><br />';
			else
				$msg= $p->t('global/emailgesendetan')." $email!<br>";
			
			return $msg; 
		}
	}
}

if(isset($_GET['person_id']))
{
	$dokument = new dokument(); 
	$dokument->getAllDokumente(); 
	echo "	<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?person_id=".$_GET['person_id']."'>
			<table>
				<tr>
					<td>Dokument: <input type='file' name='bild' />
					<input type='submit' name='submitbild' value='Upload' /></td>
				</tr>
				<tr>
					<td>Typ: <SELECT name='dokumenttyp'>";
				foreach ($dokument->result as $dok)
				{
					$selected =""; 
					if($dok->dokument_kurzbz == "LearnAgr")
						$selected = "selected";
					echo '<option '.$selected.' value="'.$dok->dokument_kurzbz.'" >'.$dok->bezeichnung."</option>\n";
				}
	
	
	echo "
			</form>
		</td></tr>";
}
else
{
	echo "Es wurde keine Person_id angegeben";
}

?>
</body>
</html>
