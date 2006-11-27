<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	if (!isset($einheitid))
		$name=$stg_kzbz.$sem.$ver.$grp.'.txt';
	else
		$name='modul_id'.$einheitid.'.txt';
	header("Content-disposition: filename=$name");
	header("Content-type: application/octetstream");
	header("Pragma: no-cache");
	header("Expires: 0");
	// doing some DOS-CRLF magic...
	$crlf="\n";
	$client=getenv("HTTP_USER_AGENT");
	if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs))
	{
		$os = $regs[1];
		// this looks better under WinX
		if (eregi("Win",$os)) $crlf="\r\n";
	}
	include('../config.inc.php');
	//include('../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	$sql_query='SELECT uid, nachname, vornamen FROM tbl_person as p join tbl_student using(uid) '.
	           'WHERE studiengang_kz='.$stgid.' AND semester='.$sem.
               ' AND verband=\''.strtoupper($ver).'\' AND gruppe='.$grp.
               ' ORDER BY nachname';
	if (isset($einheitid))
		$sql_query='SELECT tbl_student.uid, p.nachname, p.vornamen FROM tbl_person as p join tbl_student using(uid),tbl_einheitstudent WHERE tbl_einheitstudent.einheit_kurzbz=\''.$einheitid.'\' AND tbl_einheitstudent.uid=tbl_student.uid ORDER BY nachname';
	//echo $sql_query;
	if(!($result=pg_exec($conn, $sql_query)))
		die(pg_errormessage($conn));

	$anz=pg_numrows($result);
	for  ($j=0; $j<$anz; $j++)
	{
		$row=pg_fetch_object($result, $j);
		echo '#'.$row->nachname.' '.$row->vornamen.$crlf.$row->uid.$crlf;
	}
?>