<?php
include('../config.inc.php');
include('../../include/fachbereich.class.php');
include('../../include/studiengang.class.php');


if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$f=new fachbereich($conn);
$fachbereiche=$f->getAll();
$s=new studiengang($conn);
$studiengang=$s->getAll();

if (isset($_GET[stg_kz]) || isset($_POST[stg_kz]))
	$stg_kz=(isset($_GET[stg_kz])?$_GET[stg_kz]:$_POST[stg_kz]);
else
	$stg_kz=0;
if (isset($_GET[semester]) || isset($_POST[semester]))
	$semester=(isset($_GET[semester])?$_GET[semester]:$_POST[semester]);
else
	$semester=0;

if (isset($_POST['neu']))
{
	//Einf?gen in die Datenbank
	$sql_query="INSERT INTO tbl_lehrfach (fachbereich_id,bezeichnung, kurzbz, lehrevz,farbe, aktiv, studiengang_kz,semester,sprache) ".
			   "VALUES (".($_POST['fachbereich_id']==-1?'NULL':$_POST['fachbereich_id'])." ,'".
			   	$_POST['bezeichnung']."','".
			   	$_POST['kurzbz']."', '".$_POST['lehrevz']."', '".$_POST['farbe']."',true,$stg_kz,$semester,'".$_POST['sprache']."')";
	//echo $sql_query;
	$result=pg_exec($conn, $sql_query);
	if(!$result)
		echo pg_errormessage()."<br>";
}
if ($type=="editsave")
{
	//Einf?gen in die Datenbank
	$sql_query="UPDATE tbl_lehrfach SET bezeichnung='".$_POST['bezeichnung']."', ".
		"kurzbz='".$_POST['kurzbz']."', lehrevz='".$_POST['lehrevz']."',fachbereich_id='".$_POST['fachbereich_id']."',".
		"farbe='".$_POST['farbe']."',".
		"sprache='".$_POST['sprache']."',". 
		'aktiv='.($_POST['aktiv']==1?'true':'false').
		', lehre='.($_POST['lehre']==1?'true':'false').
		" WHERE lehrfach_nr=".$_POST['lehrfach_nr'];
	//echo $sql_query;
	$result=pg_exec($conn, $sql_query);
	if(!$result)
		echo pg_errormessage()."<br>";
}

if(isset($_GET['type']) && $_GET['type']=="lehre" && isset($_GET['lehrfach_nr']))
{
	if($_GET['lehrfach_nr']!='')
	{
	   $sql_qry="Update tbl_lehrfach set lehre= NOT lehre where lehrfach_nr=".$_GET['lehrfach_nr'];
       $result=pg_exec($conn, $sql_qry);
		if(!$result)
			echo pg_errormessage()."<br>";
	   
	}
	else 
	   echo "Lehrfachnummer wurde nicht übergeben, Bitte nochmals versuchen";
	
}

/*if ($type=="delete")
{
	$sql_query="DELETE FROM lehrfach WHERE id=$lehrfach_id";
	$result=pg_exec($conn, $sql_query);
	if(!$result)
		echo pg_errormessage()."<br>";
	$sql_query="DELETE FROM einheitstudent WHERE einheit_id=$einheit_id";
	$result=pg_exec($conn, $sql_query);
	if(!$result)
		echo pg_errormessage()."<br>";
	$sql_query="DELETE FROM einheit WHERE id=$einheit_id";
	$result=pg_exec($conn, $sql_query);
	if(!$result)
		echo pg_errormessage()."<br>";
}*/

$sql_query="SELECT tbl_lehrfach.lehrfach_nr AS Nummer, tbl_lehrfach.kurzbz AS Fach, tbl_lehrfach.bezeichnung AS Bezeichnung,
	tbl_lehrfach.lehrevz AS Lehrevz, tbl_lehrfach.farbe AS Farbe,
	tbl_lehrfach.aktiv,tbl_lehrfach.ects,tbl_fachbereich.kurzbz AS Fachbereich ,tbl_lehrfach.lehre as lehre, tbl_lehrfach.sprache AS Sprache
	FROM tbl_lehrfach JOIN tbl_fachbereich USING (fachbereich_id)
	WHERE tbl_lehrfach.studiengang_kz=$stg_kz AND semester=$semester ORDER BY tbl_lehrfach.kurzbz";
//echo $sql_query;
$result_lehrfach=pg_exec($conn, $sql_query);
if(!$result_lehrfach) error("Lehrfach not found!");

