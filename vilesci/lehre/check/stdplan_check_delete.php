<?php
	include('../../config.inc.php');
	$conn=pg_connect($conn_string);

	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="DELETE FROM stundenplan WHERE id=$id";
	echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
?>

<html>
<head>
<title>Stundenplan Check Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<H1>Mehrfachbelegungen L&ouml;schen</H1>


<?php 
if ($result)
	echo "Datensatz wurde erfolgreich gel&ouml;scht!";
else
	echo "Es ist ein Fehler aufgetreten!";
?><br>
<a href="stdplan_check.php"><br>
<br>
Zur&uuml;ck zu &Uuml;bersicht</a>
</body>
</html>