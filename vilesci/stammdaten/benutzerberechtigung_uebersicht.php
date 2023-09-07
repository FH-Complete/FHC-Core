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
 *		  Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *		  Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *		  Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *		  Manfred Kindl	<manfred.kindl@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/berechtigung.class.php');
require_once ('../../include/organisationseinheit.class.php');
require_once ('../../include/benutzerfunktion.class.php');

echo '<html>
<head>
<title>Berechtigungen Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';

include('../../include/meta/jquery.php');
include('../../include/meta/jquery-tablesorter.php');

echo '
<script language="JavaScript" type="text/javascript">
function checkLength()
{
	filter = document.getElementById("searchbox").value;
	if(filter.length<2)
	{
		alert ("Bitte geben Sie mindestens 2 Zeichen für die Suche ein");
		return false;
	}
	else
		return true;
}
$(document).ready(function()
	{
		if ($("#berechtigung_kurzbz").val() == "" && $("#rolle_kurzbz").val() == "")
			$("#erweitertesuche").hide();

		$("#t1").tablesorter(
		{
			sortList: [[0,0],[1,0],[2,0]], 
			widgets: ["zebra"], 
			headers: {4:{sorter:false}} 
		});
		$("#t2").tablesorter(
		{
			sortList: [[0,0],[1,0],[2,0],[3,0]], 
			widgets: ["zebra", "filter", "stickyHeaders"],
			headers: {8:{sorter:false}},
			emptyTo: "emptyMax", 
			widgetOptions : {	filter_functions:  
								{ 
									// Add select menu to this column 
									6 : { 
									"Ja" : function(e, n, f, i, $r, c, data) { return e == "Ja" || e == "" },
									"Nein" : function(e, n, f, i, $r, c, data) { return /Nein/.test(e); } 
									}, 
									7 : { 
									"Aktiv" : function(e, n, f, i, $r, c, data) { return $r.find("div").hasClass( "buttonGreen" ); }, 
									"Inaktiv" : function(e, n, f, i, $r, c, data) { return $r.find("div").hasClass( "buttonRed" ) || $r.find("div").hasClass( "buttonYellow" ); } 
									} 
								} 
							} 
		});
		$("#t3").tablesorter(
		{
			sortList: [[1,0],[2,0],[3,0]],
			widgets: ["zebra", "filter", "stickyHeaders"],
			headers: {8:{sorter:false}},
			emptyTo: "emptyMax",
			widgetOptions : {	filter_functions:  
								{ 
									// Add select menu to this column 
									6 : { 
									"Ja" : function(e, n, f, i, $r, c, data) { return /Ja/.test(e); }, 
									"Nein" : function(e, n, f, i, $r, c, data) { return /Nein/.test(e); } 
									}, 
									7 : { 
									"Aktiv" : function(e, n, f, i, $r, c, data) { return $r.find("div").hasClass( "buttonGreen" ); }, 
									"Inaktiv" : function(e, n, f, i, $r, c, data) { return $r.find("div").hasClass( "buttonRed" ) || $r.find("div").hasClass( "buttonYellow" ); } 
									} 
								} 
							} 
		});
		$("#t4").tablesorter(
		{
			sortList: [[0,0],[1,0],[2,0]], 
			widgets: ["zebra", "filter", "stickyHeaders"],
			headers: {9:{sorter:false}},
			emptyTo: "emptyMax", 
			widgetOptions : {	filter_functions:  
								{ 
									// Add select menu to this column 
									7 : { 
									"Ja" : function(e, n, f, i, $r, c, data) { return /Ja/.test(e); }, 
									"Nein" : function(e, n, f, i, $r, c, data) { return /Nein/.test(e); } 
									}, 
									8 : { 
									"Aktiv" : function(e, n, f, i, $r, c, data) { return $r.find("div").hasClass( "buttonGreen" ); }, 
									"Inaktiv" : function(e, n, f, i, $r, c, data) { return $r.find("div").hasClass( "buttonRed" ) || $r.find("div").hasClass( "buttonYellow" ); } 
									} 
								} 
							} 
		});
	});

