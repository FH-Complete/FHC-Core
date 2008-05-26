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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/datum.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur DB');

if(isset($_GET['searchstr']))
	$searchstr = $_GET['searchstr'];
else 
	$searchstr = '';
	
$datum_obj = new datum();

echo '
<html>
<head>
<title>Suchergebnis</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h2>Personensuche</h2>';
	
$stg = new studiengang($conn);
$stg->getAll('typ, kurzbz', false);

$stg_arr = array();
foreach ($stg->result as $row)
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;

echo '
	<form name="search" method="GET">
  		Bitte Suchbegriff eingeben: 
  		<input type="text" name="searchstr" size="30" value="'.htmlentities($searchstr).'">
  		<input type="submit" value="Suchen">
  	</form>';

if($searchstr!='')
{
	$qry = "SELECT person_id FROM public.tbl_person WHERE person_id in(
			SELECT distinct person_id FROM public.tbl_person LEFT JOIN public.tbl_benutzer USING(person_id) WHERE
			nachname ~* '".addslashes($searchstr)."' OR 
			vorname ~* '".addslashes($searchstr)."' OR
			alias ~* '".addslashes($searchstr)."' OR
			nachname || ' ' || vorname = '".addslashes($searchstr)."' OR 
			vorname || ' ' || nachname = '".addslashes($searchstr)."' OR 
			uid ~* '".addslashes($searchstr)."'
			) ORDER BY nachname, vorname;";
	
	
	if($result = pg_query($conn, $qry))
	{		
		// LDAP Verbindung
		$ds=ldap_connect(LDAP_SERVER);
		
		if ($ds)
		{
		    $r=ldap_bind($ds);     // this is an "anonymous" bind, typically
		}
		else
		    echo "<h4>Unable to connect to LDAP server</h4>";
		echo pg_num_rows($result).' Person(en) gefunden<br><br>';
		echo '<table>';
		echo '<tr class="liste" align="center">';
		echo "<td colspan='5'><b>Person</b></td>";
		echo "<td colspan='4'><b>Benutzer</b></td>";
		echo "<td colspan='4'><b>Mitarbeiter</b></td>";
		echo "<td colspan='4'><b>Student</b></td>";
		echo '</tr>';
		echo '<tr class="liste" align="center">';
		echo "<td><b>Nachname</b></td>";
		echo "<td><b>Vorname</b></td>";
		//echo "<td><b>SVNR</b></td>";
		echo "<td><b>Gebdatum</b></td>";
		echo "<td><b>updateAmUm</b></td>";
		echo "<td><b>updateVon</b></td>";
		echo "<td><b>Status</b></td>";
		echo "<td><b>UID</b></td>";
		echo "<td><b>Aktiv</b></td>";
		echo "<td><b>LDAP</b></td>";
		echo "<td><b>Telefon</b></td>";
		echo "<td><b>Ort</b></td>";
		echo "<td><b>updateAmUm</b></td>";
		echo "<td><b>updateVon</b></td>";
		echo "<td><b>Stg</b></td>";
		echo "<td><b>Gruppe</b></td>";
		echo "<td><b>updateAmUm</b></td>";
		echo "<td><b>updateVon</b></td>";
		echo '</tr>';
		
		while($row = pg_fetch_object($result))
		{
			$qry = "SELECT * FROM public.tbl_person WHERE person_id='$row->person_id'";
			if($result_person = pg_query($conn, $qry))
			{
				if($row_person = pg_fetch_object($result_person))
				{
					echo '<tr class="liste1">';
					echo "<td><a href='personen_details.php?person_id=$row_person->person_id'>$row_person->nachname</a></td>";
					echo "<td>$row_person->vorname</td>";
					//echo "<td>$row_person->svnr</td>";
					echo "<td>".($row_person->gebdatum!=''?$datum_obj->convertISODate($row_person->gebdatum):'')."</td>";
					echo "<td>".($row_person->updateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_person->updateamum)):'')."</td>";
					echo "<td>$row_person->updatevon</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo '</tr>';
					
					$content = '';
					$qry = "SELECT 
									*, tbl_benutzer.updateamum as bnupdateamum, tbl_benutzer.updatevon as bnupdatevon,
									tbl_mitarbeiter.updateamum as mupdateamum, tbl_mitarbeiter.updatevon as mupdatevon
							FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer on(uid=mitarbeiter_uid) 
							WHERE person_id='$row->person_id'";
					if($result_mitarbeiter = pg_query($conn, $qry))
					{
						if(pg_num_rows($result_mitarbeiter)>0)
						{
						
							while($row_mitarbeiter = pg_fetch_object($result_mitarbeiter))
							{
								$content.= '<tr >';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= "<td>Mitarbeiter</td>";
								$content.= "<td><a href='personen_details.php?uid=$row_mitarbeiter->uid'>$row_mitarbeiter->uid</a></td>";
								$content.= "<td>".($row_mitarbeiter->aktiv=='t'?'Ja':'Nein')."</td>";
								
								$content.= "<td>";								
								$sr=ldap_search($ds, LDAP_BASE_DN, "uid=".$row_mitarbeiter->uid);
								$info = ldap_get_entries($ds, $sr);
								if ($info["count"]==0)
									$content.="Nein";
								else 
									$content.="Ja";
								$content.= "</td>";
								//$content.= "<td>".($row_mitarbeiter->bnupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_mitarbeiter->bnupdateamum)):'')."</td>";
								//$content.= "<td>$row_mitarbeiter->bnupdatevon</td>";
								
								$content.= "<td>$row_mitarbeiter->telefonklappe</td>";
								$content.= "<td>$row_mitarbeiter->ort_kurzbz</td>";
								$content.= "<td>".($row_mitarbeiter->mupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_mitarbeiter->mupdateamum)):'')."</td>";
								$content.= "<td>$row_mitarbeiter->mupdatevon</td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= '</tr>';
							}
						}
					}
					
					$qry = "SELECT *, tbl_benutzer.updateamum as bnupdateamum, tbl_benutzer.updatevon as bnupdatevon,
									tbl_student.updateamum as supdateamum, tbl_student.updatevon as supdatevon
							FROM public.tbl_student JOIN public.tbl_benutzer ON(student_uid=uid) 
							WHERE person_id='$row->person_id'";
					if($result_student = pg_query($conn, $qry))
					{
						if(pg_num_rows($result_student))
						{
								
							while($row_student = pg_fetch_object($result_student))
							{
								$student = new prestudent($conn);
								$student->getLastStatus($row_student->prestudent_id);
								
								$content.= '<tr>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= '<td></td>';
								$content.= "<td>$student->rolle_kurzbz</td>";
								$content.= "<td><a href='personen_details.php?uid=$row_student->uid'>$row_student->uid</a></td>";
								$content.= "<td>".($row_student->aktiv=='t'?'Ja':'Nein')."</td>";
								
								$content.= "<td>";
								$sr=ldap_search($ds, LDAP_BASE_DN, "uid=".$row_student->uid);
								$info = ldap_get_entries($ds, $sr);
								if ($info["count"]==0)
									$content.="Nein";
								else 
									$content.="Ja";
								$content.= "</td>";
								//$content.= "<td>".($row_student->bnupdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_student->bnupdateamum)):'')."</td>";
								//$content.= "<td>$row_student->bnupdatevon</td>";
								
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td></td>";
								$content.= "<td>".$stg_arr[$row_student->studiengang_kz]."</td>";
								$content.= "<td>$row_student->semester$row_student->verband$row_student->gruppe</td>";
								$content.= "<td>".($row_student->supdateamum!=''?date('d.m.Y H:i:s', $datum_obj->mktime_fromtimestamp($row_student->supdateamum)):'')."</td>";
								$content.= "<td>$row_student->supdatevon</td>";
								$content.= '</tr>';
							}
						}
					}
										
					echo $content;
				}
			}
		}
		echo '</table>';
		ldap_close($ds);
	}
	
}
?>
</body>
</html>