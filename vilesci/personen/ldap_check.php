<?php
	require_once('../config.inc.php');
	require_once('../../include/functions.inc.php');

	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

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
			if(!$result = pg_query($conn, $qry))
				die (pg_last_error($conn));
		}
		else
			die('UID ist nicht gesetzt.');

	// LDAP Verbindung
	$ds=ldap_connect("ldap.technikum-wien.at");  // must be a valid LDAP server!
	//echo "connect result is " . $ds . "<br />";
	if ($ds)
	{
	    //echo "Binding ...";
	    $r=ldap_bind($ds);     // this is an "anonymous" bind, typically
	    // read-only access
	    //echo "Bind result is " . $r . "<br />";
	}
	else
	    echo "<h4>Unable to connect to LDAP server</h4>";

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

	if($result = pg_query($conn, $qry))
	{
		echo pg_num_rows($result);
		echo "<table class='liste'>";
		echo "<tr class='liste'><th>UID</th><th>Titel</th><th>Nachname</th><th>Vorname</th><th>Vornamen</th><th>TitelPost</th><th>Aktiv</th><th>eMail</th><th colspan='3'>Aktion</th></tr>";

		$i=0;
		while ($row=pg_fetch_object($result))
		{

 			// Search uid entry
		    $sr=ldap_search($ds, "ou=People, dc=technikum-wien, dc=at", "uid=".$row->uid);
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
		echo "Fehler beim laden der Mitarbeiter: ".pg_errormessage($conn);

	echo "Closing connection";
	ldap_close($ds);

?>

</body>
</html>
