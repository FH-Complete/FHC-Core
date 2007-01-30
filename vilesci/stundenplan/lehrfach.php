<?php
require_once('../config.inc.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrfach.class.php');
require_once('../../include/functions.inc.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$f=new fachbereich($conn);
$f->getAll();
$fachbereiche=$f->result;
$s=new studiengang($conn);
$s->getAll();
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

if (isset($_POST['neu']))
{
	$lf = new lehrfach($conn);
	$lf->new=true;
	$lf->studiengang_kz=$stg_kz;
	$lf->fachbereich_kurzbz=$_POST['fachbereich_kurzbz'];
	$lf->kurzbz=$_POST['kurzbz'];
	$lf->bezeichnung = $_POST['bezeichnung'];
	$lf->farbe = $_POST['farbe'];
	$lf->aktiv = true;
	$lf->semester = $semester;
	$lf->sprache = $_POST['sprache'];
	$lf->updateamum = date('Y-m-d H:i:s');
	$lf->updatevon = $user;
	$lf->insertamum = date('Y-m-d H:i:s');
	$lf->insertvon = $user;
	
	if(!$lf->save())
	{
		echo "<br>$lf->errormsg<br>";
	}
}

if (isset($_POST['type']) && $_POST['type']=='editsave')
{	
	$lf = new lehrfach($conn);
	$lf->new=false;
	$lf->lehrfach_id = $_POST['lehrfach_id'];
	$lf->studiengang_kz=$stg_kz;
	$lf->fachbereich_kurzbz=$_POST['fachbereich_kurzbz'];
	$lf->kurzbz=$_POST['kurzbz'];
	$lf->bezeichnung = $_POST['bezeichnung'];
	$lf->farbe = $_POST['farbe'];
	$lf->aktiv = isset($_POST['aktiv']);
	$lf->semester = $semester;
	$lf->sprache = $_POST['sprache'];
	$lf->updateamum = date('Y-m-d H:i:s');
	$lf->updatevon = $user;	
	
	if(!$lf->save())
	{
		echo "<br>$lf->errormsg<br>";
	}
}
/*
if(isset($_POST['type']) && $_POST['type']=='lehre' && isset($_GET['lehrfach_id']))
{
	if($_GET['lehrfach_id']!='' && is_numeric($_GET['lehrfach_nr']))
	{
	   $sql_qry="UPDATE lehre.tbl_lehrfach set lehre= NOT lehre WHERE lehrfach_id='".addslashes($_GET['lehrfach_nr'])."'";
       $result=pg_query($conn, $sql_qry);
		if(!$result)
			echo pg_errormessage()."<br>";
	   
	}
	else 
	   echo "Lehrfachnummer wurde nicht übergeben, Bitte nochmals versuchen";
	
}*/

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

$sql_query="SELECT tbl_lehrfach.lehrfach_id AS Nummer, tbl_lehrfach.kurzbz AS Fach, tbl_lehrfach.bezeichnung AS Bezeichnung,
	tbl_lehrfach.farbe AS Farbe, fachbereich_kurzbz as fachbereich,
	tbl_lehrfach.aktiv, tbl_lehrfach.sprache AS Sprache
	FROM lehre.tbl_lehrfach 
	WHERE tbl_lehrfach.studiengang_kz='$stg_kz' AND semester='$semester' ORDER BY tbl_lehrfach.kurzbz";
//echo $sql_query;
$result_lehrfach=pg_query($conn, $sql_query);
if(!$result_lehrfach) error("Lehrfach not found!");
$outp='';
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
if (isset($_GET['type']) && $_GET['type']=='edit')
{
	$lf=new lehrfach($conn);
	$lf->load($_GET['lehrfach_nr']);
	echo '<form name="lehrfach_edit" method="post" action="lehrfach.php">';
	echo '<p><b>Edit Lehrfach: '.$_GET['lehrfach_nr'].'</b>';
	echo '<table>';
	?>
	<tr><td><i>Fachbereich</i></td><td><SELECT name="fachbereich_kurzbz">
      			<option value="-1">- ausw&auml;hlen -</option>
	<?php
			foreach($fachbereiche as $fb)
			{
				echo "<option value=\"$fb->fachbereich_kurzbz\" ";
				if ($lf->fachbereich_kurzbz==$fb->fachbereich_kurzbz)
					echo "selected";
				echo " >$fb->fachbereich_kurzbz</option>\n";
			}
?>
		    </SELECT></td></tr>
<?php
    echo '<tr><td><i>Name</i></td><td><input type="text" name="bezeichnung" size="30" maxlength="250" value="'.$lf->bezeichnung.'"></td></tr>';
	echo '<tr><td><i>Kurzbezeichnung</i></td><td>';
	echo '<input type="text" name="kurzbz" size="30" maxlength="12" value="'.$lf->kurzbz.'"></td></tr>';
	echo '<tr><td><i>Farbe</i></td><td>';
    echo '<input type="text" name="farbe" size="30" maxlength="7" value="'.$lf->farbe.'"></td></tr>';

	echo '<tr><td>Aktiv</td><td><input type="checkbox" name="aktiv" value="1" '.($lf->aktiv=='t'?'checked':'').' />';
    echo '<tr><td>Sprache</td><td><select name="sprache">';

	$qry1="SELECT * FROM public.tbl_sprache";
	if(!$result1=pg_query($conn,$qry1))
	{
		die( "Fehler bei der DB-Connection");
	}
	
	while($row1=pg_fetch_object($result1))	
	{
	   if($row1->sprache==$lf->sprache)
	      echo "<option value='$row1->sprache' selected>$row1->sprache</option>";
	   else 
	      echo "<option value='$row1->sprache'>$row1->sprache</option>";
	}
	
	echo '</select></td></tr>';
	echo '</table>';
	echo '<input type="hidden" name="type" value="editsave">';
	echo '<input type="hidden" name="lehrfach_id" value="'.$lf->lehrfach_id.'">';
	echo '<input type="hidden" name="stg_kz" value="'.$stg_kz.'">';
	echo '<input type="hidden" name="semester" value="'.$semester.'">';
	echo '<input type="submit" name="save" value="Speichern">';
	echo '</p><hr></form>';
} 
else
{
?>

<form action="lehrfach.php" method="post" name="lehrfach_neu" id="lehrfach_neu">
  <p><b>Neues Lehrfach</b>: <br/>
  <?php
	echo '<table>';
	//echo '<tr><td><i>Nr.</i></td><td><input type="text" name="lehrfach_nr" size="30" maxlength="30" ></td></tr>';
  ?>
	<tr><td><i>Fachbereich</i></td><td><SELECT name="fachbereich_kurzbz">
      			<option value="-1">- ausw&auml;hlen -</option>
<?php
			foreach($fachbereiche as $fb)
			{
				echo "<option value=\"$fb->fachbereich_kurzbz\" ";
				echo " >$fb->fachbereich_kurzbz</option>\n";
			}
?>
		    </SELECT></td></tr>
<?php
    echo '<tr><td><i>Name</i></td><td><input type="text" name="bezeichnung" size="30" maxlength="250" value=""></td></tr>';
	echo '<tr><td><i>Kurzbezeichnung</i></td><td>';
	echo '<input type="text" name="kurzbz" size="30" maxlength="12" value=""></td></tr>';
    echo '<tr><td><i>Farbe</i></td><td>';
    echo '<input type="text" name="farbe" size="30" maxlength="7" value=""></td></tr>';
    echo '<tr><td>Sprache</td><td><select name="sprache">';

	$qry1="SELECT * FROM public.tbl_sprache";
	if(!$result1=pg_query($conn,$qry1))
		die( 'Fehler bei der DB-Connection');
	
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
	$num_rows=pg_num_rows($result_lehrfach);
	echo "<th>id</th><th>kurzbz</th><th>bezeichnung</th><th>farbe</th><th>aktiv</th><th>fachbereich</th><th>sprache</th>\n";

	for($i=0;$i<$num_rows;$i++)
	{
	   $row=pg_fetch_object($result_lehrfach);
	   echo "<tr class='liste".($i%2)."'>";
	   echo "<td>$row->nummer</td><td>$row->fach</td><td>$row->bezeichnung</td><td>$row->farbe</td><td>".($row->aktiv=='t'?'Ja':'Nein')."</td><td>$row->fachbereich</td><td>$row->sprache</td>";
	   //echo "<td><input type='checkbox' onClick='javascript:window.document.location=\"$PHP_SELF?type=lehre&stg_kz=$stg_kz&semester=$semester&lehrfach_nr=$row->nummer\"' ".($row->lehre=='t'?'checked':'')."></td>";
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