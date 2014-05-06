<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>,
 */
require_once("../../../config/cis.config.inc.php");
require_once("../../../include/gebiet.class.php");
require_once("../../../include/frage.class.php");
require_once("../../../include/vorschlag.class.php");
require_once('../../../include/functions.inc.php');
require_once("../../../include/benutzerberechtigung.class.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Testool Fragen Übersicht</title>
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/testtool', null, 's'))
	die('<span class="error">Sie haben keine Berechtigung für diese Seite</span>');

$gebiet = new gebiet(); 
$gebiet->getAll(); 
$sprache = (isset($_REQUEST['Sprache'])?$_REQUEST['Sprache']:'German');
$Auswahlgebiet = (isset($_REQUEST['AuswahlGebiet'])?$_REQUEST['AuswahlGebiet']:''); 

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" name="TesttoolUebersicht">
<table>
<tr>
<td>Gebiet:</td>
<td><select name="AuswahlGebiet"><option value="auswahl"> - Bitte Auswählen - </option>';
foreach ($gebiet->result as $gebietResult)
{
	$selected ='';
	if($Auswahlgebiet == $gebietResult->gebiet_id)
		$selected = 'selected';	
	echo '<option value="'.$gebietResult->gebiet_id.'" '.$selected.'>'.$gebietResult->gebiet_id.' - '.$gebietResult->bezeichnung.' - '.$gebietResult->kurzbz.'</option>';
}
echo '</select></td></tr>
<tr>
<td>Sprache: </td>
<td><select name="Sprache">';
if($sprache == 'German')
	echo '<option selected value="German">Deutsch</option>';
else
	echo '<option value="German">Deutsch</option>';
if($sprache == 'English')
	echo '<option selected value="English">Englisch</option>';
else
	echo '<option value="English">Englisch</option>';

echo'</select>
</td>
<tr>
<td colspan="2"><input type="submit" value="Anzeigen"></td></tr>
</table><br>';


if(isset($_REQUEST['AuswahlGebiet']))
{
	$gebiet_id = $_REQUEST['AuswahlGebiet'];
	
	$gebietdetails = new gebiet();
	$gebietdetails->load($gebiet_id);
	
	if ($gebietdetails)
	{
		echo '
		<table>
		<tr>
			<td align="right">Gebiet:</td>
			<td>'.$gebietdetails->bezeichnung.'</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td align="right">Beschreibung:</td>
			<td>'.($gebietdetails->beschreibung!=''?$gebietdetails->beschreibung:'-').'</td>
		</tr>
		<tr>
			<td align="right">Zeit:</td>
			<td>'.$gebietdetails->zeit.'</td>
		</tr>
		<tr>
			<td align="right">Multipleresponse:</td>
			<td>'.($gebietdetails->multipleresponse==true?'Ja':'Nein').'</td>
		</tr>
		<tr>
			<td align="right">Gestellte Fragen:</td>
			<td>'.$gebietdetails->maxfragen.'</td>
		</tr>
		<tr>
			<td align="right">Zufallsfrage:</td>
			<td>'.($gebietdetails->zufallfrage==true?'Ja':'Nein').'</td>
		</tr>
		<tr>
			<td align="right">Zufallsvorschlag:</td>
			<td>'.($gebietdetails->zufallvorschlag==true?'Ja':'Nein').'</td>
		</tr>';
		if ($gebietdetails->level_start!='')
		{
			echo'
			<tr>
				<td align="right">Startlevel:</td>
				<td>'.$gebietdetails->level_start.'</td>
			</tr>
			<tr>
				<td align="right">Höheres Level nach:</td>
				<td>'.$gebietdetails->level_sprung_auf.' Fragen</td>
			</tr>
			<tr>
				<td align="right">Niedrigeres Level nach:</td>
				<td>'.$gebietdetails->level_sprung_ab.' Fragen</td>
			</tr>
			<tr>
				<td align="right">Levelgleichverteilung:</td>
				<td>'.($gebietdetails->levelgleichverteilung==true?'Ja':'Nein').'</td>
			</tr>';
		}
		echo'
		<tr>
			<td align="right">Maximalpunkte:</td>
			<td>'.$gebietdetails->maxpunkte.'</td>
		</tr>
		<tr>
			<td align="right">Antworten pro Zeile:</td>
			<td>'.$gebietdetails->antwortenprozeile.'</td>
		</tr>
		
		
		</table><br><hr>';
	}
	
	$frage = new frage(); 
	$frage->getFragenGebiet($gebiet_id);
	
	foreach($frage->result as $fragen)
	{
		$sprachevorschlag = new vorschlag(); 
		$spracheFrage = new frage();
		$spracheFrage->getFrageSprache($fragen->frage_id, $sprache);
		
		echo "<b>&lt;NR:".$fragen->nummer.($fragen->level!=""?"&nbsp;&nbsp;Level: ".$fragen->level."":"").($fragen->demo=="t"?"&nbsp;&nbsp;Demo":"")."&gt;</b><br> ";
		//Sound einbinden
		if($spracheFrage->audio!='')
		{
			echo '
			<script language="JavaScript" src="../audio-player/audio-player.js"></script>
			<object type="application/x-shockwave-flash" data="../audio-player/player.swf" id="audioplayer1" height="24" width="290">
			<param name="movie" value="audio_player/player.swf" />
			<param name="FlashVars" value="playerID=audioplayer1&amp;soundFile=../sound.php%3Fsrc%3Dfrage%26frage_id%3D'.$spracheFrage->frage_id.'%26sprache%3D'.$sprache.'" />
			<param name="quality" value="high" />
			<param name="menu" value="false" />
			<param name="wmode" value="transparent" />
			</object>';
		} 
		// FRAGE anzeigen
		echo "$spracheFrage->text<br/><br/>\n"; 
		
		// Bild einbinden wenn vorhanden
		if($spracheFrage->bild!='')
				echo "<img class='testtoolfrage' src='../bild.php?src=frage&amp;frage_id=$spracheFrage->frage_id&amp;sprache=".$sprache."' /><br/><br/>\n";
				
		echo"<br><table>"; 
		
		// ANTWORTEN anzeigen
		$sprachevorschlag->getVorschlag($fragen->frage_id, $sprache, $random=false);
		$anzahlBild = 0;
		foreach($sprachevorschlag->result as $vor)
		{ 
			$vorschlag = new vorschlag();
			$vorschlag->loadVorschlagSprache($vor->vorschlag_id, $sprache);
			
			if($vorschlag->bild =='')
				echo '<tr><td align="right"><b>'.$vor->punkte.'</b></td><td style="border-left:1px solid;">&nbsp;'.$vorschlag->text.'</td></tr>';
			if($vorschlag->bild!='')
			{
				// zeilenumbruch nach 4 bilder
				if($anzahlBild%4==0)
					echo "</tr>";
				echo "<td>";
				echo "<img class='testtoolvorschlag' src='../bild.php?src=vorschlag&amp;vorschlag_id=$vor->vorschlag_id&amp;sprache=".$sprache."' /><br/>";
				echo "<br>".$vor->punkte."</td>";
				$anzahlBild++;
			}
			if($vorschlag->audio!='')
			{
				echo '
				<script language="JavaScript" src="../audio-player/audio-player.js"></script>
				<object type="application/x-shockwave-flash" data="../audio-player/player.swf" id="audioplayer1" height="24" width="290">
				<param name="movie" value="audio_player/player.swf" />
				<param name="FlashVars" value="playerID=audioplayer1&amp;soundFile=../sound.php%3Fsrc%3Dvorschlag%26vorschlag_id%3D'.$vorschlag->vorschlag_id.'%26sprache%3D'.$_SESSION['sprache'].'" />
				<param name="quality" value="high" />
				<param name="menu" value="false" />
				<param name="wmode" value="transparent" />
				</object>';
			}

		}
		echo "</table><br><hr>";
	}
}
?>

</body>