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
require_once('../include/organisationseinheit.class.php');
require_once('auth.php');
require_once('../include/wawi_kostenstelle.class.php');
require_once('../include/wawi_konto.class.php');
require_once('../include/benutzerberechtigung.class.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>WaWi Kostenstellen</title>	
	<link rel="stylesheet" href="../skin/tablesort.css" type="text/css"/>
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

$kostenstelle = new wawi_kostenstelle(); 
$user=get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('wawi/kostenstelle'))
	die('Sie haben keine Berechtigung für diese Seite');

if(isset($_GET['method']))
{
	if($_GET['method']== 'update')
	{
		//wenn id gesetzt ist --> update ansonsten neue anlegen 
		if(isset($_GET['id']))
		{
			echo '<h1>Kostenstelle - Bearbeiten</h1>';
			$id = $_GET['id'];
			if(!$rechte->isberechtigt('wawi/kostenstelle',null, 'su',$id))
				die('Sie haben keine Berechtigung für diese Kostenstelle');
			
			//gültige ID
			if(is_numeric($id))
			{
				// Kostenstelle mit der ID updaten
				$kostenstelle = new wawi_kostenstelle();
				$checked ='';
				$oe = new organisationseinheit(); 
				$oe->getAll(); 
				$oeinheiten= $oe->result; 
				
				if($kostenstelle->load($id))
				{
					if($kostenstelle->aktiv)	
					{
						$checked = 'checked';
					}
					
					echo "<form action=\"kostenstellenuebersicht.php?method=save&id=$kostenstelle->kostenstelle_id\" method=\"post\">";
					echo '<table border=0>';
					echo '<tr>';					
					echo " Organisationseinheit: <SELECT name='filter_oe_kurzbz'>";
					echo '<option value="">-- Keine Auswahl --</option>';
					
					foreach ($oeinheiten as $oei)
					{
						if($oei->oe_kurzbz==$kostenstelle->oe_kurzbz)
							$selected='selected';
						else 
						$selected='';
				
						if($oei->aktiv)
						{
							echo '<option value="'.$oei->oe_kurzbz.'" '.$selected.'>'.$oei->organisationseinheittyp_kurzbz.' '.$oei->bezeichnung.'</option>';
						}
						else 
						{
							echo '<option style="text-decoration:line-through;" value="'.$oei->oe_kurzbz.'" '.$selected.'>'.$oei->bezeichnung.'</option>';
						}	
					}
					
					echo "</SELECT>";	
			 	 	echo '<tr>';
			 	 	echo "<td>Bezeichnung</td>";
			 	 	echo "<td><input type=\"text\" size=\"32\" name=\"bezeichnung\" value=\"$kostenstelle->bezeichnung\"></td>";
			 	 	echo "</tr>";
			 	 	echo '<tr>';
			 	 	echo "<td>Kurzbezeichnung</td>";
			 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kurzbezeichnung\" value=\"$kostenstelle->kurzbz\"></td>";
			 	 	echo "</tr>";
			 	 	echo '<tr>';
			 	 	echo "<td>Budget</td>";
			 	 	echo "<td><input type=\"text\" size=\"32\" name=\"budget\" value=\"$kostenstelle->budget\"></td>";
			 	 	echo "</tr>";	
			 	 	echo '<tr>';
			 	 	echo "<td>Kostenstellen Nr.</td>";
			 	 	echo "<td><input type=\"text\" size=\"32\" name=\"kostenstelle_nr\" value=\"$kostenstelle->kostenstelle_nr\"></td>";
			 	 	echo "</tr>";	 	 	
			 	 	echo '<tr>';
			 	 	echo "<td>Aktiv?</td>";
			 	 	echo "<td><input type=\"checkbox\" name=\"aktiv\" value=\"aktiv\" $checked>";
			 	 	echo "</tr>";
			 	 	echo "<tr><td>&nbsp;</td><tr>"; 	 	
			  		echo '<tr>';
			  		echo '<td><a href=kostenstellenuebersicht.php> zurueck </a></td>';
			  		echo '<td><input type="submit" value="update"></td>';
			  		echo '</tr>';
			  		echo '</form>';
			  		echo '</table>';
			
				}
				else 
				{
					echo 'Kostenstelle wurde nicht gefunden!';
					echo "<a href=\"kostenstellenuebersicht.php\"> <br>zurück </a>";					
				}				
			}		
			else
			{
				echo 'Die Übergebene ID ist keine gültige.<br>';
				echo '<a href=kostenstellenuebersicht.php> zurueck </a>';
			}
		}
		else
		{
			echo '<h1>Kostenstelle - Neu</h1>';
			
			if(!$rechte->isberechtigt('wawi/kostenstelle',null, 'sui'))
				die('Sie haben keine Berechtigung zum Anlegen von Kostenstellen');
			
			//neue Anlegen
			$oe = new organisationseinheit(); 
			$oe->getAll(); 
			$oeinheiten= $oe->result; 

			echo "<form action=\"kostenstellenuebersicht.php?method=save\" method=\"post\">";
			echo '<table border=0>';
			echo '<tr>';
			echo " Organisationseinheit: <SELECT name='filter_oe_kurzbz'>";
			echo '<option value="">-- Keine Auswahl --</option>';

			foreach ($oeinheiten as $oei)
			{
				
				$selected='';
		
				if($oei->aktiv)
				{
					echo '<option value="'.$oei->oe_kurzbz.'" '.$selected.'>'.$oei->organisationseinheittyp_kurzbz.' '.$oei->bezeichnung.'</option>';
				}
				else 
				{
					echo '<option style="text-decoration:line-through;" value="'.$oei->oe_kurzbz.'" '.$selected.'>'.$oei->bezeichnung.'</option>';
				}	
			}
			echo "</SELECT>";
	 	 	echo '</tr>';
	 	 	echo '<tr>';
	 	 	echo "<td>Bezeichnung</td>";
	 	 	echo "<td><input type=\"text\" size=\"32\" maxlength =\"256\" name=\"bezeichnung\" value=\"\"></td>";
	 	 	echo "</tr>";
	 	 	echo '<tr>';
	 	 	echo "<td>Kurzbezeichnung</td>";
	 	 	echo "<td><input type=\"text\" size=\"32\" maxlength =\"32\" name=\"kurzbezeichnung\" value=\"\"></td>";
	 	 	echo "</tr>";
	 	 	echo '<tr>';
	 	 	echo "<td>Budget</td>";
	 	 	echo "<td><input type=\"text\" size=\"32\" maxlength =\"32\" name=\"budget\" value=\"\"></td>";
	 	 	echo '</tr>';
	 	 	echo '<tr>';
	 	 	echo "<td>Kostenstellen Nr.</td>";
	 	 	echo "<td><input type=\"text\" size=\"32\" maxlength =\"4\" name=\"kostenstelle_nr\" value=\"\"></td>";
	 	 	echo '</tr>';
	 	 	echo "<tr><td>&nbsp;</td><tr>"; 	 	
	  		echo '<tr>';
	  		echo '<td><a href=kostenstellenuebersicht.php> zurueck </a></td>';
	  		echo '<td><input type="submit" value="Anlegen"></td>';
	  		echo '</tr>';
	  		echo '</form>';
	  		echo '</table>';
		}
	}
	else if($_GET['method']=='delete')
	{
		$id = (isset($_GET['id'])?$_GET['id']:null);
		
		if(!$rechte->isberechtigt('wawi/kostenstelle',null, 'suid'))
			die('Sie haben keine Berechtigung zum Löschen von Kostenstellen');
			
		if($kostenstelle->delete($id))
		{
			echo 'Kostenstelle erfolgreich gelöscht. <br>';
			echo '<a href = "kostenstellenuebersicht.php> zurück </a>';
		}
		else
		{
			echo $kostenstelle->errormsg; 
			echo '<br><a href = "kostenstellenuebersicht.php> zurück </a>';
		}
	}
	else if($_GET['method']== "save")
	{
		//Daten in der DB speichern
		$kostenstelle = new wawi_kostenstelle();	
		$aktiv = '';
		$ausgabe ="Kostenstelle wurde erfolgreich upgedated!";
					
		if(isset($_GET['id']))
		{
			if(!$rechte->isberechtigt('wawi/kostenstelle',null, 'su',$_GET['id']))
				die('Sie haben keine Berechtigung zum Ändern der Kostenstelle');
			
			//Update
			$kostenstelle->load($_GET['id']);
			$kostenstelle->kostenstelle_id = $_GET['id'];
			$kostenstelle->ext_id = $_GET['id'];
			$kostenstelle->new = false;
			
			//Deaktivert
			if(($kostenstelle->aktiv == true) && (!isset($_POST['aktiv'])))
			{
				$kostenstelle->deaktiviertamum = date('Y-m-d H:i:s');
				$kostenstelle->deaktiviertvon = $user;
			}
			//Aktiviert
			if(isset($_POST['aktiv']))
			{
				$kostenstelle->deaktiviertamum = null;
				$kostenstelle->deaktiviertvon = null;
			}
			
			$kostenstelle->aktiv = isset($_POST['aktiv']);		
		}
		else 
		{
			if(!$rechte->isberechtigt('wawi/kostenstelle',null, 'suid'))
				die('Sie haben keine Berechtigung zum Anlegen von Kostenstellen');
			
			// neue Kostenstelle
			$kostenstelle->new = true;
			$kostenstelle->aktiv = true;
			$kostenstelle->insertamum = date('Y-m-d H:i:s');
			$kostenstelle->insertvon=$user; 
			$ausgabe = "Kostenstelle wurde erfolgreich erstellt!";
		}	

		$kostenstelle->oe_kurzbz = $_POST['filter_oe_kurzbz'];
		$kostenstelle->bezeichnung = $_POST['bezeichnung'];
		$kostenstelle->kurzbz = $_POST['kurzbezeichnung'];
		$kostenstelle->budget = $_POST['budget'];
		$kostenstelle->kostenstelle_nr = $_POST['kostenstelle_nr'];
		$kostenstelle->updateamum = date('Y-m-d H:i:s');
		$kostenstelle->updatevon = $user; 
		
			
		if(!$kostenstelle->save())
		{
			die('Fehler beim Speichern:<br>'.$kostenstelle->errormsg."<a href=\"kostenstellenuebersicht.php\"> <br>zurück </a>");
		}
		echo $ausgabe;
		echo "<a href=\"kostenstellenuebersicht.php\"> <br>zurück </a>";		
	}
	else if ($_GET['method']=="allocate")
	{
		if(!$rechte->isberechtigt('wawi/kostenstelle',null, 'su',$_GET['id']))
				die('Sie haben keine Berechtigung zum Ändern der Kostenstelle');
		
		echo '<h1>Kostenstelle - Konten zuordnen</h1>';
		$kostenstelle = new wawi_kostenstelle();
		$konto = new wawi_konto(); 
		
		$kontos = array();	// Array der Konten die einer Kostenstelle zugewiesen sind
		
		$konto->getAll(); 	
		$kontouebersicht = $konto->result; 
		$kostenstelle_id = isset($_GET['id'])?$_GET['id']:'';
		
		echo '<a href="kostenstellenuebersicht.php">zurück</a>';
		echo "<form name=\"save\" action=\"kostenstellenuebersicht.php?method=allocate&id=$kostenstelle_id\", method=\"POST\">";
		echo "<table border ='0' width='100%'>";

		if(isset($_POST['submit']))
		{
			//alle übergebenen POST Formular Daten
			$keys = array_keys($_POST);
			$active = array(); 
			$message = 'erfolgreich aktualisiert.<br><br>';
			foreach($keys as $key)
			{
				if(strstr($key,'checkbox_'))
				{
					//Ausgewählten Konten
					$konto_id=$_POST[$key];
					$active[] = $konto_id; 
					if(!$kostenstelle->check_konto_kostenstelle($kostenstelle_id, $konto_id))
					{
						if(!$kostenstelle->save_konto_kostenstelle($kostenstelle_id, $konto_id))
							$message = 'Es ist ein Fehler beim Speichern aufgetreten.<br><br>';					}
				}
			}
			if(!$kostenstelle->delete_konto_kostenstelle($kostenstelle_id, $active))
				$message = 'Es ist ein Fehler beim Speichern aufgetreten <br><br>';
			
			echo $message; 
		}
		
		//sucht nach allen Kontos der Kostenstelle und markiert diese
		foreach($kontouebersicht as $ko)
		{
			$checked = '';
			$kontos = $kostenstelle->get_konto_from_kostenstelle($kostenstelle_id);
			if(in_array($ko->konto_id,$kontos))
			{
				$checked='checked';
			}

			echo '<tr> <td>';
 			echo '<input type="checkbox" name="checkbox_'.$ko->konto_id.'" value='.$ko->konto_id." $checked>".$ko->beschreibung[1].'<br>';
 			echo '</td> </tr>';		
		}
		
		echo '<tr><td>&nbsp;</td></tr></table> <input name ="submit" type="submit" value="Speichern"></form>';
	}
	else if ($_GET['method']=="merge") 
	{			
		if(!$rechte->isberechtigt('wawi/kostenstelle',null, 'suid'))
				die('Sie haben keine Berechtigung zum Zusammenlegen von Kostenstellen');
		
		echo '<h1>Kostenstelle - Zusammenlegen</h1>';
		//Kostenstellen zusammenlegen
		$kostenstelle = new wawi_kostenstelle();
		
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
				if($kostenstelle->zusammenlegen($radio_1, $radio_2))
				{
					echo "erfolgreich zusammengelegt";
				}
				else 
				{
					echo $kostenstelle->errormsg; 
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
		
		echo "<form name=\"suche\" action=\"kostenstellenuebersicht.php?method=merge\", method=\"POST\">";
		echo "<table border ='0' width='100%'>";
		echo "<tr>";
		echo "<td width='45%%'><input name='filter1' type='text' value=\"$filter1\" size=\"64\" maxlength=\"64\" id ='suchen' onfocus=\"this.value='';\"></td>";
		echo "<td width='10%'><input type='submit' value=' suchen ' ></td>";
		echo "<td width='45%%'><input name='filter2' type='text' value=\"$filter2\" size=\"64\" maxlength=\"64\" id ='suchen' onfocus=\"this.value='';\"></td>";
		echo "</tr>";
		echo "<tr><td>&nbsp;</td></tr>";
		
		echo "</form>";
		echo '<br><a href=kostenstellenuebersicht.php>zurueck</a><br>';
		echo "</table>";
		//Tabellen anzeigen
		echo "<form name='form_table' action='kostenstellenuebersicht.php?method=merge' method='POST'>";
		echo "<table width='100%' border='0' cellspacing='0' cellpadding='0' id='myTable' class='tablesorter'>";
		echo "<tr>";	
		echo "<td valign='top'>Der wird gelöscht:";
	
		 //Tabelle 1
		echo "<table id='myTable' class='tablesorter'><thead> <tr>";
		
		echo "<th>Kostenstellen ID</th>";
		echo "<th>Organisationseinheit</th>";
		echo "<th>Bezeichnung</th>";
		echo "<th>Kurzbezeichnung</th>";
		echo "<th>Kostenstellennummer</th>";
		echo "<th>Aktiv</th>";
		echo "<th>&nbsp;</th></tr></thead><tbody>";	
	
		$kostenstelle  = new wawi_kostenstelle();
		$kostenstelle->getAll($filter1);
		$i=0;
	
		
		foreach($kostenstelle->result as $row)
		{
			//Zeilen der Tabelle ausgeben
			echo '<tr>';
			echo "<td>$row->kostenstelle_id</td>";
			echo '<td>'.$row->oe_kurzbz.'</td>';
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->kostenstelle_nr.'</td>';
			echo '<td>'.$aktiv=($row->aktiv)?'ja':'nein'.'</td>';
			echo "<td><input type='radio' name='radio_1' value='$row->kostenstelle_id' ";
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
		echo "<th>Kostenstellen ID</th>";
		echo "<th>Organisationseinheit</th>";
		echo "<th>Bezeichnung</th>";
		echo "<th>Kurzbezeichnung</th>";
		echo "<th>Kostenstellennummer</th>";
		echo "<th>Aktiv</th>";
		echo "<th>&nbsp;</th></tr></thead><tbody>";	
	
	
		$kostenstelle  = new wawi_kostenstelle();
		$kostenstelle->getAll($filter2);
		$i=0;
		foreach($kostenstelle->result as $row)
		{
			echo '<tr>';
			echo "<td><input type='radio' name='radio_2' value='$row->kostenstelle_id' ";
			echo '<td>'.$row->kostenstelle_id.'</td>';
			echo '<td>'.$row->oe_kurzbz.'</td>';
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->kostenstelle_nr.'</td>';
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
	echo '<h1>Kostenstelle - &Uuml;bersicht</h1>';
	if(!$rechte->isberechtigt('wawi/kostenstelle',null, 's'))
		die('Sie haben keine Berechtigung zum Anzeigen der Kostenstellen');
			
	if($kostenstelle->getAll())
	{
		//echo '<a href="kostenstellenuebersicht.php?method=update">neue Kostenstelle anlegen </a><br>';
		//echo '<a href="kostenstellenuebersicht.php?method=merge">Konten zusammenlegen </a><br><br>';
		
		echo '<table id="myTable" class="tablesorter"> <thead>';
		
		echo '<tr>
				<th></th>
				<th>Kostenstelle_id</th>
				<th>Kostenstelle_Nr</th>
				<th>Bezeichnung</th>
				<th>Kurzbezeichnung</th>
				<th>Budget</th>
				<th>Organisationseinheit</th>
				<th>aktiv</th>
			  </tr></thead><tbody>';
	
		foreach($kostenstelle->result as $row)
		{
			//Zeilen der Tabelle ausgeben
			echo '<tr>';
			echo "<td nowrap> <a href=\"kostenstellenuebersicht.php?method=allocate&id=$row->kostenstelle_id\" title=\"Konten zuordnen\"><img src=\"../skin/images/addKonto.png\"></a> <a href= \"kostenstellenuebersicht.php?method=update&id=$row->kostenstelle_id\" title=\"Bearbeiten\"> <img src=\"../skin/images/edit.gif\"> </a><a href=\"kostenstellenuebersicht.php?method=delete&id=$row->kostenstelle_id\" onclick='return conf_del()' title='Löschen'> <img src=\"../skin/images/delete.gif\"></a>";
			echo '<td>'.$row->kostenstelle_id.'</td>';
			echo '<td>'.$row->kostenstelle_nr.'</td>';
			echo '<td>'.$row->bezeichnung.'</td>';
			echo '<td>'.$row->kurzbz.'</td>';
			echo '<td>'.$row->budget.'</td>';
			echo '<td>'.$row->oe_kurzbz.'</td>';
			echo '<td>'.$aktiv=($row->aktiv)?'ja':'nein'.'</td>';
			echo '</tr>';
			
		}
		echo '</tbody></table>';	
	}
}



?>