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
	require_once('../config.inc.php');
	//include('../include/functions.inc.php');
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	if(isset($stgid))
	$sql_query='SELECT uid, nachname, vorname FROM campus.vw_student '.
	           'WHERE studiengang_kz='.$stgid.' AND semester='.$sem.
               ' AND verband=\''.strtoupper($ver).'\' AND gruppe='.$grp.
               ' ORDER BY nachname';
	if (isset($einheitid))
		$sql_query='SELECT uid, nachname, vorname FROM campus.vw_benutzer JOIN tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz=\''.$einheitid.'\' ORDER BY nachname';
	//echo $sql_query;
	if(!($result=pg_query($conn, $sql_query)))
		die(pg_errormessage($conn));

	$anz=pg_num_rows($result);
	for  ($j=0; $j<$anz; $j++)
	{
		$row=pg_fetch_object($result, $j);
		echo '#'.$row->nachname.' '.$row->vorname.$crlf.$row->uid.$crlf;
	}
?>