<?php
/**
 * Changes:	23.10.2004: Anpassung an neues DB-Schema (WM)
 */
	include('../../config.inc.php');
	include('../../../include/functions.inc.php');
	if (!$conn = @pg_pconnect($conn_string))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if(!($result=pg_exec($conn, "SELECT p.uid, nachname, vornamen FROM tbl_person as p join tbl_mitarbeiter using(uid) WHERE tbl_mitarbeiter.lektor=true AND p.uid NOT LIKE '\\\\_%' ORDER BY nachname")))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($result);
?>
<HTML>
<HEAD>
<TITLE>Mailinglisten</TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</HEAD>

<BODY class="background_main">
<H3>MailingListen </H3>


<?php
// File Operations
$name='tw_lkt.txt';
$name=strtolower($name);
$fp=fopen('../../../../mlists/'.$name,"w");
$crlf="\n";
for ($i=0; $i<$num_rows; $i++)
{
	$row=pg_fetch_object($result, $i);
	fwrite($fp, '#'.$row->nachname.' '.$row->vornamen.$crlf.$row->uid.$crlf);
}
fclose($fp);
echo $name.' created<br>';

?>
<P><BR>
  Die Mailinglisten der Lektoren wurden erstellt. <BR>
  Sie k&ouml;nnen nun die erstellten Datein auf den Mail-Server kopieren (<A href="mlists_copy.php">Copy
  Lists</A>).</P>
<P><A href="index.html" class="linkblue">&lt;&lt; Zur&uuml;ck</A></P>
</BODY>
</HTML>