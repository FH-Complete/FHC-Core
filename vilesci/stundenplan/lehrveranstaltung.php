<?php
require_once('../config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$s=new studiengang($conn);
$s->getAll('typ, kurzbz');
$studiengang=$s->result;

$user = get_uid();

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;
if (isset($_GET['semester']) || isset($_POST['semester']))
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
else
	$semester=0;

if(!is_numeric($stg_kz))
	$stg_kz=0;
if(!is_numeric($semester))
	$semester=0;
	

if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
{	
	//Lehre Feld setzen
	if(isset($_GET['lehre']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET lehre=".($_GET['lehre']=='t'?'false':'true')." WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichen!";
		else
			echo "Erfolgreich gespeichert";
	}
	
	//Lehrevz Speichern
	if(isset($_POST['lehrevz']))
	{
		$qry = "UPDATE lehre.tbl_lehrveranstaltung SET lehreverzeichnis='".addslashes($_POST['lehrevz'])."' WHERE lehrveranstaltung_id='".$_GET['lvid']."'";
		if(!pg_query($conn, $qry))
			echo "Fehler beim Speichern!";
		else 
			echo "Erfolgreich gespeichert";
	}
}

$sql_query="SELECT * FROM lehre.tbl_lehrveranstaltung
	WHERE studiengang_kz='$stg_kz' AND semester='$semester' ORDER BY bezeichnung";
//echo $sql_query;
$result_lv=pg_query($conn, $sql_query);
if(!$result_lv) error("Lehrveranstaltung not found!");
$outp='';
$s=array();
foreach ($studiengang as $stg)
{
	$outp.= '<A href="'.$PHP_SELF.'?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'">'.$stg->kuerzel.'</A> - ';	
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$outp.= '<BR> -- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.= '<A href="'.$PHP_SELF.'?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';	
?>

<html>
<head>
<title>Lehrveranstaltung Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Lehrveranstaltung Verwaltung (<?php echo $s[$stg_kz]->kurzbz.' - '.$semester; ?>)</H1>

<?php
echo $outp;	
?>

<h3>&Uuml;bersicht</h3>
<table class="liste">
<tr class="liste">
<?php
if ($result_lv!=0)
{
	$num_rows=pg_num_rows($result_lv);
	echo "<th>ID</th><th>Kurzbz</th><th>Bezeichnung</th><th>ECTS</th><th>Lehre</th><th>LehreVz</th><th>Aktiv</th>\n";

	for($i=0;$i<$num_rows;$i++)
	{
	   $row=pg_fetch_object($result_lv);
	   echo "<tr class='liste".($i%2)."'>";
	   echo "<td align='right'>$row->lehrveranstaltung_id</td><td>$row->kurzbz</td><td>$row->bezeichnung</td><td>$row->ects</td>";
	   echo "<td><a href='$PHP_SELF?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester&lehre=$row->lehre'><img src='../../skin/images/".($row->lehre=='t'?'true.gif':'false.gif')."'></a></td>";
	   echo "<td><form action='$PHP_SELF?lvid=$row->lehrveranstaltung_id&stg_kz=$stg_kz&semester=$semester' method='POST'><input type='text' value='$row->lehreverzeichnis' size='4' name='lehrevz'><input type='submit' value='ok'></form></td>";
	   echo "<td>".($row->aktiv=='t'?'Ja':'Nein')."</td>";
	   echo "</tr>\n";
	}
	
}
else
	echo "Kein Eintrag gefunden!";
?>
</table>

<br>
</body>
</html>