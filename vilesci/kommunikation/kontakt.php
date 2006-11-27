<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

?>

<html>
<head>
<title>Kontakte - eMail-Verteiler</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Kontakte - eMail-Verteiler</h4>
<br>
<h3>Module</h3>
<table class="liste">
<tr class="liste" >
<?php
	if(!($erg=pg_exec($conn, "SELECT studiengang_kz, bezeichnung, kurzbz FROM tbl_studiengang ORDER BY kurzbz ASC")))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($erg);
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
     	$stg_id=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT * FROM tbl_einheit WHERE studiengang_kz=$stg_id ORDER BY einheit_kurzbz";
		//echo $sql_query;
		if(!($result=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$nr_sem=pg_numrows($result);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=pg_fetch_object($result, $j);
			if(strlen($row_sem->mailgrp_kurzbz)>0)
			   echo "<a class=\"h1\" href=\"mailto:$row_sem->mailgrp_kurzbz@technikum-wien.at\">$row_sem->einheit_kurzbz</a><br>";
			else
			   echo "$row_sem->einheit_kurzbz<br>";
			echo "<a target=\"_blank\" class=\"linkgreen\" href=\"studenten_liste_export.php?einheitid=$row_sem->einheit_kurzbz\">&nbsp;&nbsp;&nbsp;&nbsp;(Liste)</a><br>";
		}
		echo"</td>";
	}
?>
</tr>
</table>
<h3>Studenten</h3>
<table class="liste">
<tr class="liste">
<?php
	if(!($erg=pg_exec($conn, "SELECT studiengang_kz, bezeichnung, kurzbz FROM tbl_studiengang ORDER BY kurzbz ASC")))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($erg);
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=@pg_fetch_object($erg, $i);
		echo "<th>$row->kurzbz<BR><SMALL>$row->bezeichnung</SMALL></th>";
	}
?>
</tr>
<tr bgcolor="#DDDDDD">
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		echo "<td nowrap valign= \"top\">";
		$row=pg_fetch_object($erg, $i);
     	$stg_id=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT DISTINCT semester FROM tbl_student WHERE studiengang_kz=$stg_id ORDER BY semester";
		//echo $sql_query;
		if(!($result_sem=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$nr_sem=pg_numrows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=pg_fetch_object($result_sem, $j);
			$stg_kzbz_lo=strtolower($stg_kzbz);
			echo "<a class=\"h1\" href=\"mailto:$stg_kzbz_lo$row_sem->semester@technikum-wien.at\">$stg_kzbz-$row_sem->semester</a><br>";

			$sql_query="SELECT DISTINCT verband FROM tbl_student WHERE studiengang_kz=$stg_id AND semester=$row_sem->semester ORDER BY verband";
			//echo $sql_query;
			if(!($result_ver=pg_exec($conn, $sql_query)))
				die(pg_errormessage($conn));
			$nr_ver=pg_numrows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver=pg_fetch_object($result_ver, $k);
				$ver_lo=strtolower($row_ver->verband);
				echo "&nbsp;- <a class=\"linkblue\" href=\"mailto:$stg_kzbz_lo$row_sem->semester$ver_lo@technikum-wien.at\">$stg_kzbz-$row_sem->semester$row_ver->verband</a><br>";

				$sql_query="SELECT DISTINCT gruppe FROM tbl_student WHERE studiengang_kz=$stg_id AND semester=$row_sem->semester AND verband='$row_ver->verband' ORDER BY gruppe";
				//echo $sql_query;
				if(!($result_grp=pg_exec($conn, $sql_query)))
					die(pg_errormessage($conn));
				$nr_grp=pg_numrows($result_grp);
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp=pg_fetch_object($result_grp, $l);
					echo "&nbsp;&nbsp;- <a class=\"linkgreen\" href=\"mailto:$stg_kzbz_lo$row_sem->semester$ver_lo$row_grp->gruppe@technikum-wien.at\">$stg_kzbz-$row_sem->semester$row_ver->verband$row_grp->gruppe</a><br>";
					echo "<a target=\"_blank\" class=\"linkgreen\" href=\"studenten_liste_export.php?stgid=$stg_id&stg_kzbz=$stg_kzbz_lo&sem=$row_sem->semester&ver=$ver_lo&grp=$row_grp->gruppe\">&nbsp;&nbsp;&nbsp;&nbsp;(Liste)</a><br>";
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
