<?php
// ***************************************************************
// * Script zum Zusammenlegen Doppelter Studenten
// * Es werden zwei Listen mit Studenten angezeigt
// * Links wird der Student markiert, der mit dem
// * rechts markierten zusammengelegt werden soll.
// * Der linke Student wird danach entfernt.
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
if (isset($_GET['nn']) || isset($_POST['nn']))
{
	$nn=(isset($_GET['nn'])?$_GET['nn']:$_POST['nn']);
}
else
{
	$nn=NULL;
}
if (isset($_GET['vn']) || isset($_POST['vn']))
{
	$vn=(isset($_GET['vn'])?$_GET['vn']:$_POST['vn']);
}
else
{
	$vn=NULL;
}
if (isset($_GET['person_id']) || isset($_POST['person_id']))
{
	$person_id=(isset($_GET['person_id'])?$_GET['person_id']:$_POST['person_id']);
}
else
{
	$person_id=NULL;
}

if (isset($_GET['order_1']) || isset($_POST['order_1']))
{
	$order_1=(isset($_GET['order_1'])?$_GET['order_1']:$_POST['order_1']);
}
else
{
	$order_1='person_id';
}
if (isset($_GET['order_2']) || isset($_POST['order_2']))
{
	$order_2=(isset($_GET['order_2'])?$_GET['order_2']:$_POST['order_2']);
}
else
{
	$order_2='person_id';
}
if (isset($_GET['radio_1']) || isset($_POST['radio_1']))
{
	$radio_1=(isset($_GET['radio_1'])?$_GET['radio_1']:$_POST['radio_1']);
}
else
{
	$radio_1=-1;
}
if (isset($_GET['radio_2']) || isset($_POST['radio_2']))
{
	$radio_2=(isset($_GET['radio_2'])?$_GET['radio_2']:$_POST['radio_2']);
}
else
{
	$radio_2=-1;
}

function kuerze($string)
{
	if(strlen($string)>40)
	{
		return substr($string,0,35)."...";
	}
	else
	{
		return $string;
	}
}

