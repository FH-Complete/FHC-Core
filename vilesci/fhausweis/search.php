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
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
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
			<th>Aktion</th>
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
	
	echo '<br>Aktueller Fotostatus: ';
	if($fs->fotostatus_kurzbz=='')
		echo 'ungeprüft';
	else
		echo $fs->fotostatus_kurzbz .' ( '.$datum_obj->formatDatum($fs->datum,'d.m.Y').' )';
	echo '<form action="bildpruefung.php" method="POST">';
	echo '<input type="hidden" name="person_id" value="'.$db->convert_html_chars($person->person_id).'" />';
	echo '<input type="submit" name="refresh" value="Bildcheck" /> ';

	$benutzer = new benutzer();
	if(!$benutzer->getBenutzerFromPerson($person->person_id))
		echo $benutzer->errormsg;
	echo '<br><br><u>Accounts:</u><br>';	
	foreach($benutzer->result as $row_account)
	{
		echo '<br><b>'.$row_account->uid.'</b>';
		echo '&nbsp;-&nbsp;<a href="../../content/zutrittskarte.php?data='.$db->convert_html_chars($row_account->uid).'" target="_blank">FH Ausweis erstellen</a>';
		echo '<br>';
		$qry = "
		SELECT 
			tbl_betriebsmittelperson.ausgegebenam, tbl_betriebsmittelperson.retouram, 
			tbl_betriebsmittel.nummer, tbl_betriebsmittel.nummer2
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
			if($db->db_num_rows($result))
			{
				while($row = $db->db_fetch_object($result))
				{
					echo '<br>FH-Ausweis zugeteilt <span style="color: gray">('.$row->nummer.' / '.$row->nummer2.')</span><br>';
					if($row->ausgegebenam!='')
						echo ' Ausgegeben am '.$datum_obj->formatDatum($row->ausgegebenam,'d.m.Y');
					else
						echo ' Noch nicht ausgegeben';
						
					if($row->retouram!='')
						echo ' - Zurückgegeben am '.$datum_obj->formatDatum($row->retouram,'d.m.Y');
						 
					echo '<br>FH-Ausweis im LDAP:';
					if($uidldap = getUidFromCardNumber($row->nummer))
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
					echo '<br>';
				}
			}
			else
				echo 'Kein FH-Ausweis gedruckt oder zugeteilt';
		}
		echo '<br>';
	}
	 
}
echo '</body>
</html>';
?>