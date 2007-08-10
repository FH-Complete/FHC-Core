<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../../vilesci/config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if(!($erg=pg_query($conn, "SELECT studiengang_kz, bezeichnung, lower(typ::varchar(1) || kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz ASC")))
		die(pg_errormessage($conn));
	$num_rows=pg_num_rows($erg);
?>
<HTML>
<HEAD>
<TITLE>Mailinglisten</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</HEAD>

<BODY class="background_main">
<H3>MailingListen </H3>


<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$row=pg_fetch_object($erg, $i);
     	$stg_id=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT DISTINCT semester FROM public.tbl_student WHERE studiengang_kz=$stg_id AND student_uid NOT LIKE '\\\\_%' AND semester<10 AND semester>0 ORDER BY semester";
		//echo $sql_query;
		if(!($result_sem=pg_query($conn, $sql_query)))
			die(pg_errormessage($conn));
		$nr_sem=pg_num_rows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=pg_fetch_object($result_sem, $j);
			echo $stg_kzbz.'-'.$row_sem->semester.'<br>';

			$sql_query="SELECT DISTINCT verband FROM public.tbl_student WHERE studiengang_kz=$stg_id AND semester=$row_sem->semester AND student_uid NOT LIKE '\\\\_%' ORDER BY verband";
			//echo $sql_query;
			if(!($result_ver=pg_query($conn, $sql_query)))
				die(pg_errormessage($conn));
			$nr_ver=pg_num_rows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver=pg_fetch_object($result_ver, $k);
				$sql_query="SELECT DISTINCT gruppe FROM public.tbl_student WHERE gruppe!='' AND gruppe IS NOT NULL AND studiengang_kz=$stg_id AND semester=$row_sem->semester AND verband='$row_ver->verband' AND student_uid NOT LIKE '\\\\_%' ORDER BY gruppe";
				//echo $sql_query;
				if(!($result_grp=pg_query($conn, $sql_query)))
					die(pg_errormessage($conn));
				$nr_grp=pg_num_rows($result_grp);
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp=pg_fetch_object($result_grp, $l);
					$stgid=$stg_id;
					$sem=$row_sem->semester;
					$ver=$row_ver->verband;
					$grp=$row_grp->gruppe;
					$sql_query='SELECT uid, nachname, vorname FROM campus.vw_student WHERE studiengang_kz='.$stgid.' AND semester='.$sem.' AND verband=\''.strtoupper($ver).'\' AND gruppe='.$grp." AND uid NOT LIKE '\\\\_%' ORDER BY nachname";
					//echo $sql_query;
					if(!($result_student=pg_query($conn, $sql_query)))
						die(pg_errormessage($conn));
					// File Operations
					$name=$stg_kzbz.$sem.$ver.$grp.'.txt';
					$name=strtolower($name);
					$fp=fopen('../../../mlists/student/'.$name,"w");
					//$fp=fopen('../../../../mlists/student/'.$name,"w");
					$crlf="\n";

					$numrows_student=pg_num_rows($result_student);
					for  ($s=0; $s<$numrows_student; $s++)
					{
						$row=pg_fetch_object($result_student, $s);
						fwrite($fp, '#'.$row->nachname.' '.$row->vorname.$crlf.$row->uid.$crlf);
					}
					fclose($fp);
					echo $name.', ';
					flush();
				}
				echo 'created<br>';
			}
		}
	}
?>
Finished!!! <BR>
<A href="index.html" class="linkblue">&lt;&lt;Zur&uuml;ck</A>
</BODY>
</HTML>