<?php
	include('../../config.inc.php');
	$conn=pg_connect(CONN_STRING);

	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="DELETE FROM tbl_reservierung WHERE reservierung_id=$id";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
?>

<html>
<head>
<title>Reservierung Check Delete</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../../include/styles.css" type="text/css">
</head>
<body>
<H1>Mehrfachbelegungen in Reservierung l&ouml;schen</H1>
<?php
if ($result)
	echo "Datensatz wurde erfolgreich gel&ouml;scht!";
else
	echo "Es ist ein Fehler aufgetreten!";
?><br>
<a href="res_check.php"><br>
<br>
Zur&uuml;ck zu &Uuml;bersicht</a>
</body>
</html>