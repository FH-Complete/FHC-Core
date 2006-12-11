<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if(!($erg=pg_exec($conn, "SELECT * FROM tbl_studiengang WHERE studiengang_kz>0 ORDER BY kurzbz")))
		die(pg_last_error($conn));
	$num_rows=pg_numrows($erg);
?>
<html>
<head>
	<title>&Uuml;bersicht der Lehrverb&auml;nde</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../skin/cis.css" type="text/css">
</head>

<body>
	<H2><table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		<td>&nbsp;<a href="index.php">Lehrveranstaltungsplan</a> &gt;&gt; Lehrverb&auml;nde</td>
		<td align="right"><A href="help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
		</tr>
		</table>
	</H2>
<table>
<tr>
<?php
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=@pg_fetch_object($erg, $i);
		echo "<th>$row->bezeichnung ($row->kurzbz)</th>";
	}
?>

</tr>
<tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		echo '<td nowrap valign="top">';
		$row=pg_fetch_object($erg, $i);
     	$stg_kz=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT DISTINCT semester FROM tbl_student WHERE studiengang_kz=$stg_kz ORDER BY semester";
		//echo $sql_query;
		if(!($result_sem=pg_exec($conn, $sql_query)))
			die(pg_last_error($conn));
		$nr_sem=pg_numrows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=pg_fetch_object($result_sem, $j);
			echo '<a href="stpl_week.php?type=verband&stg_kz='.$stg_kz."&sem=$row_sem->semester\">$stg_kzbz-$row_sem->semester</a><br>";
			$sql_query="SELECT DISTINCT verband FROM tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester ORDER BY verband";
			//echo $sql_query;
			if(!($result_ver=pg_exec($conn, $sql_query)))
				die(pg_last_error($conn));
			$nr_ver=pg_numrows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver=pg_fetch_object($result_ver, $k);
				echo "&nbsp;- <a href=\"stpl_week.php?type=verband&stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband\">$stg_kzbz-$row_sem->semester$row_ver->verband</a><br>";
				$sql_query="SELECT DISTINCT gruppe FROM tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester AND verband='$row_ver->verband' ORDER BY gruppe";
				//echo $sql_query;
				if(!($result_grp=pg_exec($conn, $sql_query)))
					die(pg_last_error($conn));
				$nr_grp=pg_numrows($result_grp);
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp=pg_fetch_object($result_grp, $l);
					echo "&nbsp;&nbsp;- <a href=\"stpl_week.php?type=verband&stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband&grp=$row_grp->gruppe\">$stg_kzbz-$row_sem->semester$row_ver->verband$row_grp->gruppe</a><br>";
				}
			}
		}
		echo"</td>";
	}
?>
</tr>
</table>
</body>
</html>