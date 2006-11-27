<?php
	include('../config.inc.php');
	
	$conn=pg_connect(CONN_STRING);
	$sql_query="SELECT id, kurzbz FROM einheit ORDER BY kurzbz";
	$result_einheit=pg_exec($conn, $sql_query);
	if(!$result_einheit) error("Einheit not found!");
	$sql_query="SELECT id, kurzbz FROM lehrfach ORDER BY kurzbz";
	$result_lehrf=pg_exec($conn, $sql_query);
	if(!$result_lehrf) error("Lehrfach not found!");
	$sql_query="SELECT id, kurzbz FROM ort ORDER BY kurzbz";
	$result_ort=pg_exec($conn, $sql_query);
	if(!$result_ort) error("Ort not found!");
	if (!isset($einheitid))
		$einheitid=1;
	if (!isset($lehrfachid))
		$lehrfachid=1;
	if (!isset($ortid))
		$ortid=1;
	if (!isset($tag))
		$tag=1;
	if (!isset($monat))
		$monat=1;
	if (!isset($jahr))
		$jahr=2002;
	if (!isset($stdbegin))
		$stdbegin=1;
	if (!isset($stdblock))
		$stdblock=1;
	if (!isset($stdsemester))
		$stdsemester=1;
?>

<html>
<head>
<title>Modulplan Eingabe</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<H1>Eingabe in Modulplan</H1>
<hr>
<form name="stdplan" method="post" action="stdplan_insert.php">
  <p>Einheit 
    <select name="einheitid">
      <?php
		$num_rows=pg_numrows($result_einheit);
		for ($i=0;$i<$num_rows;$i++) 
		{
			$row=pg_fetch_object ($result_einheit, $i);
			if ($stgid==$row->id)
				echo "<option value=\"$row->id\" selected>$row->kurzbz</option>";
			else
				echo "<option value=\"$row->id\">$row->kurzbz</option>";
		}
		?>
    </select>
    Lehrfach 
    <select name="lehrfachid">
      <?php
		$num_rows=pg_numrows($result_lehrf);
		for ($i=0;$i<$num_rows;$i++) 
		{
			$row=pg_fetch_object ($result_lehrf, $i);
			if ($lehrfachid==$row->id)
				echo "<option value=\"$row->id\" selected>$row->kurzbz</option>";
			else
				echo "<option value=\"$row->id\">$row->kurzbz</option>";
		}
		?>
    </select>
    Ort 
    <select name="ortid">
      <?php
		$num_rows=pg_numrows($result_ort);
		for ($i=0;$i<$num_rows;$i++) 
		{
			$row=pg_fetch_object ($result_ort, $i);
			if ($ortid==$row->id)
				echo "<option value=\"$row->id\" selected>$row->kurzbz</option>";
			else
				echo "<option value=\"$row->id\">$row->kurzbz</option>";
		}
	  	?>
    </select>
	</p><p>
    Tag 
    <input type="text" name="tag" size="3" maxlength="2" value="<?php echo $tag; ?>">
    Monat 
    <input type="text" name="monat" size="3" maxlength="2" value="<?php echo $monat; ?>">
    Jahr 
    <input type="text" name="jahr" size="5" maxlength="4" value="<?php echo $jahr; ?>">
    1. Stunde 
    <input type="text" name="stdbegin" size="3" maxlength="2" value="<?php echo $stdbegin; ?>">
  </p>
  <p>Stunden/Block 
    <input type="text" name="stdblock" size="3" maxlength="2" value="<?php echo $stdblock; ?>">
    Stunden/Semester 
    <input type="text" name="stdsemester" size="4" maxlength="3" value="<?php echo $stdsemester; ?>">
    Rythmus 
    <input type="text" name="rythmus" size="2" maxlength="1" value="<?php echo $rythmus; ?>">
    w&ouml;chig 
  </p>
  <p>
    <input type="hidden" name="type" value="save">
    <input type="submit" name="Save" value="Ausf&uuml;hren">
  </p>
  <hr>
</form>
<?php
if ($type=="save")
{
	$error=false;
	$stunde=$stdbegin;
	echo "Auftrag wird ausgeführt!<br>";
	echo "Kontrolle auf Doppelbelegungen! ... ";
	
	// checken auf Ort
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{  
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM stundenplan WHERE datum='$datum' AND stunde_id='$std' AND ort_id='$ortid'";
		$result=pg_exec($conn, $sql_query);
		if($result && (pg_numrows($result)>0)) 
		{
			echo "error!<br>Doppelbelegung gefunden auf Ort=$ortid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}	
	}
	// checken auf Lehrfach
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{  
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM stundenplan WHERE datum='$datum' AND stunde_id='$std' AND lehrfach_id='$lehrfachid'";
		$result=pg_exec($conn, $sql_query);
		if($result && (pg_numrows($result)>0)) 
		{
			echo "error!<br>Doppelbelegung gefunden auf Lehrfach=$lehrfachid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}		
	}
	
	// checken auf Ort im Einheitenplan
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{  
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM einheiten WHERE datum='$datum' AND stunde_id='$std' AND ort_id='$ortid'";
		$result=pg_exec($conn, $sql_query);
		if($result && (pg_numrows($result)>0)) 
		{
			echo "error!<br>Doppelbelegung gefunden im Einheitenplan auf Ort=$ortid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}	
	}
	// checken auf Lehrfach im Einheitenplan
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{  
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM einheitenplan WHERE datum='$datum' AND stunde_id='$std' AND lehrfach_id='$lehrfachid'";
		$result=pg_exec($conn, $sql_query);
		if($result && (pg_numrows($result)>0)) 
		{
			echo "error!<br>Doppelbelegung gefunden im Einheitenplan auf Lehrfach=$lehrfachid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}		
	}
	
	//Einfügen in die Datenbank
	if (!$error)
	{
		echo "OK!<br>";
		$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
		$datum=$jahr."-".$monat."-".$tag;
		for ($i=0; ($i<$stdsemester)&&!$error; $i++)
		{
			$std=$stunde+($i % $stdblock);
			if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
			{  
				$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
				$date=getdate($time+(604800*$rythmus));
				$datum=$date[year]."-".$date[mon]."-".$date[mday];
			}
			$sql_query="INSERT INTO einheitenplan (einheit_id, lehrfach_id, ort_id, datum, stunde_id) VALUES ('$einheitid', '$lehrfachid', '$ortid', '$datum', '$std')";
			//echo $sql_query;
			$result=pg_exec($conn, $sql_query);
			if(!$result) 
			{
				echo pg_errormessage()."<br>";
				$error=true;
			}
			else
				echo "Einheit_ID: $stgid - Lehrfach_ID: $lehrfachid - Ort_ID: $ortid - Datum: $datum - Stunde: $std -- Eingefuegt!<br>";
			
		}
		if (!$error)
			echo "Einfügen erfolgreich abgeschlossen!<br>";
		else
			echo "Es ist ein Fehler aufgetreten!<br>";
	}
}
?>
</body>
</html>