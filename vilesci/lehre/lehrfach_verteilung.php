<?php
	include('../config.inc.php');
	$conn=pg_connect($conn_string);
	
	$sql_query="SELECT id, kurzbz FROM studiengang WHERE studentenzahl>0 ORDER BY kurzbz";
	//echo $sql_query."<br>";
	$result_stg=pg_exec($conn, $sql_query);
	if(!$result_stg) error ("studiengang not found!");
	$sql_query="SELECT id, kurzbz FROM lehrfach ORDER BY kurzbz";
	$result_lehrf=pg_exec($conn, $sql_query);
	if(!$result_lehrf) echo "lehrfach not found!";
	$sql_query="SELECT id, kurzbz FROM ort ORDER BY kurzbz";
	$result_ort=pg_exec($conn, $sql_query);
	if(!$result_ort) echo "ort not found!";
	if (!isset($stgid))
		$stgid=1;
	$sql_query="SELECT kurzbz FROM studiengang WHERE id=$stgid";
	$result_stgbz=pg_exec($conn, $sql_query);
	if(!$result_stgbz) 
		echo "lehrfach not found!";
	else
		$stgbz=pg_result($result_stgbz,0,'kurzbz');
	if (!isset($semester))
		$semester=0;
	if (!isset($verband))
		$verband='0';
	if (!isset($gruppe))
		$gruppe=0;
	if (!isset($tag))
		$tag=1;
	if (!isset($monat))
		$monat=1;
	if (!isset($jahr))
		$jahr=2002;
	$datum=" AND datum<='2002-07-01' AND datum>='2002-02-05'";
?>

<html>
<head>
<title>Lehrfachverteilung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>
<body class="background_main">
<H1>Lehrfachverteilung</H1>
<hr>
<form name="stdplan" method="post" action="lehrfach_verteilung.php">
  <p>Studiengang 
    <select name="stgid">
      <?php
		$num_rows=pg_numrows($result_stg);
		for ($i=0;$i<$num_rows;$i++) 
		{
			$row=pg_fetch_object ($result_stg, $i);
			if ($stgid==$row->id)
				echo "<option value=\"$row->id\" selected>$row->kurzbz</option>";
			else
				echo "<option value=\"$row->id\">$row->kurzbz</option>";
		}
		?>
    </select>
    Semester 
    <select name="semester">
      <?php
		for ($i=1;$i<9;$i++) 
		{
			if ($semester==$i)
				echo "<option value=\"$i\" selected>$i</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		}
		?>
    </select>
    </p>
  <p>
    <input type="hidden" name="type" value="show">
    <input type="submit" name="Save" value="Ausf&uuml;hren">
  </p>
  <hr>
</form>
<?php
if ($type=="show")
{
	?>
	<table border="<?php echo $cfgBorder; ?>" name="Verteilung">
  	<tr>
		<th>Lehrfach</th>
		<th>Gesamt</th>
		<th><?php echo $stgbz."-".$semester; ?></th>
		<th><?php echo $stgbz."-".$semester."A"; ?></th>
		<th><?php echo $stgbz."-".$semester."A1"; ?></th>
		<th><?php echo $stgbz."-".$semester."A2"; ?></th>
		<th><?php echo $stgbz."-".$semester."B"; ?></th>
		<th><?php echo $stgbz."-".$semester."B1"; ?></th>
		<th><?php echo $stgbz."-".$semester."B2"; ?></th>
		<th><?php echo $stgbz."-".$semester."C"; ?></th>
		<th><?php echo $stgbz."-".$semester."C1"; ?></th>
		<th><?php echo $stgbz."-".$semester."C2"; ?></th>
	</tr>
	<?php

	// Selektieren der Lehrfaecher
	$sql_query="SELECT DISTINCT lehrfach_id FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester'".$datum;
	$result_lehrfach=pg_exec($conn, $sql_query);
	$foo = 0;
	while ($row=@pg_fetch_object($result_lehrfach, $foo))
	{
		$lehrfachid=$row->lehrfach_id;
		$bgcolor = $cfgBgcolorOne;
    	$foo % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
		echo "<tr bgcolor=".$bgcolor.">";
		
		$sql_query="SELECT kurzbz FROM lehrfach WHERE id='$lehrfachid'";
		$result=pg_exec($conn, $sql_query);
		$row_lfbz=pg_fetch_object ($result, 0);
		echo "<td bgcolor=$bgcolor>$row_lfbz->kurzbz</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='0' AND gruppe='0' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='A' AND gruppe='0' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='A' AND gruppe='1' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='A' AND gruppe='2' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='B' AND gruppe='0' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='B' AND gruppe='1' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='B' AND gruppe='2' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='C' AND gruppe='0' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='C' AND gruppe='1' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM stundenplan WHERE studiengang_id='$stgid' AND semester='$semester' AND verband='C' AND gruppe='2' AND lehrfach_id='$lehrfachid'".$datum." GROUP BY lehrfach_id";
		$result=pg_exec($conn, $sql_query);
		if (pg_numrows($result)>0)
			$row=pg_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		echo "</tr>\n";
		$foo++;
	}
	
}
?>
</table>
</body>
</html>