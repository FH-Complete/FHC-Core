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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/betriebsmittel.class.php');
	require_once('../../include/betriebsmittelperson.class.php');
	require_once('../../include/globals.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/variable.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/studiensemester.class.php');
	require_once('../../include/datum.class.php');
	
	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$datum_obj = new datum();
	
	$user = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('basis/betriebsmittel'))
		die('Sie haben keine Rechte fuer diese Seite');
	
	$reloadstr = "";  // neuladen der liste im oberen frame
	$htmlstr = "";
	$errorstr = ""; //fehler beim insert
		
	if (isset($_REQUEST['betriebsmittel_id']))
		$betriebsmittel_id =$_REQUEST['betriebsmittel_id'];
	
	$betriebsmittelperson_id = isset($_REQUEST['betriebsmittelperson_id'])?$_REQUEST['betriebsmittelperson_id']:'';

	$uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:'';
	$wert = isset($_REQUEST['wert'])?$_REQUEST['wert']:'';
	
	$bmbetriebsmitteltyp=isset($_POST["bmbetriebsmitteltyp"])?$_POST["bmbetriebsmitteltyp"]:'';
	$bmbeschreibung=isset($_POST["bmbeschreibung"])?$_POST["bmbeschreibung"]:'';
	$bmnummer=isset($_POST["bmnummer"])?$_POST["bmnummer"]:'';
	
	$bmpausgegebenam=isset($_POST["bmpausgegebenam"])?$_POST["bmpausgegebenam"]:'';
	$bmpretouram=isset($_POST["bmpretouram"])?$_POST["bmpretouram"]:'';
	$bmpkaution=isset($_POST["bmpkaution"])?$_POST["bmpkaution"]:'';
	$bmpanmerkung=isset($_POST["bmpanmerkung"])?$_POST["bmpanmerkung"]:'';
		
	if(isset($_POST["schick"]))
	{
		
		if(!$rechte->isBerechtigt('basis/betriebsmittel', null, 'suid'))
			die('Sie haben keine Rechte fuer diese Aktion');
		
		if($betriebsmittel_id!='')
		{
			$bm=new betriebsmittel();
			if(!$bm->load($betriebsmittel_id))
				die('Fehler beim Laden des Betriebsmittels');
			$bm->nummer = $bmnummer;
			$bm->beschreibung = $bmbeschreibung;
			$bm->updatevon = $user;
			$bm->updateamum = date('Y-m-d H:i:s');
			
			if(!$bm->save())
			{
				$reloadstr.="<br>Aktualisierung des Betriebsmittel-Datensatzes fehlgeschlagen!";
			}
			else 
			{
				$reloadstr.="<br>Betriebsmittel-Datensatz wurde aktualisiert.";
			}
			if($betriebsmittelperson_id!='')
			{
				$bmp=new betriebsmittelperson();
				if(!$bmp->load($betriebsmittelperson_id))
					die('Fehler beim Laden der Personenzuordnung');
				
				$bmp->ausgegebenam=$datum_obj->formatDatum($bmpausgegebenam,'Y-m-d');
				$bmp->retouram=$datum_obj->formatDatum($bmpretouram,'Y-m-d');
				$bmp->kaution=$bmpkaution;
				$bmp->anmerkung=$bmpanmerkung;
				$bmp->updatevon=$user;
			    $bmp->insertvon=date('Y-m-d H:i:s');
			    
				if(!$bmp->save())
				{
					$reloadstr.="<br>Aktualisierung des Betriebsmittelperson-Datensatzes fehlgeschlagen!";
				}
				else 
				{
					$reloadstr.="<br>Betriebsmittelperson-Datensatz wurde aktualisiert.";
				}
			}
		}
	}
	
	
	if (isset($betriebsmittel_id) && isset($betriebsmittelperson_id))
	{

		$bm=new betriebsmittel($betriebsmittel_id);
		$bmp=new betriebsmittelperson($betriebsmittelperson_id);
	
	
		$htmlstr .= "<form action='' method='POST'>\n";
		$htmlstr .= "<table style='padding-top:10px;'>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "	<td>Betriebsmittel</td>\n";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "		<td><b>BM-ID </b></td><td><input type='hidden' name='bmbetriebsmittel' value='$bm->betriebsmittel_id'>".$bm->betriebsmittel_id."</td>\n";
		$htmlstr .= "		<td><b>Betriebsmitteltyp:</b></td><td>$bm->betriebsmitteltyp</td>\n";

		$htmlstr .= "		<td><b>Nummer</b></td><td><input type='text' name='bmnummer' value='".$bm->nummer."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><b>Beschreibung </b></td><td colspan='8'><input type='text' name='bmbeschreibung' value='".$bm->beschreibung."' size='64' maxlength='64'></td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><b>insertamum </b></td><td>".$datum_obj->formatDatum($bm->insertamum,'d.m.Y H:i')."&nbsp;</td>\n";
		$htmlstr .= "		<td><b>insertvon </b></td><td>".$bm->insertvon."</td>\n";

		$htmlstr .= "		<td><b>updateamum</b></td><td>".$datum_obj->formatDatum($bm->updateamum,'d.m.Y H:i')."&nbsp;</td>\n";
		$htmlstr .= "		<td><b>updatevon</b></td><td>".$bm->updatevon."</td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td>&nbsp;</td>\n";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "	<td>Personenzuordnung</td>\n";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "		<td><b>BMP-ID </b></td><td><input type='hidden' name='betriebsmittelperson_id' value='$bmp->betriebsmittelperson_id'>$bmp->betriebsmittelperson_id</td>\n";
		$htmlstr .= "		<td><b>ausgegeben am </b></td><td><input type='text' name='bmpausgegebenam' value='".$datum_obj->formatDatum($bmp->ausgegebenam,'d.m.Y')."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><b>retour am </b></td><td><input type='text' name='bmpretouram' value='".$datum_obj->formatDatum($bmp->retouram,'d.m.Y')."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><b>Kaution </b></td><td><input type='text' name='bmpkaution' value='".$bmp->kaution."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><b>Anmerkung </b></td><td colspan='8'><input type='text' name='bmpanmerkung' value='".$bmp->anmerkung."' size='64' maxlength='64'></td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><b>insertamum </b></td><td>".$datum_obj->formatDatum($bmp->insertamum,'d.m.Y H:i')."&nbsp;</td>\n";
		$htmlstr .= "		<td><b>insertvon </b></td><td>".$bmp->insertvon."</td>\n";
		$htmlstr .= "		<td><b>updateamum </b></td><td>".$datum_obj->formatDatum($bmp->updateamum,'d.m.Y H:i')."&nbsp;</td>\n";
		$htmlstr .= "		<td><b>updatevon </b></td><td>".$bmp->updatevon."</td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
		$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen' onclick='return confdel()'></td>";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</table>\n";
		$htmlstr .= "</form>\n";
	}
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Betriebsmitel-Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script type="text/javascript">

function confdel()
{
	return confirm("Diesen Datensatz wirklick loeschen?");
}

</script>
</head>
<body style="background-color:#eeeeee;">
<h2>Betriebsmittel - Details</h2>
<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>
