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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
    require_once('../../include/berechtigung.class.php');
    require_once('../../include/benutzerberechtigung.class.php');
    
    $user = get_uid();
    
    $rechte = new benutzerberechtigung();
    $rechte->getBerechtigungen($user);
    
    if(!$rechte->isBerechtigt('basis/berechtigung'))
    	die('Sie habe keine Rechte um diese Seite anzuzeigen');
?>
<html>
<head>
<title>Berechtigungen Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script>
<script language="Javascript">
$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"],
					headers: {3:{sorter:false}}
				}); 
			$("#t2").tablesorter(
					{
						sortList: [[0,0]],
						widgets: ["zebra"],
						headers: {2:{sorter:false}}
					}); 
		});
function confdel()
{
	var value=prompt('Achtung! Sie sind dabei eine Rolle zu löschen. Die Zuordnungen gehen dadurch verloren! Um diese Rolle wirklich zu Löschen tippen Sie "LÖSCHEN" in das untenstehende Feld.');
	
	if(value=='LÖSCHEN')
		return true;
	else
		return false;
}
</script>
</head>

<body class="background_main">
<h2>Berechtigung - Rolle - &Uuml;bersicht</h2>

<?php 
if(isset($_GET['rolle_kurzbz']))
{
	$rolle_kurzbz = $_GET['rolle_kurzbz'];
	$berechtigung_kurzbz = (isset($_GET['berechtigung_kurzbz'])?$_GET['berechtigung_kurzbz']:'');
	$art = (isset($_GET['art'])?$_GET['art']:'');
	
	if(isset($_GET['save']))
	{
		if($rolle_kurzbz!='' && $berechtigung_kurzbz!='' && $art!='')
		{
			$berechtigung = new berechtigung();
			$berechtigung->rolle_kurzbz = $rolle_kurzbz;
			$berechtigung->berechtigung_kurzbz = $berechtigung_kurzbz;
			$berechtigung->art = $art;
			
			if($berechtigung->saveRolleBerechtigung())
			{
				echo '<b>Zuteilung gespeichert</b>';
			}
			else 
			{
				echo '<b>Fehler beim Speichern der Zuteilung:',$berechtigung->errormsg;
			}
		}
	}
	
	if(isset($_GET['delete']))
	{
		$berechtigung = new berechtigung();
		if(!$berechtigung->deleteRolleBerechtigung($rolle_kurzbz, $berechtigung_kurzbz))
			echo '<b>Fehler beim Löschen: </b>',$berechtigung->errormsg;
		else 
			echo '<b>Berechtigung gelöscht!</b>';
	}
	
	echo '<br><a href="'.$_SERVER['PHP_SELF'].'">Zurück</a>';
	echo '<h3>RolleBerechtigung "',$rolle_kurzbz,'":</h3>';
	
	$berechtigung = new berechtigung();
	$berechtigung->getBerechtigungen();
	
	echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
	echo '<input type="hidden" name="rolle_kurzbz" value="'.$rolle_kurzbz.'">';
	echo '<SELECT name="berechtigung_kurzbz">';
	
	$berechtigungen = new berechtigung();
	$berechtigungen->getRolleBerechtigung($rolle_kurzbz);
	$berechtigungen_arr = array();
	foreach ($berechtigungen->result as $row)
	{
		$berechtigungen_arr[] = $row->berechtigung_kurzbz;
	}
	foreach ($berechtigung->result as $row)
	{
		echo '<OPTION value="',$row->berechtigung_kurzbz,'" '.(array_search($row->berechtigung_kurzbz,$berechtigungen_arr)!==false?'disabled':'').'>',$row->berechtigung_kurzbz,'</OPTION>';
	}
	echo '</SELECT>';
	echo '&nbsp;<input type="text" value="suid" size="4" name="art">';
	echo '&nbsp;<input type="submit" name="save" value="Hinzufügen">';
	echo '</form>';
	
	
	echo '<table id="t1" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Art</th>
					<th>Beschreibung</th>
					<th></th>
				</tr>
			</thead>
			<tbody>';
	
	//$berechtigungen = new berechtigung();
	//$berechtigungen->getRolleBerechtigung($rolle_kurzbz);
	
	foreach($berechtigungen->result as $rolle)
	{
		echo '<tr>';
		echo '<td>',$rolle->berechtigung_kurzbz,'</td>';
		echo '<td>',$rolle->art,'</td>';
		echo '<td>',$rolle->beschreibung,'</td>';		
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?delete=1&rolle_kurzbz='.$rolle->rolle_kurzbz.'&berechtigung_kurzbz='.$rolle->berechtigung_kurzbz.'">entfernen</a></td>';
		echo '</td>';	
	}
	echo '</tbody></table>';
	
}
else 
{
	if(isset($_POST['save']))
	{
		if(isset($_POST['kurzbz']) && isset($_POST['beschreibung']))
		{
			$berechtigung = new berechtigung();
			$berechtigung->rolle_kurzbz = $_POST['kurzbz'];
			$berechtigung->beschreibung = $_POST['beschreibung'];
			$berechtigung->new = true;
			
			if($berechtigung->saveRolle())
			{
				echo 'Daten wurden gespeichert';
			}
			else 
			{
				echo 'Fehler beim Speichern:'.$berechtigung->errormsg;
			}
		}
		else 
		{
			echo 'Zum Speichern der Daten muss die kurzbz und die Beschreibung angegeben werden';
		}
	}
	
	if(isset($_GET['delete']) && isset($_GET['kurzbz']))
	{
		$berechtigung = new berechtigung();
		if($berechtigung->deleteRolle($_GET['kurzbz']))
			echo 'Rolle wurde entfernt';
		else 
			echo 'Fehler beim Löschen:'.$berechtigung->errormsg;
	}
	
	//Tabelle mit Rollen anzeigen
	$berechtigung = new berechtigung();
	$berechtigung->getRollen();
	
	echo '<h3>Rollen:</h3>';
	echo '<table id="t2" class="tablesorter">
			<thead>
				<tr>
					<th>Kurzbz</th>
					<th>Beschreibung</th>
					<th colspan="2">Aktion</th></tr>
			</thead>
			<tbody>';
	
	foreach($berechtigung->result as $rolle)
	{
		echo '<tr>';
		echo '<td>',$rolle->rolle_kurzbz,'</td>';
		echo '<td>',$rolle->beschreibung,'</td>';
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?rolle_kurzbz='.$rolle->rolle_kurzbz.'">Berechtigungen zuordnen</a></td>';
		echo '<td><a href="'.$_SERVER['PHP_SELF'].'?kurzbz='.$rolle->rolle_kurzbz.'&delete=true" onclick="return confdel()">Rolle löschen</a></td>';
		echo '</td>';	
	}
	echo '</tbody></table>';
	
	echo '<br><form method="POST">
			Kurzbz: <input type="text" name="kurzbz" value="" />
			Beschreibung: <input type="text" name="beschreibung" value="" />
			&nbsp;<input type="submit" name="save" value="Anlegen"/>
		</form>';
}
?>

</body>
</html>
