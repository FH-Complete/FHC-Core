<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect($conn_string))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	$sql_query="UPDATE lektor SET uid='$uid', titel='$titel', vornamen='$vornamen', nachname='$nachname', ";
	$sql_query.="gebdatum='$gebdatum', gebort='$gebort', gebzeit='00:00', ";
	$sql_query.="emailtw='$emailtw', emailforw='$emailforw', ";
	$sql_query.="emailalias='$emailalias', kurzbz='$kurzbz', teltw='$teltw', ";
	$sql_query.="fixangestellt='$fixangestellt' WHERE id=$id";
	//echo $sql_query;
	if(!($erg=pg_exec($conn, $sql_query)))
		die(pg_errormessage($conn));
?>

<html>
<head>
<title>Lektor Speichern</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>

<body class="background_main">
<h4>Lektor Speichern</h4>

Speichern erfolgreich!

</body>
</html>
