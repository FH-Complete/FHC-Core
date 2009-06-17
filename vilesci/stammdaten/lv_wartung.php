<?php
// *****************************************
// * Script zum Zusammenlegen Doppelter LVs
// * Es werden zwei Listen mit LVs angezeigt
// * Links wird die LV markiert mit dem rechts
// * markierten zusammengelegt werden soll.
// * Die linke LV wird danach entfernt.
// ************************************
//DB Verbindung herstellen
require_once('../config.inc.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');

if (!$conn = @pg_pconnect(CONN_STRING))
{
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
}

$msg='';
$outp='';
$smax=0;

/*if(!isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
{
	echo substr(CONN_STRING,strpos(CONN_STRING,'dbname=')+7,strpos(CONN_STRING,'user=')-strpos(CONN_STRING,'dbname=')-7);
}*/

$s=new studiengang($conn);
$s->getAll('erhalter_kz,typ,kurzbzlang',false);
$studiengang=$s->result;
$user = get_uid();


if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
{
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
}
else
{
	$stg_kz=0;
}
if (isset($_GET['semester']) || isset($_POST['semester']))
{
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
	if($semester>(isset($_GET['max'])?$_GET['max']:$_POST['max']))
	{
		$semester=(isset($_GET['max'])?$_GET['max']:$_POST['max']);
	}
}
else
{
	$semester=0;
}

