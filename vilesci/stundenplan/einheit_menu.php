<html>
<head>
<title>Einheiten Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Einheiten Verwaltung</H1>
<hr>

<?php
include('../config.inc.php');
include('../../include/studiengang.class.php');
include('../../include/einheit.class.php');
include('../../include/person.class.php');
include('../../include/student.class.php');
include('../../include/mailgrp.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Verbindung zur Datenbank konnte nicht hergestellt werden");

if (isset($_POST['newFrm']))
{
	doEdit($conn,null,true);
}
else if (isset($_GET['edit']))
{
	doEdit($conn,addslashes($_GET['kurzbz']),false);
}
else if (isset($_POST['type']) && $_POST['type']=='save')
{
	doSave();
	getUebersicht();
}
else if (isset($_POST['type']) && $_GET['type']=='delete')
{
	$e=new einheit($conn);
	$e->kurzbz=addslashes($_GET['einheit_id']);
	$e->delete();
	getUebersicht();

}
else
{
	getUebersicht();
}


function doSave()
{
	global $conn;
	$e=new einheit($conn);
	if ($_POST['new'])
	{
		$e->kurzbz=$_POST['kurzbz'];
		$e->bezeichnung=$_POST['bezeichnung'];
		$e->stg_kz=$_POST['studiengang_kz'];
		$e->semester=$_POST['semester'];
		$e->typ=$_POST['typ'];
		$e->mailgrp_kurzbz=$_POST['mailgrp_kurzbz'];
		$e->new=true;
		$e->save();
	}
	else
	{
		$e->kurzbz=$_POST['pk'];
		$e->bezeichnung=$_POST['bezeichnung'];
		$e->stg_kz=$_POST['studiengang_kz'];
		$e->semester=$_POST['semester'];
		$e->typ=$_POST['typ'];
		$e->mailgrp_kurzbz=$_POST['mailgrp_kurzbz'];
		$e->new=false;
		if (!$e->save($_POST['kurzbz']))
		echo $e->errormsg;
	}

}



function doEdit($conn,$kurzbz,$new=false)
{
    if (!$new)
	{
		$e=new einheit($conn,$kurzbz);
	}
	?>
	<form name="stdplan" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  <p><b>Einheit <?php echo ($new?'hinzufügen':'bearbeiten'); ?></b>:
  	<table border="0">
  	<tr>
  		<td><i>Name</i></td><td>
    	<input type="text" name="bezeichnung" size="20" maxlength="20" value="<?php echo $e->bezeichnung; ?>"></td>
    </tr>
    <tr><td><i>Kurzbezeichnung</i></td>
      <td><input type="text" name="kurzbz" size="10" maxlength="10" value="<?php echo $e->kurzbz; ?>">
	</td></tr>
	<tr><td><i>Studiengang</i><t/td><td>

	<SELECT name="studiengang_kz">
      			<option value="-1">- auswählen -</option>
<?php
			// Auswahl des Studiengangs
			$stg=new studiengang($conn);
			$stg_alle=$stg->getAll();
			foreach($stg_alle as $studiengang)
			{
				echo "<option value=\"$studiengang->studiengang_kz\" ";
				if ($studiengang->studiengang_kz==$e->stg_kz)
					echo "selected";
				echo " >$studiengang->kurzbz ($studiengang->bezeichnung)</option>\n";
			}
?>
		    </SELECT>

	</td></tr>
	<tr><td><i>Semester</i><t/td><td><input type="text" name="semester" size="2" maxlength="1" value="<?php echo $e->semester ?>"></td></tr>
	<tr><td><i>Typ</i><t/td><td><input type="text" name="typ" size="2" maxlength="1" value="<?php echo $e->typ ?>"></td></tr>
	<tr><td><i>Mailgrp Kurzbz</i><t/td><td><select name="mailgrp_kurzbz">
	<option value="">--keine--</option>
<?php
    $x = new mailgrp($conn);
    $erg=$x->getAll();
    
    
	    foreach($erg as $mgrp)
	    {	    	
	    	echo "<option value=\"$mgrp->mailgrp_kurzbz\" ";
			if ($mgrp->mailgrp_kurzbz==$e->mailgrp_kurzbz)
				echo "selected";
			echo " >$mgrp->mailgrp_kurzbz - $mgrp->beschreibung</option>\n";
		}
    
?>
     </SELECT>
	
	</td></tr>
	</table>

	<input type="hidden" name="pk" value="<?php echo $e->kurzbz ?>" />
    <input type="hidden" name="type" value="save">
<?php
	if ($new)
	{
?>
	   <input type="hidden" name="new" value="1">
<?php
	}
?>
    <input type="submit" name="save" value="Speichern">
  </p>
  <hr>
</form>

<?php

}

function getUebersicht()
{
    global $conn;
	$einheit=new einheit($conn);
	// Array mit allen Einheiten holen
	$einheiten=$einheit->getAll();
	//print_r($einheiten);
	?>
	<form name="import" method="post" action="einheit_import.php" enctype="multipart/form-data">
  <p><b>Import von Untis </b>(Kurswahl der Studenten)
    <input type="file" name="userfile" size="20" maxlength="30">
    <input type="submit" name="save" value="Go">
  </p>
  <hr>
</form>

<form name="stdplan" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<input type="submit" name="newFrm" value="Neue Einheit anlegen"> <br/>
</form>

<h3>&Uuml;bersicht</h3>

<table class='liste'>

<?php

	$num_rows=count($einheiten);
	$foo = 0;
	echo "<tr class='liste'><th>Kurzbz.</th><th>Bezeichnung</th><th>Stg.</th><th>Sem.</th><th>Typ</th><th>Mailgrp</th><th>Anzahl</th><th colspan=\"3\">Aktion</th></tr>";

	for ($i=0; $i<$num_rows; $i++)
	{
		$e=$einheiten[$i];
		$c=$i%2;

		echo '<tr class="liste'.$c.'">';
		echo "<td>$e->kurzbz </td>";
		echo "<td>$e->bezeichnung </td>";
		echo "<td>$e->stg_kurzbz </td>";
		echo "<td>$e->semester </td>";
		echo "<td>$e->typ </td>";
		echo "<td>$e->mailgrp_kurzbz</td>";
		
		
		echo "<td>".$einheit->countStudenten($e->kurzbz)."</td>";
		echo "<td><a href=\"einheit_det.php?kurzbz=$e->kurzbz\">Details</a></td>";
		echo "<td><a href=\"einheit_menu.php?edit=1&kurzbz=$e->kurzbz\">Edit</a></td>";
	   	echo "<td><a href=\"einheit_menu.php?einheit_id=$e->kurzbz&type=delete\">Delete</a></td>";
	   	echo "</tr>\n";
	}
?>
</table>
<?php

}


?>

</body>
</html>