if(isset($radio_1) && isset($radio_2) && $radio_1>=0 && $radio_2>=0)
{
	if($radio_1==$radio_2)
	{
		$msg="Die Datensaetze duerfen nicht die gleiche ID haben";
	}
	else
	{
		$msg='';
		$sql_query_upd1="BEGIN;";
		$sql_query_upd1.="UPDATE public.tbl_benutzer SET person_id='$radio_2' WHERE person_id='$radio_1';";
		$sql_query_upd1.="UPDATE public.tbl_konto SET person_id='$radio_2' WHERE person_id='$radio_1';";
		$sql_query_upd1.="UPDATE public.tbl_prestudent SET person_id='$radio_2' WHERE person_id='$radio_1';";
		//$sql_query_upd1.="UPDATE sync.tbl_syncperson SET person_portal='$radio_2' WHERE person_portal='$radio_1';";
		$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer1='$radio_2' WHERE pruefer1='$radio_1';";
		$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer2='$radio_2' WHERE pruefer2='$radio_1';";
		$sql_query_upd1.="UPDATE lehre.tbl_abschlusspruefung SET pruefer3='$radio_2' WHERE pruefer3='$radio_1';";
		$sql_query_upd1.="UPDATE lehre.tbl_projektbetreuer SET person_id='$radio_2' WHERE person_id='$radio_1';";
		$sql_query_upd1.="UPDATE public.tbl_adresse SET person_id='$radio_2' WHERE person_id='$radio_1';";
		$sql_query_upd1.="UPDATE public.tbl_akte SET person_id='$radio_2' WHERE person_id='$radio_1';";
		$sql_query_upd1.="UPDATE public.tbl_bankverbindung SET person_id='$radio_2' WHERE person_id='$radio_1';";
		$sql_query_upd1.="UPDATE public.tbl_kontakt SET person_id='$radio_2' WHERE person_id='$radio_1';";
		$sql_query_upd1.="UPDATE public.tbl_betriebsmittelperson SET person_id='$radio_2' WHERE person_id='$radio_1';";

		$sql_query_upd1.="DELETE FROM public.tbl_person WHERE person_id='$radio_1';";

		if(pg_query($conn,$sql_query_upd1))
		{
			$msg = "Daten erfolgreich gespeichert<br>";
			$msg .= "<br>".str_replace(';',';<br>',$sql_query_upd1);
			pg_query($conn,"COMMIT;");
			if(@pg_query($conn,'SELECT person_portal FROM sync.tbl_syncperson LIMIT 1'))
			{
				$msg.= "<br><br>Sync-Tabelle wird aktualisiert";
				$sql_query_upd1="UPDATE sync.tbl_syncperson SET person_portal='$radio_2' WHERE person_portal='$radio_1';";
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
		$radio_1=0;
		$radio_2=0;
	}
}
if((isset($radio_1) && !isset($radio_2))||(!isset($radio_1) && isset($radio_2)) || ($radio_1<0 || $radio_2<0))
{
	$msg="Es muß je ein Radio-Button pro Tabelle angeklickt werden";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">

<title>Personen-Zusammenlegung</title>
</head>
<body>

<H1>Zusammenlegen von Personendatensaetzen</H1>

<?php
echo $outp;
echo "<form name='suche' action='personen_wartung.php' method='POST'>";
echo "<table><tr>";
echo "<th>Nachname</th><th>Vorname</th><th>&nbsp;</th>";
echo "<tr>";
echo "<td><input name=\"nn\" type=\"text\" value=\"$nn\" size=\"64\" maxlength=\"64\"></td>";
echo "<td><input name='vn' type='text' value=\"$vn\" size='32' maxlength='32'></td>";
echo "<td><input type='submit' value=' suchen '></td></tr>";
echo "</table></form>";

//aufruf
?>
<br>
<center><h2><?php echo "<span style=\"font-size:0.7em\">".$msg."</span>"; ?></h2></center>
<br>
<?php
	//Tabellen anzeigen
	echo "<form name='form_table' action='personen_wartung.php?uid=$person_id&order_1=$order_1&order_2=$order_2&nn=$nn&vn=$vn' method='POST'>";
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>";
	echo "<tr>";

	echo "<td valign='top'>Der wird gelöscht:";

	 //Tabelle 1
	 echo "<table class='liste'><tr class='liste'>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=person_id&order_2=$order_2&nn=$nn&vn=$vn'>ID</a></th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=nachname&order_2=$order_2&nn=$nn&vn=$vn'>Nachname</a></th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=vorname&order_2=$order_2&nn=$nn&vn=$vn'>Vorname</a></th>";
	 echo "<th>Geburtsdatum</th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=svnr&order_2=$order_2&nn=$nn&vn=$vn'>SVNr</a></th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=ersatzkennzeichen&order_2=$order_2&nn=$nn&vn=$vn'>Ersatzkennz.</a></th>";
	 echo "<th>Ext-ID</th>";
	 echo "<th>&nbsp;</th></tr>";

	 $lf  = new person($conn);
	 $lf->getTab($nn,$vn, $order_1);
	 $i=0;
	 foreach($lf->personen as $l)
	 {
	 	echo "<tr class='liste".($i%2)."'>";
	 	echo "<td>$l->person_id</td>";
	 	echo "<td>$l->nachname</td>";
	 	echo "<td>$l->vorname</td>";
	 	echo "<td>$l->gebdatum</td>";
	 	echo "<td>$l->svnr</td>";
	 	echo "<td>$l->ersatzkennzeichen</td>";
	 	echo "<td>$l->ext_id</td>";
	 	echo "<td><input type='radio' name='radio_1' value='$l->person_id' ".((isset($radio_1) && $radio_1==$l->person_id)?'checked':'')."></td>";
	 	echo "</tr>";
	 	$i++;
	 }
	 echo "</table>";
	 echo "</td>";
	 echo "<td valign='top'><input type='submit' value='  ->  '></td>";
	 echo "<td valign='top'>Der bleibt:";

	 //Tabelle 2
	 echo "<table class='liste'><tr class='liste'>";
	 echo "<th>&nbsp;</th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=$order_1&order_2=person_id&nn=$nn&vn=$vn'>ID</a></th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=$order_1&order_2=nachname&nn=$nn&vn=$vn'>Nachname</a></th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=$order_1&order_2=vorname&nn=$nn&vn=$vn'>Vorname</a></th>";
	 echo "<th>Geburtsdatum</th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=$order_1&order_2=svnr&nn=$nn&vn=$vn'>SVNr</a></th>";
	 echo "<th><a href='personen_wartung.php?uid=$person_id&order_1=$order_1&order_2=ersatzkennzeichen&nn=$nn&vn=$vn'>Ersatzkennz.</a></th>";
	 echo "<th>Ext-ID</th>";
	 echo "</tr>";

	 $lf  = new person($conn);
	 $lf->getTab($nn, $vn, $order_2);
	 $i=0;
	 foreach($lf->personen as $l)
	 {
	 	echo "<tr class='liste".($i%2)."'>";
	 	echo "<td><input type='radio' name='radio_2' value='$l->person_id' ".((isset($radio_2) && $radio_2==$l->person_id)?'checked':'')."></td>";
	 	echo "<td>$l->person_id</td>";
	 	echo "<td>$l->nachname</td>";
	 	echo "<td>$l->vorname</td>";
	 	echo "<td>$l->gebdatum</td>";
	 	echo "<td>$l->svnr</td>";
	 	echo "<td>$l->ersatzkennzeichen</td>";
	 	echo "<td>$l->ext_id</td>";
	 	echo "</tr>";
	 	$i++;
	 }
	 echo "</table>";
	 echo "</td>";
	 echo "</tr>";
	 echo "</table>";
	 echo "</form>";

?>
</tr>
</table>
</body>
</html>
