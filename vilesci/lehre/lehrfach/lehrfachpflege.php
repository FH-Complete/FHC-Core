<?php
// *********************************************
// * Script zeigt alle Lehreinheiten an,
// * die keinem aktiven Lehrfach zu-
// * geteilt sind. Mittels einer Combo-
// * box kann ein aktives Lehrfach
// * zugewiesen werden. 
// *********************************************
	//DB Verbindung herstellen
	require_once('../../config.inc.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/lehrfach.class.php');
	
	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'');
$i=0;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">

<title>Lehrfachpflege</title>
</head>
<body>

<H1>Lehrfachpflege</h1>
<H3>Lehreinheiten mit nicht aktiven Lehrfächern</H3>
<?php
if(isset($_GET['lf_id']))
{
	$studiensemester_kurzbz = (isset($_GET['studiensemester'])?$_GET['studiensemester']:'');
	$lehreinheit_id=(isset($_GET['lehreinheit_id'])?$_GET['lehreinheit_id']:'');
	$lf_id=(isset($_GET['lf_id'])?$_GET['lf_id']:'');
	$qry_upd="UPDATE lehre.tbl_lehreinheit SET lehrfach_id='".$lf_id."' WHERE lehreinheit_id='".$lehreinheit_id."';";
	if(pg_query($conn, $qry_upd))
	{
		echo nl2br("\nErfolgreich gespeichert: ".$qry_upd);
	}
	else
	{
		echo nl2br("<span style='font-color: Red;'>\nFehler beim Speichern Student</span>");
	}	
}

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';

echo 'Studiensemester <SELECT name="studiensemester_kurzbz">';
echo "<option value=''>-- Auswahl --</option>";
$stsem_obj = new studiensemester($conn);
$stsem_obj->getAll();

foreach($stsem_obj->studiensemester as $stsem)
{
	if(isset($studiensemester_kurzbz) && $studiensemester_kurzbz!='' && $studiensemester_kurzbz==$stsem->studiensemester_kurzbz)
	{
		$selected='selected';
	}
	else 
	{
		$selected='';
	}
	
	echo "<option value='$stsem->studiensemester_kurzbz' $selected>$stsem->studiensemester_kurzbz</option>";
}

echo '</SELECT>';
echo " <INPUT type='submit' value='OK'>";

echo '</form>';

$qry="SELECT tbl_lehrveranstaltung.bezeichnung as lvbez, tbl_lehrveranstaltung.kurzbz as lvkurzbz, tbl_lehrfach.*, tbl_lehreinheit.lehreinheit_id  
	FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrfach USING (lehrfach_id) JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id) 
	WHERE NOT tbl_lehrfach.aktiv AND studiensemester_kurzbz='".$studiensemester_kurzbz."' ORDER BY studiengang_kz,semester;";

if($result = pg_query($conn, $qry))
{
	echo "<br>Anzahl der Datensätze: ".pg_num_rows($result);
	echo "<table class='liste'><tr><th>ID</th><th>LV-Kürzel</th><th>LV-Bezeichnung</th><th>Stg-Kz</th><th>Sem.</th><th>LF-Kürzel</th><th>LF-Bezeichnung</th><th>Lehrfach-Auswahl</th><th></th></tr>";
	while($row = pg_fetch_object($result))
	{
		$i++;
		echo "<tr class='liste".($i%2)."'>";
		echo "<form action='$PHP_SELF'  method='GET'>";
		echo "<input type='hidden' name='studiensemester' value='".$studiensemester_kurzbz."'>";
		echo "<input type='hidden' name='lehreinheit_id' value='".$row->lehreinheit_id."'>";
		echo "<td>".$row->lehreinheit_id."</td>";
		echo "<td>".$row->lvkurzbz."</td>";
		echo "<td>".$row->lvbez."</td>";
		echo "<td>".$row->studiengang_kz."</td>";
		echo "<td>".$row->semester."</td";
		echo "<td>".$row->kurzbz."</td>";
		echo "<td>".$row->bezeichnung."</td>";
		echo '<td><SELECT name="lf_id">';
		$qry_lf="SELECT * FROM lehre.tbl_lehrfach WHERE aktiv AND studiengang_kz='".$row->studiengang_kz."' AND semester='".$row->semester."' ORDER BY bezeichnung;";
		if($result_lf = pg_query($conn, $qry_lf))
		{
			while($row_lf = pg_fetch_object($result_lf))
			{
				if($row->bezeichnung==$row_lf->bezeichnung)
				{
					$selected='selected';
				}
				else 
				{
					$selected='';
				}
				$row_lf->bezeichnung=trim($row_lf->bezeichnung);
				echo "<option value='$row_lf->lehrfach_id' $selected>$row_lf->kurzbz - $row_lf->bezeichnung ($row_lf->fachbereich_kurzbz)</option>";
			}	
			echo "</SELECT>";
		}		
		echo "<td><input type='submit' value='Speichern'></td>";
		echo "</form>";
		if($i==20)
			break;
	}
}
?>
</body>
</html>