<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../config/wawi.config.inc.php');
require_once('auth.php');
require_once('../include/wawi_konto.class.php');
require_once('../include/benutzerberechtigung.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Konten</title>	
	<link rel="stylesheet" href="../skin/style.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	
	<script type="text/javascript" src="../include/js/jquery.js"></script> 
	<script type="text/javascript" src="../include/js/jquery.metadata.js"></script> 
	<script type="text/javascript" src="../include/js/jquery.tablesorter.js"></script>

	<script language="Javascript">
		$(document).ready(function() 
			{ 
			    $("#myTable").tablesorter(
				{
					sortList: [[1,0]],
					widthFixed: true, 
					widgets: ['zebra']
				}); 
			} 
		); 

		function conf_del()
		{
			return confirm('Diese Gruppe wirklich löschen?');
		}
			
		</script>
</head>
<body>

<?php

$id = '';
$konto = new wawi_konto();
$user=get_uid();
echo 'USER: '.$user. '<br><br>';
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('wawi/konto'))
	die('Keine Berechtigung');

if(isset($_GET['method']))
{
	if($_GET['method']== "update")
	{
		if(!$rechte->isBerechtigt('wawi/konto',null,'su'))
			die('Keine Berechtigung für Update');
		
		if(isset($_GET['id']))
		{
			//Update Konto
			$id = $_GET['id'];
			if($konto->load($id))
			{
				$beschreibung_ger = $konto->beschreibung[1];
				$beschreibung_eng = $konto->beschreibung[2];
				$checked ='';
				if($konto->aktiv)	
				{
					$checked = 'checked';
				}
				
				echo "<form action=\"kontouebersicht.php?method=save&id=$konto->konto_id\" method=\"post\">";
				echo '<table border=0>';
				echo '<tr>';
				echo '<td>Kontonummer</td>';
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kontonummer\" value=\"$konto->kontonr\"></td>";
		 	 	echo '</tr>';
		 	 	echo '<tr>';
		 	 	echo "<td>Beschreibung Deutsch</td>";
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"beschreibung_d\" value=\"$beschreibung_ger\"></td>";
		 	 	echo "</tr>";
		 	 	echo '<tr>';
		 	 	echo "<td>Beschreibung Englisch</td>";
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"beschreibung_e\" value=\"$beschreibung_eng\"></td>";
		 	 	echo "</tr>";
		 	 	echo '<tr>';
		 	 	echo "<td>Kurzbezeichnung</td>";
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kurzbezeichnung\" value=\"$konto->kurzbz\"></td>";
		 	 	echo "</tr>";
		 	 	echo '<tr>';
		 	 	echo "<td>Aktiv?</td>";
		 	 	echo "<td><input type=\"checkbox\" name=\"aktiv\" value=\"aktiv\" $checked>";
		 	 	echo "</tr>";
		 	 	echo "<tr><td>&nbsp;</td><tr>"; 	 	
		  		echo '<tr>';
		  		echo '<td><a href=kontouebersicht.php> zurueck </a></td>';
		  		echo '<td><input type="submit" value="update"></td>';
		  		echo '</tr>';
		  		echo '</form>';
		  		echo '</table>';
			}
			else 
			{
				echo 'Konto wurde nicht gefunden!';
				echo "<a href=\"kontouebersicht.php\"> <br>zurück </a>";
			}
		}
		else
		{
				if(!$rechte->isBerechtigt('wawi/konto',null,'sui'))
				die('Keine Berechtigung für Insert');
				// neues Konto anlegen
				echo "<form action=\"kontouebersicht.php?method=save\" method=\"post\">";
				echo '<table border=0>';
				echo '<tr>';
				echo '<td>Kontonummer</td>';
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kontonummer\" value=\"\"></td>";
		 	 	echo '</tr>';
		 	 	echo '<tr>';
		 	 	echo "<td>Beschreibung Deutsch</td>";
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"beschreibung_d\" value=\"\"></td>";
		 	 	echo "</tr>";
		 	 	echo '<tr>';
		 	 	echo "<td>Beschreibung Englisch</td>";
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"beschreibung_e\" value=\"\"></td>";
		 	 	echo "</tr>";
		 	 	echo '<tr>';
		 	 	echo "<td>Kurzbezeichnung</td>";
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kurzbezeichnung\" value=\"\"></td>";
		 	 	echo "</tr>";
		 	 	echo "<tr><td>&nbsp;</td><tr>"; 	 	
		  		echo '<tr>';
		  		echo '<td><a href=kontouebersicht.php> zurueck </a></td>';
		  		echo '<td><input type="submit" value="Anlegen"></td>';
		  		echo '</tr>';
		  		echo '</form>';
		  		echo '</table>';
		}
	}
	else if($_GET['method']== "save")
	{
		if(!$rechte->isBerechtigt('wawi/konto',null,'sui'))
		die('Keine Berechtigung für Insert');
		//Daten in der DB speichern
		$konto = new wawi_konto();	
		$aktiv = '';
		$ausgabe ="Konto wurde erfolgreich upgedated!";
		
		if(isset($_GET['id']))
		{
			//Update
			$konto->load($_GET['id']);
			$konto->konto_id = $_GET['id'];
			$konto->new = false;
			$konto->aktiv = isset ($_POST['aktiv']);
		}
		else 
		{
			// neues Konto
			$konto->new = true;
			$konto->aktiv = true;
			$konto->insertamum = date('Y-m-d H:i:s');
			$konto->insertvon=$user; 
			$ausgabe = "Konto wurde erfolgreich erstellt!";
		}	

		$konto->kontonr = $_POST['kontonummer'];
		$konto->beschreibung[1] = $_POST['beschreibung_d']; 
		$konto->beschreibung[2] = $_POST['beschreibung_e'];
		$konto->kurzbz = $_POST['kurzbezeichnung'];
		$konto->updateamum = date('Y-m-d H:i:s');
		$konto->updatevon = $user; 
			
		if(!$konto->save())
		{
			die('Fehler beim Speichern:'.$konto->errormsg);
		}
		echo $ausgabe;
		echo "<a href=\"kontouebersicht.php\"> <br>zurück </a>";
		
	}
	else if ($_GET['method']=="delete")
	{
		//Konto löschen
		if(!$rechte->isBerechtigt('wawi/konto',null,'suid'))
			die('Keine Berechtigung für Löschen');
		$id = $_GET['id'];
		if($konto->delete($id)==true)
		{
			echo "Datensatz erfolgreich gelöscht!";
			echo "<a href=\"kontouebersicht.php\"> <br>zurück </a>";
		}
		else
		{
			echo $konto->errormsg; 
			echo "<a href=\"kontouebersicht.php\"> <br>zurück </a>";
		}	
	}
	else if ($_GET['method']=="merge")
	{			
		//Kontos zusammenlegen
		if(!$rechte->isBerechtigt('wawi/konto',null,'su'))
			die('Keine Berechtigung für Update');
		$konto = new wawi_konto();
		
		if(isset($_POST['radio_1']) && isset($_POST['radio_2']))
		{
			$radio_1 = $_POST['radio_1'];
			$radio_2 = $_POST['radio_2'];
			
			if($radio_1==$radio_2)
			{
				echo "Die Datensaetze duerfen nicht die gleiche ID haben";
			}
			else
			{
				if($konto->zusammenlegen($radio_1, $radio_2))
				{
					echo "erfolgreich zusammengelegt";
				}
				else 
				{
					echo $konto->errormsg; 
				}
			}
		}
		else 
		{
			echo "Es muß je ein Radio-Button pro Tabelle angeklickt werden";
		}
		
		$order = '';
		$filter1 = isset($_POST['filter1'])?$_POST['filter1']:'';
		$filter2 = isset($_POST['filter2'])?$_POST['filter2']:'';
		
		echo "<form name=\"suche\" action=\"kontouebersicht.php?method=merge\", method=\"POST\">";
		echo "<table border ='0' width='100%'>";
		echo "<tr>";
		echo "<td width='45%%'><input name='filter1' type='text' value=\"$filter1\" size=\"64\" maxlength=\"64\" id ='suchen' onfocus=\"this.value='';\"></td>";
		echo "<td width='10%'><input type='submit' value=' suchen ' ></td>";
		echo "<td width='45%%'><input name='filter2' type='text' value=\"$filter2\" size=\"64\" maxlength=\"64\" id ='suchen' onfocus=\"this.value='';\"></td>";
		echo "</tr>";
		echo "<tr><td>&nbsp;</td></tr>";
		
		echo "</form>";
		echo '<br><a href=kontouebersicht.php>zurueck</a><br>';
		echo "</table>";
		//Tabellen anzeigen
		echo "<form name='form_table' action='kontouebersicht.php?method=merge' method='POST'>";
		echo "<table width='100%' border='0' cellspacing='0' cellpadding='0' id='myTable' class='tablesorter'>";
		echo "<tr>";	
		echo "<td valign='top'>Der wird gelöscht:";
	
		 //Tabelle 1
		echo "<table id='myTable' class='tablesorter'><thead> <tr>";
		echo "<th>Konto ID</th>";
		echo "<th>Kontonummer</th>";
		echo "<th>Kurzbezeichnung</th>";
		echo "<th>Beschreibung Deutsch</th>";
		echo "<th>Beschreibung Englisch</th>";
		echo "<th>Aktiv</th>";
		echo "<th>&nbsp;</th></tr></thead><tbody>";	
	
		$konto  = new wawi_konto();
		$konto->getKonto($filter1);
		$i=0;
	
		
		foreach($konto->result as $row)
		{
			//Zeilen der Tabelle ausgeben
			echo '<tr>';
			echo "<td>$row->konto_id</td>";
			echo '<td>'.$row->kontonr.'</td>';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->beschreibung[1].'</td>';
			echo '<td>'.$row->beschreibung[2].'</td>';
			echo '<td>'.$aktiv=($row->aktiv)?'ja':'nein'.'</td>';
			echo "<td><input type='radio' name='radio_1' value='$row->konto_id' ";
			echo '</tr>';

			$i++;
		}
		echo "</tbody>";
		echo "</table>"; 
		echo "</td>";
		echo "<td valign='top'><input type='submit' value='  ->  '></td>";
		echo "<td valign='top'>Der bleibt:";
		
		//Tabelle 2
		//echo "<table ><tr class='liste'>";
		echo "<table id='myTable' class='tablesorter'><thead> <tr>";
		echo "<th>&nbsp;</th>";
		echo "<th>Konto ID</th>";
		echo "<th>Kontonummer</th>";
		echo "<th>Kurzbezeichnung</th>";
		echo "<th>Beschreibung Deutsch</th>";
		echo "<th>Beschreibung Englisch</th>";
		echo "<th>Aktiv</th>";
		echo "</tr></thead><tbody>";	
	
	
		$konto  = new wawi_konto();
		$konto->getKonto($filter2);
		$i=0;
		foreach($konto->result as $row)
		{
			echo '<tr>';
			echo "<td><input type='radio' name='radio_2' value='$row->konto_id' ";
			echo "<td>$row->konto_id</td>";
			echo '<td>'.$row->kontonr.'</td>';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->beschreibung[1].'</td>';
			echo '<td>'.$row->beschreibung[2].'</td>';
			echo '<td>'.$aktiv=($row->aktiv)?'ja':'nein'.'</td>';
			echo '</tr>';

			$i++;
		}
		echo "</table>";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "</form>";		
	}
	
}
else 
{
	// Anzeige aller Konten
	if($konto->getAll(null, 'kontonr' ))
	{
		echo '<table id="myTable" class="tablesorter"> <thead>';
	
		echo '<tr>
				<th></th>
				<th>Kontonummer</th>
				<th>Kurzbzeichnung</th>
				<th>Beschreibung Deutsch</th>
				<th>Beschreibung Englisch</th>
				<th>aktiv</th>
			  </tr> </thead><tbody>';
	
		foreach($konto->result as $row)
		{
			//Zeilen der Tabelle ausgeben
			echo '<tr>';
			echo "<td nowrap> <a href= \"kontouebersicht.php?method=update&id=$row->konto_id\"> <img src=\"edit.gif\"> </a><a href=\"kontouebersicht.php?method=delete&id=$row->konto_id\" onclick='return conf_del()'> <img src=\"close.gif\"></a>";
			echo '<td>'.$row->kontonr.'</td>';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->beschreibung[1].'</td>';
			echo '<td>'.$row->beschreibung[2].'</td>';
			echo '<td>'.$aktiv=($row->aktiv)?'ja':'nein'.'</td>';
			echo '</tr>';
			
		}
		echo '</tbody></table>';
		echo '<a href="kontouebersicht.php?method=update">neues Konto anlegen </a><br>';
		echo '<a href="kontouebersicht.php?method=merge">Konten zusammenlegen </a><br><br>';
		echo '<a href="logout.php">abmelden</a><br>';

	}
}



?>