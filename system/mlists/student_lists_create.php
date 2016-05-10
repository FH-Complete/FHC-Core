<?php
/* Copyright (C) 2008 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
/**
 * Generiert die Listen fuer die Mailverteiler der Studenten
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiensemester.class.php');
	
	$db = new basis_db();
	if(!($erg=$db->db_query("SELECT studiengang_kz, bezeichnung, lower(typ::varchar(1) || kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz ASC")))
		die($db->db_last_error());
	$num_rows=$db->db_num_rows($erg);
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

 $stsem = new studiensemester();
 $studiensemester_kurzbz = $stsem->getaktorNext();

	for ($i=0; $i<$num_rows; $i++)
	{
		$row=$db->db_fetch_object($erg, $i);
		$stg_kz=$row->studiengang_kz;
		$stg_kzbz=$row->kurzbz;
		$sql_query="SELECT DISTINCT semester FROM public.tbl_studentlehrverband WHERE studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER)." AND semester<10 AND semester>0 ORDER BY semester";

		if(!($result_sem = $db->db_query($sql_query)))
			die($db->db_last_error());
		$nr_sem=$db->db_num_rows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=$db->db_fetch_object($result_sem, $j);
			echo $stg_kzbz.'-'.$row_sem->semester.'<br>';

			$sql_query="SELECT DISTINCT verband FROM public.tbl_studentlehrverband WHERE studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER)." AND semester=".$db->db_add_param($row_sem->semester)." ORDER BY verband";
			
			if(!($result_ver = $db->db_query($sql_query)))
				die($db->db_last_error());
			$nr_ver=$db->db_num_rows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver = $db->db_fetch_object($result_ver, $k);

				if ( ($row_ver->verband==' ' || $row_ver->verband=='') )
					$row_ver->verband='A';

				$sql_query="SELECT DISTINCT gruppe FROM public.tbl_studentlehrverband WHERE studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER)." AND semester=$row_sem->semester AND (verband='$row_ver->verband' OR verband='' OR verband=' ') ORDER BY gruppe";

				if(!($result_grp = $db->db_query($sql_query)))
					die($db->db_last_error());
				$nr_grp = $db->db_num_rows($result_grp);
				
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp = $db->db_fetch_object($result_grp, $l);
					$stgid=$stg_kz;
					$sem=$row_sem->semester;
					$ver=$row_ver->verband;
					$grp=$row_grp->gruppe;
					
					if ($grp=='' || $grp==' ' || is_null($grp))
						$grp='1';
					if ($grp=='1')
						$sql_query='SELECT uid, nachname, vorname FROM campus.vw_student WHERE aktiv AND studiengang_kz='.$stgid.' AND semester='.$sem." AND (verband='".strtoupper($ver)."' OR verband='' OR verband=' ') AND (gruppe='".$grp."' OR gruppe='' OR gruppe=' ') AND uid NOT LIKE '\\\\_%' ORDER BY nachname";
					else
						$sql_query='SELECT uid, nachname, vorname FROM campus.vw_student WHERE aktiv AND studiengang_kz='.$stgid.' AND semester='.$sem.' AND verband=\''.strtoupper($ver).'\' AND gruppe=\''.$grp."' AND uid NOT LIKE '\\\\_%' ORDER BY nachname";
					
					if(!($result_student = $db->db_query($sql_query)))
						die($db->db_last_error());
					// File Operations
					$name=$stg_kzbz.$sem.$ver.$grp.'.txt';
					$name=mb_strtolower($name);
					$fp=fopen('../../../mlists/student/'.$name,"w");
					
					$crlf="\n";

					$numrows_student = $db->db_num_rows($result_student);
					for  ($s=0; $s<$numrows_student; $s++)
					{
						$row = $db->db_fetch_object($result_student, $s);
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
	
	if(!($result = $db->db_query($sql_query)))
		die($db->db_last_error());
	// File Operations
	$name='student_lehrverband.txt';
	$fp=fopen('../../../mlists/student/'.$name,"w");
	$crlf="\n";
	// Wunsch von Kopper: <Studiengang>^t<gruppe>^t<uid>^t#<Nachname><space><Vorname>
	while ($row = $db->db_fetch_object($result))
		fwrite($fp, $row->kurzbz."\t".$row->semester.$row->verband.$row->gruppe."\t".$row->uid."\t#".$row->nachname.' '.$row->vorname.$crlf);
	fclose($fp);
	echo $name.', ';
	flush();
?>
Finished!!! <BR>
<A href="index.html" class="linkblue">&lt;&lt;Zur&uuml;ck</A>
</BODY>
</HTML>
