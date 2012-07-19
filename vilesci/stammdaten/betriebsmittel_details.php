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
	
	$errorstr = '';  // neuladen der liste im oberen frame
	$htmlstr = '';
		
	if (isset($_REQUEST['betriebsmittel_id']))
		$betriebsmittel_id =$_REQUEST['betriebsmittel_id'];
	
	$betriebsmittelperson_id = isset($_REQUEST['betriebsmittelperson_id'])?$_REQUEST['betriebsmittelperson_id']:'';

	$uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:'';
	$wert = isset($_REQUEST['wert'])?$_REQUEST['wert']:'';
	
	$bmbetriebsmitteltyp=isset($_POST["bmbetriebsmitteltyp"])?$_POST["bmbetriebsmitteltyp"]:'';
	$bmbeschreibung=isset($_POST["bmbeschreibung"])?$_POST["bmbeschreibung"]:'';
	$bmnummer=isset($_POST["bmnummer"])?$_POST["bmnummer"]:'';
	$bmnummer2=isset($_POST["bmnummer2"])?$_POST["bmnummer2"]:'';
	
	$bmpausgegebenam=isset($_POST["bmpausgegebenam"])?$_POST["bmpausgegebenam"]:'';
	$bmpretouram=isset($_POST["bmpretouram"])?$_POST["bmpretouram"]:'';
	$bmpkaution=isset($_POST["bmpkaution"])?$_POST["bmpkaution"]:'';
	$bmpuid=isset($_POST["bmpuid"])?$_POST["bmpuid"]:'';
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
			$bm->nummer2 = $bmnummer2;
			$bm->beschreibung = $bmbeschreibung;
			$bm->updatevon = $user;
			$bm->updateamum = date('Y-m-d H:i:s');
			
			if(!$bm->save())
			{
				$errorstr.='<br><span class="error">Aktualisierung des Betriebsmittel-Datensatzes fehlgeschlagen!</span>';
			}
			else 
			{
				$errorstr.='<br><span class="ok">Betriebsmittel-Datensatz wurde aktualisiert.</span>';
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
			    $bmp->uid = $bmpuid;
			    
				if(!$bmp->save())
				{
					$errorstr.='<br><span class="error">Aktualisierung des Betriebsmittelperson-Datensatzes fehlgeschlagen!</span>';
				}
				else 
				{
					$errorstr.='<br><span class="ok">Betriebsmittelperson-Datensatz wurde aktualisiert.</span>';
				}
			}
		}
	}
	
	
	if (isset($betriebsmittel_id) && isset($betriebsmittelperson_id))
	{

		$bm=new betriebsmittel($betriebsmittel_id);
		$bmp=new betriebsmittelperson($betriebsmittelperson_id);
	
	
		$htmlstr .= '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">'."\n";
		$htmlstr .= "<table>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "	<td>Betriebsmittel</td>\n";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= '		<td><b>BM-ID </b></td><td><input type="hidden" name="betriebsmittel_id" value="'.$db->convert_html_chars($bm->betriebsmittel_id).'">'.$db->convert_html_chars($bm->betriebsmittel_id)."</td>\n";
		$htmlstr .= '		<td><b>Betriebsmitteltyp:</b></td><td>'.$db->convert_html_chars($bm->betriebsmitteltyp)."</td>\n";

		$htmlstr .= '		<td><b>Nummer</b></td><td><input type="text" name="bmnummer" value="'.$db->convert_html_chars($bm->nummer).'" size="15" maxlength="64"></td>'."\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= '		<td><b>Beschreibung </b></td><td colspan="3"><input type="text" name="bmbeschreibung" value="'.$db->convert_html_chars($bm->beschreibung).'" size="30" maxlength="64"></td>'."\n";
		$htmlstr .= '		<td><b>Nummer2</b></td><td><input type="text" name="bmnummer2" value="'.$bm->nummer2.'" size="15" maxlenght="64"></td>'."\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><b>Anlage</b></td>";
		$htmlstr .= '		<td colspan="2">'.$datum_obj->formatDatum($bm->insertamum,'d.m.Y H:i')."&nbsp;(".$db->convert_html_chars($bm->insertvon).")</td>\n";

		$htmlstr .= "		<td><b>Letzte Änderung</b></td>";
		$htmlstr .= '		<td colspan="2">'.$datum_obj->formatDatum($bm->updateamum,'d.m.Y H:i')."&nbsp; (".$db->convert_html_chars($bm->updatevon).")</td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td>&nbsp;</td>\n";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "	<td>Personenzuordnung</td>\n";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= '		<td><b>BMP-ID </b></td><td><input type="hidden" name="betriebsmittelperson_id" value="'.$db->convert_html_chars($bmp->betriebsmittelperson_id).'">'.$db->convert_html_chars($bmp->betriebsmittelperson_id)."</td>\n";
		$htmlstr .= '		<td><b>ausgegeben am </b></td><td><input type="text" name="bmpausgegebenam" value="'.$datum_obj->formatDatum($bmp->ausgegebenam,'d.m.Y').'" size="10" maxlength="10"></td>'."\n";
		$htmlstr .= '		<td><b>retour am </b></td><td><input type="text" name="bmpretouram" value="'.$datum_obj->formatDatum($bmp->retouram,'d.m.Y').'" size="10" maxlength="10"></td>'."\n";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= '		<td><b>Kaution </b></td><td><input type="text" name="bmpkaution" value="'.$db->convert_html_chars($bmp->kaution).'" size="3" maxlength="6"></td>'."\n";
		$htmlstr .= '		<td><b>UID </b></td><td><input type="text" name="bmpuid" value="'.$db->convert_html_chars($bmp->uid).'" size="10" maxlength="32"></td>'."\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= '		<td><b>Anmerkung </b></td><td colspan="8"><input type="text" name="bmpanmerkung" value="'.$db->convert_html_chars($bmp->anmerkung).'" size="64" maxlength="64"></td>'."\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><b>Anlage </b></td>";
		$htmlstr .= '		<td colspan="2">'.$datum_obj->formatDatum($bmp->insertamum,'d.m.Y H:i')."&nbsp; (".$db->convert_html_chars($bmp->insertvon).")</td>\n";
		$htmlstr .= "		<td><b>Letzte Änderung </b></td>";
		$htmlstr .= "		<td>".$datum_obj->formatDatum($bmp->updateamum,'d.m.Y H:i')."&nbsp; (".$db->convert_html_chars($bmp->updatevon).")</td>\n";
		$htmlstr .= "	</tr><tr>";
		$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
		$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen' onclick='return confdel()'></td>";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</table>\n";
		$htmlstr .= "</form>\n";
	}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Betriebsmitel-Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script type="text/javascript">

function confdel()
{
	return confirm("Diesen Datensatz wirklick loeschen?");
}

</script>
</head>
<body>
<h2>Betriebsmittel - Details</h2>
<?php
	echo $htmlstr;
	echo $errorstr;
?>

</body>
</html>