if (isset($_GET['order_1']) || isset($_POST['order_1']))
{
	$order_1=(isset($_GET['order_1'])?$_GET['order_1']:$_POST['order_1']);
}
else
{
	$order_1='lehrveranstaltung_id';
}
if (isset($_GET['order_2']) || isset($_POST['order_2']))
{
	$order_2=(isset($_GET['order_2'])?$_GET['order_2']:$_POST['order_2']);
}
else
{
	$order_2='lehrveranstaltung_id';
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

if(!is_numeric($stg_kz))
{
	$stg_kz=0;
}
if(!is_numeric($semester))
{
	$semester=0;
}

$s=array();
foreach ($studiengang as $stg)
{
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
	$outp.= '<A href="lv_wartung.php?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'&max='.$stg->max_semester.'">'.$stg->kurzbzlang.' ('.strtoupper($stg->typ.$stg->kurzbz).') </A>  -  ';

}
$outp.= '<BR> -- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
{
	$outp.= '<A href="lv_wartung.php?stg_kz='.$stg_kz.'&semester='.$i.'&max='.$s[$stg_kz]->max_sem.'">'.$i.'</A> -- ';
}

//Initialisierung der Variablen

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
		$sql_query_upd1="BEGIN;";
		$sql_query_upd1.="UPDATE lehre.tbl_lehreinheit SET lehrveranstaltung_id='$radio_2' WHERE lehrveranstaltung_id='$radio_1';";
		$sql_query_upd1.="UPDATE lehre.tbl_zeugnisnote SET lehrveranstaltung_id='$radio_2' WHERE lehrveranstaltung_id='$radio_1';";
		$sql_query_upd1.="UPDATE campus.tbl_benutzerlvstudiensemester SET lehrveranstaltung_id='$radio_2' WHERE lehrveranstaltung_id='$radio_1';";
		$sql_query_upd1.="UPDATE campus.tbl_feedback SET lehrveranstaltung_id='$radio_2' WHERE lehrveranstaltung_id='$radio_1';";
		$sql_query_upd1.="UPDATE campus.tbl_lvgesamtnote SET lehrveranstaltung_id='$radio_2' WHERE lehrveranstaltung_id='$radio_1';";
		//updateamum vergleichen - jüngeres Datum gewinnt
		$qry1="SELECT updateamum FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$radio_1';";
		$qry2="SELECT updateamum FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$radio_1';";
		if($result1=pg_query($conn,$qry1))
		{
			if($result2=pg_query($conn,$qry2))
			{
				if($row1 = pg_fetch_object($result1))
				{
					if($row2 = pg_fetch_object($result2))
					{
						if($row2->updateamum>$row1->updateamum)
						{
							//wenn lvinfo neuer als die bestehende, ersetzt sie diese
							$sql_query_upd1.="DELETE FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$radio_2';";
							$sql_query_upd1.="UPDATE campus.tbl_lvinfo SET lehrveranstaltung_id='$radio_2' WHERE lehrveranstaltung_id='$radio_1';";
						}
						else
						{
							//wenn lvinfo älter als die bestehende, wird sie gelöscht
							$sql_query_upd1.="DELETE FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$radio_1';";
						}
					}
				}
			}
		}
		$sql_query_upd1.="UPDATE campus.tbl_lvinfo SET lehrveranstaltung_id='$radio_2' WHERE lehrveranstaltung_id='$radio_1';";
		$sql_query_upd1.="UPDATE sync.tbl_synclehrveranstaltung SET lva_vilesci='$radio_2' WHERE lva_vilesci='$radio_1';";
		$sql_query_upd1.="DELETE FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$radio_1';";
		if(pg_query($conn,$sql_query_upd1))
		{
			$msg = "Daten Erfolgreich gespeichert<br>";
			pg_query($conn,"COMMIT;");
			$msg .= "<br>".mb_eregi_replace(';',';<br>',$sql_query_upd1)."COMMIT";
		}
		else
		{
			$msg = "Die Änderung konnte nicht durchgeführt werden!";
			pg_query($conn,"ROLLBACK;");
			$msg .= "<br>".mb_eregi_replace(';',';<br><b>',$sql_query_upd1)."ROLLBACK</b>";
		}


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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">

<title>LV-Zusammenlegung</title>
</head>
<body>

<H1>Zusammenlegen von Lehrveranstaltungen (<?php echo $s[$stg_kz]->kurzbz.' - '.$semester; ?>)</H1>

<?php
echo $outp;
$smax=$s[$stg_kz]->max_sem;
//aufruf
?>
<br>
<center><h2><?php echo $msg; ?></h2></center>
<br>
<?php
		//Tabellen anzeigen
	echo "<form name='form_table' action='lv_wartung.php?stg_kz=$stg_kz&semester=$semester&max=$smax&order_1=$order_1&order_2=$order_2' method='POST'>";
	echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>";
	echo "<tr>";
		echo "<td valign='top'>Das wird gelöscht:";

	 //Tabelle 1
	 echo "<table class='liste'><tr class='liste'>";
	 echo "<th><a href='lv_wartung.php?stg_kz=$stg_kz&semester=$semester&max=$smax&order_1=lehrveranstaltung_id&order_2=$order_2'>ID</a></th>";
	 echo "<th><a href='lv_wartung.php?stg_kz=$stg_kz&semester=$semester&max=$smax&order_1=kurzbz&order_2=$order_2'>Kurzbz</a></th>";
	 echo "<th><a href='lv_wartung.php?stg_kz=$stg_kz&semester=$semester&max=$smax&order_1=bezeichnung&order_2=$order_2'>Bezeichnung</a></th>";
	 echo "<th>ECTS</th>";
	 echo "<th>SS</th>";
	 echo "<th>&nbsp;</th></tr>";

	 $lf  = new lehrveranstaltung($conn);
	 $lf->getTab($stg_kz,$semester, $order_1);
	 $i=0;
	 foreach($lf->lehrveranstaltungen as $l)
	 {
	 	echo "<tr class='liste".($i%2)."'>";
	 	echo "<td>$l->lehrveranstaltung_id</td>";
	 	echo "<td>$l->kurzbz</td>";
	 	echo "<td title='$l->bezeichnung'>".kuerze($l->bezeichnung)."</td>";
	 	echo "<td>$l->ects</td>";
	 	echo "<td>$l->semesterstunden</td>";
	 	echo "<td><input type='radio' name='radio_1' value='$l->lehrveranstaltung_id' ".((isset($radio_1) && $radio_1==$l->lehrveranstaltung_id)?'checked':'')."></td>";
	 	echo "</tr>";
	 	$i++;
	 }
	 echo "</table>";
	 echo "</td>";
	 echo "<td valign='top'><input type='submit' value='  ->  '></td>";
	 echo "<td valign='top'>Das bleibt:";

	 //Tabelle 2
	 echo "<table class='liste'><tr class='liste'>";
	 echo "<th>&nbsp;</th>";
	 echo "<th><a href='lv_wartung.php?stg_kz=$stg_kz&semester=$semester&max=$smax&order_1=$order_1&order_2=lehrveranstaltung_id'>ID</a></th>";
	 echo "<th><a href='lv_wartung.php?stg_kz=$stg_kz&semester=$semester&max=$smax&order_1=$order_1&order_2=kurzbz'>Kurzbz</a></th>";
	 echo "<th><a href='lv_wartung.php?stg_kz=$stg_kz&semester=$semester&max=$smax&order_1=$order_1&order_2=bezeichnung'>Bezeichnung</a></th>";
	 echo "<th>ECTS</th>";
	 echo "<th>SS</th></tr>";

	 $lf  = new lehrveranstaltung($conn);
	 $lf->getTab($stg_kz,$semester, $order_2);
	 $i=0;
	 foreach($lf->lehrveranstaltungen as $l)
	 {
	 	echo "<tr class='liste".($i%2)."'>";
	 	echo "<td><input type='radio' name='radio_2' value='$l->lehrveranstaltung_id' ".((isset($radio_2) && $radio_2==$l->lehrveranstaltung_id)?'checked':'')."></td>";
	 	echo "<td>$l->lehrveranstaltung_id</td>";
	 	echo "<td>$l->kurzbz</td>";
	 	echo "<td  title='$l->bezeichnung'>".kuerze($l->bezeichnung)."</td>";
	 	echo "<td>$l->ects</td>";
	 	echo "<td>$l->semesterstunden</td>";
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
