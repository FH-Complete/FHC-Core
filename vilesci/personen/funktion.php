<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
require_once('../config.inc.php');

$conn=pg_connect(CONN_STRING);
if (isset($_POST['type']) && $_POST['type']=='save')
{
	//Einfügen in die Datenbank
	$sql_query="INSERT INTO public.tbl_funktion (beschreibung, funktion_kurzbz) VALUES ('".$_POST['bezeichnung']."', '".$_POST['kurzbz']."')";
	$result=pg_query($conn, $sql_query);
	if(!$result)
		echo pg_errormessage()."<br>";
}
$sql_query="SELECT funktion_kurzbz, beschreibung FROM public.tbl_funktion ORDER BY funktion_kurzbz";
$result_funktion=pg_query($conn, $sql_query);
if(!$result_funktion)
	error("funktion not found!");
?>

<html>
<head>
	<title>Funktionen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body>
<H1>Funktionen</H1>
<h3>&Uuml;bersicht</h3>
<table class="liste">
<tr class="liste">
<?php
if ($result_funktion!=0)
{
	$num_rows=pg_num_rows($result_funktion);
	$num_fields=pg_num_fields($result_funktion);
	
	echo '<th></th>';
	for ($i=0;$i<$num_fields; $i++)
	    echo "<th>".pg_fieldname($result_funktion,$i)."</th>";
	echo '</tr>';
	for ($j=0; $j<$num_rows;$j++)
	{
		$row=pg_fetch_row($result_funktion,$j);
		
		echo "<tr class='liste".($j%2)."'>";
		echo "<td><a href=\"funktion_det.php?kurzbz=$row[0]\">Details</a></td>";
	    for ($i=0; $i<$num_fields; $i++)
			echo "<td>$row[$i]</td>";
		//echo "<td><a href=\"lehrfach_menu.php?lehrfach_id=$row[0]&type=edit&lehrfach_bz=$row[1]&lehrfach_kzbz=$row[2]&lehrfach_lehrevz=$row[3]\">Edit</a><td>";
	    //echo "<td><a href=\"einheit_menu.php?einheit_id=$row[0]&type=delete\">Delete</a><td>";
	    echo "</tr>\n";
	}
}
else
	echo "Kein Eintrag gefunden!";
?>
</table>
<hr>
<form action="funktion.php" method="post" name="lehrfach_neu" id="lehrfach_neu">
  <p><b>Neue Funktion:</b>
    <i>Kurzbezeichnung</i>
    <input type="text" name="kurzbz" size="10" maxlength="10">
	<i>Beschreibung</i>
    <input type="text" name="bezeichnung" size="20" maxlength="50">
    <input type="hidden" name="type" value="save">
    <input type="submit" name="save" value="Speichern">
  </p>
</form>
</body>
</html>