</script>
<style> 
.buttonGreen 
{ 
	width: 10px; 
	height: 10px; 
	background: #d1fab9; 
	background-image: -webkit-linear-gradient(top, #d1fab9, #00de00); 
	background-image: -moz-linear-gradient(top, #d1fab9, #00de00); 
	background-image: -ms-linear-gradient(top, #d1fab9, #00de00); 
	background-image: -o-linear-gradient(top, #d1fab9, #00de00); 
	background-image: linear-gradient(to bottom, #d1fab9, #00de00); 
	-webkit-border-radius: 10; 
	-moz-border-radius: 10; 
	border-radius: 10px; 
 
	border: solid #999 1px; 
	text-decoration: none; 
} 
.buttonYellow 
{ 
	width: 10px; 
	height: 10px; 
	background: #faf7b9; 
	background-image: -webkit-linear-gradient(top, #faf7b9, #cfde00); 
	background-image: -moz-linear-gradient(top, #faf7b9, #cfde00); 
	background-image: -ms-linear-gradient(top, #faf7b9, #cfde00); 
	background-image: -o-linear-gradient(top, #faf7b9, #cfde00); 
	background-image: linear-gradient(to bottom, #faf7b9, #cfde00); 
	-webkit-border-radius: 10; 
	-moz-border-radius: 10; 
	border-radius: 10px; 
 
	border: solid #999 1px; 
	text-decoration: none; 
} 
.buttonRed 
{ 
	width: 10px; 
	height: 10px; 
	background: #f79c9c; 
	background-image: -webkit-linear-gradient(top, #f79c9c, #cc0202); 
	background-image: -moz-linear-gradient(top, #f79c9c, #cc0202); 
	background-image: -ms-linear-gradient(top, #f79c9c, #cc0202); 
	background-image: -o-linear-gradient(top, #f79c9c, #cc0202); 
	background-image: linear-gradient(to bottom, #f79c9c, #cc0202); 
	-webkit-border-radius: 10; 
	-moz-border-radius: 10; 
	border-radius: 10px; 
	border: solid #999 1px; 
	text-decoration: none; 
} 
</style> 

</head>

<body class="background_main" onload="document.getElementById(\'searchbox\').focus()">
<h2>Berechtigungen Übersicht</h2>';

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

//Rechte pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/berechtigung'))
	die($rechte->errormsg);

$htmlstr = "";

$searchstr = (isset($_GET['searchstr'])?$_GET['searchstr']:'');
$benutzerart = (isset($_GET['benutzerart'])?$_GET['benutzerart']:'');
$benutzeraktiv = (isset($_GET['aktiv'])?$_GET['aktiv']:'aktiv');
$berechtigung_kurzbz = (isset($_GET['berechtigung_kurzbz'])?$_GET['berechtigung_kurzbz']:'');
$rolle_kurzbz = (isset($_GET['rolle_kurzbz'])?$_GET['rolle_kurzbz']:'');
$userOnly = (isset($_GET['userOnly']) ? true : false);

$htmlstr='
<table width="100%">
<tr>
	<td>
		<form accept-charset="UTF-8" name="searchbenutzer" method="GET" onsubmit="return checkLength();">
			BenutzerIn suchen:
			<input type="text" id="searchbox" name="searchstr" size="30" value="'.$searchstr.'" placeholder="Name oder UID eingeben">
			<select id="benutzerart" name="benutzerart">
				<option value="" '.($benutzerart == ''?'selected':'').'>Alle BenutzerInnen</option>
				<option value="mitarbeiter" '.($benutzerart == 'mitarbeiter'?'selected':'').'>MitarbeiterInnen</option>
				<option value="studierende" '.($benutzerart == 'studierende'?'selected':'').'>Studierende</option>
			</select>
			<select id="aktiv" name="aktiv">
				<option value="" '.($benutzeraktiv == ''?'selected':'').'>Aktiv und Inaktiv</option>
				<option value="aktiv" '.($benutzeraktiv == 'aktiv'?'selected':'').'>Aktiv</option>
				<option value="inaktiv" '.($benutzeraktiv == 'inaktiv'?'selected':'').'>Inaktiv</option>
			</select>
			<input type="submit" value="Suchen">
			&nbsp;&nbsp;
			<span style="float:right"><a href="#" onclick="$(\'#erweitertesuche\').toggle();">Erweiterte Suchoptionen ein-/ausblenden</a></span>
		</form>
		<div id="erweitertesuche">
		<hr>
		<form accept-charset="UTF-8" name="searchrechte" method="GET">
			Rechte:
			<select id="berechtigung_kurzbz" name="berechtigung_kurzbz">
				<option value=""></option>';
			$berechtigung = new berechtigung();
			$berechtigung->getBerechtigungen();
			foreach($berechtigung->result as $berechtigung)
			{
				if ($berechtigung_kurzbz == $berechtigung->berechtigung_kurzbz)
					$selected = 'selected="selected"';
				else
					$selected = '';
				$htmlstr .= '<option value="'.$berechtigung->berechtigung_kurzbz.'"  title="'.$berechtigung->beschreibung.'" '.$selected.'>'.$berechtigung->berechtigung_kurzbz.'</option>';
			}
			$htmlstr .= '</select>
			<input type="checkbox" name="userOnly" ' . ($userOnly == true ? 'checked' : '') . '> Nur User
			<input type="submit" value="Suchen">
		</form><hr>
		<form accept-charset="UTF-8" name="searchrollen" method="GET">
			Rollen:
			<select id="rolle_kurzbz" name="rolle_kurzbz">
				<option value=""></option>';
			$rollen = new berechtigung();
			$rollen->getRollen('rolle_kurzbz');
			foreach($rollen->result as $rolle)
			{
				if ($rolle_kurzbz == $rolle->rolle_kurzbz)
					$selected = 'selected="selected"';
				else
					$selected = '';
				$htmlstr .= '<option value="'.$rolle->rolle_kurzbz.'"  title="'.$rolle->beschreibung.'" '.$selected.'>'.$rolle->rolle_kurzbz.'</option>';
			}
			$htmlstr .= '</select>
			<input type="submit" value="Suchen">
		</form><hr>
		</div>
	</td>
</tr>
</table>
	';

//Benutzer suchen und Tabelle anzeigen
if(isset($_GET['searchstr']))
{
	$benutzer = new benutzer();
	$searchItems = explode(' ',$searchstr);
	if ($benutzeraktiv == 'aktiv')
		$benutzer->search($searchItems,"",true);
	if ($benutzeraktiv == 'inaktiv')
		$benutzer->search($searchItems,"",false);
	if ($benutzeraktiv == '')
		$benutzer->search($searchItems,"",null);

	if(count($benutzer->result)!=0)
	{
		$htmlstr .= "<table id='t1' class='tablesorter'><thead><tr>\n";
		$htmlstr .= "<th>Nachname</th><th>Vorname</th><th>UID</th><th>Aktiv</th><th>Aktion</th>";
		$htmlstr .= "</tr></thead><tbody>\n";

		foreach($benutzer->result as $row)
		{
			if ($benutzerart == 'mitarbeiter' && $row->mitarbeiter_uid != '')
			{
				if ($benutzerart == 'mitarbeiter' && $row->mitarbeiter_uid != '')
				{
					$benutzerrolle = new benutzerberechtigung();
					$benutzerrolle->loadBenutzerRollen($row->uid);
					$aktiv = new benutzer();
					$aktiv->load($row->uid);

					$htmlstr .= "	<tr>\n";
					$htmlstr .= "		<td>".$row->nachname."</td>\n";
					$htmlstr .= "		<td>".$row->vorname."</td>\n";
					$htmlstr .= "		<td>".$row->uid."</td>\n";
					$htmlstr .= "		<td>".($aktiv->bnaktiv?"Ja":"Nein")."</td>\n";
					$htmlstr .= "		<td><a href='benutzerberechtigung_details.php?uid=".$row->uid."' target='vilesci_detail'>".(count($benutzerrolle->berechtigungen)!=0?"Rechte bearbeiten":"Rechte vergeben")."</a></td>\n";
					$htmlstr .= "	</tr>\n";
				}
			}
			elseif ($benutzerart == 'studierende' && $row->mitarbeiter_uid == '')
			{
				$benutzerrolle = new benutzerberechtigung();
				$benutzerrolle->loadBenutzerRollen($row->uid);
				$aktiv = new benutzer();
				$aktiv->load($row->uid);

				$htmlstr .= "	<tr>\n";
				$htmlstr .= "		<td>".$row->nachname."</td>\n";
				$htmlstr .= "		<td>".$row->vorname."</td>\n";
				$htmlstr .= "		<td>".$row->uid."</td>\n";
				$htmlstr .= "		<td>".($aktiv->bnaktiv?"Ja":"Nein")."</td>\n";
				$htmlstr .= "		<td><a href='benutzerberechtigung_details.php?uid=".$row->uid."' target='vilesci_detail'>".(count($benutzerrolle->berechtigungen)!=0?"Rechte bearbeiten":"Rechte vergeben")."</a></td>\n";
				$htmlstr .= "	</tr>\n";
			}
			elseif ($benutzerart == '')
			{
				$benutzerrolle = new benutzerberechtigung();
				$benutzerrolle->loadBenutzerRollen($row->uid);
				$aktiv = new benutzer();
				$aktiv->load($row->uid);

				$htmlstr .= "	<tr>\n";
				$htmlstr .= "		<td>".$row->nachname."</td>\n";
				$htmlstr .= "		<td>".$row->vorname."</td>\n";
				$htmlstr .= "		<td>".$row->uid."</td>\n";
				$htmlstr .= "		<td>".($aktiv->bnaktiv?"Ja":"Nein")."</td>\n";
				$htmlstr .= "		<td><a href='benutzerberechtigung_details.php?uid=".$row->uid."' target='vilesci_detail'>".(count($benutzerrolle->berechtigungen)!=0?"Rechte bearbeiten":"Rechte vergeben")."</a></td>\n";
				$htmlstr .= "	</tr>\n";
			}
		}
		$htmlstr .= "</tbody></table>\n";
	}
	else
	{
		$htmlstr .= "Es wurden keine Übereinstimmungen mit Ihrem Suchbegriff gefunden";
	}
}

//Berechtigungen suchen und Tabelle anzeigen
if($berechtigung_kurzbz != '')
{
	$berechtigungen = new benutzerberechtigung();
	
	// Wenn $userOnly false ist, werden die  Rollen und Funktionen ausgegeben, die das Recht beinhalten, 
	// ansonsten werden die Rollen und Funktionen auf User aufgelöst und nur User ausgegeben 
	if ($userOnly == false) 
	{ 
		$berechtigungen->getBenutzerFromBerechtigung($berechtigung_kurzbz);
	
		if(isset($berechtigungen->result) && count($berechtigungen->result) != 0)
		{
			$htmlstr .= "<h3>".$berechtigung_kurzbz."</h3>\n";
			$htmlstr .= "<table id='t2' class='tablesorter'><thead><tr>\n";
			$htmlstr .= "<th>Rolle</th>
							<th>Funktion</th>
							<th>Nachname</th>
							<th>Vorname</th>
							<th>UID</th>
							<th>Art</th>
							<th data-value='Ja'>Benutzer Aktiv</th>
							<th data-value='Aktiv'>Status</th>
							<th>Aktion</th>";
			$htmlstr .= "</tr></thead><tbody>\n";
	
			foreach($berechtigungen->result as $row)
			{
				$benutzer = new benutzer();
				$benutzer->load($row->uid);
	
				$heute = strtotime(date('Y-m-d'));
	
				if ($row->ende!='' && strtotime($row->ende) < $heute)
				{
					$status = '<div class="buttonRed"></div>';
				}
				elseif ($row->start!='' && strtotime($row->start) > $heute)
				{
					$status = '<div class="buttonYellow"></div>';
				}
				else
				{
					$status = '<div class="buttonGreen"></div>';
				}
	
				$htmlstr .= '	<tr>';
				$htmlstr .= '		<td>'.($row->rolle_kurzbz != ''?$row->rolle_kurzbz:'').'</td>';
				$htmlstr .= '		<td>'.($row->funktion_kurzbz != ''?$row->funktion_kurzbz:'').'</td>';
				$htmlstr .= '		<td>'.($benutzer->nachname != ''?$benutzer->nachname:'').'</td>';
				$htmlstr .= '		<td>'.($benutzer->vorname != ''?$benutzer->vorname:'').'</td>';
				$htmlstr .= '		<td>'.($row->uid != ''?$row->uid:'').'</td>';
				$htmlstr .= '		<td>'.$row->art.'</td>';
				$htmlstr .= '		<td>'.(isset($row->uid)?$benutzer->bnaktiv?'Ja':'Nein':'').'</td>';
				$htmlstr .= '		<td align="center">'.$status.'</td>';
				if ($row->uid != '')
					$htmlstr .= '		<td><a href="benutzerberechtigung_details.php?uid='.$row->uid.'" target="vilesci_detail">Benutzerrechte bearbeiten</a></td>';
				elseif ($row->funktion_kurzbz != '')
					$htmlstr .= '		<td><a href="benutzerberechtigung_details.php?funktion_kurzbz='.$row->funktion_kurzbz.'" target="vilesci_detail">Funktionsrechte bearbeiten</a></td>';
				elseif ($row->rolle_kurzbz != '')
					$htmlstr .= '		<td><a href="berechtigungrolle.php?rolle_kurzbz='.$row->rolle_kurzbz.'" target="vilesci_detail">Rollenrechte bearbeiten</a></td>';
	
				$htmlstr .= '	</tr>';
			}
			$htmlstr .= '</tbody></table>';
		}
		else
		{
			$htmlstr .= "Für diese Berechtigung sind keine Einträge vorhanden";
		}
	}
	else  
	{ 
		$berechtigungen_array = array(); 
		$berechtigungen->getBenutzerFromBerechtigung($berechtigung_kurzbz); 

		if (isset($berechtigungen->result) && count($berechtigungen->result) != 0) 
		{ 
			foreach ($berechtigungen->result as $row) 
			{ 
				if ($row->uid != '') 
				{ 
					$berechtigungen_array[] = array('uid' => $row->uid, 
													'art' => $row->art, 
													'start' => $row->start, 
													'ende' => $row->ende, 
													'oe_kurzbz' => $row->oe_kurzbz, 
													'rolle_kurzbz' => '', 
													'funktion_kurzbz' => ''); 
				} 
				if ($row->rolle_kurzbz != '') 
				{ 
					$user_rolleberechtigung = new benutzerberechtigung(); 
					$user_rolleberechtigung->getBenutzerFromRolle($row->rolle_kurzbz); 
					foreach ($user_rolleberechtigung->result as $row_rolle) 
					{ 
						$berechtigungen_array[] = array('uid' => $row_rolle->uid, 
														'art' => $row_rolle->art, 
														'start' => $row_rolle->start, 
														'ende' => $row_rolle->ende, 
														'oe_kurzbz' => $row_rolle->oe_kurzbz, 
														'rolle_kurzbz' => $row_rolle->rolle_kurzbz, 
														'funktion_kurzbz' => $row->funktion_kurzbz); 
					} 
				} 
				if ($row->funktion_kurzbz != '') 
				{ 
					$user_funktion = new benutzerfunktion(); 
					$user_funktion->getBenutzerFunktionen($row->funktion_kurzbz); 
					foreach ($user_funktion->result as $row_funktion) 
					{ 
						$berechtigungen_array[] = array('uid' => $row_funktion->uid, 
														'art' => $row->art, 
														'start' => $row->start, 
														'ende' => $row->ende, 
														'oe_kurzbz' => $row_funktion->oe_kurzbz, 
														'rolle_kurzbz' => '', 
														'funktion_kurzbz' => $row->funktion_kurzbz); 
					} 
				} 
			} 
//			 var_dump($berechtigungen_array);exit; 
			// Benutzer der Rolle auflösen 
			foreach ($berechtigungen->result as $row) 
			{ 
				$user_rolleberechtigung = new benutzerberechtigung(); 
				$user_rolleberechtigung->getBenutzerFromRolle($row->rolle_kurzbz); 
			} 

			// Anzahl uniquer UIDs ermitteln
			$berechtigungen_array_uids = array_map(function ($each)
			{
				return $each['uid'];
			}, $berechtigungen_array);

			$htmlstr .= "<h3>".$berechtigung_kurzbz."</h3>\n";
			$htmlstr .= "<div style='font-size: 9pt'>".count($berechtigungen_array)." Einträge</div>";
			$htmlstr .= "<div style='font-size: 9pt'>".count(array_unique($berechtigungen_array_uids))." UIDs</div>";
			$htmlstr .= "<table id='t4' class='tablesorter'><thead><tr>\n"; 
			$htmlstr .= "	<th>Nachname</th> 
							<th>Vorname</th> 
							<th>UID</th> 
							<th>Art</th> 
							<th>OE_Kurzbz</th> 
							<th>Rolle</th> 
							<th>Funktion</th> 
							<th>Benutzer Aktiv</th> 
							<th>Status</th> 
							<th>Aktion</th>"; 
			$htmlstr .= "</tr></thead><tbody>\n"; 
			 
			foreach ($berechtigungen_array as $key => $row) 
			{ 
				$benutzer = new benutzer(); 
				$benutzer->load($row['uid']); 
				 
				$organisationseinheit = new organisationseinheit($row['oe_kurzbz']); 
				 
				$heute = strtotime(date('Y-m-d')); 
				 
				if ($row['ende'] != '' && strtotime($row['ende']) < $heute) 
				{ 
					$status = '<div class="buttonRed"></div>'; 
				} 
				elseif ($row['start'] != '' && strtotime($row['start']) > $heute) 
				{ 
					$status = '<div class="buttonYellow"></div>'; 
				} 
				else 
				{ 
					$status = '<div class="buttonGreen"></div>'; 
				} 
				 
				$htmlstr .= '	<tr>'; 
				$htmlstr .= '		<td>' . ($benutzer->nachname != '' ? $benutzer->nachname : '') . '</td>'; 
				$htmlstr .= '		<td>' . ($benutzer->vorname != '' ? $benutzer->vorname : '') . '</td>'; 
				$htmlstr .= '		<td>' . ($row['uid'] != '' ? $row['uid'] : '') . '</td>'; 
				$htmlstr .= '		<td>' . $row['art'] . '</td>'; 
				$htmlstr .= '		<td>' . $organisationseinheit->organisationseinheittyp_kurzbz . ' ' .$organisationseinheit->bezeichnung . '</td>'; 
				$htmlstr .= '		<td>' . $row['rolle_kurzbz'] . '</td>'; 
				$htmlstr .= '		<td>' . $row['funktion_kurzbz'] . '</td>'; 
				$htmlstr .= '		<td>' . (isset($row['uid']) ? $benutzer->bnaktiv ? 'Ja' : 'Nein' : '') . '</td>'; 
				$htmlstr .= '		<td align="center">' . $status . '</td>'; 
				$htmlstr .= '		<td><a href="benutzerberechtigung_details.php?uid=' . $row['uid'] . '" target="vilesci_detail">Benutzerrechte bearbeiten</a></td>';				 
				$htmlstr .= '	</tr>'; 
			} 
			$htmlstr .= '</tbody></table>'; 
		} 
		else 
		{ 
			$htmlstr .= "Für diese Berechtigung sind keine Einträge vorhanden"; 
		} 
	}
}

//Rollen suchen und Tabelle anzeigen
if($rolle_kurzbz != '')
{
	$rollen = new benutzerberechtigung();
	$rollen->getBenutzerFromRolle($rolle_kurzbz);

	if(isset($rollen->result) && count($rollen->result) != 0)
	{
		// Anzahl uniquer UIDs ermitteln
		$berechtigungen_array_uids = sizeof(array_column($rollen->result, null, 'uid'));

		$htmlstr .= "<h3>".$berechtigung_kurzbz."</h3>\n";
		$htmlstr .= "<div style='font-size: 9pt'>".count($rollen->result)." Einträge</div>";
		$htmlstr .= "<div style='font-size: 9pt'>".$berechtigungen_array_uids." UIDs</div>";
		$htmlstr .= "<table id='t3' class='tablesorter'><thead><tr>\n";
		$htmlstr .= "	<th>Rolle</th>
						<th>Funktion</th>
						<th>Nachname</th>
						<th>Vorname</th>
						<th>UID</th>
						<th>Art</th>
						<th data-value='Ja'>Benutzer Aktiv</th>
						<th data-value='Aktiv'>Status</th>
						<th>Aktion</th>";
		$htmlstr .= "</tr></thead><tbody>\n";

		foreach($rollen->result as $row)
		{
			$benutzer = new benutzer();
			$benutzer->load($row->uid);

			$heute = strtotime(date('Y-m-d'));

			if ($row->ende!='' && strtotime($row->ende) < $heute)
			{
				$status = '<div class="buttonRed"></div>';
			}
			elseif ($row->start!='' && strtotime($row->start) > $heute)
			{
				$status = '<div class="buttonYellow"></div>';
			}
			else
			{
				$status = '<div class="buttonGreen"></div>';
			}

			$htmlstr .= '	<tr>';
			$htmlstr .= '		<td>'.$row->rolle_kurzbz.'</td>';
			$htmlstr .= '		<td>'.($row->funktion_kurzbz != ''?$row->funktion_kurzbz:'').'</td>';
			$htmlstr .= '		<td>'.($benutzer->nachname != ''?$benutzer->nachname:'').'</td>';
			$htmlstr .= '		<td>'.($benutzer->vorname != ''?$benutzer->vorname:'').'</td>';
			$htmlstr .= '		<td>'.($row->uid != ''?$row->uid:'').'</td>';
			$htmlstr .= '		<td>'.$row->art.'</td>';
			$htmlstr .= '		<td>'.(isset($row->uid)?$benutzer->bnaktiv?'Ja':'Nein':'').'</td>';
			$htmlstr .= '		<td align="center">'.$status.'</td>';
			$htmlstr .= '		<td><a href="benutzerberechtigung_details.php?uid='.$row->uid.'" target="vilesci_detail">Rechte bearbeiten</a></td>';
			$htmlstr .= '	</tr>';
		}
		$htmlstr .= '</tbody></table>';
	}
	else
	{
		$htmlstr .= "Für diese Berechtigung sind keine Einträge vorhanden";
	}
}

	echo $htmlstr;
?>



</body>
</html>
