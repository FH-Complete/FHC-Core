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

echo "<html><body>";
//wandelt einen String in HEX-Werte um
function strhex($string)
{
    $hex="";
    for ($i=0;$i<strlen($string);$i++)
        $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
    return $hex;
}

//Connection Herstellen
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('admin'))
	die('Keine Berechtigung');
//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{
		//Extension herausfiltern
    	$ext = explode('.',$_FILES['bild']['name']);
        $ext = strtolower($ext[count($ext)-1]);

        //--check that it's a jpeg or gif or png
        if ($ext=='gif' || $ext=='png' || $ext=='jpg' || $ext=='jpeg')
        {
			$filename = $_FILES['bild']['tmp_name'];
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
					echo "<b>Bild wurde erfolgreich gespeichert</b><script language='Javascript'>opener.StudentAuswahl(); opener.InteressentAuswahl(); window.close();</script><br />";
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
