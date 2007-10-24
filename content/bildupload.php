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
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzerberechtigung.class.php');

$PHP_SELF = $_SERVER['PHP_SELF'];
echo "<html><body>";
//wandelt einen String in HEX-Werte um
function strhex($string)
{
    $hex="";
    for ($i=0;$i<strlen($string);$i++)
        $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
    return $hex;
}

function resize($filename, $width, $height)
{
	$ext = explode('.',$_FILES['bild']['name']);
    $ext = strtolower($ext[count($ext)-1]);

	// Hoehe und Breite neu berechnen
	list($width_orig, $height_orig) = getimagesize($filename);
	if ($width && ($width_orig < $height_orig)) 
	{
	   $width = ($height / $height_orig) * $width_orig;
	}
	else
	{
	   $height = ($width / $width_orig) * $height_orig;
	}
	
	$image_p = imagecreatetruecolor($width, $height);                       
	
	//Bilder vergroessern/verkleinern und wieder zurueckschreiben
	
	$image = imagecreatefromjpeg($filename);
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
	imagejpeg($image_p, $filename, 80);
		
	imagedestroy($image_p);
	imagedestroy($image);
}

//Connection Herstellen
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();


$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('assistenz'))
	die('Keine Berechtigung');
//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['bild']['name']);
        $ext = strtolower($ext[count($ext)-1]);

        $width=101;
		$height=130;
		
        //--check that it's a jpeg or gif or png
        if ($ext=='jpg' || $ext=='jpeg')
        {
			$filename = $_FILES['bild']['tmp_name'];
			
			//groesse auf maximal 827x1063 begrenzen
			resize($filename, 827, 1063);
			
			//im Dateisystem speichern
			if(!copy($filename, IMAGE_PATH.$_GET['person_id'].'.jpg'))
			{
				die( 'copy failed:'.IMAGE_PATH.$_GET['person_id'].'.jpg');
			}
			
			//groesse auf maximal 101x130 begrenzen
			resize($filename, 101, 130);
			
			//in DB speichern           
			//File oeffnen
			$fp = fopen($filename,'r');
			//auslesen
			$content = fread($fp, filesize($filename));
			fclose($fp);
			//in HEX-Werte umrechnen
			$content = strhex($content);

			$person = new person($conn);
			if($person->load($_GET['person_id']))
			{
				//HEX Wert in die Datenbank speichern
				$person->foto = $content;
				$person->new = false;				
				if($person->save())
					echo "<b>Bild wurde erfolgreich gespeichert</b><script language='Javascript'>opener.StudentAuswahl(); window.close();</script><br />";
				else
					echo '<b>'.$person->errormsg.'</b><br />';
			}
			else
				echo '<b>'.$person->errormsg.'</b><br />';
		}
		else
			echo "<b>File ist kein gueltiges Bild</b><br />";
	}
}

if(isset($_GET['person_id']))
{
	echo "	<form method='POST' enctype='multipart/form-data' action='$PHP_SELF?person_id=".$_GET['person_id']."'>
			Bild: <input type='file' name='bild' />
			<input type='submit' name='submitbild' value='Upload' />
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
