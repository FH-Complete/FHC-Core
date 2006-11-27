<?php
/**
 * Changes:	22.10.2004: Anpassung an neues DB-Schema (WM)
 */

	include('../config.inc.php');
	include('../../include/functions.inc.php');
	$searchstr=$_POST['searchstr'];
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if (strlen($searchstr)<3)
		die("Zuwenige Zeichen!");
	else
	{
		// Suche mit Umlauten ist problematisch, da upper() mit Umlauten
		// nicht funktioniert (zumindest nicht bei er Standard Debian Distro)
		// vielleicht hilft eine reinintialisierung mit initdb und
		// dem entsprechenden Locale Setting. Lt. Manual hat die Locale
		// vom initdb nichts mit dem tatsächlichen Encoding der Datenbank
		// zu tun
		// $searchstr=strtr($searchstr,"äöü","ÄÖÜ");

		// SQL-Injection verhindern
		$searchstr=addslashes($searchstr);
		$sql_query=sprintf("SELECT * FROM tbl_person ".
		                    "WHERE (uid~*'%s') OR (vornamen~*'%s') ".
		                    " OR (nachname~*'%s') ".
		                    "ORDER BY nachname, vornamen",
		                    $searchstr,$searchstr,$searchstr);
		//echo $sql_query;
		if(!($erg=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$num_rows=pg_numrows($erg);
	}
?>

<html>
<head>
<title>Suchergebnis</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Suchergebnisse</h4>
Results: <?php echo $num_rows; ?><br>
<br>
<table border="<?php echo $cfgBorder; ?>">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>"><th>Titel</th><th>Vornamen</th><th>Nachname</th><th>eMail</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$bgcolor = $cfgBgcolorOne;
     	$i % 2  ? 0: $bgcolor = $cfgBgcolorTwo;

		$vornamen=pg_result($erg,$i,"vornamen");
		$nachname=pg_result($erg,$i,"nachname");
		$titel=pg_result($erg,$i,"titel");
		$emailtw=pg_result($erg,$i,"email");
		?>
		<tr bgcolor=<?php echo $bgcolor; ?>>
		<td><?php echo $titel; ?></td>
		<td><?php echo $vornamen; ?></td>
		<td><?php echo $nachname; ?></td>
		<td><a href="mailto:<?php echo $emailtw; ?>"><?php echo $emailtw; ?></a></td>
		</tr>
		<?php
	}
?>
</table>
</body>
</html>
