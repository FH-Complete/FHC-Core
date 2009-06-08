<?php
// ***************************************************************
// * Script zum Anzeigen und Zusammenlegen von
// * doppelten Personen
// ***************************************************************
//DB Verbindung herstellen
require_once('../config.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/functions.inc.php');

if (!$conn = @pg_pconnect(CONN_STRING))
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$msg='';
$outp='';
$anfang='';
$ende='';
$person1='';
$person2='';

if ((isset($_GET['person2']) || isset($_POST['person2']))&&(isset($_GET['person1']) || isset($_POST['person1'])))
{
	//zusammenlegen der personen
	$person2=(isset($_GET['person2'])?$_GET['person2']:$_POST['person2']);
	$person1=(isset($_GET['person1'])?$_GET['person1']:$_POST['person1']);	
	$sql_query_upd1="BEGIN;";
		$sql_query_upd1.="UPDATE public.tbl_benutzer SET person_id='$person1' WHERE person_id='$person2';";
		$sql_query_upd1.="UPDATE public.tbl_konto SET person_id='$person1' WHERE person_id='$person2';";
		$sql_query_upd1.="UPDATE public.tbl_prestudent SET person_id='$person1' WHERE person_id='$person2';";
		//$sql_query_upd1.="UPDATE sync.tbl_syncperson SET person_portal='$radio_2' WHERE person_portal='$radio_1';";
		$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer1='$person1' WHERE pruefer1='$person2';";
		$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer2='$person1' WHERE pruefer2='$person2';";
		$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer3='$person1' WHERE pruefer3='$person2';";
		$sql_query_upd1.="UPDATE lehre.tbl_projektbetreuer SET person_id='$person1' WHERE person_id='$person2';";
		$sql_query_upd1.="UPDATE public.tbl_adresse SET person_id='$person1' WHERE person_id='$person2';";
		$sql_query_upd1.="UPDATE public.tbl_akte SET person_id='$person1' WHERE person_id='$person2';";
		$sql_query_upd1.="UPDATE public.tbl_bankverbindung SET person_id='$person1' WHERE person_id='$person2';";
		$sql_query_upd1.="UPDATE public.tbl_kontakt SET person_id='$person1' WHERE person_id='$person2';";
		$sql_query_upd1.="UPDATE public.tbl_betriebsmittelperson SET person_id='$person1' WHERE person_id='$person2';";

		$sql_query_upd1.="DELETE FROM public.tbl_person WHERE person_id='$person2';";

		if(pg_query($conn,$sql_query_upd1))
		{
			$msg = "Daten erfolgreich gespeichert<br>";
			pg_query($conn,"COMMIT;");
			$msg .= "<br>".str_replace(';',';<br>',$sql_query_upd1);
			
			if(@pg_query($conn,'SELECT person_portal FROM sync.tbl_syncperson LIMIT 1'))
			{
				$msg.= "<br><br>Sync-Tabelle wird aktualisiert";
				$sql_query_upd1="UPDATE sync.tbl_syncperson SET person_portal='$person1' WHERE person_portal='$person2';";
				pg_query($conn,$sql_query_upd1);
				$msg.= "<br>".str_replace(';',';<br>',$sql_query_upd1)."COMMIT";
			}
			if(@pg_query($conn,'SELECT person_id FROM sync.tbl_syncperson LIMIT 1'))
			{
				$msg.= "<br><br>Sync-Tabelle wird aktualisiert";
				$sql_query_upd1="UPDATE sync.tbl_syncperson SET person_id='$radio_2' WHERE person_id='$radio_1';";
				pg_query($conn,$sql_query_upd1);
				$msg.= "<br>".str_replace(';',';<br>',$sql_query_upd1)."COMMIT";
			}
		}
		else
		{
			$msg = "Die Änderung konnte nicht durchgeführt werden!";
			pg_query($conn,"ROLLBACK;");
			$msg.= "<br>".str_replace(';',';<br><b>',$sql_query_upd1)."ROLLBACK</b>";
		}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">

<title>Personen-Auflistung von Mehrfacheinträgen</title>
</head>
<body>

<H1>Mehrfache Personendatensaetze</H1>

<?php
echo $outp;
//aufruf
if ($msg=='')
	$msg="Diese Liste enth&auml;lt die ersten 50 Personendatens&auml;tze, die offenbar mehrfach vorkommen.
	<br>Der Button in der erste Spalte gibt die Person_id des Datensatzes an, der entfernt werden soll. 
	<br>Wird dieser Button angeklickt, werden alle anh&auml;ngenden Daten dem Datensatz dieser Zeile (Person_id in Spalte 2) angeh&auml;ngt.
	<br>Dadurch kann es in Folge zu Doppeleintr&auml;gen bei diesen Datens&auml;tzen kommen (z.B. Adresse).";
?>
<br>
<center><h2><?php echo "<span style=\"font-size:0.7em\">".$msg."</span>"; ?></h2></center>
<br>
<?php
	//Tabellen anzeigen 
	//echo "<form name='form_table' action='personen_listedoppelte.php' method='POST'>";
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>";
	echo "<tr>";

	 //Tabelle 1
	 echo "<table class='liste'><tr class='liste'>";
	 echo "<th>Alt.-ID</th>";
	 echo "<th>ID</th>";
	 echo "<th>Nachname</th>";
	 echo "<th>Vorname</th>";
	 echo "<th>Geburtsdatum</th>";
	 echo "<th>SVNr</th>";
	 echo "<th>Ersatzkennz.</th>";
	 echo "<th>Ext-ID</th>";
	 
	 $qry="SELECT person.person_id as person2, tbl_person.person_id AS person1, tbl_person.nachname as nachname1, tbl_person.vorname as vorname1, tbl_person.gebdatum as gebdatum1,
	 tbl_person.svnr as svnr1, tbl_person.ersatzkennzeichen as ersatzkennzeichen1, tbl_person.ext_id as ext_id1, tbl_person.* FROM tbl_person person 
	 JOIN tbl_person ON (person.vorname=tbl_person.vorname AND person.nachname=tbl_person.nachname AND person.gebdatum=tbl_person.gebdatum AND person.person_id!=tbl_person.person_id 
	 AND person.person_id!=tbl_person.person_id) ORDER BY tbl_person.nachname
	 LIMIT 50 ;";
	 $i=0;
	 if($result = pg_query($conn,$qry))
	{
		while($l=pg_fetch_object($result))
		{
			
		 	echo "<tr class='liste".($i%2)."'>";
		 	echo "<td align='center' ><form name='form_table' action='personen_listedoppelte.php' method='POST'>
		 	<input type='hidden' name='person1' value='$l->person1'><input type='submit' name='person2' value='$l->person2'>
		 	</form></td>";
		 	echo "<td>$l->person1</td>";
		 	echo "<td>$l->nachname1</td>";
		 	echo "<td>$l->vorname1</td>";
		 	echo "<td>$l->gebdatum1</td>";
		 	echo "<td>$l->svnr1</td>";
		 	echo "<td>$l->ersatzkennzeichen1</td>";
		 	echo "<td>$l->ext_id1</td>";
		 	echo "</tr>";
		 	$i++;
		}
	 }
	 echo "</table>";
	 echo "</td>";
	 //echo "<td valign='top'><input type='submit' value='  Weiter  '></td>";
	 echo "</tr></table>";
	 //echo "</form>";

?>
</tr>
</table>
</body>
</html>