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
	
	$frage = new frage(); 
	$frage->getFragenGebiet($gebiet_id);
	
	foreach($frage->result as $fragen)
	{
		$sprachevorschlag = new vorschlag(); 
		$spracheFrage = new frage();
		echo "&lt;NR:".$fragen->nummer."&gt; "; 
		// FRAGE anzeigen
		$spracheFrage->getFrageSprache($fragen->frage_id, $sprache);
		echo "$spracheFrage->text<br/><br/>\n"; 
		
		// Bild einbinden wenn vorhanden
		if($spracheFrage->bild!='')
				echo "<img class='testtoolfrage' src='../bild.php?src=frage&amp;frage_id=$spracheFrage->frage_id&amp;sprache=".$sprache."' /><br/><br/>\n";
				
			//Sound einbinden
		if($spracheFrage->audio!='')
		{
			echo '
			<script language="JavaScript" src="audio-player/audio-player.js"></script>
			<object type="application/x-shockwave-flash" data="audio-player/player.swf" id="audioplayer1" height="24" width="290">
			<param name="movie" value="audio_player/player.swf" />
			<param name="FlashVars" value="playerID=audioplayer1&amp;soundFile=../sound.php%3Fsrc%3Dfrage%26frage_id%3D'.$sprachefrage->frage_id.'%26sprache%3D'.$sprache.'" />
			<param name="quality" value="high" />
			<param name="menu" value="false" />
			<param name="wmode" value="transparent" />
			</object>';
		}
		echo"<br><table>"; 
		
		// ANTWORTEN anzeigen
		$sprachevorschlag->getVorschlag($fragen->frage_id, $sprache, $random=false);
		$anzahlBild = 0;
		foreach($sprachevorschlag->result as $vor)
		{ 
			$vorschlag = new vorschlag();
			$vorschlag->loadVorschlagSprache($vor->vorschlag_id, $sprache);
			
			if($vorschlag->bild =='')
				echo '<tr><td>'.$vorschlag->text.'</td><td style="text-align:right">'.$vor->punkte.'</td></tr>';
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
				<script language="JavaScript" src="audio-player/audio-player.js"></script>
				<object type="application/x-shockwave-flash" data="audio-player/player.swf" id="audioplayer1" height="24" width="290">
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