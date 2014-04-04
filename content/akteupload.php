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

// Oberflaeche zum Upload von Dokumenten aus dem FAS
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/akte.class.php');
require_once ('../include/dokument.class.php');
require_once('../include/dms.class.php'); 

header("Content-Type: text/html; charset=utf-8");

$PHP_SELF = $_SERVER['PHP_SELF'];
echo "<html><body>";

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('mitarbeiter'))
	die('Keine Berechtigung');

$kategorie_kurzbz = isset($_REQUEST['kategorie_kurzbz'])?$_REQUEST['kategorie_kurzbz']:'';


if(isset($_POST['submitbild']))
{
    $error = false; 
    
    // dms Eintrag anlegen
    if(isset($_POST['fileupload']))
    {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION); 
        $filename = uniqid();
        $filename.=".".$ext; 
        $uploadfile = DMS_PATH.$filename;


        if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) 
        {
            if(!chgrp($uploadfile,'dms'))
                echo 'CHGRP failed';
            if(!chmod($uploadfile, 0774))
                echo 'CHMOD failed';
            exec('sudo chown wwwrun '.$uploadfile);	

            $dms = new dms();

            $dms->version='0';
            $dms->kategorie_kurzbz=$kategorie_kurzbz;

            $dms->insertamum=date('Y-m-d H:i:s');
            //$dms->insertvon = $user;
            $dms->mimetype=$_FILES['file']['type'];
            $dms->filename = $filename;
            $dms->name = $_FILES['file']['name'];

            if($dms->save(true))
            {
                $dms_id=$dms->dms_id;

            }    	
            else
            {
                echo 'Fehler beim Speichern der Daten';
                $error = true; 
            }
        } 
        else 
        {
            echo 'Fehler beim Hochladen der Datei';
            $error = true; 
        }
    }
    
	if(isset($_FILES['file']['tmp_name']) && !$error)
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['file']['name']);
        $ext = mb_strtolower($ext[count($ext)-1]);
		
		$filename = $_FILES['file']['tmp_name'];
		
		//$fp = fopen($filename,'r');
		//auslesen
		//$content = fread($fp, filesize($filename));
		//fclose($fp);
		
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

		$extension = end(explode(".",strtolower($_FILES['file']['name'])));
		$titel = '';
		
		// da nur 32 zeichen gespeichert werden dürfen, muss anhand vom typ gekürzt werden
		if($_REQUEST['dokumenttyp']=='Lebenslf')
			$titel = $p->t('incoming/lebenslauf').".".$extension;
		if($_REQUEST['dokumenttyp']=='LearnAgr')
			$titel = $p->t('incoming/learningAgreement').".".$extension;
		if($_REQUEST['dokumenttyp']=='Motivat')
			$titel = $p->t('incoming/motivationsschreiben').".".$extension;
		if($_REQUEST['dokumenttyp']=='Zeugnis')
			$titel = $p->t('incoming/zeugnis').".".$extension;			
		if($_REQUEST['dokumenttyp']=='Lichtbil')
			$titel = $p->t('incoming/lichtbild').".".$extension;					
			
			
		$akte->dokument_kurzbz = $_REQUEST['dokumenttyp'];
                $akte->bezeichnung = $_FILES['file']['name']; 
		$akte->person_id = $_GET['person_id'];
		//$akte->inhalt = base64_encode($content);
		$akte->mimetype = $_FILES['file']['type'];
		$akte->erstelltam = date('Y-m-d H:i:s');
		$akte->gedruckt = false;
		$akte->titel = $titel; 
		//$akte->bezeichnung = $dokument->bezeichnung;
		$akte->updateamum = date('Y-m-d H:i:s');
		$akte->updatevon = $user;
		$akte->insertamum = date('Y-m-d H:i:s');
		$akte->nachgereicht = false; 
		$akte->anmerkung = ''; 
		$akte->insertvon = $user;
		$akte->uid = '';
		$akte->dms_id = $dms_id;
		$akte->new = true; 
		$akte->titel_intern = $_REQUEST['titel_intern'];
		$akte->anmerkung_intern = $_REQUEST['anmerkung_intern']; 
		
		if(!$akte->save())
		{
			echo "<b>Fehler: $akte->errormsg</b>";
		}
		else
			echo "<b>Erfolgreich gespeichert</b>"; 		
	}
}




if(isset($_GET['person_id']))
{
	$dokument = new dokument(); 
	$dokument->getAllDokumente(); 
	echo "	<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?person_id=".$_GET['person_id']."'>
			<table>
				<tr>
					<td>Dokument: <input type='file' name='file' />
					<input type='submit' name='submitbild' value='Upload' /></td>
				</tr>
				<tr>
					<td>Typ: <SELECT name='dokumenttyp'>";
			
				foreach ($dokument->result as $dok)
				{
					$onclick="document.getElementById('titel_intern').value='".$dok->dokument_kurzbz."';"; 

					echo '<option value="'.$dok->dokument_kurzbz.'" onclick="'.$onclick.'">'.$dok->bezeichnung."</option>\n";
				}
				
	echo "	</td></tr></table>
		<table>
			<tr>
				<td>Titel: </td><td><input type='text' name='titel_intern' id='titel_intern' length='35' ></td>
			</tr>
			<tr> 
				<td>Anmerkung: </td><td><input type='text' name='anmerkung_intern' id='anmerkung_intern' length='35' ></td>
			</form>
		</td></tr>
			<tr>
				<td><input type='hidden' name='kategorie_kurzbz' id='kategorie_kurzbz' value='Akte'>
				<td><input type='hidden' name='fileupload' id='fileupload'></td>
				<td><input type='submit' name='submitbild' value='Upload'></td>

			</tr>";
}
else
{
	echo "Es wurde keine Person_id angegeben";
}
?>

</body>
</html>
