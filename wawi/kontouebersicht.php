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
require_once('../include/sprache.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>WaWi Konten</title>	
	
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../skin/wawi.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script> 

	<script type="text/javascript">
	
		$(document).ready(function() 
			{ 
			    $("#myTable").tablesorter(
				{
					sortList: [[1,0]],
					widgets: ['zebra']
				}); 
			} 
		); 

		function conf_del()
		{
			return confirm('Dieses Konto wirklich löschen?');
		}
			
		</script>
</head>
<body>

<?php

$id = '';
$konto = new wawi_konto();
$user=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$sprache = new sprache(); 
$sprache->getAll(); 

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
			echo '<h1>Konto - Bearbeiten</h1>';
			//Update Konto
			$id = $_GET['id'];
			if($konto->load($id))
			{
				$checked ='';
				if($konto->aktiv)	
				{
					$checked = 'checked';
				}
				
				echo "<form action=\"kontouebersicht.php?method=save&id=$konto->konto_id\" method=\"post\">\n";
				echo '<table border=0>';
				echo '<tr>';
				echo '<td>Kontonummer</td>';
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kontonummer\" value=\"$konto->kontonr\"></td>\n";
		 	 	echo '</tr>';

				foreach($sprache->result as $s)	// Mehrsprachigkeit
				{  
					if($s->content == true)
					{
			 	 		echo '<tr>';
			 	 		echo "<td>$s->sprache</td>\n";
			 	 		echo "<td><input type=\"text\" size=\"32\" name=\"beschreibung$s->sprache\" value=\"".$konto->beschreibung[$s->sprache]."\"></td>\n";
			 	 		echo "</tr>\n";
					}
				}
		 	 	echo "<tr>\n";
		 	 	echo "<td>Kurzbezeichnung</td>\n";
		 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kurzbezeichnung\" value=\"$konto->kurzbz\"></td>\n";
		 	 	echo "</tr>\n";
		 	 	echo "<tr>\n";
		 	 	echo "<td>Aktiv?</td>\n";
		 	 	echo "<td><input type=\"checkbox\" name=\"aktiv\" value=\"aktiv\" $checked>\n";
		 	 	echo "</tr>\n";
		 	 	echo "<tr><td>&nbsp;</td></tr>\n"; 	 	
		  		echo "<tr>\n";
		  		echo "<td><a href=kontouebersicht.php> zurueck </a></td>\n";
		  		echo "<td><input type='submit' value='update'></td>\n";
		  		echo "</tr>\n";
		  		echo "</table>\n";
		  		echo "</form>\n";
			}
			else 
			{
				echo 'Konto wurde nicht gefunden!';
				echo "<a href=\"kontouebersicht.php\"> <br>zurück </a>\n";
			}
		}
		else
		{
				if(!$rechte->isBerechtigt('wawi/konto',null,'sui'))
				die('Keine Berechtigung für Insert');
				
				echo '<h1>Konto - Neu</h1>';
				// neues Konto anlegen
				echo "<form action=\"kontouebersicht.php?method=save\" method=\"post\">\n";
				echo "<table border=0>\n";
				echo "<tr>\n";
				echo "<td>Kontonummer</td>\n";
		 	 	echo "<td><input type=\"text\" size=\"32\" maxlength =\"32\" name=\"kontonummer\" value=\"\"></td>\n";
		 	 	echo "</tr>\n";
				foreach($sprache->result as $s)
				{  
					if($s->content == true)
					{
			 	 		echo "<tr>\n";
			 	 		echo "<td>$s->sprache</td>\n";
			 	 		echo "<td><input type=\"text\" size=\"32\" name=\"beschreibung$s->sprache\" value=\"\"></td>\n";
			 	 		echo "</tr>\n";
					}
				}
		 	 	echo "<tr><td>Kurzbezeichnung</td>\n";
		 	 	echo "<td><input type=\"text\" size=\"32\" maxlength =\"32\" name=\"kurzbezeichnung\" value=\"\"></td>\n";
		 	 	echo "</tr>\n";
		 	 	echo "<tr><td>&nbsp;</td></tr>\n"; 	 	
		  		echo "<tr>\n";
		  		echo "<td><a href=kontouebersicht.php> zurueck </a></td>\n";
		  		echo "<td><input type='submit' value='Anlegen'></td>\n";
		  		echo "</tr>\n";
		  		echo "</table>\n";
		  		echo "</form>\n";
		}
	}
	else if($_GET['method']== "save")
	{
		if(!$rechte->isBerechtigt('wawi/konto',null,'sui'))
		die('Keine Berechtigung für Insert');
		
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

		foreach($sprache->result as $s)
			if($s->content == true)
				$konto->beschreibung[$s->sprache] = $_POST['beschreibung'.$s->sprache]; 	
			
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
		$id = (isset($_GET['id'])?$_GET['id']:null);
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
			
		echo '<h1>Konto - Zusammenlegen</h1>';
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
		
		echo "<form name=\"suche\" action=\"kontouebersicht.php?method=merge\", method=\"POST\">\n";
		echo "<table border ='0' width='100%'>\n";
		echo "<tr\n>";
		echo "<td width='45%%'><input name='filter1' type='text' value=\"$filter1\" size=\"64\" maxlength=\"64\" id ='suchen' onfocus=\"this.value='';\"></td>\n";
		echo "<td width='10%'><input type='submit' value=' suchen ' ></td>\n";
		echo "<td width='45%%'><input name='filter2' type='text' value=\"$filter2\" size=\"64\" maxlength=\"64\" id ='suchen' onfocus=\"this.value='';\"></td>\n";
		echo "</tr>\n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "</form>\n";
		
		echo "<br><a href=kontouebersicht.php>zurueck</a><br>\n";
		echo "</table>\n";	
		//Tabellen anzeigen
	
		echo "<form name='form_table' action='kontouebersicht.php?method=merge' method='POST'>\n";
		echo "<table width='100%' border='0' cellspacing='0' cellpadding='0' id='myTable' class='tablesorter'>\n";
		echo "<tr>\n";	
		echo "<td valign='top'>Der wird gelöscht:</td>\n";
	
		 //Tabelle 1
		echo "<table id='myTable' class='tablesorter'><thead> <tr>\n";
		echo "<th>Konto ID</th>\n";
		echo "<th>Kontonummer</th>\n";
		echo "<th>Kurzbezeichnung</th>\n";
		$i = 1; 
		foreach($sprache->result as $s)
			if($s->content == true)
				echo "<th>$s->sprache</th>\n";	
				
		echo "<th>Aktiv</th>\n";
		echo "<th>&nbsp;</th></tr></thead><tbody>\n";	
	
		$konto  = new wawi_konto();
		$konto->getKonto($filter1);
		
		foreach($konto->result as $row)
		{
			//Zeilen der Tabelle ausgeben
			echo '<tr>';
			echo '<td>'.$row->konto_id."</td>\n";
			echo '<td>'.$row->kontonr."</td>\n";
			echo '<td>'.$row->kurzbz."</td>\n";
			foreach($sprache->result as $s)
				if($s->content == true)
					echo '<td>'.$row->beschreibung[$s->sprache]."</td>\n";
			
			echo '<td>'.$aktiv=($row->aktiv)?'ja':'nein'."</td>\n";
			echo "<td><input type='radio' name='radio_1' value='$row->konto_id' </td>\n";
			echo "</tr>\n";
		}
		echo "</tbody>\n";
		echo "</table>\n"; 
		echo "</td>\n";
		echo "<td valign='top'><input type='submit' value='  ->  '></td>\n";
		echo "<td valign='top'>Der bleibt:</td>\n";
		
		//Tabelle 2
		echo "<table id='myTable' class='tablesorter'><thead> <tr>\n";
		echo "<th>&nbsp;</th>\n";
		echo "<th>Konto ID</th>\n";
		echo "<th>Kontonummer</th>\n";
		echo "<th>Kurzbezeichnung</th>\n";
		
		foreach($sprache->result as $s)
			if($s->content == true)
				echo "<th>$s->sprache</th>\n";	

		echo "<th>Aktiv</th>\n";
		echo "</tr></thead><tbody>\n";	
	
		$konto  = new wawi_konto();
		$konto->getKonto($filter2);
		foreach($konto->result as $row)
		{
			echo "<tr>\n";
			echo "<td><input type='radio' name='radio_2' value='$row->konto_id' \n";
			echo "<td>$row->konto_id</td>\n";
			echo '<td>'.$row->kontonr."</td>\n";
			echo '<td>'.$row->kurzbz."</td>\n";
 
			foreach($sprache->result as $s)
				if($s->content == true)
					echo '<td>'.$row->beschreibung[$s->sprache]."</td>\n";
			
			echo '<td>'.($row->aktiv?'ja':'nein')."</td>\n";
			echo '</tr>';
		}
		echo "</table>\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n";		
	}
}
else 
{
	// Anzeige aller Konten
	if($konto->getAll(null, 'kontonr' ))
	{	
		echo '<h1>Konto - &Uuml;bersicht</h1>';
		//echo '<a href="kontouebersicht.php?method=update">neues Konto anlegen </a><br>';
		//echo '<a href="kontouebersicht.php?method=merge">Konten zusammenlegen </a><br><br>';
		
		echo "<table id='myTable' class='tablesorter'> <thead>\n";
		
		
		echo "<tr>
				<th></th>
				<th>Kontonummer</th>
				<th>Kurzbzeichnung</th>\n";
		$i = 1; 
		foreach($sprache->result as $s)
		{
			if($s->content == true)
			{
				$headline = $sprache->getSpracheFromIndex($s->index);
				echo "<th>$headline</th>\n";
			}
			$i++;
		}

		echo "<th>aktiv</th>
			  </tr> </thead><tbody>\n";
	
		foreach($konto->result as $row)
		{
			//Zeilen der Tabelle ausgeben
			echo "<tr>\n";
			echo "<td nowrap> <a href= \"kontouebersicht.php?method=update&amp;id=$row->konto_id\" title='Bearbeiten'> <img src=\"../skin/images/edit_wawi.gif\"> </a><a href=\"kontouebersicht.php?method=delete&amp;id=$row->konto_id\" onclick='return conf_del()' title='Löschen'> <img src=\"../skin/images/delete_x.png\"></a></td>\n";
			echo '<td>'.$row->kontonr."</td>\n";
			echo '<td>'.$row->kurzbz."</td>\n";
			
			$i = 1; 
			foreach($sprache->result as $s)
			{
				if($s->content == true)
				{
					echo '<td>';
					if(isset($row->beschreibung[$s->sprache]))
						echo $row->beschreibung[$s->sprache]."\n";
					echo '</td>'; 
				}
				$i++;
			}
				
			echo '<td>'.($row->aktiv?'ja':'nein')."</td>\n";
			echo "</tr>\n";
			
		}
		echo "</tbody></table>\n";
	}
}

?>