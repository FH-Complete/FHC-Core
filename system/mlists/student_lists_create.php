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
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</HEAD>

<BODY class="background_main">
<H3>MailingListen </H3>


<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$row=pg_fetch_object($erg, $i);
     	$stg_kz=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT DISTINCT semester FROM public.tbl_student WHERE studiengang_kz=$stg_kz AND student_uid NOT LIKE '\\\\_%' AND semester<10 AND semester>0 ORDER BY semester";
		//echo $sql_query;
		if(!($result_sem=pg_query($conn, $sql_query)))
			die(pg_errormessage($conn));
		$nr_sem=pg_num_rows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=pg_fetch_object($result_sem, $j);
			echo $stg_kzbz.'-'.$row_sem->semester.'<br>';

			/* // Semestergruppen falls vorhanden
			$sem=$row_sem->semester;
			$sql_query='SELECT uid, nachname, vorname FROM campus.vw_student WHERE studiengang_kz='.$stg_kz.' AND semester='.$sem." AND (verband IS NULL OR verband='') AND (gruppe='' OR gruppe IS NULL) AND uid NOT LIKE '\\\\_%' ORDER BY nachname;";
			//echo $sql_query;
			if(!($result_student=pg_query($conn, $sql_query)))
				die(pg_errormessage($conn));
			// File Operations
			if (pg_num_rows($result_student)>0)
			{
				$name=$stg_kzbz.$sem.'.txt';
				$name=strtolower($name);
				$fp=fopen('../../../mlists/student/'.$name,"w");
				$crlf="\n";
				while  ($row=pg_fetch_object($result_student))
					fwrite($fp, '#'.$row->nachname.' '.$row->vorname.$crlf.$row->uid.$crlf);
				fclose($fp);
				echo $name.', ';
				flush();
			}*/

			$sql_query="SELECT DISTINCT verband FROM public.tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester AND student_uid NOT LIKE '\\\\_%' ORDER BY verband";
			//echo $sql_query;
			if(!($result_ver=pg_query($conn, $sql_query)))
				die(pg_errormessage($conn));
			$nr_ver=pg_num_rows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver=pg_fetch_object($result_ver, $k);

				/* // Verbandsgruppen falls vorhanden
				$sem=$row_sem->semester;
				$ver=$row_ver->verband;
				$sql_query='SELECT uid, nachname, vorname FROM campus.vw_student WHERE studiengang_kz='.$stg_kz.' AND semester='.$sem.' AND verband=\''.strtoupper($ver)."' AND (gruppe='' OR gruppe IS NULL) AND uid NOT LIKE '\\\\_%' ORDER BY nachname;";
				//echo $sql_query;
				if(!($result_student=pg_query($conn, $sql_query)))
					die(pg_errormessage($conn));
				// File Operations
				if (pg_num_rows($result_student)>0)
				{
					$name=$stg_kzbz.$sem.$ver.'.txt';
					$name=strtolower($name);
					$fp=fopen('../../../mlists/student/'.$name,"w");
					$crlf="\n";
					while  ($row=pg_fetch_object($result_student))
						fwrite($fp, '#'.$row->nachname.' '.$row->vorname.$crlf.$row->uid.$crlf);
					fclose($fp);
					echo $name.', ';
					flush();
				}*/

				if ( ($row_ver->verband==' ' || $row_ver->verband=='') )
					$row_ver->verband='A';
				// if (strtoupper($row_ver->verband)=='A')
					$sql_query="SELECT DISTINCT gruppe FROM public.tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester AND (verband='$row_ver->verband' OR verband='' OR verband=' ') AND student_uid NOT LIKE '\\\\_%' ORDER BY gruppe";
				//else
				//	$sql_query="SELECT DISTINCT gruppe FROM public.tbl_student WHERE gruppe!='' AND gruppe IS NOT NULL AND studiengang_kz=$stg_kz AND semester=$row_sem->semester AND verband='$row_ver->verband' AND student_uid NOT LIKE '\\\\_%' ORDER BY gruppe";
				//echo $sql_query;
				if(!($result_grp=pg_query($conn, $sql_query)))
					die(pg_errormessage($conn));
				$nr_grp=pg_num_rows($result_grp);
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp=pg_fetch_object($result_grp, $l);
					$stgid=$stg_kz;
					$sem=$row_sem->semester;
					$ver=$row_ver->verband;
					$grp=$row_grp->gruppe;
					//echo '<BR>-'.$ver.'-<BR>';
					if ($grp=='' || $grp==' ' || is_null($grp))
						$grp='1';
					if ($grp=='1')
						$sql_query='SELECT uid, nachname, vorname FROM campus.vw_student WHERE studiengang_kz='.$stgid.' AND semester='.$sem." AND (verband='".strtoupper($ver)."' OR verband='' OR verband=' ') AND (gruppe='".$grp."' OR gruppe='' OR gruppe=' ') AND uid NOT LIKE '\\\\_%' ORDER BY nachname";
					else
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

	// ---------- Eine Datei mit allen Studentent anlegen -------------------
	$sql_query="SELECT studiengang_kz, bezeichnung, lower(typ::varchar(1) || kurzbz) as kurzbz,uid, nachname, vorname,
				semester, lower(verband) AS verband, gruppe FROM campus.vw_student JOIN tbl_studiengang USING (studiengang_kz)
				WHERE uid NOT LIKE '\\\\_%' AND semester<10 AND semester>0 AND vw_student.aktiv AND (substring(uid from 1 for 1)<'0' OR substring(uid from 1 for 1)>'9')";
	echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
		die(pg_errormessage($conn));
	// File Operations
	$name='student_lehrverband.txt';
	$fp=fopen('../../../mlists/student/'.$name,"w");
	$crlf="\n";
	// Wunsch von Kopper: <Studiengang>^t<gruppe>^t<uid>^t#<Nachname><space><Vorname>
	while ($row=pg_fetch_object($result))
		fwrite($fp, $row->kurzbz."\t".$row->semester.$row->verband.$row->gruppe."\t".$row->uid."\t#".$row->nachname.' '.$row->vorname.$crlf);
	fclose($fp);
	echo $name.', ';
	flush();
?>
Finished!!! <BR>
<A href="index.html" class="linkblue">&lt;&lt;Zur&uuml;ck</A>
</BODY>
</HTML>