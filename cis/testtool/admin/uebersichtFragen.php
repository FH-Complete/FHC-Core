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
require_once('../../../include/basis_db.class.php');
require_once("../../../include/gebiet.class.php");
require_once("../../../include/frage.class.php");
require_once("../../../include/vorschlag.class.php");
require_once('../../../include/functions.inc.php');
require_once("../../../include/benutzerberechtigung.class.php");
require_once('../../../include/studiengang.class.php');
require_once('../../../include/ablauf.class.php');

if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Testool Fragen Übersicht</title>
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body style="padding: 10px">
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
$loesungen = (isset($_REQUEST['loesungen']) && $_REQUEST['loesungen'] != '' ? true:false);

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);
$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');
$gebiet_id = (isset($_GET['gebiet_id'])?$_GET['gebiet_id']:'');

echo '<form action="'.$_SERVER['PHP_SELF'].'?stg_kz='.$stg_kz.'" method="post" name="TesttoolUebersicht">
<table>
<tr>
<td>Studiengang:</td><td>';
//Liste der Studiengänge
echo '<select onchange="window.location.href=this.value">';
		echo '<option value="'.$_SERVER['PHP_SELF'].'?" >Alle Studiengänge</option>';
foreach ($studiengang->result as $row)
{
	$stg_arr[$row->studiengang_kz] = $row->kuerzel;
	if ($stg_kz == '')
	$stg_kz = $row->studiengang_kz;
	if ($row->studiengang_kz == $stg_kz)
	$selected = 'selected="selected"';
	else
	$selected = '';

	echo '<option value="'.$_SERVER['PHP_SELF'].'?stg_kz='.$row->studiengang_kz.'" '.$selected.'>'.$db->convert_html_chars($row->kuerzel).' - '.$db->convert_html_chars($row->bezeichnung).'</option>'."\n";
}
		echo '</select>';
echo '</td>
</tr>
<tr>
<td>Gebiet:</td><td>';
//Liste der Gebiete
$qry = "SELECT * FROM testtool.tbl_ablauf WHERE studiengang_kz=".$db->db_add_param($stg_kz);
$anzahl = $db->db_num_rows($db->db_query($qry));

if ($stg_kz !== "-1" && $anzahl !== 0)
{
	$qry = "SELECT * FROM testtool.tbl_gebiet LEFT JOIN testtool.tbl_ablauf USING (gebiet_id)
		WHERE studiengang_kz=".$db->db_add_param($stg_kz)." ORDER BY semester,reihung";
}
else
	$qry = "SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung";

if (($anzahl !== 0) || ($stg_kz == '-1') && ($stg_kz !== ''))
{
	if ($result = $db->db_query($qry))
	{
		echo ' <select name="AuswahlGebiet">';
		echo '<option value="auswahl"> - Bitte Auswählen - </option>';

		while ($row = $db->db_fetch_object($result))
		{
			if ($Auswahlgebiet == $row->gebiet_id)
			{
				$selected = 'selected="selected"';
			}
			else
			{
				$selected = '';
			}

			if ($stg_kz == "-1")
			{
				echo '<option value="'.$row->gebiet_id.'" '.$selected.'>'.$row->bezeichnung.' - '.$row->kurzbz.' - ID:'.$row->gebiet_id.'</option>'."\n";
			}
			else
			{
				echo '<option value="'.$row->gebiet_id.'" '.$selected.'>('.$row->semester.') - '.$row->bezeichnung.' - '.$row->kurzbz.' - ID:'.$row->gebiet_id.'</option>'."\n";
			}

		}
		echo '</select>';
	}
}
elseif (($anzahl == 0))
{
	echo 'Keine Gebiete für diesen Studiengang';
}
echo '</td>';
/*echo '<td><select name="AuswahlGebiet"><option value="auswahl"> - Bitte Auswählen - </option>';
foreach ($gebiet->result as $gebietResult)
{
	$selected ='';
	if($Auswahlgebiet == $gebietResult->gebiet_id)
		$selected = 'selected';	
	echo '<option value="'.$gebietResult->gebiet_id.'" '.$selected.'>'.$gebietResult->gebiet_id.' - '.$gebietResult->bezeichnung.' - '.$gebietResult->kurzbz.'</option>';
}
echo '</select></td>';*/
echo '</tr>
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
</tr>
<tr>
<td>
Mit Lösungen
</td>
<td><input type="checkbox" name="loesungen" '.($loesungen ? 'checked':'').'></td>
</tr>
<tr>
<td colspan="2"><input type="submit" value="Anzeigen"></td></tr>
</table><br>';


