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
// ---------------- Kommunen Standart Include Dateien einbinden
echo "<html><body>";

$contentOUTPUT='';
//Bei Upload des Bildes
if(isset($_POST['submitbild']))
{
	if(isset($_FILES['bild']['tmp_name']))
	{
		$filename = $_FILES['bild']['tmp_name'];
		//File oeffnen
		$fp = fopen($filename,'r');
		//auslesen
		$content = fread($fp, filesize($filename));
		fclose($fp);
		//in HEX-Werte umrechnen
		$contentOUTPUT='';
		$contentOUTPUT.="<p>Orig.Name :: ".$_FILES['bild']['name'] ."    Type :: ".$_FILES['bild']['type']."</p>";
		$contentOUTPUT.='<textarea cols="80" rows="10" wrap="soft">'.base64_encode($content).'</textarea>';
	}
}

echo "	<form method='POST' enctype='multipart/form-data' action='".$_SERVER['PHP_SELF']."'>
		Bild: <input type='file' name='bild' />
		<input type='submit' name='submitbild' value='Upload' />
		</form>";
echo 'HexWert:<br />';
echo '<br />'.$contentOUTPUT;

?>
</body>
</html>