$s=array();
foreach ($studiengang as $stg)
{
	$outp.= '<A href="lehrfach.php?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'">'.$stg->kurzbzlang.'</A> - ';	
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$outp.= '<BR> -- ';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.= '<A href="lehrfach.php?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';	
?>

<html>
<head>
<title>Lehrfach Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Lehrfach Verwaltung (<?php echo $s[$stg_kz]->kurzbz.' - '.$semester; ?>)</H1>

<?php
echo $outp;	
if ($type=='edit')
{
	$qry="select * from tbl_lehrfach where lehrfach_nr=".$_GET['lehrfach_nr'];
	$result_lehrfach=pg_exec($conn, $qry);
	$row=pg_fetch_object($result_lehrfach,0);
	echo '<form name="lehrfach_edit" method="post" action="lehrfach.php">';
	echo '<p><b>Edit Lehrfach: '.$_GET['lehrfach_nr'].'</b>';
	echo '<table>';
	//echo '<tr><td><i>Nr.</i></td><td><input type="text" name="lehrfach_nr" size="30" maxlength="30" value="'.$_GET['lehrfach_nr'].'" ></td></tr>';
	?>
	<tr><td><i>Fachbereich</i></td><td><SELECT name="fachbereich_id">
      			<option value="-1">- ausw&auml;hlen -</option>
	<?php
			foreach($fachbereiche as $fb)
			{
				echo "<option value=\"$fb->id\" ";
				if ($row->fachbereich_id==$fb->id)
					echo "selected";
				echo " >$fb->kurzbz</option>\n";
			}
?>
		    </SELECT></td></tr>
<?php
    echo '<tr><td><i>Name</i></td><td><input type="text" name="bezeichnung" size="30" maxlength="250" value="'.$row->bezeichnung.'"></td></tr>';
	echo '<tr><td><i>Kurzbezeichnung</i></td><td>';
	echo '<input type="text" name="kurzbz" size="30" maxlength="12" value="'.$row->kurzbz.'"></td></tr>';
	echo '<tr><td><i>Lehre Vz</i></td><td>';
    echo '<input type="text" name="lehrevz" size="30" maxlength="100" value="'.$row->lehrevz.'"></td></tr>';
    echo '<tr><td><i>Farbe</i></td><td>';
    echo '<input type="text" name="farbe" size="30" maxlength="7" value="'.$row->farbe.'"></td></tr>';

	echo '<tr><td>Aktiv</td><td><input type="checkbox" name="aktiv" value="1" '.($row->aktiv=='t'?'checked':'').' />';
    echo '<tr><td><font title="Gibt an ob es auf der CIS seite angezeigt werden soll">Lehre</font></td><td><input type="checkbox" value="1" name="lehre" '.($row->lehre=='t'?'checked':'').'></td></tr>';
	echo '<tr><td>Sprache</td><td><select name="sprache">';

	$qry1="Select * from tbl_sprache";
	if(!$result1=pg_exec($conn,$qry1))
	{
		die( "Fehler bei der DB-Connection");
	}
	
	while($row1=pg_fetch_object($result1))	
	{
	   if($row1->sprache==$row->sprache)
	      echo "<option value='$row1->sprache' selected>$row1->sprache</option>";
	   else 
	      echo "<option value='$row1->sprache'>$row1->sprache</option>";
	}
	
	echo '</select></td></tr>';
	echo '</table>';
	echo '<input type="hidden" name="type" value="editsave">';
	echo '<input type="hidden" name="lehrfach_nr" value="'.$row->lehrfach_nr.'">';
	echo '<input type="hidden" name="stg_kz" value="'.$stg_kz.'">';
	echo '<input type="hidden" name="semester" value="'.$semester.'">';
	echo '<input type="submit" name="save" value="Speichern">';
	echo '</p><hr></form>';
} else
{
?>
<!--<form name="import" method="post" action="einheit_import.php" enctype="multipart/form-data">
  <p><b>Import von Untis </b>(Kurswahl der Studenten)
    <input type="file" name="userfile" size="20" maxlength="30">
    <input type="submit" name="save" value="Go">
  </p>
  <hr>
</form>-->

<form action="lehrfach.php" method="post" name="lehrfach_neu" id="lehrfach_neu">
  <p><b>Neues Lehrfach</b>: <br/>
  <?php
	echo '<table>';
	echo '<tr><td><i>Nr.</i></td><td><input type="text" name="lehrfach_nr" size="30" maxlength="30" ></td></tr>';
  ?>
	<tr><td><i>Fachbereich</i></td><td><SELECT name="fachbereich_id">
      			<option value="-1">- ausw&auml;hlen -</option>
<?php
			foreach($fachbereiche as $fb)
			{
				echo "<option value=\"$fb->id\" ";
				echo " >$fb->kurzbz</option>\n";
			}
?>
		    </SELECT></td></tr>
<?php
    echo '<tr><td><i>Name</i></td><td><input type="text" name="bezeichnung" size="30" maxlength="250" value="'.$row->bezeichnung.'"></td></tr>';
	echo '<tr><td><i>Kurzbezeichnung</i></td><td>';
	echo '<input type="text" name="kurzbz" size="30" maxlength="12" value="'.$row->kurzbz.'"></td></tr>';
	echo '<tr><td><i>Lehre Vz</i></td><td>';
    echo '<input type="text" name="lehrevz" size="30" maxlength="100" value="'.$row->lehrevz.'"></td></tr>';
    echo '<tr><td><i>Farbe</i></td><td>';
    echo '<input type="text" name="farbe" size="30" maxlength="7" value="'.$row->farbe.'"></td></tr>';
    echo '<tr><td><font title="Gibt an ob es auf der CIS seite angezeigt werden soll">Lehre</font></td><td><input type="checkbox" name="lehre" '.($row->lehre=='t'?'checked':'').'></td></tr>';
    echo '<tr><td>Sprache</td><td><select name="sprache">';

	$qry1="Select * from tbl_sprache";
	if(!$result1=pg_exec($conn,$qry1))
	{
		die( "Fehler bei der DB-Connection");
	}
	
	while($row1=pg_fetch_object($result1))	
	   echo "<option value='$row1->sprache'>$row1->sprache</option>";
	
	echo '</select></td></tr>	</table>';
		echo '<input type="hidden" name="stg_kz" value="'.$stg_kz.'">';
	echo '<input type="hidden" name="semester" value="'.$semester.'">';
	
?>
		   

	
    <input type="hidden" name="type" value="save">
    <input type="submit" name="neu" value="Speichern">
  </p>
  </form>
<hr>

<h3>&Uuml;bersicht</h3>
<table class="liste">
<tr class="liste">
<?php
if ($result_lehrfach!=0)
{
	$num_rows=pg_numrows($result_lehrfach);
	echo "<th>id</th><th>kurzbz</th><th>bezeichnung</th><th>lehrevz</th><th>farbe</th><th>aktiv</th><th>ects</th><th>fachbereich</th><th>sprache</th><th>lehre</th>\n";

	for($i=0;$i<$num_rows;$i++)
	{
	   $row=pg_fetch_object($result_lehrfach);
	   echo "<tr class='liste".($i%2)."'>";
	   echo "<td>$row->nummer</td><td>$row->fach</td><td>$row->bezeichnung</td><td>$row->lehrevz</td><td>$row->farbe</td><td>$row->aktiv</td><td>$row->ects</td><td>$row->fachbereich</td><td>$row->sprache</td>";
	   echo "<td><input type='checkbox' onClick='javascript:window.document.location=\"$PHP_SELF?type=lehre&stg_kz=$stg_kz&semester=$semester&lehrfach_nr=$row->nummer\"' ".($row->lehre=='t'?'checked':'')."></td>";
	   echo "<td><a href=\"lehrfach.php?lehrfach_nr=$row->nummer&type=edit&stg_kz=$stg_kz&semester=$semester\">Edit</a></td>";	
	   echo "</tr>\n";
	}
	
	/*
	$num_fields=pg_numfields($result_lehrfach);
	$foo = 0;
	for ($i=0;$i<$num_fields; $i++)
	    echo "<th>".pg_fieldname($result_lehrfach,$i)."</th>";
	for ($j=0; $j<$num_rows;$j++)
	{
		$row=pg_fetch_row($result_lehrfach,$j);
		$bgcolor = $cfgBgcolorOne;
		$foo % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
		echo "<tr class='liste".($j%2)."'>";
	    for ($i=0; $i<$num_fields; $i++)
			echo "<td bgcolor=$bgcolor>$row[$i]</td>";
		//echo "<td><a href=\"einheit_det.php?einheit_id=$row[0]&einheit_kzbz=$row[2]\">Details</a><td>";
		//echo "<td><a href=\"lehrfach_menu.php?lehrfach_nr=$row[0]&type=edit\">Edit</a></td>";
		echo "<td><a href=\"lehrfach.php?lehrfach_nr=$row[0]&type=edit&stg_kz=$stg_kz&semester=$semester\">Edit</a></td>";
	    //echo "<td><a href=\"einheit_menu.php?einheit_id=$row[0]&type=delete\">Delete</a><td>";
	    echo "</tr>\n";
		$foo++;
	}
	*/
}
else
	echo "Kein Eintrag gefunden!";
?>
</table>

<?php
}
?>
<br>
</body>
</html>