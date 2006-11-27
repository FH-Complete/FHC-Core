<?php
	include('../config.inc.php');

	$conn=pg_connect(CONN_STRING);
	$sql_query="SELECT * FROM tbl_studiengang WHERE studiengang_kz>0 ORDER BY kurzbz";
		$result_stg=pg_exec($conn, $sql_query);
		if(!$result_stg)
		error ("studiengang not found!");
	if ($mode=='del')
	{
		$sql_query="DELETE FROM tbl_stundenplan WHERE studiengang_kz=$stg_kz AND datum>='$jahrv-$monatv-$tagv' AND datum<='$jahrb-$monatb-$tagb'";
		//echo $sql_query.'<BR>';
		$result=pg_query($conn, $sql_query);
		$anz=pg_numrows($result);
		echo $anz.' Records deleted!<BR>';
	}
	if (!isset($stg_kz))
		$stg_kz=0;
	if (!isset($tagv))
		$tag=1;
	if (!isset($monatv))
		$monat=1;
	if (!isset($jahrv))
		$jahr=2002;
	if (!isset($tagb))
		$tag=1;
	if (!isset($monatb))
		$monat=1;
	if (!isset($jahrb))
		$jahr=2002;

	//echo '<BR>Beginn:'.mktime(0,0,0,2,23,2004).'<BR>';
	//echo '<BR>Ende:'.mktime(0,0,0,6,17,2004).'<BR>';
?>

<html>
<head>
<title>Delete Stundenplan</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<H1>Delete from Stundenplan</H1>
<hr>
<form name="stdplan" method="post" action="stdplan_delete.php">
  <p>Studiengang
    <select name="stg_kz">
      <?php
		$num_rows=pg_numrows($result_stg);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_stg, $i);
			if ($stg_kz==$row->id)
				echo "<option value=\"$row->studiengang_kz\" selected>$row->kurzbz</option>";
			else
				echo "<option value=\"$row->studiengang_kz\">$row->kurzbz</option>";
		}
		?>
    </select>
  </p>
  <p> Von (inkl): Tag
    <input type="text" name="tagv" size="3" maxlength="2" value="<?php echo $tagv; ?>">
    Monat
    <input type="text" name="monatv" size="3" maxlength="2" value="<?php echo $monatv; ?>">
    Jahr
    <input type="text" name="jahrv" size="5" maxlength="4" value="<?php echo $jahrv; ?>">
  </p>
  <p> Bis (inkl): Tag
    <input type="text" name="tagb" size="3" maxlength="2" value="<?php echo $tagb; ?>">
    Monat
    <input type="text" name="monatb" size="3" maxlength="2" value="<?php echo $monatb; ?>">
    Jahr
    <input type="text" name="jahrb" size="5" maxlength="4" value="<?php echo $jahrb; ?>">
  </p>
  <p>
    <input type="hidden" name="mode" value="del">
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
	// checken auf Verband
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
		$sql_query="SELECT semester, verband, gruppe, studiengang_kz FROM tbl_stundenplan WHERE datum='$datum' AND stunde_id='$std' AND studiengang_kz='$stg_kz' AND semester='$semester' AND (verband='$verband' OR verband=NULL) AND (gruppe='$gruppe' OR gruppe=NULL)";
		$result=pg_exec($conn, $sql_query);
		if($result && (pg_numrows($result)>0))
		{
			$row=pg_fetch_object($result,0);
			echo "error!<br>Doppelbelegung gefunden auf Datum=$datum - Stunde=$stunde - StudiengangID=$row->studiengang_id - Semester=$row->semester Verband=$row->verband Gruppe=$row->gruppe!<br>";
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
		$sql_query="SELECT id FROM einheitenplan WHERE datum='$datum' AND stunde_id='$std' AND ort_id='$ortid'";
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
			if (($verband=='0') && ($gruppe==0))
				$sql_query="INSERT INTO stundenplan (studiengang_id, semester, verband, gruppe, lehrfach_id, ort_id, datum, stunde_id) VALUES ('$stgid', '$semester', NULL, NULL, '$lehrfachid', '$ortid', '$datum', '$std')";
			elseif ($gruppe=0)
				$sql_query="INSERT INTO stundenplan (studiengang_id, semester, verband, gruppe, lehrfach_id, ort_id, datum, stunde_id) VALUES ('$stgid', '$semester', '$verband', NULL, '$lehrfachid', '$ortid', '$datum', '$std')";
			else
				$sql_query="INSERT INTO stundenplan (studiengang_id, semester, verband, gruppe, lehrfach_id, ort_id, datum, stunde_id) VALUES ('$stgid', '$semester', '$verband', '$gruppe', '$lehrfachid', '$ortid', '$datum', '$std')";
			//echo $sql_query;
			$result=pg_exec($conn, $sql_query);
			if(!$result)
			{
				echo pg_errormessage()."<br>";
				$error=true;
			}
			else
				echo "Studiengang_ID: $stgid - Semester: $semester - Verband: $verband - Gruppe: $gruppe - Lehrfach_ID: $lehrfachid - Ort_ID: $ortid - Datum: $datum - Stunde: $std -- Eingefuegt!<br>";

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
