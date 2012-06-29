<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/fotostatus.class.php');
require_once('../../include/datum.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet"  href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<script type="text/javascript">
		$(document).ready(function() 
		{ 
			$("#myTable").tablesorter(
			{
				sortList: [[1,0]],
				widgets: ["zebra"]
			}); 
		}); 
	</script> 
	
	<title>FH-Complete</title>
</head>
<body>
<h2>FH Ausweis</h2>
';


$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('basis/fhausweis') && !$rechte->isBerechtigt('assistenz'))
	die('Sie haben keine Berechtigung für diese Seite');
	
$db = new basis_db();
$filter = (isset($_POST['filter'])?$_POST['filter']:'');
$person_id = (isset($_GET['person_id'])?$_GET['person_id']:'');
$datum_obj = new datum();

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
Suche: <input type="text" name="filter" value="'.$db->convert_html_chars($filter).'"/>
<input type="submit" name="search" value="Suchen" />
</form>';

if(isset($_POST['search']))
{
	$person = new person();
	$person->getTab($filter);
	
	if(count($person->personen)==1)
	{
		//wenn nur ein Ergebnis zurueck kommt - gleich anzeigen
		$person_id=$person->personen[0]->person_id;
	}
	else
	{
		echo '<table class="tablesorter" id="myTable">
		<thead>
			<th>PersonID</th>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Aktive Accounts</th>
		</thead>
		<tbody>';
		foreach($person->personen as $row)
		{
			$benutzer = new benutzer();
			if(!$benutzer->getBenutzerFromPerson($row->person_id))
				echo $benutzer->errormsg;
			echo '<tr>';
			echo '<td><a href="'.$_SERVER['PHP_SELF'].'?person_id='.$row->person_id.'">Details</a></td>';
			echo '<td>'.$row->person_id.'</td>';
			echo '<td>'.$row->vorname.'</td>';
			echo '<td>'.$row->nachname.'</td>';
			echo '<td>';
			foreach($benutzer->result as $row_account)
			{
				echo $row_account->uid.' ';
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}
if($person_id!='')
{
	echo '<br><hr>';
	$person = new person();
	$person->load($person_id);
	$fs = new fotostatus();
	$fs->getLastFotoStatus($person_id);
	
	echo '<table>
		<tr>
			<td>
			<img src="../../content/bild.php?src=person&person_id='.$person_id.'" height="100px" width="75px">
			</td>
			<td>
				Vorname: '.$person->vorname.'
				<br>Nachname: '.$person->nachname.'
				<br>Geburtsdatum: '.$datum_obj->formatDatum($person->gebdatum,'d.m.Y').'
			</td>
		</tr>
		</table>';
	
	echo '<br>Aktueller Fotostatus: '.$fs->fotostatus_kurzbz .' ( '.$datum_obj->formatDatum($fs->datum,'d.m.Y').' )';
	
	$benutzer = new benutzer();
	if(!$benutzer->getBenutzerFromPerson($person->person_id))
		echo $benutzer->errormsg;
	echo '<br><br><u>Accounts:</u><br>';	
	foreach($benutzer->result as $row_account)
	{
		echo '<br><br><b>'.$row_account->uid.'</b>';
		echo '<br>Neue Karte bereits gedruckt:';
		$qry = "
		SELECT 
			tbl_betriebsmittelperson.ausgegebenam, tbl_betriebsmittel.nummer
		FROM 
			wawi.tbl_betriebsmittel 
			JOIN wawi.tbl_betriebsmittelperson USING(betriebsmittel_id) 
		WHERE
			tbl_betriebsmittel.betriebsmitteltyp='Zutrittskarte'
			AND tbl_betriebsmittelperson.uid=".$db->db_add_param($row_account->uid)."
			AND nummer2 is not null";
		$ausgegeben='';
		$nummer='';
		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				$ausgegeben = $row->ausgegebenam;
				$nummer = $row->nummer;
			}
		}
		if($db->db_num_rows($result)>0)
			echo 'Ja';
		else
			echo 'Nein';
			
		echo '<br>Neue Karte bereits ausgegeben: ';
		if($ausgegeben=='')
			echo 'Nein';
		else
			echo 'Ja ( '.$datum_obj->formatDatum($ausgegeben,'d.m.Y').' )';
			
		echo '<br>Neue Karte bereits aktiv (im LDAP): ';
		if($nummer!='')
		{
			if($uidldap = getUidFromCardNumber($nummer))
			{
				if($uidldap==$row_account->uid)
				{
					echo 'Ja';
				}
				else
				{
					echo 'Ja, aber bei UID '.$uidldap;
				}
			}
			else
			{
				echo 'Nein';
			}		
		}
		else
			echo 'Nein';
	}
	 
}
echo '</body>
</html>';
?>