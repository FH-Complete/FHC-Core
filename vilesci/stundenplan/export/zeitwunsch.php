<?php
	header("Content-disposition: filename=zeitwuensche_lektoren.txt");
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
	include('../../config.inc.php');
	include('../../../include/functions.inc.php');
	if (!$conn = @pg_pconnect($conn_string))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	$sql_query="SELECT lektor.kurzbz, zeitwunsch.lektor_id, zeitwunsch.tag, zeitwunsch.stunde_id, zeitwunsch.gewicht FROM zeitwunsch, lektor WHERE zeitwunsch.lektor_id=lektor.id ORDER BY lektor.kurzbz";
	//echo $sql_query;
	if(!($result=pg_exec($conn, $sql_query)))
		die(pg_errormessage($conn));
	$anz=pg_numrows($result);
	for  ($j=0; $j<$anz; $j++)
	{
		$row=pg_fetch_object($result, $j);
		echo '"L","'.$row->kurzbz.'",'.$row->tag.','.$row->stunde_id.','.$row->gewicht.$crlf;
	}
?>