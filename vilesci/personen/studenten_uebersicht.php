<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if(!($erg=pg_query($conn, "SELECT studiengang_kz, UPPER(typ::varchar(1) || kurzbz) as kurzbz, bezeichnung FROM public.tbl_studiengang ORDER BY kurzbz ASC")))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($erg);
?>

<html>
<head>
<title>Studenten Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Studenten Übersicht</h4>

<table border="1">
<tr>
<?php
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=@pg_fetch_object($erg, $i);
		echo "<th>$row->kurzbz<BR><SMALL>$row->bezeichnung</SMALL></th>";
	}
?>
</tr>
<tr bgcolor="#DDDDDD" valign="top">
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		echo "<td nowrap>";
		$row=pg_fetch_object($erg, $i);
		$stg_kzbz=$row->kurzbz;
		$stg_kz=$row->studiengang_kz;
		$sql_query="SELECT DISTINCT semester FROM public.tbl_student WHERE studiengang_kz=$stg_kz ORDER BY semester";
		//echo $sql_query;
		if(!($result_sem=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$nr_sem=pg_numrows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=pg_fetch_object($result_sem, $j);
			echo "<a class=\"h1\" href=\"studenten_uebersicht_det.php?stg_kz=$stg_kz&sem=$row_sem->semester\">$stg_kzbz-$row_sem->semester</a><br>";

			$sql_query="SELECT DISTINCT verband FROM public.tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester ORDER BY verband";
			//echo $sql_query;
			if(!($result_ver=pg_exec($conn, $sql_query)))
				die(pg_errormessage($conn));
			$nr_ver=pg_numrows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver=pg_fetch_object($result_ver, $k);
				echo "&nbsp;- <a class=\"linkblue\" href=\"studenten_uebersicht_det.php?stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband\">$stg_kzbz-$row_sem->semester$row_ver->verband</a><br>";

				$sql_query="SELECT DISTINCT gruppe FROM public.tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester AND verband='$row_ver->verband' ORDER BY gruppe";
				//echo $sql_query;
				if(!($result_grp=pg_exec($conn, $sql_query)))
					die(pg_errormessage($conn));
				$nr_grp=pg_numrows($result_grp);
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp=pg_fetch_object($result_grp, $l);
					echo "&nbsp;&nbsp;- <a class=\"linkgreen\" href=\"studenten_uebersicht_det.php?stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband&grp=$row_grp->gruppe\">$stg_kzbz-$row_sem->semester$row_ver->verband$row_grp->gruppe</a><br>";

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
