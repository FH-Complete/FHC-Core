<?php
function db_query($conn, $sql_query)
{
	if (!$result=pg_query($conn, $sql_query))
		return pg_last_error($conn);
	else
		return '';
}

// Auth: Benutzer des Webportals
function get_uid()
{
	return strtolower(trim($_SERVER['REMOTE_USER']));
	// fuer Testzwecke
	//return 'weisss';
}

function crlf()
{
	// doing some DOS-CRLF magic...
	$crlf="\n";
	$client=getenv("HTTP_USER_AGENT");
	if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs))
	{
		$os = $regs[1];
		// this looks better under WinX
		if (eregi("Win",$os))
			$crlf="\r\n";
	}
	return $crlf;
}

function check_lektor($uid, $conn)
{
	// uid von View 'Lektor' holen
	$sql_query="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$uid'";
	//echo $sql_query;
	$result=pg_query($conn, $sql_query) or die(pg_last_error($conn));
	$num_rows=pg_num_rows($result);
	// Wenn kein ergebnis return 0 sonst ID
	if ($num_rows>0)
	{
		$row=pg_fetch_object($result);
		return $row->mitarbeiter_uid;
	}
	else
		return 0;
}

function check_student($uid, $conn)
{
	// uid von Tabelle 'Student' holen
	$sql_query="SELECT student_uid FROM public.tbl_student WHERE student_uid='$uid'";
	//echo $sql_query;
	$result=pg_query($conn, $sql_query) or die(pg_last_error($conn));
	$num_rows=pg_numrows($result);
	// Wenn kein ergebnis return 0 sonst ID
	if ($num_rows>0)
		return pg_result($result,0,'student_uid');
	else
		return 0;
}

function kalenderwoche($datum)
{
	//$woche=date("W",mktime($date[hours],$date[minutes],$date[seconds],$date[mon],$date[mday],$date[year]));
	if (!date("w",$datum))
		$datum+=86400;
	//echo date("l j.m.Y - W",$datum);
	$woche=date("W",$datum);
	//if ($woche==53)
	//	$woche=1;
	return $woche;
}

/******************************************************************************
 * Springt zum vorhergehenden Montag, wenn $datum kein Sonntag oder Montag ist.
 *
 */
function montag($datum)
{
	// Wochentag
	$wt=date("w",$datum);
	// Sonntag?
	if (!$wt)
		$wt++;
	if($wt!=1)
		$datum-=86400*($wt-1);

	return $datum;
}

function jump_day($datum, $tage)
{
	// Ein Tag sind 86400 Sekunden
	$datum+=86400*$tage;
	return $datum;
}

function jump_week($datum, $wochen)
{
	$stunde_vor=date("G",$datum);
	// Eine Woche sind 604800 Sekunden
	$datum+=604800*$wochen;
	$stunde_nach=date("G",$datum);
	if ($stunde_nach!=$stunde_vor)
		$datum+=3600;
	return $datum;
}

function jahreskalenderjump($link)
{
	$crlf=crlf();
	$datum=mktime();
	$woche=kalenderwoche($datum);
	$datum=montag($datum);
	echo '			<SMALL><CENTER><B>Jump to KW</B><BR><SMALL>'.$crlf;
	for ($anz=1;$anz<26;$anz++)
	{
		$linknew=$link.'&datum='.$datum;
		if ($woche==53)
			$woche=1;
		echo '			<A HREF="'.$linknew.'">'.$woche.'</A>'.$crlf;
		if ($anz%5==0)
			echo '			<br>'.$crlf;
		$datum+=60*60*24*7;
		$woche++;
	}
	echo '			</SMALL></CENTER></SMALL>'.$crlf;
}

function loadVariables($conn, $user)
{
	$error_msg='';
	$num_rows=0;
	$sql_query="SELECT * FROM public.tbl_variable WHERE uid='$user'";
	if(!($result=pg_query($conn, $sql_query)))
		$error_msg.=pg_errormessage($conn).'<BR>'.$sql_query;
	else
		$num_rows=pg_numrows($result);

	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result,$i);
		global ${$row->name};
		${$row->name}=$row->wert;
	}
	if (!isset($semester_aktuell))
		if(!($result=pg_query($conn, 'SELECT * FROM public.tbl_studiensemester WHERE ende>now() ORDER BY start LIMIT 1')))
			$error_msg.=pg_errormessage($conn).'<BR>'.$sql_query;
		else
		{
			$num_rows=pg_numrows($result);
			if ($num_rows>0)
			{
				$row=pg_fetch_object($result,$i);
				global $semester_aktuell;
				$semester_aktuell=$row->studiensemester_kurzbz;
			}
		}
	if (!isset($db_stpl_table))
		$db_stpl_table='stundenplan';

	return $error_msg;
}

function writeCISlog($stat, $rm = '')
{
	if($stat=='STOP')
		$stat = 'STOP ';
	$handle = fopen(LOG_PATH.'cis.log','a');
	fwrite($handle, date('Y-m-d H:i:s').' '. $stat .' '. getmypid() .' '. $_SERVER['REMOTE_USER'] .' '. $_SERVER['REQUEST_URI'] .' '.$rm.'
');
}

// ***************************************************************
// * Liefert das Studiensemester in dem sich
// * das uebergebene Datum befindet
// * wenn sich das Datum zwischen zwei Studiensemestern befindet
// * und $naechstes=true dann wird das naechste StSem geliefert
// * wenn $naechstes=false dann wird das vorherige StSem geliefert
// ***************************************************************
function getStudiensemesterFromDatum($conn, $datum, $naechstes=true)
{
	$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE";

	if($naechstes)
		$qry.= " ende>'$datum' ORDER BY ende ASC ";
	else
		$qry.= " start<'$datum' ORDER BY ende DESC ";

	$qry.= "LIMIT 1";

	if($result = pg_query($conn, $qry))
	{
		if($row = pg_fetch_object($result))
			return $row->studiensemester_kurzbz;
		else
			return false;
	}
	else
		return false;
}
?>