if(isset($_REQUEST['AuswahlGebiet']))
{
	$gebiet_id = $_REQUEST['AuswahlGebiet'];
	
	$gebietdetails = new gebiet();
	$gebietdetails->load($gebiet_id);
	
	$qry = "SELECT DISTINCT UPPER(typ||kurzbz) AS studiengang 
			FROM testtool.tbl_ablauf JOIN public.tbl_studiengang USING (studiengang_kz)
			WHERE gebiet_id=".$db->db_add_param($gebiet_id)."
			ORDER BY studiengang";
	$result = $db->db_query($qry);
	
	if ($gebietdetails)
	{
		echo '
		<table>
		<tr>
			<td align="right">Gebiet:</td>
			<td>'.$gebietdetails->bezeichnung.'</td>
		</tr>
		<tr>
			<td valign="top">Verwendet in den Studiengängen:</td>
			<td>';
			$i=1;
			while ($row = $db->db_fetch_object($result))
			{
				echo $row->studiengang.($db->db_num_rows($result)>1 && $db->db_num_rows($result)>$i?', ':'');
				$i++;
				if ($i % 10 == 0)
					echo '<br>';
			}
			echo '</td>
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
		</tr>
		<tr>
			<td align="right">Startlevel:</td>
			<td>'.($gebietdetails->level_start!=''?$gebietdetails->level_start:'Keines').'</td>
		</tr>
		<tr>
			<td align="right">Höheres Level nach:</td>
			<td>'.($gebietdetails->level_sprung_auf!=''?$gebietdetails->level_sprung_auf.' richtigen Antwort(en)':'-').'</td>
		</tr>
		<tr>
			<td align="right">Niedrigeres Level nach:</td>
			<td>'.($gebietdetails->level_sprung_ab!=''?$gebietdetails->level_sprung_ab.' falschen Antwort(en)':'-').'</td>
		</tr>
		<tr>
			<td align="right">Levelgleichverteilung:</td>
			<td>'.($gebietdetails->levelgleichverteilung==true?'Ja':'Nein').'</td>
		</tr>
		<tr>
			<td align="right">Maximalpunkte:</td>
			<td>'.$gebietdetails->maxpunkte.'</td>
		</tr>
		<tr>
				<td align="right">Offsetpunkte:</td>
				<td>'.$gebietdetails->offsetpunkte.'</td>
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
			echo '	<audio src="../sound.php?src=frage&amp;frage_id='.$spracheFrage->frage_id.'&amp;sprache='.$sprache.'" controls="controls">
						<div>
							<p>Ihr Browser unterstützt dieses Audioelement leider nicht.</p>
						</div>
					</audio>';
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
			
			if($vorschlag->bild == '')
			{
				if ($loesungen)
				{
					echo '<tr><td style="border-right:1px solid;">'.$vor->nummer.'</td></td><td align="right"><b>'.$vor->punkte.'</b></td><td style="border-left:1px solid;">&nbsp;'.$vorschlag->text.'</td></tr>';
				}
				else
				{
					echo '<tr><td style="border-right:1px solid;">'.$vor->nummer.'</td><td>&nbsp;'.$vorschlag->text.'</td></tr>';
				}
			}
			if($vorschlag->bild!='')
			{
				// zeilenumbruch nach 4 bilder
				if($anzahlBild%4==0)
					echo "</tr>";
				echo "<td>";
				echo "<img class='testtoolvorschlag' src='../bild.php?src=vorschlag&amp;vorschlag_id=$vor->vorschlag_id&amp;sprache=".$sprache."' /><br/>";
				if ($loesungen)
				{
					echo "<br>".$vor->punkte."</td>";
				}
				else
				{
					echo "</td>";
				}

				$anzahlBild++;
			}
			if($vorschlag->audio!='')
			{
				echo '	<audio src="../sound.php?src=vorschlag&amp;vorschlag_id='.$vorschlag->vorschlag_id.'&amp;sprache='.$sprache.'" controls="controls">
							<div>
								<p>Ihr Browser unterstützt dieses Audioelement leider nicht.</p>
							</div>
						</audio>';
			}

		}
		echo "</table><br><hr>";
	}
}
?>

</body>