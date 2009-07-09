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
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
	require_once('../../include/functions.inc.php');

	// Variablen setzen
	if (isset($_GET['uid']))
		$uid=$_GET['uid'];
	if (isset($_GET['aktiv']))
		$aktiv=$_GET['aktiv'];
	else
		$aktiv='f';

	// Benutzer daktivieren
	if ($aktiv=='t')
		if (isset($uid))
		{
			$qry = "UPDATE tbl_benutzer SET aktiv=FALSE WHERE uid='$uid';";
			if(!$result = $db->db_query($qry))
				die ($db->db_last_error());
		}
		else
			die('UID ist nicht gesetzt.');

	// LDAP Verbindung
	$ds=ldap_connect(LDAP_SERVER);  // must be a valid LDAP server!
	//echo "connect result is " . $ds . "<br />";
	if ($ds)
	{
	    //echo "Binding ...";
	  if (!$r=ldap_bind($ds))     // this is an "anonymous" bind, typically
		    die("<h4>Unable to connect to LDAP server</h4>");
	}
	else
	    die("<h4>Unable to connect to LDAP server</h4>");

?>
<html>
<head>
	<title>LDAP-Check</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
	<h1>LDAP-Check (Benutzer)</h1>
	<br>
	Benutzer werden geladen und die UID im LDAP geprueft.
	<BR><BR>
	Folgende Benutzer sind aktiv und haben keinen LDAP-Eintrag:<BR>

	<?php
	$qry = "SELECT uid, titelpre,titelpost, nachname, vorname, vornamen, tbl_benutzer.aktiv FROM tbl_benutzer JOIN tbl_person USING (person_id) WHERE tbl_benutzer.aktiv AND uid NOT LIKE '\\\\_%' ";

	if($result = $db->db_query($qry))
	{
		echo $db->db_num_rows($result);
		echo "<table class='liste'>";
		echo "<tr class='liste'><th>UID</th><th>Titel</th><th>Nachname</th><th>Vorname</th><th>Vornamen</th><th>TitelPost</th><th>Aktiv</th><th>eMail</th><th colspan='3'>Aktion</th></tr>";

		$i=0;
		while ($row=$db->db_fetch_object($result))
		{

 			// Search uid entry
		    $sr=ldap_search($ds, LDAP_BASE_DN, "uid=".$row->uid);
			//echo "Search result is " . $sr . "<br />";
			//echo "Number of entires returned is " . ldap_count_entries($ds, $sr) . "<br />";
			//echo "Getting entries ...<p>";
			$info = ldap_get_entries($ds, $sr);
			//echo "Data for " . $info["count"] . " items returned:<p>";
		    /*for ($i=0; $i<$info["count"]; $i++)
		    {
				echo "dn is: " . $info[$i]["dn"] . "<br />";
			    echo "first cn entry is: " . $info[$i]["cn"][0] . "<br />";
			    echo "first email entry is: " . $info[$i]["mail"][0] . "<br /><hr />";
			}*/

			if ($info["count"]==0)
			{
				echo "<tr class='liste". ($i%2) ."'>";
				echo "<td nowrap>".$row->uid."</td>";

				echo "<td nowrap>".$row->titelpre."</td>";
				echo "<td nowrap>".$row->nachname."</td>";
				echo "<td nowrap>".$row->vorname."</td>";
				echo "<td nowrap>".$row->vornamen."</td>";
				echo "<td nowrap>".$row->titelpost."</td>";
				echo "<td nowrap><a href='?uid=".$row->uid."&aktiv=".$row->aktiv."'><img src='../../skin/images/".($row->aktiv=='t'?'true':'false').".gif'></a></td>";

				$email=$row->uid.'@technikum-wien.at';
				echo "<td nowrap><a href='mailto:$email'>$email</a></td>";
				//echo "<td nowrap class='button'><a href='lektor_edit.php?id=".$row->uid."'>Edit</a></td>";
				//echo "</td>";
				//echo "<td nowrap class='button'><a href='lektor_uebersicht.php?del=1&uid=".$row->uid."' onClick='javascript: return confdel();'>Delete</a></td>";
				echo "</tr>";
				$i++;
				flush();
			}
			if ($i>19)
				break;
		}
		echo "</table>";
		echo $i.' Ergebnisse<BR>';
	}
	else
		echo "Fehler beim laden der Mitarbeiter: ".$db->db_last_error();

	echo "Closing connection";
	ldap_close($ds);

?>

</body>
</html>
