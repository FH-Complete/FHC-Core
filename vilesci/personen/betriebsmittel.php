<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/betriebsmittelperson.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$user = get_uid();

//Berechtigung pruefen
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('admin',0) && !$rechte->isBerechtigt('support'))
	die('Sie haben keine Berechtigung fuer diese Seite');
   
echo '<html>
	<head>
		<title>Betriebsmittel</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">
		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich loeschen");
		}
		</script>
	</head>
	<body class="Background_main">
	<h2>Betriebsmittel</h2>
	';

$search = (isset($_REQUEST['search'])?$_REQUEST['search']:'');

//Loeschen eines Datensatzes
if(isset($_GET['type']) && $_GET['type']=='delete')
{
	if(isset($_GET['betriebsmittel_id']) && is_numeric($_GET['betriebsmittel_id'])
	   && isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	{
		$bmp = new betriebsmittelperson($conn);
		if($bmp->delete($_GET['betriebsmittel_id'], $_GET['person_id']))
			echo '<b>Datensatz wurde geloescht</b>';
		else 
			echo '<b>Fehler beim Loeschen des Datensatzes: '.$bmp->errormsg.'</b>';
	}
	else 
	{
		die('Fehlerhafte Parameteruebergabe');
	}
}

echo '<table width="100%"><tr><td>
	  <form method="POST"  enctype="multipart/form-data" accept-charset="UTF-8"  action="'. $_SERVER['PHP_SELF'].'">
		Nummer/Uid: <input type="text" name="search" value="'.$search.'">&nbsp;
		<input type="submit" value="Suchen" name="suche">
	  </form></td><td align="right"><a href="betriebsmitteldetail.php" target="detail">NEU</a></td></tr></table><br><br>';

if($search!='')
{
	//Suche in Datenbank
	$qry = "SELECT distinct tbl_betriebsmittelperson.*, tbl_betriebsmittel.*, tbl_person.*, tbl_betriebsmittelperson.updateamum as updateamum, tbl_betriebsmittelperson.updatevon as updatevon FROM 
			public.tbl_betriebsmittelperson 
			JOIN public.tbl_betriebsmittel USING(betriebsmittel_id) 
			JOIN public.tbl_person USING(person_id) 
			LEFT JOIN public.tbl_benutzer USING(person_id) 
			WHERE nummer='".addslashes($search)."' OR uid='".addslashes($search)."'";
	if($result = pg_query($conn, $qry))
	{
		echo "<b>Datenbank Result</b><br><table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
				<thead>
				<tr class='liste'>";
		echo "<th class='table-sortable:default'>Nummer</th>
		  <th class='table-sortable:default'>Typ</th>
		  <th class='table-sortable:default'>Kaution</th>
		  <th class='table-sortable:default'>Ausgegeben</th>
		  <th class='table-sortable:default'>Retour</th>
		  <th class='table-sortable:default'>Vorname</th>
		  <th class='table-sortable:default'>Nachname</th>
		  <th class='table-sortable:default'>UID</th>
		  <th class='table-sortable:default'>UpdateAmUm</th>
		  <th class='table-sortable:default'>UpdateVon</th>
		  <th class='table-sortable:default' colspan='2'>Aktion</th>";
		echo '</tr></thead>';
		echo '<tbody>';
		
		while($row = pg_fetch_object($result))
		{
			echo "<tr>";
			echo "<td>$row->nummer</td>";
			echo "<td>$row->betriebsmitteltyp</td>";
			echo "<td>$row->kaution</td>";
			echo "<td>$row->ausgegebenam</td>";
			echo "<td>$row->retouram</td>";
			echo "<td>$row->vorname</td>";
			echo "<td>$row->nachname</td>";
			echo "<td>";
			//Alle UIDs zu dieser Person suchen
			$qry_uid = "SELECT uid FROM public.tbl_benutzer WHERE person_id='$row->person_id'";
			if($result_uid = pg_query($conn, $qry_uid))
			{
				while($row_uid = pg_fetch_object($result_uid))
				{
					echo "<a href='personen_details.php?uid=$row_uid->uid' target='_top'>$row_uid->uid</a><br>";
				}
			}
			echo "</td>";
			echo "<td>$row->updateamum</td>";
			echo "<td>$row->updatevon</td>";
			echo "<td><a href='betriebsmitteldetail.php?betriebsmittel_id=$row->betriebsmittel_id&person_id=$row->person_id' target='detail'>edit</a></td>";
			echo "<td><a href='".$_SERVER['PHP_SELF']."?search=$search&type=delete&betriebsmittel_id=$row->betriebsmittel_id&person_id=$row->person_id' onclick='return confdel()'>delete</a></td>";
			echo "</tr>";
		}
		
		echo '</tbody>';
		echo '</table>';
	}
	
	//Suche im LDAP
	
	// LDAP Verbindung herstellen
	$ds=ldap_connect(LDAP_SERVER);
	
	if ($ds)
		$r=ldap_bind($ds);     // this is an "anonymous" bind, typically
	else
	    echo "<h4>Unable to connect to LDAP server</h4>";
	
	if(is_numeric($search))
    	$sr=ldap_search($ds, "ou=People, dc=technikum-wien, dc=at", "departmentNumber=".$search);
    else 
    	$sr=ldap_search($ds, "ou=People, dc=technikum-wien, dc=at", "uid=".$search);
    
	$info = ldap_get_entries($ds, $sr);

	echo "<br><b>LDAP Result</b><br><table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
				<thead>
				<tr class='liste'>";
	echo "<th class='table-sortable:default'>Kartennummer</th>
	  <th class='table-sortable:default'>UID</th>
	  <th class='table-sortable:default'>Name</th>";
	echo '</tr></thead>';
	echo '<tbody>';
	//Kartennummer
	echo '<tr><td>'.(isset($info[0]['departmentnumber'][0])?$info[0]['departmentnumber'][0]:'').'</td>';
	//UID
	echo '<td>'.(isset($info[0]['uid'][0])?$info[0]['uid'][0]:'').'</td>';
	//Gesamter Name
	echo '<td>'.(isset($info[0]['cn'][0])?$info[0]['cn'][0]:'').'</td></tr>';	
	echo '</tbody></table>';
}
?>
</body>
</html>